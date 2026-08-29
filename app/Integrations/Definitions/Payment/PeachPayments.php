<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class PeachPayments implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'peach',
            group: 'payment',
            label: 'Peach Payments',
            driver: \App\Services\Payments\PeachPaymentsGateway::class,
            fields: [
                CredentialField::text('entity_id', 'Entity ID'),
                CredentialField::text('merchant_id', 'Merchant ID'),
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
                CredentialField::secret('webhook_token', 'Webhook Token'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['ZAR', 'KES', 'MUR', 'USD'],
            countries: ['ZA', 'KE', 'MU'],
            docsUrl: 'https://developer.peachpayments.com/docs/checkout-overview',
            hint: 'South Africa, Kenya and Mauritius — cards, EFT and vouchers.',
            sort: 48,
        );
    }
}
