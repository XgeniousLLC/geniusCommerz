<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** DHL Express — MyDHL API, global express. */
class DhlExpressCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://express.api.dhl.com/mydhlapi'
            : 'https://express.api.dhl.com/mydhlapi/test';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->get($this->base().'/rates', array_filter([
            'accountNumber'          => $this->cred('account_number'),
            'originCountryCode'      => $shipment->from['country'] ?? null,
            'originCityName'         => $shipment->from['city'] ?? null,
            'originPostalCode'       => $shipment->from['zip'] ?? null,
            'destinationCountryCode' => $shipment->to['country'] ?? null,
            'destinationCityName'    => $shipment->to['city'] ?? null,
            'destinationPostalCode'  => $shipment->to['zip'] ?? null,
            'weight'                 => $this->weightIn($shipment, 'kg'),
            'length'                 => $shipment->length ?? 20,
            'width'                  => $shipment->width ?? 15,
            'height'                 => $shipment->height ?? 10,
            'plannedShippingDate'    => now()->addDay()->format('Y-m-d'),
            'isCustomsDeclarable'    => $shipment->isInternational() ? 'true' : 'false',
            'unitOfMeasurement'      => 'metric',
        ]));

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json('products', []) as $product) {
            // DHL returns several price breakdowns; BILLC is the billing currency total.
            $price = collect($product['totalPrice'] ?? [])->firstWhere('currencyType', 'BILLC')
                ?? ($product['totalPrice'][0] ?? null);

            if (! $price) {
                continue;
            }

            $quotes[] = $this->quote(
                (float) ($price['price'] ?? 0),
                $price['priceCurrency'] ?? 'EUR',
                $product['productName'] ?? 'DHL Express',
                null,
                $product['productCode'] ?? null,
                $product,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get($this->base().'/shipments/'.$trackingCode.'/tracking');

        return [
            'status' => (string) ($response->json('shipments.0.status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'DHL Express';
    }

    protected function request(): PendingRequest
    {
        return Http::withBasicAuth((string) $this->cred('api_key'), (string) $this->cred('api_secret'))
            ->timeout(30)->acceptJson();
    }
}
