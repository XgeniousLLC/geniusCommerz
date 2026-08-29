<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Youverify implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'youverify',
            group: 'fraud',
            label: 'Youverify',
            driver: \App\Services\Fraud\YouverifyDriver::class,
            fields: [
                CredentialField::secret('api_token', 'API Token'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['NG', 'GH', 'KE'],
            docsUrl: 'https://doc.youverify.co/',
            hint: 'Nigeria and West Africa — phone and identity lookups.',
            sort: 27,
        );
    }
}
