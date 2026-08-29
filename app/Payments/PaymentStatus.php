<?php

namespace App\Payments;

enum PaymentStatus: string
{
    /** Driver wants the customer sent to a hosted page. */
    case Redirect = 'redirect';

    /** Accepted by the gateway, awaiting asynchronous confirmation. */
    case Pending = 'pending';

    /** No online payment is taken (cash on delivery); the order proceeds unpaid. */
    case Deferred = 'deferred';

    case Paid = 'paid';

    case Failed = 'failed';

    case Cancelled = 'cancelled';

    /** How an order's payment_status should read for this outcome. */
    public function orderPaymentStatus(): string
    {
        return match ($this) {
            self::Paid                       => 'paid',
            self::Redirect, self::Pending    => 'pending',
            self::Failed                     => 'failed',
            self::Cancelled, self::Deferred  => 'unpaid',
        };
    }

    public function isSettled(): bool
    {
        return $this === self::Paid;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Paid, self::Failed, self::Cancelled], true);
    }
}
