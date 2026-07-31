<?php

namespace App\Services;

use App\Models\IntegrationCredential;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

class NovaPoshtaService
{
    private const ENDPOINT = 'https://api.novaposhta.ua/v2.0/json/';

    public function cities(string $query, int $limit = 20): array
    {
        $key = 'nova-poshta:cities:'.sha1(Str::lower(trim($query)).':'.$limit);

        return Cache::remember($key, now()->addDay(), fn () => collect($this->call('Address', 'getCities', [
                'FindByString' => $query,
                'Limit' => min($limit, 50),
            ]))->map(fn (array $city) => [
                'ref' => $city['Ref'],
                'name' => $city['Description'],
                'area' => $city['AreaDescription'] ?? null,
                'type' => $city['SettlementTypeDescription'] ?? null,
            ])->values()->all());
    }

    public function warehouses(string $cityRef, string $query = '', int $limit = 30): array
    {
        $key = 'nova-poshta:warehouses:'.sha1($cityRef.':'.Str::lower(trim($query)).':'.$limit);

        return Cache::remember($key, now()->addHours(6), function () use ($cityRef, $query, $limit): array {
            $warehouses = collect($this->call('Address', 'getWarehouses', [
                'CityRef' => $cityRef,
                'FindByString' => $query,
                'Limit' => min($limit, 50),
            ]))->map(fn (array $warehouse) => $this->formatWarehouse($warehouse))->values()->all();

            foreach ($warehouses as $warehouse) {
                Cache::put('nova-poshta:warehouse:'.$warehouse['ref'], $warehouse, now()->addHours(6));
            }

            return $warehouses;
        });
    }

    public function warehouse(string $cityRef, string $warehouseRef): ?array
    {
        $cached = Cache::get('nova-poshta:warehouse:'.$warehouseRef);
        if ($cached) {
            return $cached;
        }

        $warehouse = collect($this->call('Address', 'getWarehouses', [
            'CityRef' => $cityRef,
            'WarehouseId' => $warehouseRef,
            'Limit' => 1,
        ]))->first();

        if (! $warehouse || ($warehouse['CityRef'] ?? null) !== $cityRef) {
            return null;
        }

        return $this->formatWarehouse($warehouse);
    }

    private function formatWarehouse(array $warehouse): array
    {
        return [
            'ref' => $warehouse['Ref'],
            'name' => $warehouse['Description'],
            'address' => $warehouse['ShortAddress'] ?? null,
            'number' => $warehouse['Number'] ?? null,
            'category' => $warehouse['CategoryOfWarehouse'] ?? null,
        ];
    }

    private function call(string $model, string $method, array $properties): array
    {
        $credential = IntegrationCredential::where('provider', 'nova_poshta')->first();

        if (! $credential || ! $credential->is_active || blank($credential->api_key)) {
            throw new RuntimeException('Інтеграція Нової пошти ще не налаштована.');
        }

        $response = $this->client()->post(self::ENDPOINT, [
            'apiKey' => $credential->api_key,
            'modelName' => $model,
            'calledMethod' => $method,
            'methodProperties' => $properties,
        ])->throw()->json();

        if (! ($response['success'] ?? false)) {
            throw new RuntimeException(collect($response['errors'] ?? [])->first() ?: 'Нова пошта тимчасово недоступна.');
        }

        return $response['data'] ?? [];
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->asJson()->timeout(12)->retry(2, 200);
    }
}
