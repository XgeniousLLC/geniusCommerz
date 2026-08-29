<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Loggi implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'loggi',
            group: 'carrier',
            label: 'Loggi',
            driver: \App\Services\Carriers\LoggiCarrier::class,
            fields: [
                CredentialField::text('email', 'Account Email'),
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('shop_id', 'Shop ID'),
            ],
            environments: ['live'],
            countries: ['BR'],
            docsUrl: 'https://docs.loggi.com/',
            hint: 'Brazil — last-mile delivery. GraphQL API.',
            sort: 42,
        );
    }
}
