<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MarketingAttribution;
use App\Services\NovaPoshtaService;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class CheckoutController extends Controller
{
    public function show(Request $request, CartService $cart, PromoCodeService $promos): Response
    {
        $items = $cart->items();
        $subtotal = (int) collect($items)->sum('total');
        $code = (string) $request->session()->get('promo_code', '');
        $promo = $code !== '' ? $promos->findValid($code, $subtotal) : null;
        if ($code !== '' && ! $promo) {
            $request->session()->forget('promo_code');
        }
        $promoDiscount = $promo ? $promos->discount($promo, $subtotal) : 0;

        return Inertia::render('Checkout', [
            'items' => $items,
            'total' => $subtotal,
            'promo' => $promo ? ['code' => $promo->code, 'discount' => $promoDiscount] : null,
            'amountDue' => $subtotal - $promoDiscount,
        ]);
    }

    public function applyPromo(Request $request, CartService $cart, PromoCodeService $promos): SymfonyResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:64']]);
        $subtotal = (int) collect($cart->items())->sum('total');
        $promo = $promos->findValid($data['code'], $subtotal);
        if (! $promo) {
            throw ValidationException::withMessages(['code' => 'Промокод недійсний або не відповідає умовам акції.']);
        }
        $request->session()->put('promo_code', $promo->code);

        return back();
    }

    public function store(Request $r, CartService $cart, CheckoutService $checkout, NovaPoshtaService $novaPoshta, MarketingAttribution $attribution): SymfonyResponse
    {
        $phoneDigits = preg_replace('/\D+/', '', (string) $r->input('phone'));

        if (str_starts_with($phoneDigits, '38')) {
            $phoneDigits = substr($phoneDigits, 2);
        }

        $r->merge(['phone' => '+38'.$phoneDigits]);

        $d = $r->validate(
            [
                'first_name' => 'required|string|max:60',
                'last_name' => 'required|string|max:60',
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
            'first_name' => $d['first_name'],
            'last_name' => $d['last_name'],
            'customer_name' => $d['first_name'].' '.$d['last_name'],
            'email' => $d['email'],
            'phone' => $d['phone'],
            'shipping_address' => [
                'provider' => 'nova_poshta',
                'city' => $d['city'],
                'city_ref' => $d['city_ref'],
                'address' => $warehouse['name'],
                'warehouse_ref' => $warehouse['ref'],
            ],
            'marketing_attribution' => $attribution->from($r),
        ], $cart->items(), $d['payment_method'], (string) $r->session()->get('promo_code', ''));
        $cart->clear();
        $r->session()->forget('promo_code');

        if (str_starts_with($payment['checkout_url'], config('app.url'))) {
            return redirect($payment['checkout_url']);
        }

        return Inertia::location($payment['checkout_url']);
    }
}
