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

    public function create(array $customer, array $cart, string $paymentMethod = 'online'): array
    {
        return DB::transaction(function () use ($customer, $cart, $paymentMethod) {
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
                $total += $v->effective_price_amount * $q;
            }$merchant = $this->selector->select($total);
            $cashOnDelivery = $paymentMethod === 'cash_on_delivery';
            $order = Order::create([...$customer, 'number' => 'LAM-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'payment_method' => $paymentMethod, 'status' => $cashOnDelivery ? 'confirmed' : 'pending_payment', 'payment_status' => $cashOnDelivery ? 'cash_on_delivery' : 'pending', 'subtotal_amount' => $total, 'total_amount' => $total, 'currency' => 'UAH']);
            foreach ($resolved as [$v,$q]) {
                $price = $v->effective_price_amount;
                $order->items()->create(['product_variant_id' => $v->id, 'sku' => $v->sku, 'name' => $v->product->name.' — '.$v->name, 'quantity' => $q, 'unit_price_amount' => $price, 'total_amount' => $price * $q]);
            }

            if ($cashOnDelivery) {
                return [$order, ['checkout_url' => route('orders.thank-you', $order)]];
            }

            $payment = Payment::create(['order_id' => $order->id, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'provider' => $this->provider->name(), 'amount' => $total, 'currency' => 'UAH', 'idempotency_key' => (string) Str::uuid()]);

            return [$order, $this->provider->createPayment($payment)];
        });
    }
}
