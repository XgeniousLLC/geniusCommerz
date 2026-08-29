<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Iyzico implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'iyzico',
            group: 'payment',
            label: 'iyzico',
            driver: \App\Services\Payments\IyzicoGateway::class,
            fields: [
                CredentialField::text('api_key', 'API Key'),
                CredentialField::secret('secret_key', 'Secret Key'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['TRY', 'USD', 'EUR', 'GBP'],
            countries: ['TR'],
            docsUrl: 'https://docs.iyzico.com/en/products/pay-with-iyzico',
            hint: 'Turkey — hosted checkout form with instalments.',
            sort: 45,
        );
    }
}
