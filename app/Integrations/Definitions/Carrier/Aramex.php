<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Aramex implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'aramex',
            group: 'carrier',
            label: 'Aramex',
            driver: \App\Services\Carriers\AramexCarrier::class,
            fields: [
                CredentialField::text('username', 'Username'),
                CredentialField::secret('password', 'Password'),
                CredentialField::text('account_number', 'Account Number'),
                CredentialField::secret('account_pin', 'Account PIN'),
                CredentialField::text('account_entity', 'Account Entity', 'e.g. DXB'),
                CredentialField::text('account_country', 'Account Country', 'e.g. AE'),
            ],
            environments: ['live'],
            countries: ['AE', 'SA', 'KW', 'BH', 'QA', 'OM', 'JO', 'EG', 'LB'],
            docsUrl: 'https://www.aramex.com/us/en/developers-solution-center',
            hint: 'Gulf and MENA leader, with global lanes.',
            sort: 50,
        );
    }
}
