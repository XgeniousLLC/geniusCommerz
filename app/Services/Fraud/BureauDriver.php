<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;

/**
 * Bureau (India) — phone and identity intelligence.
 *
 * Scores an Indian mobile on tenure, porting history and network signals. This is the
 * closest Indian analogue to the Bangladeshi courier-history checkers: it answers "is
 * this number real and settled" rather than "has this person refused parcels".
 */
class BureauDriver extends ProviderDriver implements FraudInterface
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://api.bureau.id'
            : 'https://api.sandbox.bureau.id';
    }

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'Bureau is not configured. Add your credentials in Integrations.'];
        }

        $response = Http::withBasicAuth((string) $this->cred('client_id'), (string) $this->cred('client_secret'))
            ->timeout(15)
            ->post($this->base().'/v1/suppliers/telco/phone-intelligence', array_filter([
                'phoneNumber' => ltrim((string) PhoneNumber::toE164($phone, 'IN'), '+'),
                'countryCode' => 'IN',
                'email'       => $context['email'] ?? null,
            ]));

        if (! $response->successful()) {
            return ['error' => 'Bureau: '.($response->json('message') ?? 'lookup failed')];
        }

        $body = $response->json();

        // Bureau reports a 0-1 risk level; scale it and treat a disconnected number as
        // high risk regardless of the numeric score.
        $score = (float) ($body['riskLevel'] ?? $body['risk_score'] ?? 0.5) * 100;
        $dead  = ($body['phoneStatus'] ?? null) === 'DISCONNECTED';

        return FraudScorer::fromRiskScore(
            $score,
            $body,
            'bureau',
            forceLevel: $dead ? 'high_risk' : null,
        );
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active
            && (string) $this->cred('client_id') !== ''
            && (string) $this->cred('client_secret') !== '';
    }

    public function name(): string
    {
        return 'Bureau';
    }
}
