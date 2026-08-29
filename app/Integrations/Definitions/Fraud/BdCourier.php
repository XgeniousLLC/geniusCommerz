<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class BdCourier implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'bdcourier',
            group: 'fraud',
            label: 'BDCourier',
            driver: \App\Services\BdCourierFraudService::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'Bearer token from api.bdcourier.com'),
            ],
            environments: ['live'],
            countries: ['BD'],
            sort: 20,
        );
    }
}
