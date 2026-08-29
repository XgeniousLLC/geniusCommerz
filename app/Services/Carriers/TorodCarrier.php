<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Torod — Saudi multi-carrier aggregator (SMSA, Aramex, Naqel and others in one call). */
class TorodCarrier extends CarrierDriver
{
    private const API = 'https://api.torod.co/api/v1';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post(self::API.'/shipping-rates', [
            'origin_city'      => $shipment->from['city'] ?? null,
            'destination_city' => $shipment->to['city'] ?? null,
            'origin_country'   => $shipment->from['country'] ?? 'SA',
            'destination_country' => $shipment->to['country'] ?? 'SA',
            'weight'           => $this->weightIn($shipment, 'kg'),
            'cod_amount'       => 0,
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json('data', []) as $option) {
            $quotes[] = $this->quote(
                (float) ($option['price'] ?? 0),
                $option['currency'] ?? 'SAR',
                $option['courier_name'] ?? 'Torod',
                isset($option['delivery_days']) ? (int) $option['delivery_days'] : null,
                (string) ($option['courier_id'] ?? ''),
                $option,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get(self::API.'/track/'.$trackingCode);

        return [
            'status' => (string) ($response->json('data.status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Torod';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken((string) $this->cred('api_token'))->timeout(30)->acceptJson();
    }
}
