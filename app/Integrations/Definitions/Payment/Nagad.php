<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Nagad implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'nagad',
            group: 'payment',
            label: 'Nagad',
            driver: \App\Services\Payments\NagadGateway::class,
            fields: [
                CredentialField::text('merchant_id', 'Merchant ID'),
                CredentialField::text('merchant_number', 'Merchant Number'),
                CredentialField::textarea('public_key', 'Nagad Public Key (PEM)'),
                CredentialField::textarea('private_key', 'Merchant Private Key (PEM)'),
                CredentialField::optional('base_url', 'Endpoint', 'Override if Nagad gave you a different host'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['BDT'],
            countries: ['BD'],
            docsUrl: 'https://nagad.com.bd/merchant',
            hint: 'Bangladesh — RSA-signed two-leg checkout.',
            sort: 11,
        );
    }
}
