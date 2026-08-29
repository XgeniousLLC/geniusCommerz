<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** GIG Logistics — Nigerian nationwide delivery. */
class GigLogisticsCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://giglthirdpartyapi.azurewebsites.net'
            : 'https://thirdpartyapi.azurewebsites.net';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post($this->base().'/api/thirdparty/price', [
            'ReceiverAddress'     => $shipment->to['street1'] ?? null,
            'ReceiverStationId'   => (int) $this->cred('receiver_station_id', 0),
            'SenderStationId'     => (int) $this->cred('sender_station_id', 0),
            'ReceiverLocality'    => $shipment->to['city'] ?? null,
            'VehicleType'         => 'BIKE',
            'PreShipmentItems'    => [[
                'Weight'      => $this->weightIn($shipment, 'kg'),
                'ItemName'    => 'Order',
                'Quantity'    => 1,
                'Value'       => (string) ($shipment->declaredValue ?? 0),
                'ShipmentType' => 'Regular',
            ]],
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $amount = $response->json('Object.GrandTotal') ?? $response->json('Object.DeliveryPrice');

        return $amount === null
            ? []
            : [$this->quote((float) $amount, 'NGN', 'GIGL Standard', raw: $response->json('Object') ?? [])];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get($this->base().'/api/thirdparty/track/'.$trackingCode);

        return [
            'status' => (string) ($response->json('Object.0.Status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'GIG Logistics';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->token())->timeout(30)->acceptJson();
    }

    private function token(): string
    {
        return Cache::remember('gigl_token_'.($this->integration->environment ?: 'sandbox'), 3000, function () {
            $response = Http::timeout(20)->post($this->base().'/api/thirdparty/login', [
                'username' => $this->cred('username'),
                'password' => $this->cred('password'),
            ]);

            $token = $response->json('Object.access_token') ?? $response->json('access_token');

            if (! $token) {
                throw new \RuntimeException('GIG Logistics authentication failed.');
            }

            return (string) $token;
        });
    }
}
