<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Sinch — global coverage. */
class SinchGateway extends ProviderDriver implements SmsInterface
{
    private function base(): string
    {
        // Sinch shards by region; the default US host works for most accounts.
        $region = (string) $this->cred('region', 'us');

        return "https://{$region}.sms.api.sinch.com/xms/v1/".$this->cred('service_plan_id');
    }

    public function send(string $to, string $message): bool
    {
        $response = $this->request()->post($this->base().'/batches', array_filter([
            'from' => $this->cred('sender_id'),
            'to'   => [ltrim($to, '+')],
            'body' => $message,
        ]));

        if (! $response->successful()) {
            throw new \RuntimeException('Sinch: '.($response->json('text') ?? $response->json('message') ?? 'send failed'));
        }

        return true;
    }

    /** Sinch reports spend through billing, not a prepaid balance endpoint. */
    public function balance(): ?string
    {
        return null;
    }

    public function name(): string
    {
        return 'Sinch';
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('api_token'))->timeout(20)->acceptJson();
    }
}
