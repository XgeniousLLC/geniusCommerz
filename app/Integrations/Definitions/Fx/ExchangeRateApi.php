<?php

namespace App\Integrations\Definitions\Fx;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class ExchangeRateApi implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'exchangerate_api',
            group: 'fx',
            label: 'ExchangeRate-API',
            driver: \App\Services\Fx\ExchangeRateApiDriver::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
            ],
            environments: ['live'],
            docsUrl: 'https://www.exchangerate-api.com/docs/overview',
            hint: 'Paid tiers refresh more often and carry an SLA.',
            sort: 20,
        );
    }
}
