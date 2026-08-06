<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\IntegrationCredential;
use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Payments\MonoPaymentProvider;
use App\Services\PaymentCallbackService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            'name' => 'Сережка — Тестова',
            'receipt_name' => 'Сережки',
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
                && $request['merchantPaymInfo']['destination'] === 'Сережки'
                && $request['merchantPaymInfo']['basketOrder'][0]['name'] === 'Сережки'
                && str_ends_with($request['webHookUrl'], '/payments/mono/webhook');
        });
    }

    public function test_fop3_invoice_uses_its_own_mono_merchant_token(): void
    {
        config(['services.payments.mono_tokens.privat' => 'fop-3-token']);
        Http::fake([
            'api.monobank.ua/api/merchant/invoice/create' => Http::response([
                'invoiceId' => 'p2_fop3_invoice',
                'pageUrl' => 'https://pay.mbnk.biz/p2_fop3_invoice',
            ]),
        ]);
        $payment = $this->payment();
        $payment->order->update(['payment_destination' => 'privat']);
        $payment->order->items()->create([
            'sku' => 'SKU-FOP3',
            'name' => 'Кольє — Тестове',
            'receipt_name' => 'Кольє',
            'quantity' => 1,
            'unit_price_amount' => 145000,
            'total_amount' => 145000,
        ]);

        (new MonoPaymentProvider)->createPayment($payment);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.monobank.ua/api/merchant/invoice/create'
            && $request->header('X-Token')[0] === 'fop-3-token'
        );
    }

    public function test_fop3_invoice_is_rejected_without_its_merchant_token(): void
    {
        config(['services.payments.mono_tokens.privat' => null]);
        $payment = $this->payment();
        $payment->order->update(['payment_destination' => 'privat']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mono merchant token for privat is not configured.');

        (new MonoPaymentProvider)->createPayment($payment);
    }

    public function test_checkout_uses_an_inertia_location_for_the_external_mono_page(): void
    {
        $entity = LegalEntity::create(['name' => 'Test FOP']);
        MerchantAccount::create(['legal_entity_id' => $entity->id, 'provider' => 'mono', 'code' => 'mono-checkout', 'is_default' => true]);
        $category = Category::create(['name' => 'Earrings', 'slug' => 'earrings']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Сережка', 'slug' => 'test-earring', 'description' => 'Test']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'TEST-1', 'name' => 'Тестова', 'price_amount' => 100, 'stock_on_hand' => 1]);
        IntegrationCredential::updateOrCreate(['provider' => 'nova_poshta'], ['api_key' => 'nova-secret', 'is_active' => true]);
        $cityRef = '11111111-1111-4111-8111-111111111111';
        $warehouseRef = '22222222-2222-4222-8222-222222222222';
        Http::fake(function ($request) use ($cityRef, $warehouseRef) {
            if ($request->url() === 'https://api.novaposhta.ua/v2.0/json/') {
                return Http::response(['success' => true, 'data' => [[
                    'Ref' => $warehouseRef,
                    'CityRef' => $cityRef,
                    'Description' => 'Відділення №1',
                ]]]);
            }

            if ($request->url() === 'https://api.monobank.ua/api/merchant/invoice/create') {
                return Http::response(['invoiceId' => 'p2_checkout', 'pageUrl' => 'https://pay.mbnk.biz/p2_checkout']);
            }

            return Http::response([], 404);
        });

        $response = $this->withSession(['cart' => [$variant->id => ['quantity' => 1]]])
            ->post(route('checkout.store'), [
                'first_name' => 'Філіп',
                'last_name' => 'Сокольський',
                'email' => 'buyer@example.test',
                'phone' => '+380958831985',
                'city' => 'Київ',
                'city_ref' => $cityRef,
                'warehouse' => 'Відділення №1',
                'warehouse_ref' => $warehouseRef,
                'payment_method' => 'online',
            ], ['X-Inertia' => 'true']);

        $response->assertStatus(409)
            ->assertHeader('X-Inertia-Location', 'https://pay.mbnk.biz/p2_checkout');
    }

    public function test_signed_success_webhook_confirms_order_and_is_idempotent(): void
    {
        Http::preventStrayRequests();
        $this->withMiddleware(PreventRequestForgery::class);
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

    public function test_salesdrive_failure_cannot_roll_back_a_successful_mono_payment(): void
    {
        config([
            'services.salesdrive.enabled' => true,
            'services.salesdrive.orders_key' => 'orders-secret',
            'services.salesdrive.payments_key' => 'payments-secret',
        ]);
        Http::fake([
            'lamari.salesdrive.me/*' => Http::response(['status' => 'error'], 500),
        ]);
        $payment = $this->payment(['provider_payment_id' => 'p2_paid_salesdrive_error']);
        $payload = [
            'invoiceId' => 'p2_paid_salesdrive_error',
            'status' => 'success',
            'amount' => 145000,
            'ccy' => 980,
            'reference' => $payment->idempotency_key,
            'modifiedDate' => '2026-08-05T12:00:00Z',
        ];
        [$raw, $signature] = $this->signed($payload);

        $this->assertTrue(app(PaymentCallbackService::class)->handle($raw, $signature));

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $payment->order->fresh()->payment_status);
        $this->assertNotNull($payment->order->fresh()->salesdrive_sync_error);
        $this->assertDatabaseHas('webhook_events', [
            'provider' => 'mono',
            'status' => 'processed',
        ]);
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
