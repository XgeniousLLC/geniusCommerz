<?php

namespace App\Services\Fraud;

use App\Contracts\FraudInterface;
use App\Services\FraudScorer;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Uqudo (UAE) — identity and fraud signals for the Gulf.
 *
 * Worth being plain about the market: there are very few Gulf-native fraud vendors, and
 * most Gulf merchants run SEON, Sift or IPQualityScore. Uqudo is included because it is
 * regionally headquartered and understands GCC identity documents and numbering.
 */
class UqudoDriver extends ProviderDriver implements FraudInterface
{
    private const API = 'https://id.uqudo.io';

    public function check(string $phone, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return ['error' => 'Uqudo is not configured. Add your client credentials in Integrations.'];
        }

        try {
            $token = $this->accessToken();
        } catch (\Throwable $e) {
            return ['error' => 'Uqudo: '.$e->getMessage()];
        }

        $response = Http::withToken($token)->timeout(15)
            ->post(self::API.'/api/v1/risk/phone', array_filter([
                'phoneNumber' => $phone,
                'email'       => $context['email'] ?? null,
                'ipAddress'   => $context['ip'] ?? null,
                'countryCode' => $context['country'] ?? null,
            ]));

        if (! $response->successful()) {
            return ['error' => 'Uqudo: '.($response->json('message') ?? 'lookup failed')];
        }

        $body = $response->json();

        return FraudScorer::fromRiskScore((float) ($body['riskScore'] ?? 50), $body, 'uqudo');
    }

    public function isConfigured(): bool
    {
        return (bool) $this->integration->is_active
            && (string) $this->cred('client_id') !== ''
            && (string) $this->cred('client_secret') !== '';
    }

    public function name(): string
    {
        return 'Uqudo';
    }

    private function accessToken(): string
    {
        return Cache::remember('uqudo_token', 3000, function () {
            $response = Http::asForm()->timeout(15)->post(self::API.'/api/v1/auth/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->cred('client_id'),
                'client_secret' => $this->cred('client_secret'),
            ]);

            if (! $response->json('access_token')) {
                throw new \RuntimeException('authentication failed');
            }

            return (string) $response->json('access_token');
        });
    }
}
