<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\Integration;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class SteadfastService implements CourierInterface
{
    private Integration $integration;

    public function __construct()
    {
        $this->integration = Integration::forProvider('steadfast')
            ?? new Integration(['credentials' => [], 'environment' => 'live']);
    }

    public function createOrder(Order $order, array $extra = []): array
    {
        $shipping = $order->shipping_address ?? [];

        $payload = [
            'invoice'             => $order->order_number,
            'recipient_name'      => $order->customer_name,
            'recipient_phone'     => $order->customer_phone,
            'recipient_address'   => $shipping['address'] ?? ($shipping['line1'] ?? ''),
            'cod_amount'          => (float) ($extra['cod_amount'] ?? $order->total),
            'note'                => $order->notes ?? '',
        ];

        $response = $this->http()->post('/create_order', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Steadfast createOrder failed: ' . $response->body());
        }

        $data = $response->json();
        return [
            'consignment_id' => $data['consignment']['consignment_id'] ?? null,
            'tracking_code'  => $data['consignment']['tracking_code'] ?? null,
            'raw'            => $data,
        ];
    }

    public function getStatus(string $consignmentId): array
    {
        $response = $this->http()->get("/status_by_cid/{$consignmentId}");

        $data = $response->json();
        return [
            'status' => $data['delivery_status'] ?? 'unknown',
            'raw'    => $data,
        ];
    }

    public function getCities(): array
    {
        // Steadfast doesn't expose a city/zone API; locations are text-based
        return [];
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
        // Steadfast uses fixed pricing per contract
        return null;
    }

    public function name(): string
    {
        return 'Steadfast';
    }

    private function http()
    {
        return Http::withHeaders([
            'Api-Key'    => $this->integration->getCredential('api_key'),
            'Secret-Key' => $this->integration->getCredential('secret_key'),
            'Content-Type' => 'application/json',
        ])->baseUrl($this->baseUrl());
    }

    private function baseUrl(): string
    {
        return rtrim(
            $this->integration->getCredential('base_url', 'https://portal.steadfast.com.bd/api/v1'),
            '/'
        );
    }
}
