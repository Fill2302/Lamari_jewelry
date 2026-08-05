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
        $data = $request->validate([
            'quantity' => 'required|integer|min:0|max:10',
            'cart_open' => 'sometimes|boolean',
        ]);
        $cart->update($variant, $data['quantity']);

        return ($data['cart_open'] ?? true)
            ? back()->with('cartOpen', true)
            : back();
    }

    public function changeVariant(Request $request, ProductVariant $variant, CartService $cart): RedirectResponse
    {
        $data = $request->validate(['variant_id' => 'required|integer|exists:product_variants,id']);
        $replacement = ProductVariant::findOrFail($data['variant_id']);
        $cart->changeVariant($variant, $replacement);

        return back();
    }

    public function remove(ProductVariant $variant, CartService $cart): RedirectResponse
    {
        $cart->remove($variant->id);

        return back();
    }
}
