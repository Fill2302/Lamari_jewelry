<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\PaymentRoutingSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function variant(int $price = 189000, int $stock = 3): ProductVariant
    {
        $entity = LegalEntity::create(['name' => 'Test FOP']);
        MerchantAccount::create(['legal_entity_id' => $entity->id, 'provider' => 'fake', 'code' => 'default', 'is_default' => true]);
        $category = Category::create(['name' => 'Rings', 'slug' => 'rings']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Aurelia', 'slug' => 'aurelia', 'description' => 'Test']);

        return ProductVariant::create(['product_id' => $product->id, 'sku' => 'AUR-16', 'name' => '16', 'price_amount' => $price, 'stock_on_hand' => $stock]);
    }

    public function test_order_uses_server_price_and_reserves_stock(): void
    {
        $v = $this->variant();
        [$order] = $this->app->make(CheckoutService::class)->create(['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '+380000000000', 'shipping_address' => ['city' => 'Kyiv']], [['variant' => $v, 'quantity' => 2, 'total' => 1]]);
        $this->assertSame(378000, $order->total_amount);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(2, $v->fresh()->stock_reserved);
        $this->assertNotNull($order->merchant_account_id);
        $this->assertNotNull($order->legal_entity_id);
    }

    public function test_valid_promo_code_reduces_order_and_payment_amount(): void
    {
        $variant = $this->variant(price: 10000);
        $promo = PromoCode::create([
            'code' => 'LAMARI10',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        [$order] = $this->app->make(CheckoutService::class)->create(
            ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '+380000000000', 'shipping_address' => ['city' => 'Kyiv']],
            [['variant' => $variant, 'quantity' => 1]],
            'online',
            'lamari10',
        );

        $this->assertSame(10000, $order->subtotal_amount);
        $this->assertSame(1000, $order->discount_amount);
        $this->assertSame(9000, $order->total_amount);
        $this->assertSame($promo->id, $order->promo_code_id);
        $this->assertSame(9000, $order->payments->first()->amount);
        $this->assertSame(1, $promo->fresh()->used_count);
    }

    public function test_invalid_or_ineligible_promo_code_is_not_applied(): void
    {
        $variant = $this->variant(price: 10000);
        PromoCode::create([
            'code' => 'BIGORDER',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'minimum_order_amount' => 20000,
            'is_active' => true,
        ]);

        [$order] = $this->app->make(CheckoutService::class)->create(
            ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '+380000000000', 'shipping_address' => ['city' => 'Kyiv']],
            [['variant' => $variant, 'quantity' => 1]],
            'online',
            'BIGORDER',
        );

        $this->assertNull($order->promo_code_id);
        $this->assertSame(0, $order->discount_amount);
        $this->assertSame(10000, $order->total_amount);
    }

    public function test_new_order_numbers_start_at_one_and_increment(): void
    {
        $variant = $this->variant();
        $customer = ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '+380000000000', 'shipping_address' => ['city' => 'Kyiv']];
        $checkout = $this->app->make(CheckoutService::class);

        [$first] = $checkout->create($customer, [['variant' => $variant, 'quantity' => 1]]);
        [$second] = $checkout->create($customer, [['variant' => $variant, 'quantity' => 1]]);

        $this->assertSame('1', $first->number);
        $this->assertSame('2', $second->number);
    }

    public function test_order_fails_when_stock_is_insufficient(): void
    {
        $v = $this->variant(stock: 1);
        $this->expectException(\RuntimeException::class);
        $this->app->make(CheckoutService::class)->create(['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '1', 'shipping_address' => []], [['variant' => $v, 'quantity' => 2]]);
        $this->assertSame(0, $v->fresh()->stock_reserved);
    }

    public function test_cash_on_delivery_order_skips_online_payment(): void
    {
        $v = $this->variant();

        [$order, $checkout] = $this->app->make(CheckoutService::class)->create(
            ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '+380000000000', 'shipping_address' => ['city' => 'Kyiv']],
            [['variant' => $v, 'quantity' => 1]],
            'cash_on_delivery',
        );

        $this->assertSame('cash_on_delivery', $order->payment_method);
        $this->assertSame('cash_on_delivery', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertCount(0, $order->payments);
        $this->assertSame(route('orders.thank-you', $order), $checkout['checkout_url']);
    }

    public function test_unpaid_checkout_does_not_create_a_salesdrive_order(): void
    {
        config([
            'services.salesdrive.enabled' => true,
            'services.salesdrive.orders_key' => 'orders-secret',
            'services.salesdrive.payments_key' => 'payments-secret',
        ]);
        Http::fake();

        $this->app->make(CheckoutService::class)->create(
            ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '+380000000000', 'shipping_address' => ['city' => 'Kyiv']],
            [['variant' => $this->variant(), 'quantity' => 1]],
        );

        Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'salesdrive.me'));
    }

    public function test_mixed_bank_cart_routes_entire_order_to_privatbank(): void
    {
        $monoVariant = $this->variant();
        $monoVariant->product->update(['payment_destination' => 'mono']);
        $category = Category::create(['name' => 'Necklaces', 'slug' => 'necklaces']);
        $privatProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Privat item',
            'slug' => 'privat-item',
            'description' => 'Test',
            'payment_destination' => 'privat',
        ]);
        $privatVariant = ProductVariant::create([
            'product_id' => $privatProduct->id,
            'sku' => 'PRI-1',
            'name' => 'Default',
            'price_amount' => 10000,
            'stock_on_hand' => 2,
        ]);

        [$order] = $this->app->make(CheckoutService::class)->create(
            ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '1', 'shipping_address' => []],
            [['variant' => $monoVariant, 'quantity' => 1], ['variant' => $privatVariant, 'quantity' => 1]],
        );

        $this->assertSame('privat', $order->payment_destination);
        $this->assertSame(['mono', 'privat'], $order->items->pluck('payment_destination')->all());
    }

    public function test_mixed_bank_destination_can_be_changed_to_monobank(): void
    {
        PaymentRoutingSetting::query()->update(['mixed_cart_destination' => 'mono']);
        $monoVariant = $this->variant();
        $monoVariant->product->update(['payment_destination' => 'mono']);
        $category = Category::create(['name' => 'Mixed', 'slug' => 'mixed']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Privat', 'slug' => 'privat', 'description' => 'Test', 'payment_destination' => 'privat']);
        $privatVariant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'PRI-2', 'name' => 'Default', 'price_amount' => 10000, 'stock_on_hand' => 2]);

        [$order] = $this->app->make(CheckoutService::class)->create(
            ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '1', 'shipping_address' => []],
            [['variant' => $monoVariant, 'quantity' => 1], ['variant' => $privatVariant, 'quantity' => 1]],
        );

        $this->assertSame('mono', $order->payment_destination);
    }

    public function test_fop3_product_selects_the_fop3_merchant_account(): void
    {
        config(['services.payments.default' => 'fake']);
        $fop3Entity = LegalEntity::create(['name' => 'ФОП-3']);
        $fop3Merchant = MerchantAccount::create([
            'legal_entity_id' => $fop3Entity->id,
            'provider' => 'fake',
            'code' => 'fake-fop-3',
            'payment_destination' => 'privat',
            'is_default' => true,
        ]);
        $variant = $this->variant();
        $variant->product->update(['payment_destination' => 'privat']);
        PromoCode::create([
            'code' => 'FOP3TEST99',
            'discount_type' => 'percent',
            'discount_value' => 99,
            'is_active' => true,
        ]);

        [$order] = $this->app->make(CheckoutService::class)->create(
            ['customer_name' => 'Filip', 'email' => 'f@example.com', 'phone' => '1', 'shipping_address' => []],
            [['variant' => $variant, 'quantity' => 1]],
            'online',
            'FOP3TEST99',
        );

        $this->assertSame('privat', $order->payment_destination);
        $this->assertSame($fop3Merchant->id, $order->merchant_account_id);
        $this->assertSame($fop3Entity->id, $order->legal_entity_id);
        $this->assertSame(1890, $order->total_amount);
    }
}
