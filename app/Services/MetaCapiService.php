<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MetaCapiService
{
    private ?string $pixelId;
    private ?string $token;

    public function __construct()
    {
        $this->pixelId = SiteSetting::get('tracking.meta_pixel_id');
        $this->token   = SiteSetting::get('tracking.meta_capi_token');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->pixelId) && ! empty($this->token);
    }

    /**
     * Build user_data payload with every available signal.
     *
     * @param array{email?:string,phone?:string,name?:string,city?:string,zip?:string,user_id?:int} $customer
     */
    public function buildUserData(Request $request, array $customer = []): array
    {
        $ud = [];

        // Hashed PII
        if (! empty($customer['email'])) {
            $ud['em'] = [hash('sha256', strtolower(trim($customer['email'])))];
        }

        if (! empty($customer['phone'])) {
            $ud['ph'] = [hash('sha256', preg_replace('/\D/', '', $customer['phone']))];
        }

        if (! empty($customer['name'])) {
            $parts = explode(' ', trim($customer['name']), 2);
            $ud['fn'] = [hash('sha256', strtolower($parts[0]))];
            if (isset($parts[1])) {
                $ud['ln'] = [hash('sha256', strtolower($parts[1]))];
            }
        }

        if (! empty($customer['city'])) {
            $ud['ct'] = [hash('sha256', strtolower(trim($customer['city'])))];
        }

        if (! empty($customer['zip'])) {
            $ud['zp'] = [hash('sha256', trim($customer['zip']))];
        }

        if (! empty($customer['user_id'])) {
            $ud['external_id'] = [hash('sha256', (string) $customer['user_id'])];
        }

        // Non-hashed signals from request
        $ud['client_ip_address'] = $request->ip();
        $ud['client_user_agent'] = $request->userAgent();

        // Facebook cookies — read from request cookies
        $fbc = $request->cookie('_fbc');
        $fbp = $request->cookie('_fbp');

        if ($fbc) $ud['fbc'] = $fbc;
        if ($fbp) $ud['fbp'] = $fbp;

        return $ud;
    }

    /**
     * Send one event to Meta CAPI.
     *
     * @return array{success:bool,status:int|null,body:string|null,error:string|null}
     */
    public function send(
        string  $eventName,
        array   $customData,
        array   $userData,
        string  $sourceUrl,
        ?string $eventId = null,
    ): array {
        if (! $this->isConfigured()) {
            return ['success' => false, 'status' => null, 'body' => null, 'error' => 'Meta CAPI not configured'];
        }

        $payload = [
            'event_name'       => $eventName,
            'event_time'       => time(),
            'event_source_url' => $sourceUrl,
            'action_source'    => 'website',
            'user_data'        => $userData,
        ];

        if (! empty($customData)) {
            $payload['custom_data'] = $customData;
        }

        if ($eventId) {
            $payload['event_id'] = $eventId;
        }

        try {
            $res = Http::timeout(5)->post(
                "https://graph.facebook.com/v18.0/{$this->pixelId}/events?access_token={$this->token}",
                ['data' => [$payload]]
            );

            return [
                'success' => $res->successful(),
                'status'  => $res->status(),
                'body'    => $res->body(),
                'error'   => $res->successful() ? null : ($res->json('error.message') ?? 'HTTP ' . $res->status()),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => null, 'body' => null, 'error' => $e->getMessage()];
        }
    }
}
