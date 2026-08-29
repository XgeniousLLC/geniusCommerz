<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Flutterwave implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'flutterwave',
            group: 'payment',
            label: 'Flutterwave',
            driver: \App\Services\Payments\FlutterwaveGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'FLWSECK_…'),
                CredentialField::secret('secret_hash', 'Webhook Secret Hash', 'The value you set as the webhook hash in Flutterwave'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['NGN', 'GHS', 'KES', 'UGX', 'TZS', 'ZAR', 'RWF', 'XAF', 'XOF', 'USD', 'GBP', 'EUR'],
            docsUrl: 'https://developer.flutterwave.com/docs/collecting-payments/standard/',
            hint: 'Pan-African cards, mobile money and bank transfer.',
            sort: 23,
        );
    }
}
