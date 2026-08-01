<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\NovaPoshtaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(CartService $cart): Response
    {
        $items = $cart->items();

        return Inertia::render('Checkout', ['items' => $items, 'total' => collect($items)->sum('total')]);
    }

    public function store(Request $r, CartService $cart, CheckoutService $checkout, NovaPoshtaService $novaPoshta): RedirectResponse
    {
        $phoneDigits = preg_replace('/\D+/', '', (string) $r->input('phone'));

        if (str_starts_with($phoneDigits, '38')) {
            $phoneDigits = substr($phoneDigits, 2);
        }

        $r->merge(['phone' => '+38'.$phoneDigits]);

        $d = $r->validate(
            [
                'customer_name' => 'required|string|max:120',
                'email' => 'required|email',
                'phone' => ['required', 'regex:/^\+380\d{9}$/'],
                'city' => 'required|string|max:120',
                'city_ref' => ['required', 'uuid'],
                'warehouse' => 'required|string|max:255',
                'warehouse_ref' => ['required', 'uuid'],
                'payment_method' => ['required', 'in:online,cash_on_delivery'],
            ],
            ['phone.regex' => 'Введіть повний номер у форматі +38 0XX XXX XX XX.'],
        );
        try {
            $warehouse = $novaPoshta->warehouse($d['city_ref'], $d['warehouse_ref']);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'warehouse' => 'Не вдалося перевірити відділення. Спробуйте ще раз.',
            ]);
        }

        if (! $warehouse) {
            throw ValidationException::withMessages([
                'warehouse' => 'Оберіть відділення або поштомат зі списку Нової пошти.',
            ]);
        }

        [$order,$payment] = $checkout->create([
            'customer_name' => $d['customer_name'],
            'email' => $d['email'],
            'phone' => $d['phone'],
            'shipping_address' => [
                'provider' => 'nova_poshta',
                'city' => $d['city'],
                'city_ref' => $d['city_ref'],
                'address' => $warehouse['name'],
                'warehouse_ref' => $warehouse['ref'],
            ],
        ], $cart->items(), $d['payment_method']);
        $cart->clear();

        return redirect($payment['checkout_url']);
    }
}
