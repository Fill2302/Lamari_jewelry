<?php

namespace App\Services;

use App\Contracts\Payments\PaymentProvider;
use App\Models\Payment;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PaymentCallbackService
{
    public function __construct(
        private PaymentProvider $provider,
        private SalesDriveSyncService $salesDrive,
        private TelegramOrderNotifier $telegram,
    ) {}

    public function handle(string $raw, ?string $signature): bool
    {
        return $this->handleWith($this->provider, $raw, $signature);
    }

    public function handleWith(PaymentProvider $provider, string $raw, ?string $signature): bool
    {
        if (! $provider->verifySignature($raw, $signature)) {
            throw new RuntimeException('Invalid callback signature.');
        }

        $cb = $provider->parseCallback(json_decode($raw, true, flags: JSON_THROW_ON_ERROR));

        $processedPayment = null;
        $processed = DB::transaction(function () use ($cb, $provider, &$processedPayment) {
            $event = WebhookEvent::firstOrCreate(['provider' => $provider->name(), 'external_id' => $cb->externalId], ['event_type' => 'payment.updated', 'signature_valid' => true, 'payload' => $cb->payload]);
            if (! $event->wasRecentlyCreated || $event->processed_at) {
                return false;
            }

            $payment = Payment::where('provider_payment_id', $cb->paymentId)->lockForUpdate()->firstOrFail();
            if ($cb->amount !== null && $cb->amount !== $payment->amount) {
                throw new RuntimeException('Callback amount does not match payment.');
            }
            if ($cb->currency !== null && $cb->currency !== 980) {
                throw new RuntimeException('Callback currency does not match payment.');
            }
            if ($cb->reference !== null && ! hash_equals($payment->idempotency_key, $cb->reference)) {
                throw new RuntimeException('Callback reference does not match payment.');
            }
            if ($payment->status === 'paid' && $cb->status !== 'paid') {
                $event->update(['status' => 'ignored', 'processed_at' => now()]);

                return false;
            }

            $payment->update(['status' => $cb->status, 'payload' => $cb->payload]);
            if ($cb->status === 'paid') {
                $orderUpdate = $payment->order->payment_method === 'wayforpay_deposit'
                    ? ['payment_status' => 'deposit_paid', 'status' => 'confirmed', 'prepaid_amount' => $payment->amount, 'cod_amount' => max(0, $payment->order->total_amount - $payment->amount)]
                    : ['payment_status' => 'paid', 'status' => 'confirmed'];
                $payment->order()->update($orderUpdate);
                $payment->refresh()->load('order.items');
                $processedPayment = $payment;
            }

            $event->update(['status' => 'processed', 'processed_at' => now()]);

            return true;
        });

        if ($processedPayment) {
            try {
                $this->salesDrive->syncPaid($processedPayment);
            } catch (Throwable $e) {
                report($e);
            }

            $this->telegram->notifyPaid($processedPayment);
        }

        return $processed;
    }
}
