<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(CartService $cart): Response
    {
        $items = $cart->items();

        return Inertia::render('Checkout', ['items' => $items, 'total' => collect($items)->sum('total')]);
    }

    public function store(Request $r, CartService $cart, CheckoutService $checkout): RedirectResponse
    {
        $d = $r->validate(['customer_name' => 'required|string|max:120', 'email' => 'required|email', 'phone' => 'required|string|max:30', 'city' => 'required|string|max:120', 'address' => 'required|string|max:255']);
        [$order,$payment] = $checkout->create(['customer_name' => $d['customer_name'], 'email' => $d['email'], 'phone' => $d['phone'], 'shipping_address' => ['city' => $d['city'], 'address' => $d['address']]], $cart->items());
        $cart->clear();

        return redirect($payment['checkout_url']);
    }
}
