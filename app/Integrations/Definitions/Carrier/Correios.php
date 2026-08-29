<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Correios implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'correios',
            group: 'carrier',
            label: 'Correios',
            driver: \App\Services\Carriers\CorreiosCarrier::class,
            fields: [
                CredentialField::text('username', 'Username'),
                CredentialField::secret('access_code', 'Access Code'),
                CredentialField::text('posting_card', 'Posting Card'),
                CredentialField::optional('contract', 'Contract Number'),
                CredentialField::optional('dr_number', 'DR Number'),
                CredentialField::optional('service_code', 'Service Code', 'e.g. 03298 for PAC'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['BR'],
            docsUrl: 'https://www.correios.com.br/atendimento/developers',
            hint: 'Brazil\'s national postal operator.',
            sort: 41,
        );
    }
}
