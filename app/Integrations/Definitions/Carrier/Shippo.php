<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Shippo implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'shippo',
            group: 'carrier',
            label: 'Shippo',
            driver: \App\Services\Carriers\ShippoCarrier::class,
            fields: [
                CredentialField::secret('api_token', 'API Token'),
                CredentialField::optional('default_carrier', 'Default Tracking Carrier', 'e.g. usps, ups, dhl_express'),
            ],
            environments: ['live'],
            docsUrl: 'https://docs.goshippo.com/',
            hint: 'Global aggregator — different carrier mix to EasyPost.',
            sort: 63,
        );
    }
}
