<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Clickatell — South African origin, strong across Africa and beyond. */
class ClickatellGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://platform.clickatell.com';

    public function send(string $to, string $message): bool
    {
        $response = Http::withHeaders(['Authorization' => (string) $this->cred('api_key')])
            ->timeout(20)
            ->post(self::API.'/v1/message', [
                'messages' => [array_filter([
                    'channel' => 'sms',
                    'to'      => ltrim($to, '+'),
                    'content' => $message,
                    'from'    => $this->cred('sender_id'),
                ])],
            ]);

        $error = $response->json('messages.0.error');

        if (! $response->successful() || $error) {
            throw new \RuntimeException('Clickatell: '.($error['description'] ?? $response->json('error.description') ?? 'send failed'));
        }

        return true;
    }

    /** Clickatell reports balance in the portal rather than over the messaging API. */
    public function balance(): ?string
    {
        return null;
    }

    public function name(): string
    {
        return 'Clickatell';
    }
}
