<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class JazzCash implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'jazzcash',
            group: 'payment',
            label: 'JazzCash',
            driver: \App\Services\Payments\JazzCashGateway::class,
            fields: [
                CredentialField::text('merchant_id', 'Merchant ID'),
                CredentialField::secret('password', 'Password'),
                CredentialField::secret('integrity_salt', 'Integrity Salt'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['PKR'],
            countries: ['PK'],
            docsUrl: 'https://sandbox.jazzcash.com.pk/Sandbox/Home/Documentation',
            hint: 'Pakistan — mobile wallet, cards and bank. Signed browser form post.',
            sort: 39,
        );
    }
}
