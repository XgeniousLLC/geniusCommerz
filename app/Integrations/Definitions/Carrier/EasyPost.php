<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class EasyPost implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'easypost',
            group: 'carrier',
            label: 'EasyPost',
            driver: \App\Services\Carriers\EasyPostCarrier::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'Test keys start EZTK…, production EZAK…'),
            ],
            docsUrl: 'https://docs.easypost.com/docs/shipments',
            hint: 'One integration covering USPS, UPS, FedEx, DHL and more.',
            sort: 10,
        );
    }
}
