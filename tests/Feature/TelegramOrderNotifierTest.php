<?php

namespace Tests\Feature;

use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TelegramOrderNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class TelegramOrderNotifierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.telegram_orders.enabled' => true,
            'services.telegram_orders.bot_token' => 'bot-secret',
            'services.telegram_orders.chat_id' => '-1003987764577',
            'services.telegram_orders.source' => 'test.lamari.jewelry',
        ]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
    }

    public function test_created_message_contains_full_order_details(): void
    {
        [$order] = $this->records();

        app(TelegramOrderNotifier::class)->notifyCreated($order);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.telegram.org/botbot-secret/sendMessage'
            && $request['chat_id'] === '-1003987764577'
            && str_contains($request['text'], 'LAM-TG-TEST')
            && str_contains($request['text'], 'Очікує оплати')
            && str_contains($request['text'], 'TEST-MONO-1UAH')
            && str_contains($request['text'], '1,00 грн')
            && str_contains($request['text'], 'Філіп &amp; Компанія')
            && str_contains($request['text'], 'Відділення №12'));
    }

    public function test_paid_message_references_same_order_and_amount(): void
    {
        [$order, $payment] = $this->records();

        app(TelegramOrderNotifier::class)->notifyPaid($payment);

        Http::assertSent(fn ($request): bool => str_contains($request['text'], 'Оплачено')
            && str_contains($request['text'], $order->number)
            && str_contains($request['text'], '1,00 грн'));
    }

    private function records(): array
    {
        $entity = LegalEntity::create(['name' => 'Test FOP']);
        $merchant = MerchantAccount::create(['legal_entity_id' => $entity->id, 'provider' => 'mono', 'code' => 'mono-tg-test', 'is_default' => true]);
        $order = Order::create([
            'number' => 'LAM-TG-TEST', 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $entity->id,
            'email' => 'buyer@example.test', 'phone' => '+380000000000', 'customer_name' => 'Філіп & Компанія',
            'shipping_address' => ['city' => 'Київ', 'address' => 'Відділення №12'],
            'payment_method' => 'online', 'payment_status' => 'pending',
            'subtotal_amount' => 100, 'total_amount' => 100,
        ]);
        $order->items()->create(['sku' => 'TEST-MONO-1UAH', 'name' => 'Сережка', 'quantity' => 1, 'unit_price_amount' => 100, 'total_amount' => 100]);
        $payment = Payment::create([
            'order_id' => $order->id, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $entity->id,
            'provider' => 'mono', 'amount' => 100, 'currency' => 'UAH', 'idempotency_key' => (string) Str::uuid(),
        ]);

        return [$order, $payment];
    }
}
