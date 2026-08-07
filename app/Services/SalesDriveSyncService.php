<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class SalesDriveSyncService
{
    public function __construct(private SalesDriveClient $client) {}

    public function enabled(): bool
    {
        return (bool) config('services.salesdrive.enabled');
    }

    public function syncPending(Order $order): void
    {
        if (! $this->enabled() || $order->salesdrive_created_at) {
            return;
        }

        try {
            $order->loadMissing('items');
            $name = preg_split('/\s+/', trim($order->customer_name), 2);
            $shipping = $order->shipping_address ?? [];
            $attribution = data_get($order->marketing_attribution, 'last_touch', []);
            $paymentMethod = $this->referenceValue('/api/payment-methods/', (string) config('services.salesdrive.payment_method'));
            $deliveryMethod = $this->referenceValue('/api/delivery-methods/', (string) config('services.salesdrive.delivery_method'));

            $id = $order->salesdrive_order_id ?: $this->client->createOrder([
                'fName' => $name[0] ?? $order->customer_name,
                'lName' => $name[1] ?? '',
                'phone' => $order->phone,
                'email' => $order->email,
                'products' => $order->items->map(fn ($item): array => [
                    'id' => $this->externalSku($item->sku, $item->name),
                    'name' => $item->name,
                    'costPerItem' => $item->unit_price_amount / 100,
                    'amount' => $item->quantity,
                    'sku' => $this->externalSku($item->sku, $item->name),
                ])->values()->all(),
                'payment_method' => $paymentMethod,
                'shipping_method' => $deliveryMethod,
                'shipping_address' => trim(($shipping['city'] ?? '').', '.($shipping['address'] ?? ''), ', '),
                'novaposhta' => [
                    'ServiceType' => 'Warehouse',
                    'payer' => 'recipient',
                    'city' => $shipping['city'] ?? '',
                    'WarehouseNumber' => $this->warehouseNumber((string) ($shipping['address'] ?? '')),
                ],
                'comment' => 'Замовлення з '.config('services.salesdrive.source').'. '.($order->payment_method === 'wayforpay_deposit' ? 'Передплата 150 грн через WayForPay, залишок післяплатою.' : 'Передоплата онлайн.'),
                'externalId' => $order->number,
                'sajt' => (string) config('services.salesdrive.source'),
                'utmSourceFull' => $attribution['landing_url'] ?? null,
                'utmSource' => $attribution['utm_source'] ?? null,
                'utmMedium' => $attribution['utm_medium'] ?? null,
                'utmCampaign' => $attribution['utm_campaign'] ?? null,
                'utmContent' => $attribution['utm_content'] ?? null,
                'utmTerm' => $attribution['utm_term'] ?? null,
                'utmPage' => $attribution['landing_url'] ?? null,
            ]);
            if (! $order->salesdrive_order_id) {
                $order->update(['salesdrive_order_id' => $id]);
            }
            $this->client->updateOrder($id, ['statusId' => $this->statusId((string) config('services.salesdrive.pending_status'))]);
            $order->update(['salesdrive_created_at' => now(), 'salesdrive_sync_error' => null]);
        } catch (Throwable $e) {
            $order->update(['salesdrive_sync_error' => mb_substr($e->getMessage(), 0, 2000)]);
            throw $e;
        }
    }

    public function syncPaid(Payment $payment): void
    {
        if (! $this->enabled() || $payment->salesdrive_payment_id) {
            return;
        }

        $order = $payment->order;
        $this->syncPending($order);
        $this->client->updateOrder($order->salesdrive_order_id, [
            'statusId' => $this->statusId((string) config($order->payment_method === 'wayforpay_deposit' ? 'services.salesdrive.deposit_status' : 'services.salesdrive.paid_status')),
            'paymentDate' => now()->toDateTimeString(),
        ]);
        if ((int) config('services.salesdrive.organization_id') < 1 || blank(config('services.salesdrive.account_number'))) {
            throw new RuntimeException('SalesDrive organization and account are not configured.');
        }
        $paymentId = $this->client->addPayment([
            'organizationId' => (int) config('services.salesdrive.organization_id'),
            'datetime' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'accountNumber' => (string) config('services.salesdrive.account_number'),
            'sum' => $payment->amount / 100,
            'description' => ($payment->provider === 'wayforpay' ? 'Передплата WayForPay за ' : 'Передоплата Mono за ').$order->number,
            'uniqueId' => 'lamari-'.$payment->provider.'-'.$payment->idempotency_key,
            'orderId' => $order->salesdrive_order_id,
            'orderExternalId' => $order->number,
            'autoAttachToOrderType' => 'order_id',
        ]);
        $payment->update(['salesdrive_payment_id' => $paymentId]);
        $order->update(['salesdrive_paid_at' => now(), 'salesdrive_sync_error' => null]);
    }

    private function statusId(string $name): int
    {
        $value = $this->reference('/api/statuses/', $name);

        return (int) $value['id'];
    }

    private function referenceValue(string $endpoint, string $name): string
    {
        $value = $this->reference($endpoint, $name);

        return (string) ($value['parameter'] ?? $value['id']);
    }

    private function reference(string $endpoint, string $name): array
    {
        $items = Cache::remember('salesdrive.ref.'.md5($endpoint), now()->addMinutes(10), fn () => $this->client->reference($endpoint));
        $match = collect($items)->first(fn (array $item): bool => mb_strtolower(trim((string) ($item['name'] ?? ''))) === mb_strtolower(trim($name)));
        if (! $match) {
            throw new RuntimeException("SalesDrive value not found: {$name}");
        }

        return $match;
    }

    private function warehouseNumber(string $address): string
    {
        return preg_match('/(?:№|#|відділення\s*)\s*(\d+)/iu', $address, $matches) ? $matches[1] : $address;
    }

    private function externalSku(string $sku, string $itemName): string
    {
        if (! preg_match('/\s—\s*(\d+(?:[.,]\d+)?)\s*см\s*$/iu', $itemName, $matches)) {
            return $sku;
        }

        $length = preg_quote($matches[1], '/');

        return preg_replace('/-'.$length.'$/u', '', $sku) ?: $sku;
    }
}
