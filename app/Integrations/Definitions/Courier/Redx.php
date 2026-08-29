<?php

namespace App\Integrations\Definitions\Courier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Redx implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'redx',
            group: 'courier',
            label: 'RedX Courier',
            driver: \App\Services\Couriers\RedxService::class,
            fields: [
                CredentialField::text('api_key', 'API Key'),
                CredentialField::text('base_url', 'Base URL'),
            ],
            countries: ['BD'],
            sort: 30,
        );
    }
}
