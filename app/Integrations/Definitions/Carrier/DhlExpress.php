<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class DhlExpress implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'dhl',
            group: 'carrier',
            label: 'DHL Express',
            driver: \App\Services\Carriers\DhlExpressCarrier::class,
            fields: [
                CredentialField::text('api_key', 'API Key'),
                CredentialField::secret('api_secret', 'API Secret'),
                CredentialField::text('account_number', 'Account Number'),
            ],
            environments: ['sandbox', 'live'],
            docsUrl: 'https://developer.dhl.com/api-reference/dhl-express-mydhl-api',
            hint: 'Global express, 220+ countries.',
            sort: 60,
        );
    }
}
