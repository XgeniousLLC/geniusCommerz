<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class AwsSns implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'awssns',
            group: 'sms',
            label: 'Amazon SNS',
            driver: \App\Services\Sms\AwsSnsGateway::class,
            fields: [
                CredentialField::text('access_key_id', 'Access Key ID'),
                CredentialField::secret('secret_access_key', 'Secret Access Key'),
                CredentialField::text('region', 'Region', 'e.g. us-east-1'),
                CredentialField::optional('sender_id', 'Sender ID', 'Where the destination country supports alphanumeric senders'),
            ],
            environments: ['live'],
            sort: 80,
        );
    }
}
