<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Bureau implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'bureau',
            group: 'fraud',
            label: 'Bureau',
            driver: \App\Services\Fraud\BureauDriver::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['IN'],
            docsUrl: 'https://docs.bureau.id/',
            hint: 'India — mobile tenure, porting history and network signals.',
            sort: 24,
        );
    }
}
