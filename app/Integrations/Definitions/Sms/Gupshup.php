<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Gupshup implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'gupshup',
            group: 'sms',
            label: 'Gupshup',
            driver: \App\Services\Sms\GupshupGateway::class,
            fields: [
                CredentialField::text('user_id', 'User ID'),
                CredentialField::secret('password', 'Password'),
                CredentialField::text('sender_id', 'Sender ID / Mask'),
            ],
            environments: ['live'],
            countries: ['IN'],
            hint: 'India — enterprise messaging.',
            sort: 101,
        );
    }
}
