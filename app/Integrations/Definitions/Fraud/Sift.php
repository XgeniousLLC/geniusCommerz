<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Sift implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'sift',
            group: 'fraud',
            label: 'Sift',
            driver: \App\Services\Fraud\SiftDriver::class,
            fields: [
                CredentialField::secret('api_key', 'REST API Key'),
            ],
            environments: ['live'],
            docsUrl: 'https://developers.sift.com/docs/curl/score-api/overview',
            hint: 'Machine-learning scoring. Improves as order events are fed in over time.',
            sort: 21,
        );
    }
}
