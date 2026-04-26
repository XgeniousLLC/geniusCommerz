<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\Integration;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PathaoService implements CourierInterface
{
    private Integration $integration;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->integration = Integration::forProvider('pathao')
            ?? new Integration(['credentials' => [], 'environment' => 'sandbox']);
    }

    public function createOrder(Order $order, array $extra = []): array
    {
        $shipping = $order->shipping_address ?? [];

        $payload = array_merge([
            'store_id'            => $extra['store_id'] ?? $this->integration->getCredential('store_id'),
            'merchant_order_id'   => $order->order_number,
            'recipient_name'      => $order->customer_name,
            'recipient_phone'     => $order->customer_phone,
            'recipient_address'   => $shipping['address'] ?? ($shipping['line1'] ?? ''),
            'recipient_city'      => $extra['city_id'] ?? null,
            'recipient_zone'      => $extra['zone_id'] ?? null,
            'recipient_area'      => $extra['area_id'] ?? null,
            'delivery_type'       => $extra['delivery_type'] ?? 48,
            'item_type'           => $extra['item_type'] ?? 2,
            'special_instruction' => $order->notes ?? '',
            'item_quantity'       => $order->items()->sum('quantity'),
            'item_weight'         => $extra['item_weight'] ?? 0.5,
            'amount_to_collect'   => $extra['amount_to_collect'] ?? $order->total,
            'item_description'    => $extra['item_description'] ?? '',
        ], $extra);

        $response = Http::withToken($this->token())
            ->post($this->baseUrl() . '/aladdin/api/v1/orders', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Pathao createOrder failed: ' . $response->body());
        }

        $data = $response->json('data', []);
        return [
            'consignment_id' => $data['consignment_id'] ?? null,
            'tracking_code'  => $data['order_tracking_code'] ?? null,
            'raw'            => $data,
        ];
    }

    public function getStatus(string $consignmentId): array
    {
        $response = Http::withToken($this->token())
            ->get($this->baseUrl() . "/aladdin/api/v1/orders/{$consignmentId}/info");

        $data = $response->json('data', []);
        return [
            'status' => $data['order_status'] ?? 'unknown',
            'raw'    => $data,
        ];
    }

    public function getCities(): array
    {
        return Cache::remember('pathao_cities', 86400, function () {
            $response = Http::withToken($this->token())
                ->get($this->baseUrl() . '/aladdin/api/v1/countries/1/city-list');

            return collect($response->json('data.data', []))
                ->map(fn ($c) => ['id' => $c['city_id'], 'name' => $c['city_name']])
                ->values()
                ->all();
        });
    }

    public function getZones(int $cityId): array
    {
        return Cache::remember("pathao_zones_{$cityId}", 86400, function () use ($cityId) {
            $response = Http::withToken($this->token())
                ->get($this->baseUrl() . "/aladdin/api/v1/cities/{$cityId}/zone-list");

            return collect($response->json('data.data', []))
                ->map(fn ($z) => ['id' => $z['zone_id'], 'name' => $z['zone_name']])
                ->values()
                ->all();
        });
    }

    public function getAreas(int $zoneId): array
    {
        return Cache::remember("pathao_areas_{$zoneId}", 86400, function () use ($zoneId) {
            $response = Http::withToken($this->token())
                ->get($this->baseUrl() . "/aladdin/api/v1/zones/{$zoneId}/area-list");

            return collect($response->json('data.data', []))
                ->map(fn ($a) => ['id' => $a['area_id'], 'name' => $a['area_name']])
                ->values()
                ->all();
        });
    }

    public function calculateCharge(array $params): ?float
    {
        $response = Http::withToken($this->token())
            ->post($this->baseUrl() . '/aladdin/api/v1/merchant/price-plan', [
                'store_id'      => $params['store_id'] ?? $this->integration->getCredential('store_id'),
                'item_type'     => $params['item_type'] ?? 2,
                'delivery_type' => $params['delivery_type'] ?? 48,
                'item_weight'   => $params['item_weight'] ?? 0.5,
                'recipient_city' => $params['city_id'] ?? null,
                'recipient_zone' => $params['zone_id'] ?? null,
            ]);

        return $response->json('data.price') ?? null;
    }

    public function name(): string
    {
        return 'Pathao';
    }

    private function token(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $cacheKey = 'pathao_token_' . $this->integration->environment;
        return $this->accessToken = Cache::remember($cacheKey, 3000, function () {
            $response = Http::post($this->baseUrl() . '/aladdin/api/v1/issue-token', [
                'client_id'     => $this->integration->getCredential('client_id'),
                'client_secret' => $this->integration->getCredential('client_secret'),
                'username'      => $this->integration->getCredential('username'),
                'password'      => $this->integration->getCredential('password'),
                'grant_type'    => 'password',
            ]);

            $token = $response->json('token_info.access_token');
            if (! $token) {
                throw new \RuntimeException('Pathao authentication failed: ' . $response->body());
            }
            return $token;
        });
    }

    private function baseUrl(): string
    {
        return rtrim(
            $this->integration->getCredential('base_url',
                $this->integration->environment === 'live'
                    ? 'https://api-hermes.pathao.com'
                    : 'https://hermes-sandbox.pathao.com'
            ),
            '/'
        );
    }
}
