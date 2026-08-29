<?php

namespace App\Integrations\Definitions\Courier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Pathao implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'pathao',
            group: 'courier',
            label: 'Pathao Courier',
            driver: \App\Services\Couriers\PathaoService::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
                CredentialField::text('username', 'Username'),
                CredentialField::secret('password', 'Password'),
                CredentialField::text('base_url', 'Base URL'),
            ],
            countries: ['BD'],
            sort: 10,
        );
    }
}
