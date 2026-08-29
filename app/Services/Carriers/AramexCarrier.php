<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Aramex — dominant across the Gulf and MENA, with global lanes. */
class AramexCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://ws.aramex.net/ShippingAPI.V2/RateCalculator/Service_1_0.svc/json'
            : 'https://ws.dev.aramex.net/ShippingAPI.V2/RateCalculator/Service_1_0.svc/json';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post($this->base().'/CalculateRate', [
            'ClientInfo'          => $this->clientInfo(),
            'OriginAddress'       => $this->address($shipment->from),
            'DestinationAddress'  => $this->address($shipment->to),
            'ShipmentDetails'     => [
                'PaymentType'      => 'P',
                'ProductGroup'     => $shipment->isInternational() ? 'EXP' : 'DOM',
                'ProductType'      => $shipment->isInternational() ? 'PPX' : 'OND',
                'ActualWeight'     => ['Unit' => 'KG', 'Value' => $this->weightIn($shipment, 'kg')],
                'ChargeableWeight' => ['Unit' => 'KG', 'Value' => $this->weightIn($shipment, 'kg')],
                'NumberOfPieces'   => 1,
            ],
            'Transaction' => ['Reference1' => $shipment->reference ?? ''],
        ]);

        if ($response->failed() || $response->json('HasErrors')) {
            $this->fail($response, 'rating failed');
        }

        $total = $response->json('TotalAmount');

        return $total === null ? [] : [$this->quote(
            (float) ($total['Value'] ?? 0),
            $total['CurrencyCode'] ?? 'AED',
            $shipment->isInternational() ? 'Aramex Express' : 'Aramex Domestic',
            raw: $response->json(),
        )];
    }

    public function track(string $trackingCode): array
    {
        $tracking = str_replace('/RateCalculator/Service_1_0.svc', '/Tracking/Service_1_0.svc', $this->base());

        $response = $this->request()->post($tracking.'/TrackShipments', [
            'ClientInfo' => $this->clientInfo(),
            'Shipments'  => [$trackingCode],
        ]);

        return [
            'status' => (string) ($response->json('TrackingResults.0.Value.0.UpdateDescription') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Aramex';
    }

    protected function request(): PendingRequest
    {
        return Http::timeout(30)->acceptJson();
    }

    private function clientInfo(): array
    {
        return [
            'UserName'    => (string) $this->cred('username'),
            'Password'    => (string) $this->cred('password'),
            'Version'     => 'v1.0',
            'AccountNumber'  => (string) $this->cred('account_number'),
            'AccountPin'     => (string) $this->cred('account_pin'),
            'AccountEntity'  => (string) $this->cred('account_entity'),
            'AccountCountryCode' => (string) $this->cred('account_country'),
        ];
    }

    /** @param array<string, mixed> $address */
    private function address(array $address): array
    {
        return [
            'Line1'       => $address['street1'] ?? '',
            'City'        => $address['city'] ?? '',
            'StateOrProvinceCode' => $address['state'] ?? '',
            'PostCode'    => $address['zip'] ?? '',
            'CountryCode' => $address['country'] ?? '',
        ];
    }
}
