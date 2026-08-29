<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** MessageBird (Bird) — strong European coverage. */
class MessageBirdGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://rest.messagebird.com';

    public function send(string $to, string $message): bool
    {
        $response = $this->request()->post(self::API.'/messages', [
            'originator' => $this->cred('originator'),
            'recipients' => [$to],
            'body'       => $message,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('MessageBird: '.($response->json('errors.0.description') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = $this->request()->get(self::API.'/balance');

        return $response->successful()
            ? trim($response->json('amount').' '.$response->json('type'))
            : null;
    }

    public function name(): string
    {
        return 'MessageBird';
    }

    private function request()
    {
        return Http::withHeaders(['Authorization' => 'AccessKey '.$this->cred('access_key')])->timeout(20);
    }
}
