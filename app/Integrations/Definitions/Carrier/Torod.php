<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Torod implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'torod',
            group: 'carrier',
            label: 'Torod',
            driver: \App\Services\Carriers\TorodCarrier::class,
            fields: [
                CredentialField::secret('api_token', 'API Token'),
            ],
            environments: ['live'],
            countries: ['SA', 'AE', 'KW', 'BH'],
            docsUrl: 'https://torod.co/',
            hint: 'Saudi aggregator — SMSA, Aramex and Naqel in one call.',
            sort: 53,
        );
    }
}
