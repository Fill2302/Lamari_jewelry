<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    public function test_homepage_categories_match_the_first_four_products_in_category_catalog_order(): void
    {
        $category = Category::create([
            'name' => 'Кольє',
            'slug' => 'homepage-necklaces',
            'is_active' => true,
            'show_on_home' => true,
        ]);

        $products = collect(range(1, 6))->map(fn (int $position) => Product::create([
            'category_id' => $category->id,
            'name' => "Кольє {$position}",
            'slug' => "homepage-necklace-{$position}",
            'description' => '',
            'is_active' => true,
            'category_position' => 1000,
        ]));
        $products->last()->update(['is_active' => false]);

        $category->memberProducts()->sync([
            $products[0]->id => ['position' => 3],
            $products[1]->id => ['position' => 1],
            $products[2]->id => ['position' => 1],
            $products[3]->id => ['position' => 2],
            $products[4]->id => ['position' => 4],
            $products[5]->id => ['position' => 0],
        ]);

        $expected = [$products[2]->id, $products[1]->id, $products[3]->id, $products[0]->id];

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('categories.0.member_products', 4)
                ->where('categories.0.member_products', fn ($items) => $items
                    ->pluck('id')->all() === $expected));

        $this->get("/categories/{$category->slug}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products', fn ($items) => $items
                    ->take(4)->pluck('id')->all() === $expected));
    }

    use RefreshDatabase;

    public function test_seeded_storefront_and_seo_endpoints_work(): void
    {
        $this->seed();
        $this->get('/')
            ->assertOk()
            ->assertSee('Home', false)
            ->assertInertia(fn ($page) => $page
                ->has('categories')
                ->has('newProducts', 1)
                ->has('hitProducts'));
        $this->get('/categories/necklaces')->assertOk();
        $this->get('/products/crystal-pearl-necklace')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('product.slug', 'crystal-pearl-necklace')
                ->where('productCardSetting.delivery_payment_title', 'Доставка та оплата')
                ->where('productCardSetting.tarnish_question', 'Чи темніють прикраси?'));
        $this->assertDatabaseHas('product_media', ['type' => 'image', 'is_active' => true]);
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /*?*');
    }

    public function test_products_follow_manual_catalog_and_category_positions(): void
    {
        $this->seed();

        $first = Product::with('category')->firstOrFail();
        $second = $first->replicate();
        $second->slug = "{$first->slug}-second";
        $second->save();

        $first->update(['catalog_position' => 2, 'category_position' => 1]);
        $second->update(['catalog_position' => 1, 'category_position' => 2]);

        $this->get('/catalog')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.0.id', $second->id)
                ->where('catalogControls.sort', 'manual'));

        $this->get("/categories/{$first->category->slug}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.0.id', $first->id)
                ->where('catalogControls.sort', 'manual'));
    }

    public function test_catalog_search_finds_products_by_name_and_variant_sku(): void
    {
        $this->seed();

        $product = Product::with('variants')->firstOrFail();
        $nameFragment = mb_strtolower(mb_substr($product->name, 0, 5));
        $skuFragment = strtolower(substr($product->variants->firstOrFail()->sku, 0, 5));

        $this->get('/catalog?q='.urlencode($nameFragment))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.0.id', $product->id)
                ->where('searchQuery', $nameFragment));

        $this->get('/catalog?q='.urlencode($skuFragment))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.0.id', $product->id)
                ->where('searchQuery', $skuFragment));
    }

    public function test_a_product_can_appear_in_its_regular_category_and_a_collection(): void
    {
        $regular = Category::create(['name' => 'Кольє', 'slug' => 'necklaces', 'is_active' => true]);
        $summer = Category::create(['name' => 'Літня колекція', 'slug' => 'summer', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $regular->id,
            'name' => 'Літнє кольє',
            'slug' => 'summer-necklace',
            'description' => 'Test',
            'is_active' => true,
        ]);
        $product->categories()->attach($summer->id, ['position' => 1]);

        $this->get('/categories/necklaces')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('products.0.id', $product->id));

        $this->get('/categories/summer')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('products.0.id', $product->id));
    }
}
