<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class AfricasTalking implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'africastalking',
            group: 'sms',
            label: 'Africa\'s Talking',
            driver: \App\Services\Sms\AfricasTalkingGateway::class,
            fields: [
                CredentialField::text('username', 'Username', 'Use \'sandbox\' in sandbox mode'),
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::optional('sender_id', 'Sender ID', 'Alphanumeric short code where approved'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['KE', 'UG', 'TZ', 'NG', 'RW', 'MW', 'ZA'],
            hint: 'Kenya, Uganda, Tanzania, Nigeria and more.',
            sort: 120,
        );
    }
}
