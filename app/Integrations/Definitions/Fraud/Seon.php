<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Seon implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'seon',
            group: 'fraud',
            label: 'SEON',
            driver: \App\Services\Fraud\SeonDriver::class,
            fields: [
                CredentialField::secret('licence_key', 'Licence Key'),
            ],
            environments: ['live'],
            docsUrl: 'https://docs.seon.io/api-reference/fraud-api',
            hint: 'Digital-footprint scoring from email, phone and IP. Europe-headquartered, global reach.',
            sort: 20,
        );
    }
}
