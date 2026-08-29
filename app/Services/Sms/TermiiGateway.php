<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Termii — Nigeria and West Africa. */
class TermiiGateway extends ProviderDriver implements SmsInterface
{
    private function base(): string
    {
        return rtrim((string) $this->cred('base_url', 'https://api.ng.termii.com'), '/');
    }

    public function send(string $to, string $message): bool
    {
        $response = Http::timeout(20)->post($this->base().'/api/sms/send', [
            'to'      => ltrim($to, '+'),
            'from'    => $this->cred('sender_id'),
            'sms'     => $message,
            'type'    => 'plain',
            'channel' => (string) $this->cred('channel', 'generic'),
            'api_key' => $this->cred('api_key'),
        ]);

        if (($response->json('code') ?? '') !== 'ok') {
            throw new \RuntimeException('Termii: '.($response->json('message') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = Http::timeout(15)->get($this->base().'/api/get-balance', [
            'api_key' => $this->cred('api_key'),
        ]);

        return $response->json('balance') !== null
            ? $response->json('balance').' '.$response->json('currency')
            : null;
    }

    public function name(): string
    {
        return 'Termii';
    }
}
