<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Moyasar implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'moyasar',
            group: 'payment',
            label: 'Moyasar',
            driver: \App\Services\Payments\MoyasarGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret API Key'),
                CredentialField::secret('webhook_secret', 'Webhook Token'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['SAR', 'USD'],
            countries: ['SA'],
            docsUrl: 'https://docs.moyasar.com/',
            hint: 'Saudi Arabia — mada, Apple Pay, STC Pay and cards.',
            sort: 33,
        );
    }
}
