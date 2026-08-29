<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Africa's Talking — Kenya, Uganda, Tanzania, Nigeria and more. */
class AfricasTalkingGateway extends ProviderDriver implements SmsInterface
{
    private function base(): string
    {
        // The sandbox lives on a separate host and only accepts the username "sandbox".
        return $this->isLive()
            ? 'https://api.africastalking.com/version1'
            : 'https://api.sandbox.africastalking.com/version1';
    }

    public function send(string $to, string $message): bool
    {
        $response = $this->request()->asForm()->post($this->base().'/messaging', array_filter([
            'username' => $this->cred('username'),
            'to'       => $to,          // Africa's Talking expects E.164 with the plus
            'message'  => $message,
            'from'     => $this->cred('sender_id'),
        ]));

        $recipients = $response->json('SMSMessageData.Recipients', []);

        if (! $recipients) {
            throw new \RuntimeException('Africa\'s Talking: '.($response->json('SMSMessageData.Message') ?? 'send failed'));
        }

        $status = $recipients[0]['status'] ?? '';

        if (strtolower($status) !== 'success') {
            throw new \RuntimeException('Africa\'s Talking: '.$status);
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = $this->request()->get($this->base().'/user', ['username' => $this->cred('username')]);

        return $response->json('UserData.balance');
    }

    public function name(): string
    {
        return 'Africa\'s Talking';
    }

    private function request()
    {
        return Http::withHeaders([
            'apiKey' => (string) $this->cred('api_key'),
            'Accept' => 'application/json',
        ])->timeout(20);
    }
}
