<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Blue Dart (DHL Group India) — domestic express. */
class BlueDartCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://apigateway.bluedart.com'
            : 'https://apigateway-sandbox.bluedart.com';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post($this->base().'/in/transportation/finder/v1/GetServicesforPincodeandProduct', [
            'pPinCode'      => $shipment->to['zip'] ?? null,
            'pProductCode'  => 'A',      // apex / domestic priority
            'pSubProductCode' => 'P',
            'profile'       => $this->profile(),
        ]);

        if ($response->failed()) {
            $this->fail($response, 'serviceability check failed');
        }

        // Blue Dart's finder confirms serviceability; the contracted rate comes from the
        // account, so a configurable base rate stands in rather than a fabricated price.
        if (! ($response->json('GetServicesforPincodeandProductResult.IsError') === false)) {
            return [];
        }

        return [$this->quote(
            (float) $this->cred('base_rate', 0),
            'INR',
            'Domestic Priority',
            raw: $response->json(),
        )];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get($this->base().'/in/transportation/tracking/v1/shipment', [
            'handler'  => 'tnt',
            'loginid'  => $this->cred('login_id'),
            'numbers'  => $trackingCode,
            'format'   => 'json',
            'lickey'   => $this->cred('licence_key'),
            'scan'     => 1,
            'action'   => 'custawbquery',
            'verno'    => 1,
        ]);

        return [
            'status' => (string) ($response->json('ShipmentData.Shipment.0.Status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Blue Dart';
    }

    protected function request(): PendingRequest
    {
        return Http::withHeaders(['JWTToken' => $this->token()])->timeout(30)->acceptJson();
    }

    private function profile(): array
    {
        return [
            'Api_type'   => 'S',
            'LicenceKey' => (string) $this->cred('licence_key'),
            'LoginID'    => (string) $this->cred('login_id'),
        ];
    }

    private function token(): string
    {
        return Cache::remember('bluedart_token_'.($this->integration->environment ?: 'sandbox'), 3000, function () {
            $response = Http::withHeaders([
                'ClientID'     => (string) $this->cred('client_id'),
                'clientSecret' => (string) $this->cred('client_secret'),
            ])->get($this->base().'/in/transportation/token/v1/login');

            if (! $response->json('JWTToken')) {
                throw new \RuntimeException('Blue Dart authentication failed.');
            }

            return (string) $response->json('JWTToken');
        });
    }
}
