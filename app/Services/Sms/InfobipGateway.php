<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/**
 * Infobip — global coverage.
 *
 * Each account is issued its own API host, so the base URL is a credential rather than
 * a constant.
 */
class InfobipGateway extends ProviderDriver implements SmsInterface
{
    private function base(): string
    {
        $host = trim((string) $this->cred('base_url'));

        return rtrim(str_starts_with($host, 'http') ? $host : "https://{$host}", '/');
    }

    public function send(string $to, string $message): bool
    {
        $response = $this->request()->post($this->base().'/sms/2/text/advanced', [
            'messages' => [array_filter([
                'destinations' => [['to' => ltrim($to, '+')]],
                'from'         => $this->cred('sender_id'),
                'text'         => $message,
            ])],
        ]);

        $status = $response->json('messages.0.status.groupName');

        if (! $response->successful() || ! in_array($status, ['PENDING', 'DELIVERED'], true)) {
            throw new \RuntimeException('Infobip: '.($response->json('messages.0.status.description')
                ?? $response->json('requestError.serviceException.text') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = $this->request()->get($this->base().'/account/1/balance');

        return $response->json('balance') !== null
            ? $response->json('balance').' '.$response->json('currency')
            : null;
    }

    public function name(): string
    {
        return 'Infobip';
    }

    private function request()
    {
        return Http::withHeaders(['Authorization' => 'App '.$this->cred('api_key')])
            ->timeout(20)->acceptJson();
    }
}
