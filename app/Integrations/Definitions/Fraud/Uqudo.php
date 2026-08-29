<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Uqudo implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'uqudo',
            group: 'fraud',
            label: 'Uqudo',
            driver: \App\Services\Fraud\UqudoDriver::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
            ],
            environments: ['live'],
            countries: ['AE', 'SA', 'KW', 'BH', 'QA', 'OM', 'EG', 'JO'],
            docsUrl: 'https://docs.uqudo.com/',
            hint: 'UAE-headquartered, understands GCC identity documents. Gulf-native fraud vendors are scarce — SEON and Sift also serve the region well.',
            sort: 25,
        );
    }
}
