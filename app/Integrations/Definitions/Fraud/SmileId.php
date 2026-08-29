<?php

namespace App\Integrations\Definitions\Fraud;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class SmileId implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'smileid',
            group: 'fraud',
            label: 'Smile ID',
            driver: \App\Services\Fraud\SmileIdDriver::class,
            fields: [
                CredentialField::text('partner_id', 'Partner ID'),
                CredentialField::secret('api_key', 'API Key'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['NG', 'KE', 'GH', 'ZA', 'UG', 'TZ', 'RW'],
            docsUrl: 'https://docs.usesmileid.com/',
            hint: 'Pan-African identity verification and phone intelligence.',
            sort: 26,
        );
    }
}
