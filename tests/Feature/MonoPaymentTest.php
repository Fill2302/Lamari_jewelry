<?php

namespace Tests\Feature;

use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\MonoPaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class MonoPaymentTest extends TestCase
{
    use RefreshDatabase;

    private string $privateKey;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payments.default' => 'mono',
            'services.payments.mono_token' => 'test-token',
            'services.payments.mono_base_url' => 'https://api.monobank.ua',
        ]);

        $resource = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        openssl_pkey_export($resource, $privateKey);
        $this->privateKey = $privateKey;
        $publicKey = openssl_pkey_get_details($resource)['key'];
        config(['services.payments.mono_public_key' => base64_encode($publicKey)]);
    }

    public function test_creates_mono_invoice_with_server_side_amount_and_callback_urls(): void
    {
        Http::fake([
            'api.monobank.ua/api/merchant/invoice/create' => Http::response([
                'invoiceId' => 'p2_test_invoice',
                'pageUrl' => 'https://pay.mbnk.biz/p2_test_invoice',
            ]),
        ]);
        $payment = $this->payment();
        $payment->order->items()->create([
            'sku' => 'SKU-1',
            'name' => 'Кольє — 45 см',
            'quantity' => 1,
            'unit_price_amount' => 145000,
            'total_amount' => 145000,
        ]);

        $result = (new MonoPaymentProvider)->createPayment($payment);

        $this->assertSame('p2_test_invoice', $payment->fresh()->provider_payment_id);
        $this->assertSame('https://pay.mbnk.biz/p2_test_invoice', $result['checkout_url']);
        Http::assertSent(function ($request) use ($payment): bool {
            return $request->url() === 'https://api.monobank.ua/api/merchant/invoice/create'
                && $request->header('X-Token')[0] === 'test-token'
                && $request['amount'] === 145000
                && $request['ccy'] === 980
                && $request['merchantPaymInfo']['reference'] === $payment->idempotency_key
                && str_ends_with($request['webHookUrl'], '/payments/mono/webhook');
        });
    }

    public function test_signed_success_webhook_confirms_order_and_is_idempotent(): void
    {
        config([
            'app.staging_protected' => true,
            'app.staging_username' => 'preview',
            'app.staging_password' => 'secret',
        ]);
        $payment = $this->payment(['provider_payment_id' => 'p2_paid']);
        $payload = [
            'invoiceId' => 'p2_paid',
            'status' => 'success',
            'amount' => 145000,
            'ccy' => 980,
            'reference' => $payment->idempotency_key,
            'modifiedDate' => '2026-08-04T12:00:00Z',
        ];
        [$raw, $signature] = $this->signed($payload);

        $this->postJson(route('payments.mono.webhook'), $payload, ['X-Sign' => $signature])
            ->assertOk()
            ->assertSee('processed');
        $this->postJson(route('payments.mono.webhook'), $payload, ['X-Sign' => $signature])
            ->assertOk()
            ->assertSee('ignored');

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $payment->order->fresh()->payment_status);
        $this->assertSame('confirmed', $payment->order->fresh()->status);
    }

    public function test_webhook_rejects_invalid_signature_and_wrong_amount(): void
    {
        $payment = $this->payment(['provider_payment_id' => 'p2_guarded']);
        $payload = [
            'invoiceId' => 'p2_guarded',
            'status' => 'success',
            'amount' => 1,
            'ccy' => 980,
            'reference' => $payment->idempotency_key,
            'modifiedDate' => '2026-08-04T12:00:01Z',
        ];

        $this->postJson(route('payments.mono.webhook'), $payload, ['X-Sign' => 'invalid'])
            ->assertServerError();

        [, $signature] = $this->signed($payload);
        $this->postJson(route('payments.mono.webhook'), $payload, ['X-Sign' => $signature])
            ->assertServerError();
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_late_processing_webhook_cannot_downgrade_paid_payment(): void
    {
        $payment = $this->payment(['provider_payment_id' => 'p2_late', 'status' => 'paid']);
        $payload = [
            'invoiceId' => 'p2_late',
            'status' => 'processing',
            'amount' => 145000,
            'ccy' => 980,
            'reference' => $payment->idempotency_key,
            'modifiedDate' => '2026-08-04T11:59:59Z',
        ];
        [, $signature] = $this->signed($payload);

        $this->postJson(route('payments.mono.webhook'), $payload, ['X-Sign' => $signature])
            ->assertOk()
            ->assertSee('ignored');
        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_provider_refuses_to_create_invoice_without_token(): void
    {
        config(['services.payments.mono_token' => null]);
        $this->expectException(RuntimeException::class);
        (new MonoPaymentProvider)->createPayment($this->payment());
    }

    private function payment(array $overrides = []): Payment
    {
        $entity = LegalEntity::create(['name' => 'Test FOP']);
        $merchant = MerchantAccount::create([
            'legal_entity_id' => $entity->id,
            'provider' => 'mono',
            'code' => 'mono-test',
            'is_default' => true,
        ]);
        $order = Order::create([
            'number' => 'LAM-MONO-TEST',
            'merchant_account_id' => $merchant->id,
            'legal_entity_id' => $entity->id,
            'email' => 'buyer@example.test',
            'phone' => '+380000000000',
            'customer_name' => 'Test Buyer',
            'shipping_address' => [],
            'subtotal_amount' => 145000,
            'total_amount' => 145000,
        ]);

        return Payment::create(array_merge([
            'order_id' => $order->id,
            'merchant_account_id' => $merchant->id,
            'legal_entity_id' => $entity->id,
            'provider' => 'mono',
            'amount' => 145000,
            'currency' => 'UAH',
            'idempotency_key' => (string) Str::uuid(),
        ], $overrides));
    }

    private function signed(array $payload): array
    {
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES);
        openssl_sign($raw, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return [$raw, base64_encode($signature)];
    }
}
