<?php

namespace Tests\Feature;

use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\WayForPayPaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WayForPayPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('w', 32)),
            'services.payments.wayforpay_domain' => 'test.lamari.jewelry',
            'services.payments.wayforpay_merchants' => [
                'fop-2' => ['account' => 'merchant_fop_2', 'secret' => 'secret-fop-2'],
                'fop-3' => ['account' => 'merchant_fop_3', 'secret' => 'secret-fop-3'],
            ],
        ]);
    }

    public function test_builds_fixed_150_uah_purchase_for_selected_merchant(): void
    {
        $payment = $this->payment('fop-2');

        $result = app(WayForPayPaymentProvider::class)->createPayment($payment);
        $fields = $payment->fresh()->payload['checkout'];

        $this->assertSame('merchant_fop_2', $fields['merchantAccount']);
        $this->assertSame('test.lamari.jewelry', $fields['merchantDomainName']);
        $this->assertSame('150.00', $fields['amount']);
        $this->assertSame(['Передплата за замовлення №WFP-1'], $fields['productName']);
        $this->assertSame('https://test.lamari.jewelry/payments/wayforpay/return/'.$payment->id, $fields['returnUrl']);
        $this->assertSame('https://test.lamari.jewelry/payments/wayforpay/webhook', $fields['serviceUrl']);
        $this->assertSame($payment->idempotency_key, $payment->fresh()->provider_payment_id);
        $this->assertStringContainsString('/payments/wayforpay/', $result['checkout_url']);

        $base = implode(';', [
            'merchant_fop_2', 'test.lamari.jewelry', $payment->idempotency_key,
            $fields['orderDate'], '150.00', 'UAH', 'Передплата за замовлення №WFP-1', 1, '150.00',
        ]);
        $this->assertSame(hash_hmac('md5', $base, 'secret-fop-2'), $fields['merchantSignature']);
    }

    public function test_uses_each_environment_domain_for_return_and_callback_urls(): void
    {
        config(['services.payments.wayforpay_domain' => 'lamari.jewelry']);
        $payment = $this->payment('fop-2');

        app(WayForPayPaymentProvider::class)->createPayment($payment);
        $fields = $payment->fresh()->payload['checkout'];

        $this->assertSame('lamari.jewelry', $fields['merchantDomainName']);
        $this->assertSame('https://lamari.jewelry/payments/wayforpay/return/'.$payment->id, $fields['returnUrl']);
        $this->assertSame('https://lamari.jewelry/payments/wayforpay/webhook', $fields['serviceUrl']);
    }

    public function test_approved_callback_marks_only_deposit_paid_and_sets_cod_balance(): void
    {
        $payment = $this->payment('fop-3');
        app(WayForPayPaymentProvider::class)->createPayment($payment);
        $payload = [
            'merchantAccount' => 'merchant_fop_3',
            'orderReference' => $payment->idempotency_key,
            'amount' => 150,
            'currency' => 'UAH',
            'authCode' => 'AUTH1',
            'cardPan' => '42****4242',
            'transactionStatus' => 'Approved',
            'reasonCode' => 1100,
            'processingDate' => 1786104000,
        ];
        $payload['merchantSignature'] = hash_hmac('md5', implode(';', [
            'merchant_fop_3', $payment->idempotency_key, 150, 'UAH', 'AUTH1',
            '42****4242', 'Approved', 1100,
        ]), 'secret-fop-3');

        $this->postJson(route('payments.wayforpay.webhook'), $payload)
            ->assertOk()
            ->assertJson(['orderReference' => $payment->idempotency_key, 'status' => 'accept']);

        $order = $payment->order->fresh();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('deposit_paid', $order->payment_status);
        $this->assertSame(15000, $order->prepaid_amount);
        $this->assertSame(85000, $order->cod_amount);
        $this->assertSame(100000, $order->total_amount);
    }

    public function test_callback_rejects_signature_from_other_fop(): void
    {
        $payment = $this->payment('fop-2');
        app(WayForPayPaymentProvider::class)->createPayment($payment);
        $payload = [
            'merchantAccount' => 'merchant_fop_2',
            'orderReference' => $payment->idempotency_key,
            'amount' => 150,
            'currency' => 'UAH',
            'authCode' => '',
            'cardPan' => '',
            'transactionStatus' => 'Approved',
            'reasonCode' => 1100,
            'merchantSignature' => hash_hmac('md5', 'wrong', 'secret-fop-3'),
        ];

        $this->postJson(route('payments.wayforpay.webhook'), $payload)->assertServerError();
        $this->assertSame('pending', $payment->fresh()->status);
    }

    private function payment(string $merchantCode): Payment
    {
        $entity = LegalEntity::create(['name' => strtoupper($merchantCode)]);
        $merchant = MerchantAccount::create([
            'legal_entity_id' => $entity->id,
            'provider' => 'mono',
            'code' => $merchantCode,
            'is_default' => true,
        ]);
        $order = Order::create([
            'number' => 'WFP-1',
            'merchant_account_id' => $merchant->id,
            'legal_entity_id' => $entity->id,
            'first_name' => 'Філіп',
            'last_name' => 'Сокольський',
            'email' => 'buyer@example.test',
            'phone' => '+380958831985',
            'customer_name' => 'Філіп Сокольський',
            'shipping_address' => [],
            'payment_method' => 'wayforpay_deposit',
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'prepaid_amount' => 0,
            'cod_amount' => 85000,
        ]);

        return Payment::create([
            'order_id' => $order->id,
            'merchant_account_id' => $merchant->id,
            'legal_entity_id' => $entity->id,
            'provider' => 'wayforpay',
            'amount' => 15000,
            'currency' => 'UAH',
            'idempotency_key' => 'wfp-'.$merchantCode,
        ]);
    }
}
