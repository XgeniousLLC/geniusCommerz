<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Shiprocket — Indian multi-carrier aggregator.
 *
 * Returns one quote per courier partner, so the cheapest is genuinely a choice between
 * carriers rather than services of one carrier.
 */
class ShiprocketCarrier extends CarrierDriver
{
    private const API = 'https://apiv2.shiprocket.in/v1/external';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->get(self::API.'/courier/serviceability/', [
            'pickup_postcode'   => $shipment->from['zip'] ?? null,
            'delivery_postcode' => $shipment->to['zip'] ?? null,
            'weight'            => $this->weightIn($shipment, 'kg'),
            'cod'               => 0,
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json('data.available_courier_companies', []) as $courier) {
            $quotes[] = $this->quote(
                (float) ($courier['rate'] ?? 0),
                'INR',
                $courier['courier_name'] ?? 'Courier',
                isset($courier['estimated_delivery_days']) ? (int) $courier['estimated_delivery_days'] : null,
                (string) ($courier['courier_company_id'] ?? ''),
                $courier,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get(self::API.'/courier/track/awb/'.$trackingCode);

        return [
            'status' => (string) ($response->json('tracking_data.shipment_track.0.current_status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Shiprocket';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->token())->timeout(30)->acceptJson();
    }

    private function token(): string
    {
        return Cache::remember('shiprocket_token', 3000, function () {
            $response = Http::timeout(20)->post(self::API.'/auth/login', [
                'email'    => $this->cred('email'),
                'password' => $this->cred('password'),
            ]);

            if (! $response->json('token')) {
                throw new \RuntimeException('Shiprocket authentication failed.');
            }

            return (string) $response->json('token');
        });
    }
}
