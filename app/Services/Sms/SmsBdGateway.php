<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;

class SmsBdGateway extends ProviderDriver implements SmsInterface
{

    public function send(string $to, string $message): bool
    {
        $response = Http::withHeaders([
            'api-key'   => $this->integration->getCredential('api_key'),
            'sender-id' => $this->integration->getCredential('sender_id'),
        ])->post($this->baseUrl(), [
            'message' => $message,
            'msisdn'  => PhoneNumber::national($to, 'BD') ?? $to,
        ]);

        return $response->successful() && ($response->json('status') === 'success' || $response->status() === 200);
    }

    public function balance(): ?string
    {
        $response = Http::withHeaders([
            'api-key' => $this->integration->getCredential('api_key'),
        ])->get('https://sms.smsbd.in/api/v2/balance');

        return $response->json('balance');
    }

    public function name(): string
    {
        return 'SMS.BD';
    }

    private function baseUrl(): string
    {
        return $this->integration->getCredential('base_url', 'https://sms.smsbd.in/api/v2/send');
    }
}
