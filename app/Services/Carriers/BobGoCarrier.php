<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Bob Go — South African multi-carrier aggregator (Courier Guy, Fastway, PostNet…). */
class BobGoCarrier extends CarrierDriver
{
    private const API = 'https://api.bobgo.co.za';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post(self::API.'/rates', [
            'collection_address' => $this->address($shipment->from),
            'delivery_address'   => $this->address($shipment->to),
            'parcels' => [[
                'submitted_length_cm' => $shipment->length ?? 20,
                'submitted_width_cm'  => $shipment->width ?? 20,
                'submitted_height_cm' => $shipment->height ?? 10,
                'submitted_weight_kg' => $this->weightIn($shipment, 'kg'),
            ]],
            'declared_value' => $shipment->declaredValue ?? 0,
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json('rates', []) as $rate) {
            $quotes[] = $this->quote(
                (float) ($rate['rate'] ?? 0),
                'ZAR',
                trim(($rate['provider'] ?? '').' '.($rate['service_level']['name'] ?? '')),
                $rate['service_level']['delivery_date_to'] ?? null ? null : null,
                $rate['id'] ?? null,
                $rate,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get(self::API.'/tracking', ['tracking_reference' => $trackingCode]);

        return [
            'status' => (string) ($response->json('shipments.0.status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Bob Go';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken((string) $this->cred('api_key'))->timeout(30)->acceptJson();
    }

    /** @param array<string, mixed> $address */
    private function address(array $address): array
    {
        return array_filter([
            'company'   => $address['name'] ?? null,
            'street_address' => $address['street1'] ?? null,
            'local_area' => $address['city'] ?? null,
            'city'      => $address['city'] ?? null,
            'zone'      => $address['state'] ?? null,
            'country'   => $address['country'] ?? 'ZA',
            'code'      => $address['zip'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
