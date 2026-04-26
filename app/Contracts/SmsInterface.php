<?php

namespace App\Contracts;

interface SmsInterface
{
    /**
     * Send an SMS message to a phone number.
     * $to should be a full number (e.g. 01XXXXXXXXX or +8801XXXXXXXXX).
     * Returns true on success, throws \RuntimeException on failure.
     */
    public function send(string $to, string $message): bool;

    /**
     * Return remaining SMS balance or null if not supported.
     */
    public function balance(): ?string;

    /**
     * Human-readable gateway name.
     */
    public function name(): string;
}
