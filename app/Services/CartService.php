<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Contracts\Session\Session;

class CartService
{
    public function __construct(private Session $session) {}

    public function add(ProductVariant $v, int $q): void
    {
        $c = $this->session->get('cart', []);
        $current = $c[$v->id]['quantity'] ?? 0;
        $c[$v->id] = ['quantity' => min($current + $q, $v->available_stock)];
        $this->session->put('cart', $c);
    }

    public function remove(int $id): void
    {
        $c = $this->session->get('cart', []);
        unset($c[$id]);
        $this->session->put('cart', $c);
    }

    public function items(): array
    {
        $c = $this->session->get('cart', []);

        return ProductVariant::with('product')->whereIn('id', array_keys($c))->get()->map(fn ($v) => ['variant' => $v, 'quantity' => $c[$v->id]['quantity'], 'total' => $v->price_amount * $c[$v->id]['quantity']])->all();
    }

    public function clear(): void
    {
        $this->session->forget('cart');
    }
}
