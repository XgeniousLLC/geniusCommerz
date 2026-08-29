<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Taqnyat implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'taqnyat',
            group: 'sms',
            label: 'Taqnyat',
            driver: \App\Services\Sms\TaqnyatGateway::class,
            fields: [
                CredentialField::secret('bearer_token', 'Bearer Token'),
                CredentialField::text('sender_id', 'Sender Name'),
            ],
            environments: ['live'],
            countries: ['SA'],
            hint: 'Saudi Arabia — locally registered sender names.',
            sort: 111,
        );
    }
}
