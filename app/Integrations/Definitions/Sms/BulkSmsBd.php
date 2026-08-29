<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class BulkSmsBd implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'bulksmsbd',
            group: 'sms',
            label: 'BulkSMSBD',
            driver: \App\Services\Sms\BulkSmsBdGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('sender_id', 'Sender ID', 'Approved sender name or number'),
                CredentialField::text('base_url', 'Base URL', 'https://bulksmsbd.net/api/smsapi'),
            ],
            countries: ['BD'],
            sort: 10,
        );
    }
}
