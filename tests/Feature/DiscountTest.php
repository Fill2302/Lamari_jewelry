<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_discount_applies_to_products_in_subcategories(): void
    {
        $parent = Category::create(['name' => 'Прикраси', 'slug' => 'jewelry']);
        $child = Category::create(['name' => 'Кольє', 'slug' => 'necklaces', 'parent_id' => $parent->id]);
        $product = Product::create(['name' => 'Кольє', 'slug' => 'necklace', 'description' => '', 'category_id' => $child->id]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'K1', 'name' => '45', 'price_amount' => 100000, 'stock_on_hand' => 1]);
        Discount::create(['name' => 'Літо', 'percentage' => 20, 'scope' => 'category', 'category_id' => $parent->id]);

        $this->assertSame(80000, $variant->effective_price_amount);
        $this->assertSame(20, $variant->discount_percentage);
    }

    public function test_largest_matching_discount_is_used(): void
    {
        $category = Category::create(['name' => 'Кольє', 'slug' => 'necklaces']);
        $product = Product::create(['name' => 'Кольє', 'slug' => 'necklace', 'description' => '', 'category_id' => $category->id]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'K1', 'name' => '45', 'price_amount' => 100000, 'stock_on_hand' => 1]);
        Discount::create(['name' => 'Усі', 'percentage' => 10, 'scope' => 'all']);
        Discount::create(['name' => 'Товар', 'percentage' => 25, 'scope' => 'product', 'product_id' => $product->id]);

        $this->assertSame(75000, $variant->effective_price_amount);
    }
}
