<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Smsa implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'smsa',
            group: 'carrier',
            label: 'SMSA Express',
            driver: \App\Services\Carriers\SmsaCarrier::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
            ],
            environments: ['live'],
            countries: ['SA'],
            docsUrl: 'https://smsaexpress.com/',
            hint: 'Saudi Arabia\'s largest domestic express network.',
            sort: 51,
        );
    }
}
