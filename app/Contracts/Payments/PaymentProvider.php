<?php

namespace App\Contracts\Payments;

use App\Models\Payment;

interface PaymentProvider
{
    public function name(): string;

    public function createPayment(Payment $payment): array;

    public function verifySignature(string $payload, ?string $signature): bool;

    public function parseCallback(array $payload): PaymentCallback;
}
