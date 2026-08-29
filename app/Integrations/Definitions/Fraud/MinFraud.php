<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class MinFraud implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'minfraud',
            group: 'fraud',
            label: 'MaxMind minFraud',
            driver: \App\Services\Fraud\MinFraudDriver::class,
            fields: [
                CredentialField::text('account_id', 'Account ID'),
                CredentialField::secret('licence_key', 'Licence Key'),
            ],
            environments: ['live'],
            docsUrl: 'https://dev.maxmind.com/minfraud/api-documentation/',
            hint: 'IP, email and phone risk scoring. Requires an IP address to score well.',
            sort: 22,
        );
    }
}
