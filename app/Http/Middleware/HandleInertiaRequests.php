<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $cart = app(CartService::class);

        return [
            ...parent::share($request),
            'cartCount' => collect($request->session()->get('cart', []))->sum('quantity'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'cartOpen' => fn () => (bool) $request->session()->get('cartOpen'),
            ],
            'cartPreview' => function () use ($cart) {
                $items = $cart->items();

                return [
                    'items' => $items,
                    'subtotal' => collect($items)->sum('total'),
                ];
            },
            'catalogMenu' => fn () => Category::whereNull('parent_id')->where('is_active', true)
                ->with(['children' => fn ($query) => $query->where('is_active', true)])
                ->get(['id', 'parent_id', 'name', 'slug']),
        ];
    }
}
