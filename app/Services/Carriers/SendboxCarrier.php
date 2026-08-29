<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Sendbox — Nigerian logistics with domestic and international lanes. */
class SendboxCarrier extends CarrierDriver
{
    private const API = 'https://live.sendbox.co';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post(self::API.'/shipping/shipment_delivery_quote', [
            'origin_city'         => $shipment->from['city'] ?? null,
            'origin_state'        => $shipment->from['state'] ?? null,
            'origin_country'      => $shipment->from['country'] ?? 'NG',
            'destination_city'    => $shipment->to['city'] ?? null,
            'destination_state'   => $shipment->to['state'] ?? null,
            'destination_country' => $shipment->to['country'] ?? 'NG',
            'weight'              => $this->weightIn($shipment, 'kg'),
            'items'               => [[
                'name'     => 'Order',
                'quantity' => 1,
                'weight'   => $this->weightIn($shipment, 'kg'),
                'value'    => $shipment->declaredValue ?? 0,
            ]],
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json('rates', []) as $rate) {
            $quotes[] = $this->quote(
                (float) ($rate['fee'] ?? $rate['amount'] ?? 0),
                $rate['currency'] ?? 'NGN',
                $rate['name'] ?? 'Standard',
                isset($rate['delivery_days']) ? (int) $rate['delivery_days'] : null,
                $rate['code'] ?? null,
                $rate,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get(self::API.'/shipping/tracking/'.$trackingCode);

        return [
            'status' => (string) ($response->json('status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Sendbox';
    }

    protected function request(): PendingRequest
    {
        return Http::withHeaders(['Authorization' => (string) $this->cred('app_id')])
            ->withToken((string) $this->cred('access_token'))
            ->timeout(30)->acceptJson();
    }
}
