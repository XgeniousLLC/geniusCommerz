<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class FraudBdService
{
    private const BASE_URL    = 'https://fraudbd.com/api';
    private const SANDBOX_KEY = '1302e523911213bc507c3c6dd35ebdb908044b42982345012452ac8f86406cc9';

    private string $apiKey;
    private bool   $sandbox;

    public function __construct()
    {
        $integration  = Integration::forProvider('fraudbd');
        $this->apiKey = $integration?->getCredential('api_key') ?? '';
        $this->sandbox = ! $integration?->is_active || empty($this->apiKey);

        if ($this->sandbox) {
            $this->apiKey = self::SANDBOX_KEY;
        }
    }

    public function check(string $phone): array
    {
        $phone = $this->normalisePhone($phone);

        $response = Http::timeout(10)
            ->withHeaders([
                'api_key'      => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->post(self::BASE_URL . '/check-courier-info', [
                'phone_number' => $phone,
            ]);

        if ($response->status() === 401) {
            return ['error' => 'Invalid FraudBD API key. Check Integrations → FraudBD.'];
        }

        if ($response->status() === 429) {
            return ['error' => 'FraudBD rate limit reached. Try again in a minute.'];
        }

        if (! $response->successful()) {
            return ['error' => 'FraudBD returned an error: ' . $response->status()];
        }

        $body = $response->json();

        if (! ($body['status'] ?? false)) {
            return ['error' => $body['message'] ?? 'Unknown error from FraudBD.'];
        }

        return [
            'phone'    => $phone,
            'sandbox'  => $this->sandbox,
            'couriers' => $this->parseCouriers($body['data'] ?? []),
        ];
    }

    private function parseCouriers(array $data): array
    {
        $raw = $data['courier_summaries'] ?? $data;

        if (! is_array($raw)) {
            return [];
        }

        return array_map(function (array $c) {
            $rating = $c['customer_rating'] ?? null;
            $risk   = $c['risk_level']      ?? null;

            return [
                'name'          => $c['name']         ?? ($c['courier'] ?? 'Unknown'),
                'logo'          => $c['logo']          ?? null,
                'rating'        => $rating,
                'risk_level'    => $risk,
                'risk_color'    => $this->riskColor($risk),
                'rating_color'  => $this->ratingColor($rating),
                'success_rate'  => $c['success_rate']  ?? null,
                'cancel_rate'   => $c['cancel_rate']   ?? null,
                'total_orders'  => $c['total_orders']  ?? null,
            ];
        }, array_values($raw));
    }

    private function riskColor(?string $level): string
    {
        return match ($level) {
            'low'       => 'green',
            'medium'    => 'yellow',
            'high'      => 'orange',
            'very_high' => 'red',
            default     => 'gray',
        };
    }

    private function ratingColor(?string $rating): string
    {
        return match ($rating) {
            'excellent'     => 'green',
            'good'          => 'blue',
            'moderate'      => 'yellow',
            'risky'         => 'red',
            'new_customer'  => 'gray',
            default         => 'gray',
        };
    }

    private function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        // Convert +880 / 880 prefix to 0
        if (str_starts_with($phone, '880') && strlen($phone) === 13) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }
}
