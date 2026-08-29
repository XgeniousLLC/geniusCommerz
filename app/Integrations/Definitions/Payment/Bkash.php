<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Bkash implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'bkash',
            group: 'payment',
            label: 'bKash',
            driver: \App\Services\Payments\BkashGateway::class,
            fields: [
                CredentialField::text('app_key', 'App Key'),
                CredentialField::secret('app_secret', 'App Secret'),
                CredentialField::text('username', 'Username'),
                CredentialField::secret('password', 'Password'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Refund,
            ],
            currencies: ['BDT'],
            countries: ['BD'],
            docsUrl: 'https://developer.bka.sh/docs/checkout-url-basics',
            hint: 'Bangladesh — tokenized checkout. No webhook: the customer return triggers capture.',
            sort: 10,
        );
    }
}
