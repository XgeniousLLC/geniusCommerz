<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Taqnyat — Saudi Arabia. */
class TaqnyatGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://api.taqnyat.sa';

    public function send(string $to, string $message): bool
    {
        $response = $this->request()->post(self::API.'/v1/messages', [
            'recipients' => [ltrim($to, '+')],
            'body'       => $message,
            'sender'     => $this->cred('sender_id'),
        ]);

        // Taqnyat mirrors HTTP semantics in a statusCode field.
        if ((int) ($response->json('statusCode') ?? $response->status()) !== 201) {
            throw new \RuntimeException('Taqnyat: '.($response->json('message') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = $this->request()->get(self::API.'/account/balance');

        return $response->json('balance') !== null
            ? (string) $response->json('balance')
            : null;
    }

    public function name(): string
    {
        return 'Taqnyat';
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('bearer_token'))->timeout(20)->acceptJson();
    }
}
