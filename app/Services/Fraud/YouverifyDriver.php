<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Youverify — Nigeria and West Africa, phone and identity lookups. */
class YouverifyDriver extends ProviderDriver implements FraudInterface
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://api.youverify.co/v2/api'
            : 'https://api.sandbox.youverify.co/v2/api';
    }

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'Youverify is not configured. Add a token in Integrations.'];
        }

        $response = Http::withHeaders(['token' => (string) $this->cred('api_token')])
            ->timeout(15)
            ->post($this->base().'/identity/ng/phone', [
                'mobile'      => ltrim($phone, '+'),
                'isSubjectConsent' => true,
            ]);

        if (! $response->successful() || $response->json('success') === false) {
            return ['error' => 'Youverify: '.($response->json('message') ?? 'lookup failed')];
        }

        $data  = $response->json('data', []);
        $found = ($data['status'] ?? '') === 'found';

        return FraudScorer::fromRiskScore(
            $found ? 15 : 70,
            $data,
            'youverify',
            forceLevel: $found ? 'safe' : 'mid_risk',
        );
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active && (string) $this->cred('api_token') !== '';
    }

    public function name(): string
    {
        return 'Youverify';
    }
}
