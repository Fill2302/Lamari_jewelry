<?php

namespace Tests\Feature;

use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\PaymentCallbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    private function payment(): Payment
    {
        $e = LegalEntity::create(['name' => 'FOP']);
        $m = MerchantAccount::create(['legal_entity_id' => $e->id, 'provider' => 'fake', 'code' => 'fake', 'is_default' => true]);
        $o = Order::create(['number' => 'LAM-TEST', 'merchant_account_id' => $m->id, 'legal_entity_id' => $e->id, 'email' => 'a@b.test', 'phone' => '1', 'customer_name' => 'Test', 'shipping_address' => [], 'subtotal_amount' => 10000, 'total_amount' => 10000]);

        return Payment::create(['order_id' => $o->id, 'merchant_account_id' => $m->id, 'legal_entity_id' => $e->id, 'provider' => 'fake', 'provider_payment_id' => 'fake_1', 'amount' => 10000, 'idempotency_key' => (string) Str::uuid()]);
    }

    public function test_duplicate_callback_is_idempotent(): void
    {
        $payment = $this->payment();
        $raw = json_encode(['event_id' => 'evt-1', 'payment_id' => 'fake_1', 'status' => 'paid']);
        $sig = hash_hmac('sha256', $raw, (string) config('services.payments.fake_secret'));
        $service = $this->app->make(PaymentCallbackService::class);
        $this->assertTrue($service->handle($raw, $sig));
        $this->assertFalse($service->handle($raw, $sig));
        $this->assertSame(1, WebhookEvent::count());
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('confirmed', $payment->order->fresh()->status);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $this->payment();
        $this->expectException(\RuntimeException::class);
        $this->app->make(PaymentCallbackService::class)->handle('{}', 'invalid');
    }
}
