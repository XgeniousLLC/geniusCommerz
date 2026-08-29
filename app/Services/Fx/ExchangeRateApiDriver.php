<?php

namespace App\Services\Fx;

use App\Contracts\ExchangeRateInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** exchangerate-api.com — paid tiers with higher refresh frequency and an SLA. */
class ExchangeRateApiDriver extends ProviderDriver implements ExchangeRateInterface
{
    public function rates(string $base): array
    {
        $key = rawurlencode((string) $this->cred('api_key'));

        $response = Http::timeout(20)->get("https://v6.exchangerate-api.com/v6/{$key}/latest/".strtoupper($base));

        if ($response->failed() || $response->json('result') !== 'success') {
            throw new \RuntimeException('Exchange rate lookup failed: '.($response->json('error-type') ?? $response->status()));
        }

        return array_map('floatval', $response->json('conversion_rates', []));
    }

    public function name(): string
    {
        return 'ExchangeRate-API';
    }
}
