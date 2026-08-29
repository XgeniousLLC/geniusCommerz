<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Telnyx — global coverage. */
class TelnyxGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://api.telnyx.com/v2';

    public function send(string $to, string $message): bool
    {
        $response = $this->request()->post(self::API.'/messages', array_filter([
            // Either a purchased number or a messaging profile may be the sender.
            'from'                 => $this->cred('from_number'),
            'messaging_profile_id' => $this->cred('messaging_profile_id'),
            'to'                   => $to,   // Telnyx expects E.164 with the plus
            'text'                 => $message,
        ]));

        if (! $response->successful()) {
            throw new \RuntimeException('Telnyx: '.($response->json('errors.0.detail') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = $this->request()->get(self::API.'/balance');

        return $response->json('data.balance') !== null
            ? $response->json('data.balance').' '.$response->json('data.currency')
            : null;
    }

    public function name(): string
    {
        return 'Telnyx';
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('api_key'))->timeout(20)->acceptJson();
    }
}
