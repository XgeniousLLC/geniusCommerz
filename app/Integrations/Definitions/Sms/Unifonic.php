<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Unifonic implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'unifonic',
            group: 'sms',
            label: 'Unifonic',
            driver: \App\Services\Sms\UnifonicGateway::class,
            fields: [
                CredentialField::secret('app_sid', 'App SID'),
                CredentialField::text('sender_id', 'Sender ID'),
            ],
            environments: ['live'],
            countries: ['SA', 'AE', 'KW', 'BH', 'QA', 'OM', 'EG'],
            hint: 'Saudi Arabia and the wider Gulf.',
            sort: 110,
        );
    }
}
