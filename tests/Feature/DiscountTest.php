<?php

namespace Tests\Feature;

use App\Filament\Resources\Discounts\Pages\CreateDiscount;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_one_discount_can_apply_to_multiple_products(): void
    {
        $category = Category::create(['name' => 'Кольє', 'slug' => 'necklaces']);
        $firstProduct = Product::create(['name' => 'Перше', 'slug' => 'first', 'description' => '', 'category_id' => $category->id]);
        $secondProduct = Product::create(['name' => 'Друге', 'slug' => 'second', 'description' => '', 'category_id' => $category->id]);
        $firstVariant = ProductVariant::create(['product_id' => $firstProduct->id, 'sku' => 'K1-45', 'name' => '45', 'price_amount' => 100000, 'stock_on_hand' => 1]);
        $secondVariant = ProductVariant::create(['product_id' => $secondProduct->id, 'sku' => 'K2-50', 'name' => '50', 'price_amount' => 200000, 'stock_on_hand' => 1]);
        $discount = Discount::create(['name' => 'Обрані', 'percentage' => 15, 'scope' => 'product']);
        $discount->products()->attach([$firstProduct->id, $secondProduct->id]);

        $this->assertSame(85000, $firstVariant->effective_price_amount);
        $this->assertSame(170000, $secondVariant->effective_price_amount);
    }

    public function test_one_discount_can_apply_to_multiple_category_branches(): void
    {
        $firstCategory = Category::create(['name' => 'Кольє', 'slug' => 'necklaces']);
        $secondCategory = Category::create(['name' => 'Браслети', 'slug' => 'bracelets']);
        $firstChild = Category::create(['name' => 'З бісеру', 'slug' => 'beaded-necklaces', 'parent_id' => $firstCategory->id]);
        $secondChild = Category::create(['name' => 'З бісеру', 'slug' => 'beaded-bracelets', 'parent_id' => $secondCategory->id]);
        $firstProduct = Product::create(['name' => 'Кольє', 'slug' => 'necklace', 'description' => '', 'category_id' => $firstChild->id]);
        $secondProduct = Product::create(['name' => 'Браслет', 'slug' => 'bracelet', 'description' => '', 'category_id' => $secondChild->id]);
        $firstVariant = ProductVariant::create(['product_id' => $firstProduct->id, 'sku' => 'K1', 'name' => '45', 'price_amount' => 100000, 'stock_on_hand' => 1]);
        $secondVariant = ProductVariant::create(['product_id' => $secondProduct->id, 'sku' => 'B1', 'name' => '18', 'price_amount' => 100000, 'stock_on_hand' => 1]);
        $discount = Discount::create(['name' => 'Два розділи', 'percentage' => 30, 'scope' => 'category']);
        $discount->categories()->attach([$firstCategory->id, $secondChild->id]);

        $this->assertSame(70000, $firstVariant->effective_price_amount);
        $this->assertSame(70000, $secondVariant->effective_price_amount);
    }

    public function test_admin_can_create_a_discount_for_multiple_categories(): void
    {
        $firstCategory = Category::create(['name' => 'Кольє', 'slug' => 'necklaces']);
        $secondCategory = Category::create(['name' => 'Браслети', 'slug' => 'bracelets']);

        Livewire::test(CreateDiscount::class)
            ->fillForm([
                'name' => 'Кілька розділів',
                'percentage' => 20,
                'scope' => 'category',
                'category_ids' => [$firstCategory->id, $secondCategory->id],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $discount = Discount::where('name', 'Кілька розділів')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$firstCategory->id, $secondCategory->id],
            $discount->categories()->pluck('categories.id')->all(),
        );
    }

    public function test_admin_can_create_a_discount_for_multiple_products(): void
    {
        $category = Category::create(['name' => 'Кольє', 'slug' => 'necklaces']);
        $firstProduct = Product::create(['name' => 'Перше', 'slug' => 'first', 'description' => '', 'category_id' => $category->id]);
        $secondProduct = Product::create(['name' => 'Друге', 'slug' => 'second', 'description' => '', 'category_id' => $category->id]);
        ProductVariant::create(['product_id' => $firstProduct->id, 'sku' => 'K1-45', 'name' => '45', 'price_amount' => 100000, 'stock_on_hand' => 1]);
        ProductVariant::create(['product_id' => $secondProduct->id, 'sku' => 'K2-50', 'name' => '50', 'price_amount' => 100000, 'stock_on_hand' => 1]);

        Livewire::test(CreateDiscount::class)
            ->fillForm([
                'name' => 'Кілька товарів',
                'percentage' => 25,
                'scope' => 'product',
                'product_ids' => [$firstProduct->id, $secondProduct->id],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $discount = Discount::where('name', 'Кілька товарів')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$firstProduct->id, $secondProduct->id],
            $discount->products()->pluck('products.id')->all(),
        );
    }
}
