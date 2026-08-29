<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Mram implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'mram',
            group: 'sms',
            label: 'MRAM SMS',
            driver: \App\Services\Sms\MramGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'From msg.mram.com.bd → Developers API'),
                CredentialField::text('sender_id', 'Sender ID', 'Approved sender ID / masking'),
                CredentialField::optional('label', 'SMS Label', 'Optional — transactional or promotional'),
                CredentialField::text('base_url', 'Base URL', 'https://msg.mram.com.bd/smsapi'),
            ],
            countries: ['BD'],
            sort: 30,
        );
    }
}
