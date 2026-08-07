<?php

namespace Tests\Feature;

use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Services\SalesDriveSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class SalesDriveSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'services.salesdrive.enabled' => true,
            'services.salesdrive.base_url' => 'https://lamari.salesdrive.me',
            'services.salesdrive.orders_key' => 'orders-secret',
            'services.salesdrive.payments_key' => 'payments-secret',
            'services.salesdrive.pending_status' => 'Оплата',
            'services.salesdrive.paid_status' => 'Підтверджено',
            'services.salesdrive.payment_method' => 'Оплата карткою на сайті',
            'services.salesdrive.delivery_method' => 'Нова Пошта',
            'services.salesdrive.organization_id' => 5,
            'services.salesdrive.account_number' => 'monobank-test-account',
        ]);
    }

    public function test_creates_pending_order_with_customer_product_delivery_and_attribution(): void
    {
        $this->fakeSalesDrive();
        [$order] = $this->records();

        app(SalesDriveSyncService::class)->syncPending($order);

        $this->assertSame(321, $order->fresh()->salesdrive_order_id);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://lamari.salesdrive.me/handler/'
            && $request->header('X-Api-Key')[0] === 'orders-secret'
            && $request['externalId'] === 'LAM-SD-TEST'
            && $request['sajt'] === 'test.lamari.jewelry'
            && $request['products'][0]['id'] === 'TEST-MONO-1UAH'
            && $request['products'][0]['costPerItem'] === 1
            && $request['utmSource'] === 'instagram');
        Http::assertSent(fn ($request): bool => $request->url() === 'https://lamari.salesdrive.me/api/order/update/'
            && $request['id'] === 321
            && $request['data']['statusId'] === 11);
    }

    public function test_sends_base_sku_to_salesdrive_without_changing_internal_variant_sku(): void
    {
        $this->fakeSalesDrive();
        [$order] = $this->records();
        $item = $order->items()->firstOrFail();
        $item->update(['sku' => 'K423-43', 'name' => 'Кольє — 43 см']);

        app(SalesDriveSyncService::class)->syncPending($order->fresh());

        $this->assertSame('K423-43', $item->fresh()->sku);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://lamari.salesdrive.me/handler/'
            && $request['products'][0]['id'] === 'K423'
            && $request['products'][0]['sku'] === 'K423'
            && $request['products'][0]['name'] === 'Кольє — 43 см');
    }

    public function test_paid_sync_updates_same_order_adds_one_idempotent_payment(): void
    {
        $this->fakeSalesDrive();
        [$order, $payment] = $this->records();
        $service = app(SalesDriveSyncService::class);

        $service->syncPaid($payment);
        $service->syncPaid($payment->fresh());

        $this->assertSame(321, $order->fresh()->salesdrive_order_id);
        $this->assertSame(654, $payment->fresh()->salesdrive_payment_id);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://lamari.salesdrive.me/api/payment/'
            && $request->header('X-Api-Key')[0] === 'payments-secret'
            && $request['organizationId'] === 5
            && $request['accountNumber'] === 'monobank-test-account'
            && $request['orderId'] === 321
            && $request['orderExternalId'] === 'LAM-SD-TEST'
            && $request['sum'] === 1
            && str_starts_with($request['uniqueId'], 'lamari-mono-'));
        Http::assertSentCount(7);
    }

    public function test_routes_each_merchant_to_its_own_salesdrive_account_and_key(): void
    {
        config(['services.salesdrive.payment_accounts' => [
            'salesdrive-fop2-test' => ['organization_id' => 2, 'account_number' => 'fop-popova', 'payments_key' => 'popova-secret'],
            'salesdrive-fop3-test' => ['organization_id' => 3, 'account_number' => 'fop-hrushchenko', 'payments_key' => 'hrushchenko-secret'],
        ]]);
        $paymentId = 700;
        Http::fake(function ($request) use (&$paymentId) {
            return match ($request->url()) {
                'https://lamari.salesdrive.me/api/statuses/' => Http::response(['success' => true, 'data' => [['id' => 12, 'name' => 'Підтверджено']]]),
                'https://lamari.salesdrive.me/api/order/update/' => Http::response(['success' => true]),
                'https://lamari.salesdrive.me/api/payment/' => Http::response(['success' => true, 'data' => ['paymentId' => $paymentId++]]),
                default => Http::response([], 404),
            };
        });
        [$popovaOrder, $popovaPayment] = $this->records('salesdrive-fop2-test', 'LAM-SD-POPOVA');
        [$hrushchenkoOrder, $hrushchenkoPayment] = $this->records('salesdrive-fop3-test', 'LAM-SD-HRUSHCHENKO');
        $popovaOrder->update(['salesdrive_order_id' => 401, 'salesdrive_created_at' => now()]);
        $hrushchenkoOrder->update(['salesdrive_order_id' => 402, 'salesdrive_created_at' => now()]);

        $service = app(SalesDriveSyncService::class);
        $service->syncPaid($popovaPayment);
        $service->syncPaid($hrushchenkoPayment);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://lamari.salesdrive.me/api/payment/'
            && $request->header('X-Api-Key')[0] === 'popova-secret'
            && $request['organizationId'] === 2
            && $request['accountNumber'] === 'fop-popova'
            && $request['orderId'] === 401);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://lamari.salesdrive.me/api/payment/'
            && $request->header('X-Api-Key')[0] === 'hrushchenko-secret'
            && $request['organizationId'] === 3
            && $request['accountNumber'] === 'fop-hrushchenko'
            && $request['orderId'] === 402);
    }

    public function test_does_not_fall_back_to_another_fop_when_merchant_mapping_is_missing(): void
    {
        config(['services.salesdrive.payment_accounts' => [
            'salesdrive-fop2-test' => ['organization_id' => 2, 'account_number' => 'fop-popova', 'payments_key' => 'popova-secret'],
        ]]);
        $this->fakeSalesDrive();
        [$order, $payment] = $this->records('salesdrive-unmapped-test', 'LAM-SD-UNMAPPED');
        $order->update(['salesdrive_order_id' => 403, 'salesdrive_created_at' => now()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SalesDrive payment account is not configured for merchant salesdrive-unmapped-test.');

        app(SalesDriveSyncService::class)->syncPaid($payment);
    }

    public function test_retry_after_status_failure_does_not_create_a_duplicate_order(): void
    {
        $updates = 0;
        $this->fakeSalesDrive(function () use (&$updates) {
            $updates++;

            return $updates === 1 ? Http::response([], 500) : Http::response(['success' => true]);
        });
        [$order] = $this->records();
        $service = app(SalesDriveSyncService::class);

        try {
            $service->syncPending($order);
        } catch (\Throwable) {
        }
        $this->assertSame(321, $order->fresh()->salesdrive_order_id);

        $service->syncPending($order->fresh());

        $this->assertSame(1, collect(Http::recorded())->filter(fn (array $pair): bool => $pair[0]->url() === 'https://lamari.salesdrive.me/handler/')->count());
        $this->assertNotNull($order->fresh()->salesdrive_created_at);
    }

    private function fakeSalesDrive(?callable $orderUpdate = null): void
    {
        Http::fake(function ($request) use ($orderUpdate) {
            return match ($request->url()) {
                'https://lamari.salesdrive.me/api/payment-methods/' => Http::response(['success' => true, 'data' => [['id' => 5, 'name' => 'Оплата карткою на сайті', 'parameter' => 'online']]]),
                'https://lamari.salesdrive.me/api/delivery-methods/' => Http::response(['success' => true, 'data' => [['id' => 7, 'name' => 'Нова Пошта', 'parameter' => 'novaposhta']]]),
                'https://lamari.salesdrive.me/api/statuses/' => Http::response(['success' => true, 'data' => [['id' => 11, 'name' => 'Оплата'], ['id' => 12, 'name' => 'Підтверджено']]]),
                'https://lamari.salesdrive.me/handler/' => Http::response(['success' => true, 'data' => ['orderId' => 321, 'userId' => 1]]),
                'https://lamari.salesdrive.me/api/order/update/' => $orderUpdate ? $orderUpdate() : Http::response(['success' => true]),
                'https://lamari.salesdrive.me/api/payment/' => Http::response(['success' => true, 'data' => ['paymentId' => 654]]),
                default => Http::response([], 404),
            };
        });
    }

    private function records(string $merchantCode = 'mono-sd-test', string $orderNumber = 'LAM-SD-TEST'): array
    {
        $entity = LegalEntity::create(['name' => 'Test FOP']);
        $merchant = MerchantAccount::create(['legal_entity_id' => $entity->id, 'provider' => 'mono', 'code' => $merchantCode, 'is_default' => true]);
        $order = Order::create([
            'number' => $orderNumber, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $entity->id,
            'email' => 'buyer@example.test', 'phone' => '+380000000000', 'customer_name' => 'Філіп Сокольський',
            'shipping_address' => ['city' => 'Київ', 'address' => 'Відділення №12'],
            'marketing_attribution' => ['last_touch' => ['utm_source' => 'instagram', 'landing_url' => 'https://test.lamari.jewelry/products/test']],
            'subtotal_amount' => 100, 'total_amount' => 100,
        ]);
        $order->items()->create(['sku' => 'TEST-MONO-1UAH', 'name' => 'Сережка', 'quantity' => 1, 'unit_price_amount' => 100, 'total_amount' => 100]);
        $payment = Payment::create([
            'order_id' => $order->id, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $entity->id,
            'provider' => 'mono', 'provider_payment_id' => 'mono-test-'.$merchantCode, 'amount' => 100, 'currency' => 'UAH',
            'idempotency_key' => (string) Str::uuid(),
        ]);

        return [$order, $payment];
    }
}
