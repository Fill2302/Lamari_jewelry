<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(CartService $cart): Response
    {
        $items = $cart->items();

        return Inertia::render('Cart', ['items' => $items, 'total' => collect($items)->sum('total')]);
    }

    public function add(Request $r, ProductVariant $variant, CartService $cart): RedirectResponse
    {
        $data = $r->validate(['quantity' => 'required|integer|min:1|max:10']);
        $cart->add($variant, $data['quantity']);

        return back()->with(['success' => 'Прикрасу додано до кошика.', 'cartOpen' => true]);
    }

    public function update(Request $request, ProductVariant $variant, CartService $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => 'required|integer|min:0|max:10']);
        $cart->update($variant, $data['quantity']);

        return back()->with('cartOpen', true);
    }

    public function remove(ProductVariant $variant, CartService $cart): RedirectResponse
    {
        $cart->remove($variant->id);

        return back();
    }
}
