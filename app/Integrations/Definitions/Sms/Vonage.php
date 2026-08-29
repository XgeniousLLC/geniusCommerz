<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Vonage implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'vonage',
            group: 'sms',
            label: 'Vonage',
            driver: \App\Services\Sms\VonageGateway::class,
            fields: [
                CredentialField::text('api_key', 'API Key'),
                CredentialField::secret('api_secret', 'API Secret'),
                CredentialField::text('from', 'Sender ID / From Number', 'An approved alphanumeric sender or a number in E.164'),
            ],
            environments: ['live'],
            sort: 50,
        );
    }
}
