<?php

namespace App\Services\Carriers;

use App\Contracts\ShippingRateInterface;
use App\Services\ProviderDriver;
use App\Shipping\ShipmentRequest;
use App\Shipping\ShippingQuote;
use Illuminate\Support\Facades\Http;

/**
 * EasyPost — one integration covering USPS, UPS, FedEx, DHL and others.
 *
 * An aggregator rather than three direct carrier integrations: each direct API has its own
 * auth, rating model and label format, and a single store gains little from maintaining
 * them separately.
 */
class EasyPostCarrier extends ProviderDriver implements ShippingRateInterface
{
    private const API = 'https://api.easypost.com/v2';

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post(self::API.'/shipments', [
            'shipment' => [
                'to_address'   => $this->address($shipment->to),
                'from_address' => $this->address($shipment->from),
                'parcel'       => array_filter([
                    // EasyPost rates in ounces and inches.
                    'weight' => $shipment->weightInOunces(),
                    'length' => $shipment->length ? round($shipment->length / 2.54, 2) : null,
                    'width'  => $shipment->width ? round($shipment->width / 2.54, 2) : null,
                    'height' => $shipment->height ? round($shipment->height / 2.54, 2) : null,
                ], fn ($v) => $v !== null),
                'reference' => $shipment->reference,
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('EasyPost: '.($response->json('error.message') ?? 'rating failed'));
        }

        $quotes = [];

        foreach ($response->json('rates', []) as $rate) {
            $quotes[] = new ShippingQuote(
                carrier: $rate['carrier'] ?? 'Carrier',
                service: $rate['service'] ?? '',
                amount: (float) ($rate['rate'] ?? 0),
                currency: $rate['currency'] ?? 'USD',
                estimatedDays: isset($rate['delivery_days']) ? (int) $rate['delivery_days'] : null,
                rateId: $rate['id'] ?? null,
                raw: $rate,
            );
        }

        usort($quotes, fn ($a, $b) => $a->amount <=> $b->amount);

        return $quotes;
    }

    public function buyLabel(string $rateId): array
    {
        // A rate id belongs to a shipment; buying requires both.
        $rate = $this->request()->get(self::API.'/rates/'.$rateId);

        if ($rate->failed()) {
            throw new \RuntimeException('EasyPost: unknown rate '.$rateId);
        }

        $shipmentId = $rate->json('shipment_id');

        $response = $this->request()->post(self::API."/shipments/{$shipmentId}/buy", [
            'rate' => ['id' => $rateId],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('EasyPost: '.($response->json('error.message') ?? 'label purchase failed'));
        }

        return [
            'tracking_code' => (string) $response->json('tracking_code'),
            'label_url'     => $response->json('postage_label.label_url'),
            'carrier'       => $response->json('selected_rate.carrier'),
            'raw'           => $response->json(),
        ];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->post(self::API.'/trackers', [
            'tracker' => ['tracking_code' => $trackingCode],
        ]);

        return [
            'status' => (string) ($response->json('status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'EasyPost';
    }

    /** @param array<string, mixed> $address */
    private function address(array $address): array
    {
        return array_filter([
            'name'    => $address['name'] ?? null,
            'street1' => $address['street1'] ?? null,
            'street2' => $address['street2'] ?? null,
            'city'    => $address['city'] ?? null,
            'state'   => $address['state'] ?? null,
            'zip'     => $address['zip'] ?? null,
            'country' => $address['country'] ?? null,
            'phone'   => $address['phone'] ?? null,
            'email'   => $address['email'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function request()
    {
        // EasyPost uses the API key as the basic-auth username with an empty password.
        return Http::withBasicAuth((string) $this->cred('api_key'), '')->timeout(30)->acceptJson();
    }
}
