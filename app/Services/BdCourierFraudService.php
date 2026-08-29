<?php

namespace App\Services;

use App\Contracts\FraudInterface;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class BdCourierFraudService implements FraudInterface
{
    private const BASE_URL = 'https://api.bdcourier.com';

    private string $apiKey;
    private bool   $configured;

    public function __construct()
    {
        $integration      = Integration::forProvider('bdcourier');
        $this->apiKey     = $integration?->getCredential('api_key') ?? '';
        $this->configured = $integration?->is_active && ! empty($this->apiKey);
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    public function name(): string
    {
        return 'BDCourier';
    }

    public function check(string $phone, array $context = []): array
    {
        $phone = $this->normalisePhone($phone);

        if (! $this->configured) {
            return ['error' => 'BDCourier API key not configured. Add it in Integrations → BDCourier.'];
        }

        $response = Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->post(self::BASE_URL . '/courier-check', [
                'phone' => $phone,
            ]);

        if ($response->status() === 401) {
            return ['error' => 'Invalid BDCourier API key. Check Integrations → BDCourier.'];
        }

        if ($response->status() === 429) {
            return ['error' => 'BDCourier rate limit reached. Try again in a minute.'];
        }

        if (! $response->successful()) {
            return ['error' => 'BDCourier returned an error: ' . $response->status()];
        }

        $body = $response->json();

        if (($body['status'] ?? '') !== 'success') {
            return ['error' => $body['message'] ?? 'Unknown error from BDCourier.'];
        }

        return [
            'phone'    => $phone,
            'provider' => 'bdcourier',
            'couriers' => $this->parseCouriers($body['data'] ?? []),
            'summary'  => $this->parseSummary($body['data']['summary'] ?? []),
            'reports'  => $this->parseReports($body['reports'] ?? []),
        ];
    }

    private function parseCouriers(array $data): array
    {
        $couriers = [];

        foreach ($data as $key => $c) {
            if ($key === 'summary' || ! is_array($c)) {
                continue;
            }

            $ratio = (float) ($c['success_ratio'] ?? 0);

            $couriers[] = [
                'name'             => $c['name']              ?? ucfirst($key),
                'logo'             => $c['logo']              ?? null,
                'total_parcel'     => $c['total_parcel']      ?? 0,
                'success_parcel'   => $c['success_parcel']    ?? 0,
                'cancelled_parcel' => $c['cancelled_parcel']  ?? 0,
                'success_ratio'    => $ratio,
                'ratio_color'      => $this->ratioColor($ratio),
            ];
        }

        return $couriers;
    }

    private function parseSummary(array $summary): array
    {
        if (empty($summary)) {
            return [];
        }

        return [
            'total_parcel'     => $summary['total_parcel']     ?? 0,
            'success_parcel'   => $summary['success_parcel']   ?? 0,
            'cancelled_parcel' => $summary['cancelled_parcel'] ?? 0,
            'success_ratio'    => (float) ($summary['success_ratio'] ?? 0),
        ];
    }

    private function parseReports(array $reports): array
    {
        return array_map(fn (array $r) => [
            'id'          => $r['id']          ?? null,
            'name'        => $r['name']        ?? 'Unknown',
            'details'     => $r['details']     ?? '',
            'courier'     => $r['courierName'] ?? null,
            'courier_logo'=> $r['courierLogo'] ?? null,
            'created_at'  => $r['created_at']  ?? null,
        ], $reports);
    }

    private function ratioColor(float $ratio): string
    {
        if ($ratio >= 85) return 'success';
        if ($ratio >= 70) return 'warning';
        return 'danger';
    }

    private function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '880') && strlen($phone) === 13) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }
}
