<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Ravelin — UK-headquartered, strong in European e-commerce. */
class RavelinDriver extends ProviderDriver implements FraudInterface
{
    private const API = 'https://api.ravelin.com';

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'Ravelin is not configured. Add a secret API key in Integrations.'];
        }

        $response = Http::withToken((string) $this->cred('api_key'))
            ->timeout(15)
            ->post(self::API.'/v2/checkout', [
                'timestamp' => now()->getTimestampMs(),
                'customer'  => array_filter([
                    'customerId'  => $context['email'] ?? $phone,
                    'email'       => $context['email'] ?? null,
                    'telephone'   => ['countryCode' => $context['country'] ?? null, 'number' => $phone],
                    'countryCode' => $context['country'] ?? null,
                ]),
            ]);

        if (! $response->successful()) {
            return ['error' => 'Ravelin: '.($response->json('message') ?? 'lookup failed')];
        }

        $body   = $response->json();
        $action = strtoupper((string) ($body['action'] ?? ''));

        // Ravelin answers with a recommendation, so map that rather than a raw score.
        $level = match ($action) {
            'PREVENT' => 'high_risk',
            'REVIEW'  => 'mid_risk',
            'ALLOW'   => 'safe',
            default   => 'unknown',
        };

        return FraudScorer::fromRiskScore(
            (float) ($body['score'] ?? 50),
            $body,
            'ravelin',
            forceLevel: $level,
        );
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active && (string) $this->cred('api_key') !== '';
    }

    public function name(): string
    {
        return 'Ravelin';
    }
}
