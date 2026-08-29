<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Midtrans implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'midtrans',
            group: 'payment',
            label: 'Midtrans',
            driver: \App\Services\Payments\MidtransGateway::class,
            fields: [
                CredentialField::secret('server_key', 'Server Key', 'Mid-server-… ; also signs webhooks'),
                CredentialField::optional('client_key', 'Client Key'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['IDR'],
            countries: ['ID'],
            docsUrl: 'https://docs.midtrans.com/reference/snap-1',
            hint: 'Indonesian cards, bank transfer, e-wallets and convenience stores.',
            sort: 24,
        );
    }
}
