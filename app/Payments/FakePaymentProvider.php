<?php

namespace App\Payments;

use App\Contracts\Payments\PaymentCallback;
use App\Contracts\Payments\PaymentProvider;
use App\Models\Payment;

class FakePaymentProvider implements PaymentProvider
{
    public function name(): string
    {
        return 'fake';
    }

    public function createPayment(Payment $payment): array
    {
        $id = 'fake_'.$payment->id;
        $payment->update(['provider_payment_id' => $id]);

        return ['payment_id' => $id, 'checkout_url' => route('payments.fake.show', $payment)];
    }

    public function verifySignature(string $payload, ?string $signature): bool
    {
        return is_string($signature) && hash_equals(hash_hmac('sha256', $payload, (string) config('services.payments.fake_secret')), $signature);
    }

    public function parseCallback(array $payload): PaymentCallback
    {
        return new PaymentCallback((string) $payload['event_id'], (string) $payload['payment_id'], (string) $payload['status'], $payload);
    }
}
