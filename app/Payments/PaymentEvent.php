<?php

namespace App\Payments;

/**
 * A normalised webhook notification.
 *
 * `id` is the gateway's own event id and is what makes replay protection possible —
 * gateways retry, and the same event will arrive more than once.
 */
class PaymentEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly PaymentStatus $status,
        /** Our reference, echoed back by the gateway. Identifies the payment attempt. */
        public readonly ?string $reference = null,
        public readonly ?string $transactionId = null,
        public readonly array $raw = [],
    ) {}

    /** True when the event carries no payment outcome we act on. */
    public function isIgnorable(): bool
    {
        return $this->reference === null && $this->transactionId === null;
    }
}
