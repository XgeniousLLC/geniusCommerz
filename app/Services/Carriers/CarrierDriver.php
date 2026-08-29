<?php

namespace App\Services\Carriers;

use App\Contracts\ShippingRateInterface;
use App\Services\ProviderDriver;
use App\Shipping\ShipmentRequest;
use App\Shipping\ShippingQuote;
use Illuminate\Http\Client\PendingRequest;

/**
 * Shared behaviour for carriers.
 *
 * Two shapes exist in the wild and both are represented here. Aggregators (EasyPost,
 * Shippo, Melhor Envio, Shiprocket) return a list of rates you then buy by id. Direct
 * carriers (Delhivery, Aramex, SMSA) quote a single price and create the waybill in one
 * step — for those, rates() returns one quote and buyLabel() books the shipment.
 */
abstract class CarrierDriver extends ProviderDriver implements ShippingRateInterface
{
    abstract public function name(): string;

    abstract protected function request(): PendingRequest;

    /**
     * Carriers that book in a single step have nothing to buy by rate id.
     *
     * Saying so plainly beats returning a fake tracking code that looks like success.
     */
    public function buyLabel(string $rateId): array
    {
        throw new \RuntimeException($this->name().' books shipments directly rather than from a saved rate.');
    }

    /** A single-quote helper for direct carriers. */
    protected function quote(
        float $amount,
        string $currency,
        string $service = 'Standard',
        ?int $days = null,
        ?string $rateId = null,
        array $raw = [],
    ): ShippingQuote {
        return new ShippingQuote(
            carrier: $this->name(),
            service: $service,
            amount: $amount,
            currency: $currency,
            estimatedDays: $days,
            rateId: $rateId,
            raw: $raw,
        );
    }

    /** @param list<ShippingQuote> $quotes */
    protected function cheapestFirst(array $quotes): array
    {
        usort($quotes, fn (ShippingQuote $a, ShippingQuote $b) => $a->amount <=> $b->amount);

        return $quotes;
    }

    protected function fail($response, string $action): never
    {
        $body = is_object($response) && method_exists($response, 'json') ? ($response->json() ?? []) : [];

        throw new \RuntimeException($this->name().": {$action} — ".(
            $body['message']
            ?? $body['error']
            ?? ($body['errors'][0]['message'] ?? 'request failed')
        ));
    }

    /** Weight in the unit a carrier expects, from the kilograms we carry internally. */
    protected function weightIn(ShipmentRequest $shipment, string $unit): float
    {
        return match ($unit) {
            'g'  => round($shipment->weight * 1000, 0),
            'lb' => round($shipment->weight * 2.20462, 2),
            'oz' => $shipment->weightInOunces(),
            default => round($shipment->weight, 3),
        };
    }
}
