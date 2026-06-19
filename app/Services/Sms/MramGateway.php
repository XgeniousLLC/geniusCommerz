<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class MramGateway implements SmsInterface
{
    /**
     * Error codes returned by msg.mram.com.bd in the response body.
     * @see Developers API — Error Code & Meaning
     */
    private const ERROR_CODES = [
        '1002' => 'Sender Id/Masking Not Found',
        '1003' => 'API Not Found',
        '1004' => 'SPAM Detected',
        '1005' => 'Internal Error',
        '1006' => 'Internal Error',
        '1007' => 'Balance Insufficient',
        '1008' => 'Message is empty',
        '1009' => 'Message Type Not Set (text/unicode)',
        '1010' => 'Invalid User & Password',
        '1011' => 'Invalid User Id',
        '1012' => 'Invalid Number',
        '1013' => 'API limit error',
        '1014' => 'No matching template',
        '1015' => 'SMS Content Validation Fails',
        '1016' => 'IP address not allowed!!',
        '1019' => 'Sms Purpose Missing',
    ];

    private Integration $integration;

    public function __construct()
    {
        $this->integration = Integration::forProvider('mram')
            ?? new Integration(['credentials' => []]);
    }

    public function send(string $to, string $message): bool
    {
        $params = [
            'api_key'  => $this->integration->getCredential('api_key'),
            'type'     => $this->messageType($message),
            'contacts' => $to,
            'senderid' => $this->integration->getCredential('sender_id'),
            'msg'      => $message,
        ];

        // Optional transactional/promotional label.
        if ($label = $this->integration->getCredential('label')) {
            $params['label'] = $label;
        }

        $response = Http::get($this->baseUrl(), $params);

        if (! $response->successful()) {
            throw new \RuntimeException('MRAM SMS request failed (HTTP ' . $response->status() . ').');
        }

        $body = trim((string) $response->body());

        if (isset(self::ERROR_CODES[$body])) {
            throw new \RuntimeException('MRAM SMS error ' . $body . ': ' . self::ERROR_CODES[$body]);
        }

        return true;
    }

    public function balance(): ?string
    {
        $apiKey = $this->integration->getCredential('api_key');
        if (! $apiKey) {
            return null;
        }

        $response = Http::get(rtrim($this->host(), '/') . '/miscapi/' . $apiKey . '/getBalance');

        return $response->successful() ? trim((string) $response->body()) : null;
    }

    public function name(): string
    {
        return 'MRAM SMS';
    }

    /**
     * Detect Bangla / non-ASCII content and switch to unicode mode.
     */
    private function messageType(string $message): string
    {
        return preg_match('/[^\x00-\x7F]/', $message) ? 'unicode' : 'text';
    }

    private function baseUrl(): string
    {
        return $this->integration->getCredential('base_url', 'https://msg.mram.com.bd/smsapi');
    }

    private function host(): string
    {
        $base = $this->baseUrl();
        return preg_replace('#/smsapi.*$#', '', $base) ?: 'https://msg.mram.com.bd';
    }
}
