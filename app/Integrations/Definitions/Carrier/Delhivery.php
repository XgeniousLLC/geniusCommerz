<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Delhivery implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'delhivery',
            group: 'carrier',
            label: 'Delhivery',
            driver: \App\Services\Carriers\DelhiveryCarrier::class,
            fields: [
                CredentialField::secret('api_token', 'API Token'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['IN'],
            docsUrl: 'https://one.delhivery.com/developer-portal',
            hint: 'India\'s largest logistics network.',
            sort: 20,
        );
    }
}
