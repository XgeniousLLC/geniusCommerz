<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/**
 * SEON — Europe-headquartered, global coverage.
 *
 * Builds a digital-footprint score from email, phone and IP: how old the email is, which
 * social accounts it appears on, whether the IP is a proxy. Richer than a phone lookup,
 * so it improves markedly when an email is supplied alongside the number.
 */
class SeonDriver extends ProviderDriver implements FraudInterface
{
    private const API = 'https://api.seon.io/SeonRestService/fraud-api/v2.0';

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'SEON is not configured. Add a licence key in Integrations.'];
        }

        $response = Http::withHeaders(['X-API-KEY' => (string) $this->cred('licence_key')])
            ->timeout(15)
            ->post(self::API, array_filter([
                'phone_number' => $phone,
                'email'        => $context['email'] ?? null,
                'ip'           => $context['ip'] ?? null,
                'config'       => ['phone' => ['include' => 'flags,history,id']],
            ]));

        if (! $response->successful() || $response->json('success') === false) {
            return ['error' => 'SEON: '.($response->json('error.message') ?? 'lookup failed')];
        }

        $data = $response->json('data', []);

        return FraudScorer::fromRiskScore(
            (float) ($data['fraud_score'] ?? 50),
            $data,
            'seon',
        );
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active && (string) $this->cred('licence_key') !== '';
    }

    public function name(): string
    {
        return 'SEON';
    }
}
