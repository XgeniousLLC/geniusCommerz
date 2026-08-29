<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Monnify implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'monnify',
            group: 'payment',
            label: 'Monnify',
            driver: \App\Services\Payments\MonnifyGateway::class,
            fields: [
                CredentialField::text('api_key', 'API Key'),
                CredentialField::secret('secret_key', 'Secret Key'),
                CredentialField::text('contract_code', 'Contract Code'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['NGN'],
            countries: ['NG'],
            docsUrl: 'https://developers.monnify.com/api/',
            hint: 'Nigeria — cards, bank transfer and USSD.',
            sort: 34,
        );
    }
}
