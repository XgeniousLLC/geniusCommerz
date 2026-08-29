<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Kwik Delivery — Nigerian same-day and on-demand delivery. */
class KwikCarrier extends CarrierDriver
{
    private const API = 'https://api.kwik.delivery';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post(self::API.'/v1/get_delivery_charges', [
            'access_token'  => $this->token(),
            'domain_name'   => $this->cred('domain_name'),
            'vendor_id'     => $this->cred('vendor_id'),
            'pickup_city'   => $shipment->from['city'] ?? null,
            'delivery_city' => $shipment->to['city'] ?? null,
            'total_weight'  => $this->weightIn($shipment, 'kg'),
            'is_multiple_tasks' => 0,
        ]);

        if ($response->failed() || (int) ($response->json('status') ?? 0) !== 200) {
            $this->fail($response, 'rating failed');
        }

        $amount = $response->json('data.total_amount') ?? $response->json('data.amount');

        return $amount === null
            ? []
            : [$this->quote((float) $amount, 'NGN', 'Kwik Same-day', 1, raw: $response->json('data') ?? [])];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->post(self::API.'/v1/track_job', [
            'access_token' => $this->token(),
            'domain_name'  => $this->cred('domain_name'),
            'job_id'       => $trackingCode,
        ]);

        return [
            'status' => (string) ($response->json('data.job_status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Kwik Delivery';
    }

    protected function request(): PendingRequest
    {
        return Http::timeout(30)->acceptJson();
    }

    private function token(): string
    {
        return Cache::remember('kwik_token', 3000, function () {
            $response = Http::timeout(20)->post(self::API.'/v1/vendor_login', [
                'email'       => $this->cred('email'),
                'password'    => $this->cred('password'),
                'domain_name' => $this->cred('domain_name'),
            ]);

            if (! $response->json('data.access_token')) {
                throw new \RuntimeException('Kwik authentication failed.');
            }

            return (string) $response->json('data.access_token');
        });
    }
}
