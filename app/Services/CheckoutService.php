<?php

namespace App\Services;

use App\Contracts\Payments\PaymentProvider;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentRoutingSetting;
use App\Models\ProductVariant;
use App\Payments\WayForPayPaymentProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private MerchantSelector $selector,
        private PaymentProvider $provider,
        private WayForPayPaymentProvider $wayForPay,
        private TelegramOrderNotifier $telegram,
    ) {}

    public function create(array $customer, array $cart, string $paymentMethod = 'online', string $promoCode = ''): array
    {
        [$order, $payment] = DB::transaction(function () use ($customer, $cart, $paymentMethod, $promoCode) {
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
            $promo = $promoCode !== '' ? app(PromoCodeService::class)->findValid($promoCode, $total, true) : null;
            $promoDiscount = $promo ? app(PromoCodeService::class)->discount($promo, $total) : 0;
            $amountDue = $total - $promoDiscount;
            $destinations = collect($resolved)
                ->map(fn (array $item): string => $item[0]->product->payment_destination ?? 'unassigned')
                ->unique();
            $mixedDestination = PaymentRoutingSetting::query()->value('mixed_cart_destination') ?: 'privat';
            $paymentDestination = $destinations->contains('privat') && $destinations->contains('mono')
                ? $mixedDestination
                : ($destinations->contains('privat') ? 'privat' : ($destinations->contains('mono') ? 'mono' : 'unassigned'));
            $merchant = $this->selector->select($amountDue, $paymentDestination);
            $cashOnDelivery = $paymentMethod === 'cash_on_delivery';
            $deposit = $paymentMethod === 'wayforpay_deposit';
            if ($deposit && $amountDue <= 15000) {
                throw new RuntimeException('Deposit payment requires an order total above 150 UAH.');
            }
            $number = (string) DB::table('order_number_sequences')->insertGetId(['created_at' => now()]);
            $order = Order::create([...$customer, 'number' => $number, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'payment_method' => $paymentMethod, 'payment_destination' => $paymentDestination, 'status' => $cashOnDelivery ? 'confirmed' : 'pending_payment', 'payment_status' => $cashOnDelivery ? 'cash_on_delivery' : 'pending', 'promo_code_id' => $promo?->id, 'subtotal_amount' => $total, 'discount_amount' => $promoDiscount, 'total_amount' => $amountDue, 'prepaid_amount' => 0, 'cod_amount' => $deposit ? $amountDue - 15000 : ($cashOnDelivery ? $amountDue : 0), 'currency' => 'UAH']);
            if ($promo) {
                $promo->increment('used_count');
            }
            foreach ($resolved as [$v,$q,$itemTotal]) {
                $price = (int) round($itemTotal / $q);
                $order->items()->create(['product_variant_id' => $v->id, 'sku' => $v->sku, 'name' => $v->product->name.' — '.$v->name, 'receipt_name' => $v->product->receipt_name ?: $v->product->name, 'payment_destination' => $v->product->payment_destination ?? 'unassigned', 'quantity' => $q, 'unit_price_amount' => $price, 'total_amount' => $itemTotal]);
            }

            if ($cashOnDelivery) {
                return [$order, null];
            }

            $provider = $deposit ? $this->wayForPay : $this->provider;
            $payment = Payment::create(['order_id' => $order->id, 'merchant_account_id' => $merchant->id, 'legal_entity_id' => $merchant->legal_entity_id, 'provider' => $provider->name(), 'amount' => $deposit ? 15000 : $amountDue, 'currency' => 'UAH', 'idempotency_key' => (string) Str::uuid()]);

            return [$order, $payment];
        });

        $this->telegram->notifyCreated($order->loadMissing('items'));

        if (! $payment) {
            return [$order, ['checkout_url' => route('orders.thank-you', $order)]];
        }

        $provider = $payment->provider === 'wayforpay' ? $this->wayForPay : $this->provider;

        return [$order, $provider->createPayment($payment)];
    }
}
