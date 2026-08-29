<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Ravelin implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'ravelin',
            group: 'fraud',
            label: 'Ravelin',
            driver: \App\Services\Fraud\RavelinDriver::class,
            fields: [
                CredentialField::secret('api_key', 'Secret API Key'),
            ],
            environments: ['live'],
            countries: ['GB', 'IE', 'FR', 'DE', 'NL', 'ES', 'IT', 'SE', 'DK', 'NO', 'PL'],
            docsUrl: 'https://developer.ravelin.com/apis/checkout/',
            hint: 'UK-headquartered, strong across European e-commerce. Returns an allow/review/prevent recommendation.',
            sort: 23,
        );
    }
}
