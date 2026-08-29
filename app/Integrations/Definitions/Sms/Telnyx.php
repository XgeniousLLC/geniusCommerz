<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Telnyx implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'telnyx',
            group: 'sms',
            label: 'Telnyx',
            driver: \App\Services\Sms\TelnyxGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::optional('from_number', 'From Number', 'In E.164, e.g. +15551234567'),
                CredentialField::optional('messaging_profile_id', 'Messaging Profile ID', 'Use instead of a fixed from number'),
            ],
            environments: ['live'],
            hint: 'Global coverage with alphanumeric sender support.',
            sort: 132,
        );
    }
}
