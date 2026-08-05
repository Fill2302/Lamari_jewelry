<?php

namespace App\Payments;

use App\Contracts\Payments\PaymentCallback;
use App\Contracts\Payments\PaymentProvider;
use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MonoPaymentProvider implements PaymentProvider
{
    public function name(): string
    {
        return 'mono';
    }

    public function createPayment(Payment $payment): array
    {
        $payment->loadMissing('order.items');

        $response = $this->client()->post('/api/merchant/invoice/create', [
            'amount' => $payment->amount,
            'ccy' => 980,
            'merchantPaymInfo' => [
                'reference' => $payment->idempotency_key,
                'destination' => $this->paymentDestination($payment),
                'comment' => 'Замовлення '.$payment->order->number,
                'customerEmails' => array_values(array_filter([$payment->order->email])),
                'basketOrder' => $payment->order->items->map(fn ($item): array => [
                    'name' => mb_substr($item->name, 0, 128),
                    'qty' => $item->quantity,
                    'sum' => $item->unit_price_amount,
                    'total' => $item->total_amount,
                    'unit' => 'шт.',
                    'code' => mb_substr($item->sku, 0, 64),
                ])->values()->all(),
            ],
            'redirectUrl' => route('payments.mono.return', $payment),
            'webHookUrl' => route('payments.mono.webhook'),
            'validity' => 3600,
            'paymentType' => 'debit',
        ])->throw()->json();

        $invoiceId = (string) ($response['invoiceId'] ?? '');
        $pageUrl = (string) ($response['pageUrl'] ?? '');
        if ($invoiceId === '' || $pageUrl === '') {
            throw new RuntimeException('Mono returned an incomplete invoice response.');
        }

        $payment->update([
            'provider_payment_id' => $invoiceId,
            'payload' => ['invoice' => $response],
        ]);

        return ['payment_id' => $invoiceId, 'checkout_url' => $pageUrl];
    }

    private function paymentDestination(Payment $payment): string
    {
        $names = $payment->order->items
            ->pluck('name')
            ->map(fn (string $name): string => trim(explode(' — ', $name, 2)[0]))
            ->filter()
            ->unique()
            ->implode(', ');

        return mb_substr($names !== '' ? $names : 'Біжутерія', 0, 128);
    }

    public function verifySignature(string $payload, ?string $signature): bool
    {
        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $decodedSignature = base64_decode($signature, true);
        $decodedKey = base64_decode($this->publicKey(), true);
        if ($decodedSignature === false || $decodedKey === false) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($decodedKey);
        if ($publicKey === false) {
            return false;
        }

        return openssl_verify($payload, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    public function parseCallback(array $payload): PaymentCallback
    {
        $invoiceId = (string) ($payload['invoiceId'] ?? '');
        if ($invoiceId === '') {
            throw new RuntimeException('Mono callback has no invoiceId.');
        }

        return new PaymentCallback(
            externalId: $invoiceId.':'.((string) ($payload['modifiedDate'] ?? $payload['status'] ?? 'unknown')),
            paymentId: $invoiceId,
            status: match ((string) ($payload['status'] ?? '')) {
                'success' => 'paid',
                'failure' => 'failed',
                'expired' => 'expired',
                'reversed' => 'refunded',
                default => 'pending',
            },
            payload: $payload,
            amount: isset($payload['amount']) ? (int) $payload['amount'] : null,
            currency: isset($payload['ccy']) ? (int) $payload['ccy'] : null,
            reference: isset($payload['reference']) ? (string) $payload['reference'] : null,
        );
    }

    private function client(): PendingRequest
    {
        $token = (string) config('services.payments.mono_token');
        if ($token === '') {
            throw new RuntimeException('Mono merchant token is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.payments.mono_base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders(['X-Token' => $token, 'X-Cms' => 'Lamari', 'X-Cms-Version' => '1'])
            ->connectTimeout(5)
            ->timeout(15)
            ->retry(2, 250, throw: false);
    }

    private function publicKey(): string
    {
        $configured = trim((string) config('services.payments.mono_public_key'));
        if ($configured !== '') {
            return $configured;
        }

        return Cache::remember('payments.mono.public_key', now()->addDay(), function (): string {
            $key = (string) ($this->client()->get('/api/merchant/pubkey')->throw()->json('key') ?? '');
            if ($key === '') {
                throw new RuntimeException('Mono public key is unavailable.');
            }

            return $key;
        });
    }
}
