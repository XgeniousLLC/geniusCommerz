<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Shippo — global aggregator, an alternative to EasyPost with different carrier coverage. */
class ShippoCarrier extends CarrierDriver
{
    private const API = 'https://api.goshippo.com';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post(self::API.'/shipments/', [
            'address_from' => $this->address($shipment->from),
            'address_to'   => $this->address($shipment->to),
            'parcels'      => [[
                'length'        => (string) ($shipment->length ?? 20),
                'width'         => (string) ($shipment->width ?? 15),
                'height'        => (string) ($shipment->height ?? 10),
                'distance_unit' => 'cm',
                'weight'        => (string) $this->weightIn($shipment, 'kg'),
                'mass_unit'     => 'kg',
            ]],
            'async' => false,
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json('rates', []) as $rate) {
            $quotes[] = $this->quote(
                (float) ($rate['amount'] ?? 0),
                $rate['currency'] ?? 'USD',
                trim(($rate['provider'] ?? '').' '.($rate['servicelevel']['name'] ?? '')),
                isset($rate['estimated_days']) ? (int) $rate['estimated_days'] : null,
                $rate['object_id'] ?? null,
                $rate,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function buyLabel(string $rateId): array
    {
        $response = $this->request()->post(self::API.'/transactions/', [
            'rate'            => $rateId,
            'label_file_type' => 'PDF',
            'async'           => false,
        ]);

        if ($response->failed() || $response->json('status') === 'ERROR') {
            $this->fail($response, 'label purchase failed');
        }

        return [
            'tracking_code' => (string) $response->json('tracking_number'),
            'label_url'     => $response->json('label_url'),
            'carrier'       => $response->json('rate.provider'),
            'raw'           => $response->json() ?? [],
        ];
    }

    public function track(string $trackingCode): array
    {
        $carrier  = (string) $this->cred('default_carrier', 'shippo');
        $response = $this->request()->get(self::API."/tracks/{$carrier}/{$trackingCode}");

        return [
            'status' => (string) ($response->json('tracking_status.status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Shippo';
    }

    protected function request(): PendingRequest
    {
        return Http::withHeaders(['Authorization' => 'ShippoToken '.$this->cred('api_token')])
            ->timeout(30)->acceptJson();
    }

    /** @param array<string, mixed> $address */
    private function address(array $address): array
    {
        return array_filter([
            'name'    => $address['name'] ?? 'Customer',
            'street1' => $address['street1'] ?? null,
            'city'    => $address['city'] ?? null,
            'state'   => $address['state'] ?? null,
            'zip'     => $address['zip'] ?? null,
            'country' => $address['country'] ?? null,
            'phone'   => $address['phone'] ?? null,
            'email'   => $address['email'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
