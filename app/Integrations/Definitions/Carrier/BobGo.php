<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class BobGo implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'bobgo',
            group: 'carrier',
            label: 'Bob Go',
            driver: \App\Services\Carriers\BobGoCarrier::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
            ],
            environments: ['live'],
            countries: ['ZA'],
            docsUrl: 'https://docs.bobgo.co.za/',
            hint: 'South Africa — aggregates Courier Guy, Fastway, PostNet and more.',
            sort: 33,
        );
    }
}
