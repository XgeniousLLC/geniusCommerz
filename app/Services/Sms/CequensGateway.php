<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Cequens — Egypt, UAE and wider MENA. */
class CequensGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://apis.cequens.com/sms/v1/messages';

    public function send(string $to, string $message): bool
    {
        $response = Http::withToken((string) $this->cred('api_key'))
            ->timeout(20)
            ->post(self::API, [
                'senderName'  => $this->cred('sender_name'),
                'messageType' => $this->isUnicode($message) ? 'unicode' : 'text',
                'messageText' => $message,
                'recipients'  => ltrim($to, '+'),
            ]);

        if (! $response->successful() || ($response->json('replyCode') ?? 0) !== 0) {
            throw new \RuntimeException('Cequens: '.($response->json('replyMessage') ?? 'send failed'));
        }

        return true;
    }

    /** Cequens exposes balance through account management, not the messaging API. */
    public function balance(): ?string
    {
        return null;
    }

    public function name(): string
    {
        return 'Cequens';
    }

    private function isUnicode(string $message): bool
    {
        return (bool) preg_match('/[^\x00-\x7F]/', $message);
    }
}
