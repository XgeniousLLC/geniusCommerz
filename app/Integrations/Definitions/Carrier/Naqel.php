<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Naqel implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'naqel',
            group: 'carrier',
            label: 'Naqel Express',
            driver: \App\Services\Carriers\NaqelCarrier::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('password', 'Password'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['SA', 'AE', 'BH', 'KW', 'QA', 'OM'],
            docsUrl: 'https://www.naqelexpress.com/',
            hint: 'Saudi Arabia and the wider Gulf.',
            sort: 52,
        );
    }
}
