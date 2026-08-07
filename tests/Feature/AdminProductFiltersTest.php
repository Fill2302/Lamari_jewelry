<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProductFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    public function test_category_filter_includes_products_from_its_subcategories(): void
    {
        $rings = Category::create(['name' => 'Каблучки', 'slug' => 'rings']);
        $naturalStones = Category::create([
            'name' => 'З натуральним камінням',
            'slug' => 'natural-stone-rings',
            'parent_id' => $rings->id,
        ]);
        $earrings = Category::create(['name' => 'Сережки', 'slug' => 'earrings']);

        $ring = Product::create([
            'name' => 'Каблучка з оніксом',
            'slug' => 'onyx-ring',
            'description' => '',
            'category_id' => $naturalStones->id,
        ]);
        $earring = Product::create([
            'name' => 'Сережки',
            'slug' => 'earrings-product',
            'description' => '',
            'category_id' => $earrings->id,
        ]);

        Livewire::test(ListProducts::class)
            ->filterTable('parent_category_id', $rings->id)
            ->assertCanSeeTableRecords([$ring])
            ->assertCanNotSeeTableRecords([$earring]);
    }

    public function test_subcategory_filter_only_shows_products_from_that_subcategory(): void
    {
        $rings = Category::create(['name' => 'Каблучки', 'slug' => 'rings']);
        $naturalStones = Category::create([
            'name' => 'З натуральним камінням',
            'slug' => 'natural-stone-rings',
            'parent_id' => $rings->id,
        ]);
        $minimalist = Category::create([
            'name' => 'Мінімалістичні',
            'slug' => 'minimalist-rings',
            'parent_id' => $rings->id,
        ]);

        $naturalStoneRing = Product::create([
            'name' => 'Каблучка з оніксом',
            'slug' => 'onyx-ring',
            'description' => '',
            'category_id' => $naturalStones->id,
        ]);
        $minimalistRing = Product::create([
            'name' => 'Тонка каблучка',
            'slug' => 'thin-ring',
            'description' => '',
            'category_id' => $minimalist->id,
        ]);

        Livewire::test(ListProducts::class)
            ->filterTable('subcategory_id', $naturalStones->id)
            ->assertCanSeeTableRecords([$naturalStoneRing])
            ->assertCanNotSeeTableRecords([$minimalistRing]);
    }
}
