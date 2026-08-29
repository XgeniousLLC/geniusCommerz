<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/**
 * IPQualityScore — a global fraud signal.
 *
 * Fundamentally different from the Bangladeshi providers: those score a phone against
 * courier delivery history, which only exists in Bangladesh. This scores phone validity,
 * carrier, disposable-email and IP reputation, which works anywhere.
 */
class IpQualityScoreDriver extends ProviderDriver implements FraudInterface
{
    private const API = 'https://ipqualityscore.com/api/json';

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'IPQualityScore is not configured. Add an API key in Integrations.'];
        }

        $key = rawurlencode((string) $this->cred('api_key'));

        $response = Http::timeout(10)->get(self::API."/phone/{$key}/".rawurlencode($phone), array_filter([
            'country' => $context['country'] ?? null,
            'email'   => $context['email'] ?? null,
            'ip'      => $context['ip'] ?? null,
        ]));

        if (in_array($response->status(), [401, 403], true)) {
            return ['error' => 'Invalid IPQualityScore API key.'];
        }

        if (! $response->successful() || $response->json('success') === false) {
            return ['error' => 'IPQualityScore: '.($response->json('message') ?? 'request failed')];
        }

        return FraudScorer::fromIpQualityScore($response->json());
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active && (string) $this->cred('api_key') !== '';
    }

    public function name(): string
    {
        return 'IPQualityScore';
    }
}
