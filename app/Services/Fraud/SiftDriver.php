<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/**
 * Sift — global machine-learning fraud scoring.
 *
 * Sift learns from the events you send it, so a score is only as good as the history
 * behind the user id. A first-time lookup on an unknown user returns a weak signal;
 * accuracy improves once order events are being fed in continuously.
 */
class SiftDriver extends ProviderDriver implements FraudInterface
{
    private const API = 'https://api.sift.com';

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'Sift is not configured. Add an API key in Integrations.'];
        }

        $userId = $context['email'] ?? $phone;

        $response = Http::timeout(15)->get(self::API.'/v205/score/'.rawurlencode($userId), [
            'api_key'   => $this->cred('api_key'),
            'abuse_types' => 'payment_abuse',
        ]);

        if ($response->status() === 404) {
            return FraudScorer::fromRiskScore(50, ['unknown_user' => true], 'sift', forceLevel: 'unknown');
        }

        if (! $response->successful()) {
            return ['error' => 'Sift: '.($response->json('error_message') ?? 'lookup failed')];
        }

        $body = $response->json();
        // Sift returns 0-1; scale to the 0-100 the scorer expects.
        $score = (float) ($body['scores']['payment_abuse']['score'] ?? 0.5) * 100;

        return FraudScorer::fromRiskScore($score, $body, 'sift');
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active && (string) $this->cred('api_key') !== '';
    }

    public function name(): string
    {
        return 'Sift';
    }
}
