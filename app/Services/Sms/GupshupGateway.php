<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

/** Gupshup Enterprise (India). */
class GupshupGateway extends ProviderDriver implements SmsInterface
{
    private const API = 'https://enterprise.smsgupshup.com/GatewayAPI/rest';

    public function send(string $to, string $message): bool
    {
        $response = Http::timeout(20)->get(self::API, [
            'method'      => 'SendMessage',
            'send_to'     => ltrim($to, '+'),
            'msg'         => $message,
            'msg_type'    => $this->isUnicode($message) ? 'UNICODE_TEXT' : 'TEXT',
            'userid'      => $this->cred('user_id'),
            'auth_scheme' => 'plain',
            'password'    => $this->cred('password'),
            'v'           => '1.1',
            'format'      => 'text',
            'mask'        => $this->cred('sender_id'),
        ]);

        // Gupshup answers with a plain "success | id" or "error | reason" line.
        if (! str_starts_with(strtolower(trim($response->body())), 'success')) {
            throw new \RuntimeException('Gupshup: '.trim($response->body()));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = Http::timeout(15)->get(self::API, [
            'method'      => 'GetBalance',
            'userid'      => $this->cred('user_id'),
            'auth_scheme' => 'plain',
            'password'    => $this->cred('password'),
            'format'      => 'text',
        ]);

        return $response->successful() ? trim($response->body()) : null;
    }

    public function name(): string
    {
        return 'Gupshup';
    }

    private function isUnicode(string $message): bool
    {
        return (bool) preg_match('/[^\x00-\x7F]/', $message);
    }
}
