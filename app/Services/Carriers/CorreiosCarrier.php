<?php

namespace App\Services\Carriers;

use App\Shipping\ShipmentRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Correios — Brazil's national postal operator. */
class CorreiosCarrier extends CarrierDriver
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://api.correios.com.br'
            : 'https://apihom.correios.com.br';
    }

    public function rates(ShipmentRequest $shipment): array
    {
        $service = (string) $this->cred('service_code', '03298');   // PAC contract

        $response = $this->request()->get($this->base().'/preco/v1/nacional/'.$service, [
            'cepOrigem'   => $this->digits($shipment->from['zip'] ?? ''),
            'cepDestino'  => $this->digits($shipment->to['zip'] ?? ''),
            'psObjeto'    => $this->weightIn($shipment, 'g'),        // Correios weighs in grams
            'tpObjeto'    => '2',                                    // package
            'comprimento' => $shipment->length ?? 20,
            'largura'     => $shipment->width ?? 15,
            'altura'      => $shipment->height ?? 10,
            'nuContrato'  => $this->cred('contract'),
            'nuDR'        => $this->cred('dr_number'),
        ]);

        if ($response->failed()) {
            $this->fail($response, 'rating failed');
        }

        // Correios returns Brazilian decimal formatting, e.g. "24,50".
        $price = str_replace(',', '.', (string) $response->json('pcFinal'));

        return $price === '' ? [] : [$this->quote((float) $price, 'BRL', 'Correios', raw: $response->json())];
    }

    public function track(string $trackingCode): array
    {
        $response = $this->request()->get($this->base().'/srorastro/v1/objetos/'.$trackingCode);

        return [
            'status' => (string) ($response->json('objetos.0.eventos.0.descricao') ?? 'unknown'),
            'raw'    => $response->json() ?? [],
        ];
    }

    public function name(): string
    {
        return 'Correios';
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->token())->timeout(30)->acceptJson();
    }

    private function token(): string
    {
        return Cache::remember('correios_token_'.($this->integration->environment ?: 'sandbox'), 3000, function () {
            $response = Http::withBasicAuth((string) $this->cred('username'), (string) $this->cred('access_code'))
                ->timeout(20)
                ->post($this->base().'/token/v1/autentica/cartaopostagem', [
                    'numero' => $this->cred('posting_card'),
                ]);

            if (! $response->json('token')) {
                throw new \RuntimeException('Correios authentication failed.');
            }

            return (string) $response->json('token');
        });
    }

    private function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
