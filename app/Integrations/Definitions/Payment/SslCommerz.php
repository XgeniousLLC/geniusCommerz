<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class SslCommerz implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'sslcommerz',
            group: 'payment',
            label: 'SSLCOMMERZ',
            driver: \App\Services\Payments\SslCommerzGateway::class,
            fields: [
                CredentialField::text('store_id', 'Store ID'),
                CredentialField::secret('store_password', 'Store Password'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['BDT', 'USD', 'EUR', 'GBP'],
            countries: ['BD'],
            docsUrl: 'https://developer.sslcommerz.com/',
            hint: 'Bangladesh — cards, mobile banking and net banking.',
            sort: 5,
        );
    }
}
