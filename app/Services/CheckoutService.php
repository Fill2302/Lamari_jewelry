<?php

namespace App\Services;

use App\Contracts\Payments\PaymentProvider;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutService
{
    public function __construct(private MerchantSelector $selector, private PaymentProvider $provider) {}

    public function create(array $customer, array $cart): array
    {
        return DB::transaction(function () use ($customer, $cart) {
            if (! $cart) {
                throw new RuntimeException('Cart is empty.');
            }$resolved = [];
            $total = 0;
            foreach ($cart as $item) {
                $v = ProductVariant::with('product')->lockForUpdate()->findOrFail($item['variant']->id);
                $q = (int) $item['quantity'];
                if ($q < 1 || $v->available_stock < $q) {
                    throw new RuntimeException("Insufficient stock for {$v->sku}.");
                }$v->increment('stock_reserved', $q);
                $resolved[] = [$v, $q];
                $total += $v->price_amount * $q;
            }$merchant = $this->selector->select($total);
            $order = Order::create([...$customer, 'number' => 'LAM-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'subtotal_amount' => $total, 'total_amount' => $total, 'currency' => 'UAH']);
            foreach ($resolved as [$v,$q]) {
                $order->items()->create(['product_variant_id' => $v->id, 'sku' => $v->sku, 'name' => $v->product->name.' — '.$v->name, 'quantity' => $q, 'unit_price_amount' => $v->price_amount, 'total_amount' => $v->price_amount * $q]);
            }$payment = Payment::create(['order_id' => $order->id, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'provider' => $this->provider->name(), 'amount' => $total, 'currency' => 'UAH', 'idempotency_key' => (string) Str::uuid()]);

            return [$order, $this->provider->createPayment($payment)];
        });
    }
}
