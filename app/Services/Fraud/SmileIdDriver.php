<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Smile ID — pan-African identity verification and phone intelligence. */
class SmileIdDriver extends ProviderDriver implements FraudInterface
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://api.smileidentity.com/v1'
            : 'https://testapi.smileidentity.com/v1';
    }

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'Smile ID is not configured. Add your partner ID and API key in Integrations.'];
        }

        $timestamp = now()->toIso8601String();

        $response = Http::timeout(20)->post($this->base().'/verify_phone_number', array_filter([
            'partner_id'    => $this->cred('partner_id'),
            'signature'     => $this->signature($timestamp),
            'timestamp'     => $timestamp,
            'phone_number'  => ltrim($phone, '+'),
            'country'       => $context['country'] ?? null,
        ]));

        if (! $response->successful()) {
            return ['error' => 'Smile ID: '.($response->json('error') ?? 'lookup failed')];
        }

        $body     = $response->json();
        // Smile ID answers with a verification verdict rather than a numeric score.
        $verified = ($body['ResultCode'] ?? null) === '1012' || ($body['Actions']['Verify_ID_Number'] ?? '') === 'Verified';

        return FraudScorer::fromRiskScore(
            $verified ? 10 : 75,
            $body,
            'smileid',
            forceLevel: $verified ? 'safe' : 'mid_risk',
        );
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active
            && (string) $this->cred('partner_id') !== ''
            && (string) $this->cred('api_key') !== '';
    }

    public function name(): string
    {
        return 'Smile ID';
    }

    /** Smile ID signs timestamp + partner id with the API key. */
    private function signature(string $timestamp): string
    {
        return base64_encode(hash_hmac(
            'sha256',
            $timestamp.$this->cred('partner_id').'sid_request',
            (string) $this->cred('api_key'),
            true,
        ));
    }
}
