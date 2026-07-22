<?php

namespace App\Services;

use App\Contracts\Payments\PaymentProvider;
use App\Models\Payment;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentCallbackService
{
    public function __construct(private PaymentProvider $provider) {}

    public function handle(string $raw, ?string $signature): bool
    {
        if (! $this->provider->verifySignature($raw, $signature)) {
            throw new RuntimeException('Invalid callback signature.');
        }$cb = $this->provider->parseCallback(json_decode($raw, true, flags: JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($cb) {
            $event = WebhookEvent::firstOrCreate(['provider' => $this->provider->name(), 'external_id' => $cb->externalId], ['event_type' => 'payment.updated', 'signature_valid' => true, 'payload' => $cb->payload]);
            if (! $event->wasRecentlyCreated || $event->processed_at) {
                return false;
            }$payment = Payment::where('provider_payment_id', $cb->paymentId)->lockForUpdate()->firstOrFail();
            $payment->update(['status' => $cb->status, 'payload' => $cb->payload]);
            if ($cb->status === 'paid') {
                $payment->order()->update(['payment_status' => 'paid', 'status' => 'confirmed']);
            }$event->update(['status' => 'processed', 'processed_at' => now()]);

            return true;
        });
    }
}
