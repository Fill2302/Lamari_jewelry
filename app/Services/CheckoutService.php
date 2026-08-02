<?php

namespace App\Services;

use App\Contracts\Payments\PaymentProvider;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
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
            foreach ($cart as $item) {
                $v = ProductVariant::with('product')->lockForUpdate()->findOrFail($item['variant']->id);
                $q = (int) $item['quantity'];
                if ($q < 1 || $v->available_stock < $q) {
                    throw new RuntimeException("Insufficient stock for {$v->sku}.");
                }$v->increment('stock_reserved', $q);
                $resolved[] = [$v, $q];
            }

            /** @var Collection<int, ProductVariant> $variants */
            $variants = collect($resolved)->map(fn (array $item): ProductVariant => $item[0]);
            $quantities = collect($resolved)->mapWithKeys(fn (array $item): array => [$item[0]->id => $item[1]])->all();
            $percentages = app(DiscountService::class)->percentagesForCart($variants, $quantities);
            $resolved = collect($resolved)->map(function (array $item) use ($percentages): array {
                [$variant, $quantity] = $item;
                $itemTotal = collect($percentages[$variant->id] ?? array_fill(0, $quantity, 0))->sum(
                    fn (int $percentage): int => (int) round($variant->original_price_amount * (100 - $percentage) / 100),
                );

                return [$variant, $quantity, $itemTotal];
            })->all();
            $total = collect($resolved)->sum(fn (array $item): int => $item[2]);
            $destinations = collect($resolved)
                ->map(fn (array $item): string => $item[0]->product->payment_destination ?? 'unassigned')
                ->unique();
            $paymentDestination = $destinations->contains('privat')
                ? 'privat'
                : ($destinations->contains('mono') ? 'mono' : 'unassigned');
            $merchant = $this->selector->select($total);
            $cashOnDelivery = $paymentMethod === 'cash_on_delivery';
            $order = Order::create([...$customer, 'number' => 'LAM-'.now()->format('ymd').'-'.strtoupper(Str::random(6)), 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'payment_method' => $paymentMethod, 'payment_destination' => $paymentDestination, 'status' => $cashOnDelivery ? 'confirmed' : 'pending_payment', 'payment_status' => $cashOnDelivery ? 'cash_on_delivery' : 'pending', 'subtotal_amount' => $total, 'total_amount' => $total, 'currency' => 'UAH']);
            foreach ($resolved as [$v,$q,$itemTotal]) {
                $price = (int) round($itemTotal / $q);
                $order->items()->create(['product_variant_id' => $v->id, 'sku' => $v->sku, 'name' => $v->product->name.' — '.$v->name, 'payment_destination' => $v->product->payment_destination ?? 'unassigned', 'quantity' => $q, 'unit_price_amount' => $price, 'total_amount' => $itemTotal]);
            }

            if ($cashOnDelivery) {
                return [$order, ['checkout_url' => route('orders.thank-you', $order)]];
            }

            $payment = Payment::create(['order_id' => $order->id, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'provider' => $this->provider->name(), 'amount' => $total, 'currency' => 'UAH', 'idempotency_key' => (string) Str::uuid()]);

            return [$order, $this->provider->createPayment($payment)];
        });
    }
}
