<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Plivo — global coverage, basic-auth REST API. */
class PlivoGateway extends ProviderDriver implements SmsInterface
{
    public function send(string $to, string $message): bool
    {
        $authId = (string) $this->cred('auth_id');

        $response = Http::withBasicAuth($authId, (string) $this->cred('auth_token'))
            ->timeout(20)
            ->post("https://api.plivo.com/v1/Account/{$authId}/Message/", [
                'src'  => $this->cred('from'),
                'dst'  => $to,
                'text' => $message,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Plivo: '.($response->json('error') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $authId   = (string) $this->cred('auth_id');
        $response = Http::withBasicAuth($authId, (string) $this->cred('auth_token'))
            ->timeout(15)
            ->get("https://api.plivo.com/v1/Account/{$authId}/");

        return $response->successful() ? (string) $response->json('cash_credits') : null;
    }

    public function name(): string
    {
        return 'Plivo';
    }
}
