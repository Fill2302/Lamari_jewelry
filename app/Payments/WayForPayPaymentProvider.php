<?php

namespace App\Payments;

use App\Contracts\Payments\PaymentCallback;
use App\Contracts\Payments\PaymentProvider;
use App\Models\Payment;
use RuntimeException;

class WayForPayPaymentProvider implements PaymentProvider
{
    public function name(): string
    {
        return 'wayforpay';
    }

    public function createPayment(Payment $payment): array
    {
        $payment->loadMissing('order', 'merchantAccount');
        $credentials = $this->credentialsForMerchantCode($payment->merchantAccount->code);
        $orderDate = now()->timestamp;
        $amount = $this->amount($payment->amount);
        $productName = ['Передплата за замовлення №'.$payment->order->number];
        $productCount = [1];
        $productPrice = [$amount];
        $merchantDomain = (string) config('services.payments.wayforpay_domain');
        $siteUrl = 'https://'.trim($merchantDomain, '/');
        $fields = [
            'merchantAccount' => $credentials['account'],
            'merchantAuthType' => 'SimpleSignature',
            'merchantDomainName' => $merchantDomain,
            'orderReference' => $payment->idempotency_key,
            'orderDate' => $orderDate,
            'amount' => $amount,
            'currency' => 'UAH',
            'productName' => $productName,
            'productCount' => $productCount,
            'productPrice' => $productPrice,
            'clientFirstName' => $payment->order->first_name,
            'clientLastName' => $payment->order->last_name,
            'clientEmail' => $payment->order->email,
            'clientPhone' => $payment->order->phone,
            'language' => 'UA',
            'returnUrl' => $siteUrl.route('payments.wayforpay.return', $payment, false),
            'serviceUrl' => $siteUrl.route('payments.wayforpay.webhook', [], false),
        ];
        $signatureValues = [
            $fields['merchantAccount'], $fields['merchantDomainName'], $fields['orderReference'],
            $fields['orderDate'], $fields['amount'], $fields['currency'],
            ...$productName, ...$productCount, ...$productPrice,
        ];
        $fields['merchantSignature'] = $this->sign($signatureValues, $credentials['secret']);

        $payment->update([
            'provider_payment_id' => $payment->idempotency_key,
            'payload' => ['checkout' => $fields],
        ]);

        return ['payment_id' => $payment->idempotency_key, 'checkout_url' => route('payments.wayforpay.checkout', $payment)];
    }

    public function verifySignature(string $payload, ?string $signature): bool
    {
        $data = json_decode($payload, true);
        if (! is_array($data) || ! isset($data['merchantAccount'], $data['merchantSignature'])) {
            return false;
        }
        $credentials = $this->credentialsForAccount((string) $data['merchantAccount']);
        $expected = $this->sign([
            $data['merchantAccount'], $data['orderReference'] ?? '', $data['amount'] ?? '',
            $data['currency'] ?? '', $data['authCode'] ?? '', $data['cardPan'] ?? '',
            $data['transactionStatus'] ?? '', $data['reasonCode'] ?? '',
        ], $credentials['secret']);

        return hash_equals($expected, (string) $data['merchantSignature']);
    }

    public function parseCallback(array $payload): PaymentCallback
    {
        $reference = (string) ($payload['orderReference'] ?? '');
        if ($reference === '') {
            throw new RuntimeException('WayForPay callback has no orderReference.');
        }
        $payment = Payment::where('provider', $this->name())->where('provider_payment_id', $reference)->firstOrFail();
        $payment->loadMissing('merchantAccount');
        $expectedAccount = (string) $this->credentialsForMerchantCode($payment->merchantAccount->code)['account'];
        if (! hash_equals($expectedAccount, (string) ($payload['merchantAccount'] ?? ''))) {
            throw new RuntimeException('WayForPay callback merchant does not match payment.');
        }

        return new PaymentCallback(
            externalId: $reference.':'.((string) ($payload['processingDate'] ?? $payload['transactionStatus'] ?? 'unknown')),
            paymentId: $reference,
            status: match ((string) ($payload['transactionStatus'] ?? '')) {
                'Approved' => 'paid',
                'Declined' => 'failed',
                'Expired' => 'expired',
                'Refunded', 'Voided' => 'refunded',
                default => 'pending',
            },
            payload: $payload,
            amount: isset($payload['amount']) ? (int) round(((float) $payload['amount']) * 100) : null,
            currency: isset($payload['currency']) ? ((string) $payload['currency'] === 'UAH' ? 980 : 0) : null,
            reference: $reference,
        );
    }

    public function acceptance(string $orderReference): array
    {
        $payment = Payment::where('provider', $this->name())->where('provider_payment_id', $orderReference)->firstOrFail();
        $payment->loadMissing('merchantAccount');
        $credentials = $this->credentialsForMerchantCode($payment->merchantAccount->code);
        $time = now()->timestamp;

        return [
            'orderReference' => $orderReference,
            'status' => 'accept',
            'time' => $time,
            'signature' => $this->sign([$orderReference, 'accept', $time], $credentials['secret']),
        ];
    }

    private function credentialsForMerchantCode(string $code): array
    {
        $merchants = (array) config('services.payments.wayforpay_merchants', []);
        $credentials = $merchants[$code] ?? null;
        if (! is_array($credentials) || blank($credentials['account'] ?? null) || blank($credentials['secret'] ?? null)) {
            throw new RuntimeException("WayForPay credentials are not configured for merchant {$code}.");
        }

        return $credentials;
    }

    private function credentialsForAccount(string $account): array
    {
        foreach ((array) config('services.payments.wayforpay_merchants', []) as $credentials) {
            if (is_array($credentials) && hash_equals((string) ($credentials['account'] ?? ''), $account)) {
                return $credentials;
            }
        }
        throw new RuntimeException('Unknown WayForPay merchant account.');
    }

    private function sign(array $values, string $secret): string
    {
        return hash_hmac('md5', implode(';', array_map(static fn ($value): string => (string) $value, $values)), $secret);
    }

    private function amount(int $kopecks): string
    {
        return number_format($kopecks / 100, 2, '.', '');
    }
}
