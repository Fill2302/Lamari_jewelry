<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
