<?php

namespace Tests\Feature;

use App\Filament\Resources\Discounts\Pages\CreateDiscount;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
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

    public function test_quantity_discount_uses_the_highest_reached_tier(): void
    {
        [$first, $second, $third] = $this->makeCartVariants([10000, 20000, 30000]);
        Discount::create([
            'name' => 'Більше товарів — більша знижка',
            'percentage' => 0,
            'mode' => 'quantity',
            'scope' => 'all',
            'quantity_rules' => [
                ['min_quantity' => 1, 'percentage' => 5, 'apply_to' => 'all'],
                ['min_quantity' => 2, 'percentage' => 10, 'apply_to' => 'all'],
                ['min_quantity' => 3, 'percentage' => 15, 'apply_to' => 'all'],
            ],
        ]);

        $this->withSession(['cart' => [
            $first->id => ['quantity' => 1],
            $second->id => ['quantity' => 1],
            $third->id => ['quantity' => 1],
        ]]);
        $items = app(CartService::class)->items();

        $this->assertSame(51000, collect($items)->sum('total'));
        $this->assertSame(15, $items[0]['discount_percentage']);
    }

    public function test_quantity_discount_can_start_from_two_items(): void
    {
        [$variant] = $this->makeCartVariants([10000]);
        Discount::create([
            'name' => 'Від двох', 'percentage' => 0, 'mode' => 'quantity', 'scope' => 'all',
            'quantity_rules' => [['min_quantity' => 2, 'percentage' => 10, 'apply_to' => 'all']],
        ]);
        $this->withSession(['cart' => [$variant->id => ['quantity' => 1]]]);

        $this->assertSame(10000, app(CartService::class)->items()[0]['total']);
    }

    public function test_quantity_discount_can_apply_only_to_the_second_cheapest_item(): void
    {
        [$expensive, $cheap] = $this->makeCartVariants([30000, 10000]);
        Discount::create([
            'name' => 'Другий товар', 'percentage' => 0, 'mode' => 'quantity', 'scope' => 'all',
            'quantity_rules' => [[
                'min_quantity' => 2, 'percentage' => 50, 'apply_to' => 'position', 'position' => 2,
            ]],
        ]);
        $this->withSession(['cart' => [
            $expensive->id => ['quantity' => 1],
            $cheap->id => ['quantity' => 1],
        ]]);

        $items = collect(app(CartService::class)->items())->keyBy('variant.id');
        $this->assertSame(15000, $items[$expensive->id]['total']);
        $this->assertSame(10000, $items[$cheap->id]['total']);
    }

    public function test_admin_can_create_quantity_discount_rules(): void
    {
        Livewire::test(CreateDiscount::class)
            ->fillForm([
                'name' => 'Сходинки',
                'mode' => 'quantity',
                'scope' => 'all',
                'quantity_rules' => [
                    ['min_quantity' => 2, 'percentage' => 10, 'apply_to' => 'all'],
                    ['min_quantity' => 3, 'percentage' => 15, 'apply_to' => 'all'],
                ],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $discount = Discount::where('name', 'Сходинки')->firstOrFail();
        $this->assertSame(0, $discount->percentage);
        $this->assertCount(2, $discount->quantity_rules);
    }

    private function makeCartVariants(array $prices): array
    {
        $category = Category::create(['name' => 'Тест', 'slug' => 'test-'.uniqid()]);

        return collect($prices)->map(function (int $price, int $index) use ($category): ProductVariant {
            $product = Product::create([
                'name' => 'Товар '.$index,
                'slug' => 'product-'.uniqid(),
                'description' => '',
                'category_id' => $category->id,
            ]);

            return ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'SKU-'.$index,
                'name' => 'Стандарт',
                'price_amount' => $price,
                'stock_on_hand' => 10,
            ]);
        })->all();
    }
}
