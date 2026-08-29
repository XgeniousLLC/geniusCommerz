<?php

namespace App\Integrations\Definitions\Courier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Steadfast implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'steadfast',
            group: 'courier',
            label: 'Steadfast Courier',
            driver: \App\Services\Couriers\SteadfastService::class,
            fields: [
                CredentialField::text('api_key', 'API Key'),
                CredentialField::secret('secret_key', 'Secret Key'),
                CredentialField::text('base_url', 'Base URL'),
            ],
            countries: ['BD'],
            sort: 20,
        );
    }
}
