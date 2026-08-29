<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Ups implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'ups',
            group: 'carrier',
            label: 'UPS',
            driver: \App\Services\Carriers\UpsCarrier::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
                CredentialField::text('account_number', 'Shipper Number'),
            ],
            environments: ['sandbox', 'live'],
            docsUrl: 'https://developer.ups.com/api/reference',
            hint: 'Global parcel network.',
            sort: 62,
        );
    }
}
