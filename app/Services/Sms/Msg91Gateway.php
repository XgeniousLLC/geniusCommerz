<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;

/** MSG91 (India) — transactional SMS. */
class Msg91Gateway extends ProviderDriver implements SmsInterface
{
    public function send(string $to, string $message): bool
    {
        $response = Http::withHeaders(['authkey' => (string) $this->cred('auth_key')])
            ->timeout(20)
            ->post('https://control.msg91.com/api/v2/sendsms', [
                'sender'  => $this->cred('sender_id'),
                'route'   => (string) $this->cred('route', '4'),
                'country' => '91',
                // MSG91 wants the number without a leading +.
                'sms'     => [['message' => $message, 'to' => [ltrim($to, '+')]]],
            ]);

        if (! $response->successful() || ($response->json('type') ?? '') === 'error') {
            throw new \RuntimeException('MSG91: '.($response->json('message') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = Http::timeout(15)->get('https://control.msg91.com/api/balance.php', [
            'authkey' => $this->cred('auth_key'),
            'type'    => $this->cred('route', '4'),
        ]);

        return $response->successful() ? trim($response->body()) : null;
    }

    public function name(): string
    {
        return 'MSG91';
    }
}
