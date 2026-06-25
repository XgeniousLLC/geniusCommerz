<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokService
{
    private ?string $pixelId;
    private ?string $accessToken;

    public function __construct()
    {
        $this->pixelId     = SiteSetting::get('tracking.tiktok_pixel_id');
        $this->accessToken = SiteSetting::get('tracking.tiktok_access_token');
    }

    public function isConfigured(): bool
    {
        return !empty($this->pixelId) && !empty($this->accessToken);
    }

    /**
     * @return array{success: bool, http_status: int|null, body: string|null, error: string|null}
     */
    public function sendPurchase(array $order, ?string $email = null, ?string $phone = null, ?string $ip = null, ?string $userAgent = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'http_status' => null, 'body' => null, 'error' => 'TikTok not configured'];
        }

        $userData = [];
        if ($email) {
            $userData['email'] = [hash('sha256', strtolower(trim($email)))];
        }
        if ($phone) {
            $userData['phone_number'] = [hash('sha256', preg_replace('/\D/', '', $phone))];
        }
        if ($ip) {
            $userData['ip']         = $ip;
            $userData['user_agent'] = $userAgent;
        }

        $payload = [
            'pixel_code' => $this->pixelId,
            'event'      => 'Purchase',
            'event_time' => time(),
            'event_id'   => 'order_' . $order['id'],
            'user'       => $userData,
            'properties' => [
                'currency'     => $order['currency'] ?? 'BDT',
                'value'        => (float) $order['total'],
                'content_type' => 'product',
                'contents'     => collect($order['items'] ?? [])->map(fn ($item) => [
                    'content_id'   => (string) ($item['product_id'] ?? ''),
                    'content_name' => $item['product_name'] ?? '',
                    'quantity'     => (int) ($item['quantity'] ?? 1),
                    'price'        => (float) ($item['price'] ?? 0),
                ])->values()->all(),
            ],
        ];

        try {
            $res = Http::withToken($this->accessToken)
                ->timeout(5)
                ->post('https://business-api.tiktok.com/open_api/v1.3/event/track/', [
                    'data' => [$payload],
                ]);

            return [
                'success'     => $res->successful(),
                'http_status' => $res->status(),
                'body'        => $res->body(),
                'error'       => $res->successful() ? null : ($res->json('message') ?? 'HTTP ' . $res->status()),
            ];
        } catch (\Throwable $e) {
            Log::warning('TikTok Events API error: ' . $e->getMessage());

            return ['success' => false, 'http_status' => null, 'body' => null, 'error' => $e->getMessage()];
        }
    }
}
