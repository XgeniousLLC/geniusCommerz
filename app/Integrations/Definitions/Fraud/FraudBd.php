<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class FraudBd implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'fraudbd',
            group: 'fraud',
            label: 'FraudBD',
            driver: \App\Services\FraudBdService::class,
            fields: [
                CredentialField::text('api_key', 'API Key'),
                CredentialField::text('base_url', 'Base URL'),
            ],
            environments: ['live'],
            countries: ['BD'],
            sort: 10,
        );
    }
}
