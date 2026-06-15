<?php

namespace App\Services;

use App\Contracts\SmsInterface;
use App\Models\Integration;

class SmsService
{
    public function driver(?string $provider = null): SmsInterface
    {
        if ($provider === null) {
            $integration = Integration::defaultSms();
            $provider    = $integration?->provider;
        }

        return match ($provider) {
            'bulksmsbd' => app(Sms\BulkSmsBdGateway::class),
            'smsbd'     => app(Sms\SmsBdGateway::class),
            'twilio'    => app(Sms\TwilioGateway::class),
            default     => throw new \RuntimeException("No active default SMS gateway is configured. Go to Admin → Integrations to set one."),
        };
    }

    public function send(string $to, string $message): bool
    {
        return $this->driver()->send($to, $message);
    }

    public function hasDefault(): bool
    {
        return Integration::defaultSms() !== null;
    }

    public static function renderTemplate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }
        return $template;
    }
}
