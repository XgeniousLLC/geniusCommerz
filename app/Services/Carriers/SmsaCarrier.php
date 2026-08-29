<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** SMSA Express — Saudi Arabia's largest domestic express network. */
class SmsaCarrier extends CarrierDriver
{
    private const API = 'https://ecomapis.smsaexpress.com/api';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post(self::API.'/c/rate', [
            'OriginCity'      => $shipment->from['city'] ?? null,
            'DestinationCity' => $shipment->to['city'] ?? null,
            'Weight'          => $this->weightIn($shipment, 'kg'),
            'PickupDate'      => now()->format('Y-m-d'),
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $amount = $response->json('Total') ?? $response->json('rate');

        return $amount === null ? [] : [$this->quote(
            (float) $amount,
            'SAR',
            'SMSA Domestic',
            raw: $response->json(),
        )];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get(self::API.'/c/track/'.$trackingCode);

        return [
            'status' => (string) ($response->json('Waybills.0.Status') ?? $response->json('status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'SMSA Express';
    }

    protected function request(): PendingRequest
    {
        return Http::withHeaders(['apikey' => (string) $this->cred('api_key')])
            ->timeout(30)->acceptJson();
    }
}
