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

    public function update(ProductVariant $variant, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($variant->id);

            return;
        }

        $cart = $this->session->get('cart', []);
        $cart[$variant->id] = ['quantity' => min($quantity, $variant->available_stock)];
        $this->session->put('cart', $cart);
    }

    public function changeVariant(ProductVariant $from, ProductVariant $to): void
    {
        $cart = $this->session->get('cart', []);
        $quantity = $cart[$from->id]['quantity'] ?? 0;

        if ($quantity < 1 || $from->product_id !== $to->product_id || ! $to->is_active) {
            return;
        }

        unset($cart[$from->id]);
        $quantity += $cart[$to->id]['quantity'] ?? 0;
        $cart[$to->id] = ['quantity' => min($quantity, $to->available_stock)];
        $this->session->put('cart', $cart);
    }

    public function items(): array
    {
        $c = $this->session->get('cart', []);

        return ProductVariant::with('product.media', 'product.variants')->whereIn('id', array_keys($c))->get()->map(fn ($v) => ['variant' => $v, 'quantity' => $c[$v->id]['quantity'], 'total' => $v->price_amount * $c[$v->id]['quantity']])->all();
    }

    public function clear(): void
    {
        $this->session->forget('cart');
    }
}
