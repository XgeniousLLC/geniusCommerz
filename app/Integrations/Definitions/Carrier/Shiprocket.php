<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Shiprocket implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'shiprocket',
            group: 'carrier',
            label: 'Shiprocket',
            driver: \App\Services\Carriers\ShiprocketCarrier::class,
            fields: [
                CredentialField::text('email', 'Account Email'),
                CredentialField::secret('password', 'Password'),
            ],
            environments: ['live'],
            countries: ['IN'],
            docsUrl: 'https://apidocs.shiprocket.in/',
            hint: 'Indian aggregator — quotes several courier partners at once.',
            sort: 21,
        );
    }
}
