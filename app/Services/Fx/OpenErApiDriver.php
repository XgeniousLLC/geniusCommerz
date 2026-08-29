<?php

namespace App\Services\Fx;

use App\Contracts\ExchangeRateInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/**
 * open.er-api.com — free, no API key, and it covers BDT.
 *
 * The sensible default so rate refresh works out of the box rather than requiring a
 * signup before multi-currency is usable at all.
 */
class OpenErApiDriver extends ProviderDriver implements ExchangeRateInterface
{
    public function rates(string $base): array
    {
        $response = Http::timeout(20)->get('https://open.er-api.com/v6/latest/'.strtoupper($base));

        if ($response->failed() || $response->json('result') !== 'success') {
            throw new \RuntimeException('Exchange rate lookup failed: '.($response->json('error-type') ?? $response->status()));
        }

        return array_map('floatval', $response->json('rates', []));
    }

    public function name(): string
    {
        return 'open.er-api.com';
    }
}
