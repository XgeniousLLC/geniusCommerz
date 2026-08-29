<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Unifonic — Saudi Arabia and the wider Gulf. */
class UnifonicGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://el.cloud.unifonic.com/rest';

    public function send(string $to, string $message): bool
    {
        $response = Http::asForm()->timeout(20)->post(self::API.'/SMS/messages', [
            'AppSid'    => $this->cred('app_sid'),
            'SenderID'  => $this->cred('sender_id'),
            'Recipient' => ltrim($to, '+'),
            'Body'      => $message,
        ]);

        if (! $response->json('success')) {
            throw new \RuntimeException('Unifonic: '.($response->json('message') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = Http::asForm()->timeout(15)
            ->post(self::API.'/Account/GetBalance', ['AppSid' => $this->cred('app_sid')]);

        return $response->json('data.Balance') !== null
            ? $response->json('data.Balance').' '.$response->json('data.Currency')
            : null;
    }

    public function name(): string
    {
        return 'Unifonic';
    }
}
