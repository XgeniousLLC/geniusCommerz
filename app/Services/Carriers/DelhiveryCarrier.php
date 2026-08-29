<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Delhivery — India's largest logistics network. */
class DelhiveryCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive() ? 'https://track.delhivery.com' : 'https://staging-express.delhivery.com';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->get($this->base().'/api/kinko/v1/invoice/charges/.json', [
            'md'   => 'S',                                   // surface
            'ss'   => 'Delivered',
            'd_pin' => $shipment->to['zip'] ?? null,
            'o_pin' => $shipment->from['zip'] ?? null,
            'cgm'  => $this->weightIn($shipment, 'g'),       // Delhivery charges in grams
            'pt'   => 'Pre-paid',
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $charge = $response->json('0.total_amount');

        if ($charge === null) {
            return [];
        }

        return [$this->quote((float) $charge, 'INR', 'Surface', raw: $response->json())];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get($this->base().'/api/v1/packages/json/', ['waybill' => $trackingCode]);

        return [
            'status' => (string) ($response->json('ShipmentData.0.Shipment.Status.Status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Delhivery';
    }

    protected function request(): PendingRequest
    {
        return Http::withHeaders(['Authorization' => 'Token '.$this->cred('api_token')])
            ->timeout(30)->acceptJson();
    }
}
