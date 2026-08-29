<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Stripe implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'stripe',
            group: 'payment',
            label: 'Stripe',
            driver: \App\Services\Payments\StripeGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'sk_test_… in sandbox, sk_live_… in live'),
                CredentialField::secret('webhook_secret', 'Webhook Signing Secret', 'whsec_… from the endpoint you add in Stripe'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
                Capability::PartialRefund,
            ],
            // Stripe Checkout settles in ~135 currencies; no useful restriction to model.
            currencies: ['*'],
            docsUrl: 'https://stripe.com/docs/payments/checkout',
            hint: 'Cards, wallets and ~50 local payment methods through one integration.',
            sort: 2,
        );
    }
}
