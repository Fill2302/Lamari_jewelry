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
        return Inertia::render('Home', ['categories' => Category::where('is_active', true)->with(['products' => fn ($q) => $q->where('is_active', true)->with('variants')->limit(4)])->get()]);
    }

    public function category(Category $category): Response
    {
        return Inertia::render('Category', ['category' => $category, 'products' => $category->products()->where('is_active', true)->with('variants')->get()]);
    }

    public function product(Product $product): Response
    {
        $product->load('category', 'variants');

        return Inertia::render('Product', ['product' => $product]);
    }
}
