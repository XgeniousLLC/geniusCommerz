<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Cashfree implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'cashfree',
            group: 'payment',
            label: 'Cashfree',
            driver: \App\Services\Payments\CashfreeGateway::class,
            fields: [
                CredentialField::text('client_id', 'App ID'),
                CredentialField::secret('client_secret', 'Secret Key'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['INR'],
            countries: ['IN'],
            docsUrl: 'https://docs.cashfree.com/reference/pg-new-apis-endpoint',
            hint: 'India — cards, UPI, netbanking and wallets.',
            sort: 30,
        );
    }
}
