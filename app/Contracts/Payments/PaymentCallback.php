<?php

namespace App\Contracts\Payments;

final readonly class PaymentCallback
{
    public function __construct(
        public string $externalId,
        public string $paymentId,
        public string $status,
        public array $payload,
        public ?int $amount = null,
        public ?int $currency = null,
        public ?string $reference = null,
    ) {}
}
