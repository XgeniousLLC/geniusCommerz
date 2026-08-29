<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** FedEx — global express and ground. */
class FedExCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive() ? 'https://apis.fedex.com' : 'https://apis-sandbox.fedex.com';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post($this->base().'/rate/v1/rates/quotes', [
            'accountNumber' => ['value' => (string) $this->cred('account_number')],
            'requestedShipment' => [
                'shipper'   => ['address' => $this->address($shipment->from)],
                'recipient' => ['address' => $this->address($shipment->to)],
                'pickupType' => 'DROPOFF_AT_FEDEX_LOCATION',
                'rateRequestType' => ['ACCOUNT', 'LIST'],
                'requestedPackageLineItems' => [[
                    'weight' => ['units' => 'KG', 'value' => $this->weightIn($shipment, 'kg')],
                    'dimensions' => [
                        'length' => (int) ($shipment->length ?? 20),
                        'width'  => (int) ($shipment->width ?? 15),
                        'height' => (int) ($shipment->height ?? 10),
                        'units'  => 'CM',
                    ],
                ]],
            ],
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json('output.rateReplyDetails', []) as $detail) {
            $shipmentDetail = $detail['ratedShipmentDetails'][0] ?? null;

            if (! $shipmentDetail) {
                continue;
            }

            $quotes[] = $this->quote(
                (float) ($shipmentDetail['totalNetCharge'] ?? 0),
                $shipmentDetail['currency'] ?? 'USD',
                $detail['serviceName'] ?? ($detail['serviceType'] ?? 'FedEx'),
                null,
                $detail['serviceType'] ?? null,
                $detail,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->post($this->base().'/track/v1/trackingnumbers', [
            'trackingInfo' => [['trackingNumberInfo' => ['trackingNumber' => $trackingCode]]],
            'includeDetailedScans' => false,
        ]);

        return [
            'status' => (string) ($response->json('output.completeTrackResults.0.trackResults.0.latestStatusDetail.description') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'FedEx';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->token())->timeout(30)->acceptJson();
    }

    private function token(): string
    {
        return Cache::remember('fedex_token_'.($this->integration->environment ?: 'sandbox'), 3000, function () {
            $response = Http::asForm()->timeout(20)->post($this->base().'/oauth/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->cred('client_id'),
                'client_secret' => $this->cred('client_secret'),
            ]);

            if (! $response->json('access_token')) {
                throw new \RuntimeException('FedEx authentication failed.');
            }

            return (string) $response->json('access_token');
        });
    }

    /** @param array<string, mixed> $address */
    private function address(array $address): array
    {
        return array_filter([
            'postalCode'  => $address['zip'] ?? null,
            'countryCode' => $address['country'] ?? null,
            'city'        => $address['city'] ?? null,
            'stateOrProvinceCode' => $address['state'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
