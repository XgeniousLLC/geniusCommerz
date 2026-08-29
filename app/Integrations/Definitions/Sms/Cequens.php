<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Cequens implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'cequens',
            group: 'sms',
            label: 'Cequens',
            driver: \App\Services\Sms\CequensGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('sender_name', 'Sender Name'),
            ],
            environments: ['live'],
            countries: ['EG', 'AE', 'SA', 'JO', 'KW', 'QA'],
            hint: 'Egypt, UAE and wider MENA.',
            sort: 112,
        );
    }
}
