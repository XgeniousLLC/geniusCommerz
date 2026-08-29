<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Sendbox implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'sendbox',
            group: 'carrier',
            label: 'Sendbox',
            driver: \App\Services\Carriers\SendboxCarrier::class,
            fields: [
                CredentialField::text('app_id', 'App ID'),
                CredentialField::secret('access_token', 'Access Token'),
            ],
            environments: ['live'],
            countries: ['NG', 'GH', 'KE'],
            docsUrl: 'https://docs.sendbox.co/',
            hint: 'Nigeria — domestic and international lanes.',
            sort: 30,
        );
    }
}
