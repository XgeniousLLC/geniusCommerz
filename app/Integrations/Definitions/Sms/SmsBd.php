<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class SmsBd implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'smsbd',
            group: 'sms',
            label: 'SMS.BD',
            driver: \App\Services\Sms\SmsBdGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('sender_id', 'Sender ID'),
                CredentialField::text('base_url', 'Base URL', 'https://sms.smsbd.in/api/v2/send'),
            ],
            countries: ['BD'],
            sort: 20,
        );
    }
}
