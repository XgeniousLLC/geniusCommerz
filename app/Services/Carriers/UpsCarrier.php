<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** UPS — global parcel. */
class UpsCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive() ? 'https://onlinetools.ups.com' : 'https://wwwcie.ups.com';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post($this->base().'/api/rating/v2409/Shop', [
            'RateRequest' => [
                'Request' => ['RequestOption' => 'Shop'],
                'Shipment' => [
                    'Shipper' => [
                        'ShipperNumber' => (string) $this->cred('account_number'),
                        'Address' => $this->address($shipment->from),
                    ],
                    'ShipTo'   => ['Address' => $this->address($shipment->to)],
                    'ShipFrom' => ['Address' => $this->address($shipment->from)],
                    'Package'  => [[
                        'PackagingType' => ['Code' => '02'],
                        'PackageWeight' => [
                            'UnitOfMeasurement' => ['Code' => 'KGS'],
                            'Weight' => (string) $this->weightIn($shipment, 'kg'),
                        ],
                    ]],
                ],
            ],
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $rated  = $response->json('RateResponse.RatedShipment', []);
        // UPS collapses a single result into an object rather than a list.
        $rated  = isset($rated['Service']) ? [$rated] : $rated;
        $quotes = [];

        foreach ($rated as $rate) {
            $quotes[] = $this->quote(
                (float) ($rate['TotalCharges']['MonetaryValue'] ?? 0),
                $rate['TotalCharges']['CurrencyCode'] ?? 'USD',
                'UPS '.($rate['Service']['Code'] ?? ''),
                isset($rate['GuaranteedDelivery']['BusinessDaysInTransit'])
                    ? (int) $rate['GuaranteedDelivery']['BusinessDaysInTransit'] : null,
                $rate['Service']['Code'] ?? null,
                $rate,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()
            ->withHeaders(['transId' => uniqid(), 'transactionSrc' => 'geniuscommerz'])
            ->get($this->base().'/api/track/v1/details/'.$trackingCode);

        return [
            'status' => (string) ($response->json('trackResponse.shipment.0.package.0.currentStatus.description') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'UPS';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->token())->timeout(30)->acceptJson();
    }

    private function token(): string
    {
        return Cache::remember('ups_token_'.($this->integration->environment ?: 'sandbox'), 3000, function () {
            $response = Http::asForm()
                ->withBasicAuth((string) $this->cred('client_id'), (string) $this->cred('client_secret'))
                ->timeout(20)
                ->post($this->base().'/security/v1/oauth/token', ['grant_type' => 'client_credentials']);

            if (! $response->json('access_token')) {
                throw new \RuntimeException('UPS authentication failed.');
            }

            return (string) $response->json('access_token');
        });
    }

    /** @param array<string, mixed> $address */
    private function address(array $address): array
    {
        return array_filter([
            'AddressLine'       => $address['street1'] ?? null,
            'City'              => $address['city'] ?? null,
            'StateProvinceCode' => $address['state'] ?? null,
            'PostalCode'        => $address['zip'] ?? null,
            'CountryCode'       => $address['country'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
