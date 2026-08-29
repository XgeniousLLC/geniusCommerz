<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class BlueDart implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'bluedart',
            group: 'carrier',
            label: 'Blue Dart',
            driver: \App\Services\Carriers\BlueDartCarrier::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
                CredentialField::text('login_id', 'Login ID'),
                CredentialField::secret('licence_key', 'Licence Key'),
                CredentialField::optional('base_rate', 'Contract Base Rate', 'Your negotiated rate; the finder only confirms serviceability'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['IN'],
            docsUrl: 'https://bluedartexpress.in/api',
            hint: 'Indian domestic express (DHL Group).',
            sort: 22,
        );
    }
}
