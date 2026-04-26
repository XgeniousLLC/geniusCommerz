<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\Integration;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RedxService implements CourierInterface
{
    private Integration $integration;

    public function __construct()
    {
        $this->integration = Integration::forProvider('redx')
            ?? new Integration(['credentials' => [], 'environment' => 'live']);
    }

    public function createOrder(Order $order, array $extra = []): array
    {
        $shipping = $order->shipping_address ?? [];

        $payload = [
            'name'               => $order->customer_name,
            'phone'              => $order->customer_phone,
            'address'            => $shipping['address'] ?? ($shipping['line1'] ?? ''),
            'merchant_invoice_id'=> $order->order_number,
            'cash_collection_amount' => (float) ($extra['amount_to_collect'] ?? $order->total),
            'delivery_area'      => $extra['area'] ?? ($shipping['area'] ?? ''),
            'delivery_area_id'   => $extra['area_id'] ?? null,
            'parcel_weight'      => $extra['parcel_weight'] ?? 500,
            'instruction'        => $order->notes ?? '',
            'value'              => (float) $order->total,
        ];

        $response = $this->http()->post('/parcel', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('RedX createOrder failed: ' . $response->body());
        }

        $data = $response->json();
        return [
            'consignment_id' => $data['parcel']['tracking_id'] ?? null,
            'tracking_code'  => $data['parcel']['tracking_id'] ?? null,
            'raw'            => $data,
        ];
    }

    public function getStatus(string $consignmentId): array
    {
        $response = $this->http()->get("/parcel/track/{$consignmentId}");

        $data = $response->json();
        return [
            'status' => $data['parcel']['status'] ?? 'unknown',
            'raw'    => $data,
        ];
    }

    public function getCities(): array
    {
        return Cache::remember('redx_areas', 86400, function () {
            $response = $this->http()->get('/areas');
            return collect($response->json('areas', []))
                ->map(fn ($a) => ['id' => $a['id'], 'name' => $a['name']])
                ->values()
                ->all();
        });
    }

    public function getZones(int $cityId): array
    {
        return [];
    }

    public function getAreas(int $zoneId): array
    {
        return [];
    }

    public function calculateCharge(array $params): ?float
    {
        // RedX doesn't expose a charge calculation API; charges are per contract
        return null;
    }

    public function name(): string
    {
        return 'RedX';
    }

    private function http()
    {
        return Http::withHeaders([
            'API-ACCESS-TOKEN' => 'Bearer ' . $this->integration->getCredential('api_key'),
            'Content-Type'     => 'application/json',
        ])->baseUrl($this->baseUrl());
    }

    private function baseUrl(): string
    {
        return rtrim(
            $this->integration->getCredential('base_url', 'https://openapi.redx.com.bd/v1.0.0-beta'),
            '/'
        );
    }
}
