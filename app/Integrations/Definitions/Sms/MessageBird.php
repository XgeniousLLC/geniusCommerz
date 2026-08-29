<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class MessageBird implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'messagebird',
            group: 'sms',
            label: 'MessageBird',
            driver: \App\Services\Sms\MessageBirdGateway::class,
            fields: [
                CredentialField::secret('access_key', 'Access Key'),
                CredentialField::text('originator', 'Originator', 'Sender name or number shown to the recipient'),
            ],
            environments: ['live'],
            sort: 60,
        );
    }
}
