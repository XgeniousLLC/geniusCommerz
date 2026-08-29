<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Melhor Envio — Brazilian aggregator covering Correios, Jadlog, Loggi and others. */
class MelhorEnvioCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://melhorenvio.com.br/api/v2'
            : 'https://sandbox.melhorenvio.com.br/api/v2';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $response = $this->request()->post($this->base().'/me/shipment/calculate', [
            'from' => ['postal_code' => $this->digits($shipment->from['zip'] ?? '')],
            'to'   => ['postal_code' => $this->digits($shipment->to['zip'] ?? '')],
            'package' => [
                'weight' => $this->weightIn($shipment, 'kg'),
                'width'  => $shipment->width ?? 15,
                'height' => $shipment->height ?? 10,
                'length' => $shipment->length ?? 20,
            ],
            'options' => ['insurance_value' => $shipment->declaredValue ?? 0, 'receipt' => false, 'own_hand' => false],
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        $quotes = [];

        foreach ($response->json() ?? [] as $service) {
            // Unavailable services come back with an error rather than a price.
            if (! empty($service['error']) || ! isset($service['price'])) {
                continue;
            }

            $quotes[] = $this->quote(
                (float) $service['price'],
                'BRL',
                trim(($service['company']['name'] ?? '').' '.($service['name'] ?? '')),
                isset($service['delivery_time']) ? (int) $service['delivery_time'] : null,
                (string) ($service['id'] ?? ''),
                $service,
            );
        }

        return $this->cheapestFirst($quotes);
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->post($this->base().'/me/shipment/tracking', ['orders' => [$trackingCode]]);

        return [
            'status' => (string) ($response->json($trackingCode.'.status') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Melhor Envio';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken((string) $this->cred('access_token'))
            ->withHeaders(['User-Agent' => (string) $this->cred('user_agent', 'geniuscommerz')])
            ->timeout(30)->acceptJson();
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
