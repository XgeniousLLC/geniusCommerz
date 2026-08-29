<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Naqel Express — Saudi Arabia and the wider Gulf. */
class NaqelCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://api.naqelexpress.com/api'
            : 'https://apitest.naqelexpress.com/api';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post($this->base().'/Shipment/CalculatePrice', [
            'ClientID'        => $this->cred('client_id'),
            'Password'        => $this->cred('password'),
            'ClientAddress'   => ['CityCode' => $shipment->from['city'] ?? null],
            'ConsigneeAddress' => ['CityCode' => $shipment->to['city'] ?? null],
            'Weight'          => $this->weightIn($shipment, 'kg'),
            'PiecesCount'     => 1,
            'CODAmount'       => 0,
            'DestinationCountry' => $shipment->to['country'] ?? 'SA',
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $amount = $response->json('TotalAmount') ?? $response->json('Total');

        return $amount === null ? [] : [$this->quote(
            (float) $amount,
            'SAR',
            $shipment->isInternational() ? 'Naqel International' : 'Naqel Domestic',
            raw: $response->json(),
        )];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->post($this->base().'/Tracking/GetWaybillTracking', [
            'ClientID'  => $this->cred('client_id'),
            'Password'  => $this->cred('password'),
            'WaybillNo' => $trackingCode,
        ]);

        return [
            'status' => (string) ($response->json('Tracking.0.ActivityDescription') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Naqel Express';
    }

    protected function request(): PendingRequest
    {
        return Http::timeout(30)->acceptJson();
    }
}
