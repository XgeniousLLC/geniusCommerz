<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Clickatell implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'clickatell',
            group: 'sms',
            label: 'Clickatell',
            driver: \App\Services\Sms\ClickatellGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::optional('sender_id', 'Sender ID'),
            ],
            environments: ['live'],
            countries: ['ZA', 'NG', 'KE', 'GH'],
            hint: 'South African origin, strong across Africa.',
            sort: 122,
        );
    }
}
