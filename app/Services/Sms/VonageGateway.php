<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Vonage (formerly Nexmo) — global coverage, numbers in E.164 without the leading +. */
class VonageGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://rest.nexmo.com/sms/json';

    public function send(string $to, string $message): bool
    {
        $response = Http::asForm()->timeout(20)->post(self::API, [
            'api_key'    => $this->cred('api_key'),
            'api_secret' => $this->cred('api_secret'),
            'from'       => $this->cred('from'),
            'to'         => ltrim($to, '+'),
            'text'       => $message,
            'type'       => $this->isUnicode($message) ? 'unicode' : 'text',
        ]);

        $status = $response->json('messages.0.status');

        if ($status !== '0') {
            throw new \RuntimeException('Vonage: '.($response->json('messages.0.error-text') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = Http::timeout(15)->get('https://rest.nexmo.com/account/get-balance', [
            'api_key'    => $this->cred('api_key'),
            'api_secret' => $this->cred('api_secret'),
        ]);

        return $response->successful() ? (string) $response->json('value') : null;
    }

    public function name(): string
    {
        return 'Vonage';
    }

    /** Non-GSM characters cost more and must be flagged, or they arrive mangled. */
    private function isUnicode(string $message): bool
    {
        return (bool) preg_match('/[^\x00-\x7F]/', $message);
    }
}
