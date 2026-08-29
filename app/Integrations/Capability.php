<?php

namespace App\Integrations;

/**
 * What a provider can do. Deliberately small — each case gates real behaviour somewhere,
 * rather than describing the provider for its own sake.
 */
enum Capability: string
{
    /** Customer leaves the site and returns; decides the whole checkout branch. */
    case HostedRedirect = 'hosted_redirect';

    /** Charged in-page (client secret / tokenised card). */
    case DirectCharge = 'direct_charge';

    /** Publishes webhooks, so a callback route and signing secret are needed. */
    case Webhook = 'webhook';

    case Refund = 'refund';

    case PartialRefund = 'partial_refund';

    public function label(): string
    {
        return match ($this) {
            self::HostedRedirect => 'Hosted redirect',
            self::DirectCharge   => 'On-site charge',
            self::Webhook        => 'Webhooks',
            self::Refund         => 'Refunds',
            self::PartialRefund  => 'Partial refunds',
        };
    }
}
