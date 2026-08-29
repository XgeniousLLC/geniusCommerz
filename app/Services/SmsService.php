<?php

namespace App\Services;

use App\Contracts\SmsInterface;
use App\Models\SiteSetting;
use App\Support\PhoneNumber;

class SmsService extends ProviderManager
{
    protected function group(): string
    {
        return 'sms';
    }

    protected function contract(): string
    {
        return SmsInterface::class;
    }

    protected function missingDefaultMessage(): string
    {
        return 'No active default SMS gateway is configured. Go to Admin → Integrations to set one.';
    }

    public function driver(?string $provider = null): SmsInterface
    {
        return parent::driver($provider);
    }

    /**
     * Send via the default gateway.
     *
     * The number is normalised to E.164 here so every driver receives one known shape and
     * converts from it — domestic gateways back to a local number, international ones
     * as-is. $country is only needed when $to is in national form.
     */
    public function send(string $to, string $message, ?string $country = null): bool
    {
        return $this->driver()->send($this->normalise($to, $country), $message);
    }

    /**
     * E.164 where possible, otherwise the original string — a gateway rejecting an
     * unreadable number is more useful than this silently dropping the message.
     */
    public function normalise(string $to, ?string $country = null): string
    {
        $country ??= SiteSetting::get('general.store_country', 'BD');

        return PhoneNumber::toE164($to, $country) ?? $to;
    }

    public static function renderTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{{'.$key.'}}', (string) $value, $template);
        }

        return $template;
    }
}
