<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Infobip implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'infobip',
            group: 'sms',
            label: 'Infobip',
            driver: \App\Services\Sms\InfobipGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('base_url', 'API Host', 'Account-specific, e.g. xyz123.api.infobip.com'),
                CredentialField::optional('sender_id', 'Sender ID'),
            ],
            environments: ['live'],
            hint: 'Global coverage with per-account API hosts.',
            sort: 130,
        );
    }
}
