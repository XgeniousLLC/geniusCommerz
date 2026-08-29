<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Loggi — Brazilian last-mile delivery.
 *
 * Loggi's public API is GraphQL rather than REST, so quoting is a single query document
 * instead of a path.
 */
class LoggiCarrier extends CarrierDriver
{
    private const API = 'https://www.loggi.com/graphql/';

    public function rates(ShipmentRequest $shipment): array
    {
        $query = <<<'GQL'
        query Estimate($shopId: ID!, $zip: String!, $weight: Float!) {
          shipmentEstimate(shopId: $shopId, destinationZip: $zip, weightGrams: $weight) {
            price
            estimatedDays
            serviceName
          }
        }
        GQL;

        $response = $this->request()->post(self::API, [
            'query'     => $query,
            'variables' => [
                'shopId' => (string) $this->cred('shop_id'),
                'zip'    => preg_replace('/\D+/', '', $shipment->to['zip'] ?? ''),
                'weight' => $this->weightIn($shipment, 'g'),
            ],
        ]);

        if ($response->failed() || $response->json('errors')) {
            $this->fail($response, 'rating failed');
        }

        $estimate = $response->json('data.shipmentEstimate');

        return $estimate === null ? [] : [$this->quote(
            (float) ($estimate['price'] ?? 0),
            'BRL',
            $estimate['serviceName'] ?? 'Loggi',
            isset($estimate['estimatedDays']) ? (int) $estimate['estimatedDays'] : null,
            raw: $estimate,
        )];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->post(self::API, [
            'query'     => 'query Track($code: String!) { packageStatus(trackingCode: $code) { status } }',
            'variables' => ['code' => $trackingCode],
        ]);

        return [
            'status' => (string) ($response->json('data.packageStatus.status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Loggi';
    }

    protected function request(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'ApiKey '.$this->cred('email').':'.$this->cred('api_key'),
        ])->timeout(30)->acceptJson();
    }
}
