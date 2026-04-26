<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class BulkSmsBdGateway implements SmsInterface
{
    private Integration $integration;

    public function __construct()
    {
        $this->integration = Integration::forProvider('bulksmsbd')
            ?? new Integration(['credentials' => []]);
    }

    public function send(string $to, string $message): bool
    {
        $response = Http::get($this->baseUrl(), [
            'api_key'   => $this->integration->getCredential('api_key'),
            'senderid'  => $this->integration->getCredential('sender_id'),
            'number'    => $to,
            'message'   => $message,
        ]);

        $body = $response->json();
        return isset($body['response_code']) && $body['response_code'] == 202;
    }

    public function balance(): ?string
    {
        $response = Http::get('https://bulksmsbd.net/api/getBalanceApi', [
            'api_key' => $this->integration->getCredential('api_key'),
        ]);

        return $response->json('balance');
    }

    public function name(): string
    {
        return 'BulkSMSBD';
    }

    private function baseUrl(): string
    {
        return $this->integration->getCredential('base_url', 'https://bulksmsbd.net/api/smsapi');
    }
}
