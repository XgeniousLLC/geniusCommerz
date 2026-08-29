<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class IpQualityScore implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'ipqualityscore',
            group: 'fraud',
            label: 'IPQualityScore',
            driver: \App\Services\Fraud\IpQualityScoreDriver::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'From your IPQualityScore dashboard'),
            ],
            environments: ['live'],
            docsUrl: 'https://www.ipqualityscore.com/documentation/phone-number-validation-api/overview',
            hint: 'Global phone, email and IP reputation — works outside Bangladesh.',
            sort: 5,
        );
    }
}
