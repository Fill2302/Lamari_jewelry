<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SalesDriveClient
{
    public function createOrder(array $data): int
    {
        $response = $this->orders()->post('/handler/', ['getResultData' => 1, ...$data])->throw()->json();
        $id = (int) data_get($response, 'data.orderId', 0);
        if (! ($response['success'] ?? false) || $id < 1) {
            throw new RuntimeException('SalesDrive did not return a created order ID.');
        }

        return $id;
    }

    public function updateOrder(int $id, array $data): void
    {
        $response = $this->orders()->post('/api/order/update/', compact('id', 'data'))->throw()->json();
        if (! ($response['success'] ?? false)) {
            throw new RuntimeException('SalesDrive rejected the order update.');
        }
    }

    public function addPayment(array $data): int
    {
        $response = $this->payments()->post('/api/payment/', $data)->throw()->json();
        $id = (int) data_get($response, 'data.paymentId', 0);
        if (! ($response['success'] ?? false) || $id < 1) {
            throw new RuntimeException('SalesDrive did not return a created payment ID.');
        }

        return $id;
    }

    public function reference(string $endpoint): array
    {
        $response = $this->orders()->get($endpoint)->throw()->json();
        if (! ($response['success'] ?? false) || ! is_array($response['data'] ?? null)) {
            throw new RuntimeException('SalesDrive reference response is invalid.');
        }

        return $response['data'];
    }

    private function orders(): PendingRequest
    {
        return $this->client((string) config('services.salesdrive.orders_key'));
    }

    private function payments(): PendingRequest
    {
        return $this->client((string) config('services.salesdrive.payments_key'));
    }

    private function client(string $key): PendingRequest
    {
        if ($key === '') {
            throw new RuntimeException('SalesDrive API key is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.salesdrive.base_url'), '/'))
            ->acceptJson()->asJson()->withHeaders(['X-Api-Key' => $key])
            ->connectTimeout(5)->timeout(15)->retry(2, 250, throw: false);
    }
}
