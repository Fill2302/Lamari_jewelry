<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home', ['categories' => Category::whereNull('parent_id')->where('is_active', true)->with(['products' => fn ($q) => $q->where('is_active', true)->with('variants')->limit(4), 'children'])->get()]);
    }

    public function category(Category $category): Response
    {
        $category->load('children');
        $categoryIds = $category->children->pluck('id')->prepend($category->id);

        return Inertia::render('Category', [
            'category' => $category,
            'products' => Product::whereIn('category_id', $categoryIds)->where('is_active', true)->with('variants', 'media')->get(),
        ]);
    }

    public function product(Product $product): Response
    {
        $product->load('category.parent', 'variants', 'media');

        return Inertia::render('Product', ['product' => $product]);
    }
}
