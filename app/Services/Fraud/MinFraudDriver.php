<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** MaxMind minFraud — global IP, email and phone risk scoring. */
class MinFraudDriver extends ProviderDriver implements FraudInterface
{
    private const API = 'https://minfraud.maxmind.com/minfraud/v2.0/score';

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'minFraud is not configured. Add your account ID and licence key in Integrations.'];
        }

        $response = Http::withBasicAuth((string) $this->cred('account_id'), (string) $this->cred('licence_key'))
            ->timeout(15)
            ->post(self::API, [
                // minFraud requires an IP; fall back to the caller's so a phone-only
                // check still returns something rather than erroring.
                'device' => ['ip_address' => $context['ip'] ?? '127.0.0.1'],
                'email'  => array_filter(['address' => $context['email'] ?? null]),
                'billing' => array_filter([
                    'phone_number'       => $phone,
                    'phone_country_code' => $context['dial'] ?? null,
                    'country'            => $context['country'] ?? null,
                ]),
            ]);

        if (! $response->successful()) {
            return ['error' => 'minFraud: '.($response->json('error') ?? 'lookup failed')];
        }

        $body = $response->json();

        return FraudScorer::fromRiskScore((float) ($body['risk_score'] ?? 50), $body, 'minfraud');
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active
            && (string) $this->cred('account_id') !== ''
            && (string) $this->cred('licence_key') !== '';
    }

    public function name(): string
    {
        return 'MaxMind minFraud';
    }
}
