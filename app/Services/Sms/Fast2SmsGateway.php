<?php

namespace App\Services\Sms;

use App\Contracts\SmsInterface;
use App\Services\ProviderDriver;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;

/**
 * Fast2SMS (India).
 *
 * Domestic-only: it takes a bare 10-digit Indian number, so the country code is stripped
 * back off the E.164 form the rest of the system carries.
 */
class Fast2SmsGateway extends ProviderDriver implements SmsInterface
{
    public function send(string $to, string $message): bool
    {
        $national = PhoneNumber::national($to, 'IN') ?? $to;
        $number   = ltrim(preg_replace('/\D+/', '', $national), '0');

        $response = Http::withHeaders(['authorization' => (string) $this->cred('api_key')])
            ->timeout(20)
            ->post('https://www.fast2sms.com/dev/bulkV2', [
                'route'    => (string) $this->cred('route', 'q'),
                'message'  => $message,
                'language' => $this->isUnicode($message) ? 'unicode' : 'english',
                'numbers'  => $number,
                'flash'    => '0',
            ]);

        if (! $response->json('return')) {
            throw new \RuntimeException('Fast2SMS: '.($response->json('message') ?? 'send failed'));
        }

        return true;
    }

    public function balance(): ?string
    {
        $response = Http::timeout(15)->get('https://www.fast2sms.com/dev/wallet', [
            'authorization' => $this->cred('api_key'),
        ]);

        return $response->json('wallet') !== null ? (string) $response->json('wallet') : null;
    }

    public function name(): string
    {
        return 'Fast2SMS';
    }

    private function isUnicode(string $message): bool
    {
        return (bool) preg_match('/[^\x00-\x7F]/', $message);
    }
}
