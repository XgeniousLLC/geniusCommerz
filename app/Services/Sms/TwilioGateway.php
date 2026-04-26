<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class TwilioGateway implements SmsInterface
{
    private Integration $integration;

    public function __construct()
    {
        $this->integration = Integration::forProvider('twilio')
            ?? new Integration(['credentials' => []]);
    }

    public function send(string $to, string $message): bool
    {
        $sid   = $this->integration->getCredential('account_sid');
        $token = $this->integration->getCredential('auth_token');
        $from  = $this->integration->getCredential('from_number');

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To'   => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        return $response->successful() && isset($response->json()['sid']);
    }

    public function balance(): ?string
    {
        $sid   = $this->integration->getCredential('account_sid');
        $token = $this->integration->getCredential('auth_token');

        $response = Http::withBasicAuth($sid, $token)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Balance.json");

        return $response->json('balance');
    }

    public function name(): string
    {
        return 'Twilio';
    }
}
