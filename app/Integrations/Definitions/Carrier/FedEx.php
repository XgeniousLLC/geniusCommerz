<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class FedEx implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'fedex',
            group: 'carrier',
            label: 'FedEx',
            driver: \App\Services\Carriers\FedExCarrier::class,
            fields: [
                CredentialField::text('client_id', 'API Key'),
                CredentialField::secret('client_secret', 'Secret Key'),
                CredentialField::text('account_number', 'Account Number'),
            ],
            environments: ['sandbox', 'live'],
            docsUrl: 'https://developer.fedex.com/api/en-us/catalog/rate.html',
            hint: 'Global express and ground.',
            sort: 61,
        );
    }
}
