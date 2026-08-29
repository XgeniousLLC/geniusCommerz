<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Paytm implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'paytm',
            group: 'payment',
            label: 'Paytm',
            driver: \App\Services\Payments\PaytmGateway::class,
            fields: [
                CredentialField::text('merchant_id', 'Merchant ID (MID)'),
                CredentialField::secret('merchant_key', 'Merchant Key'),
                CredentialField::optional('website', 'Website Name', 'e.g. WEBSTAGING or DEFAULT'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['INR'],
            countries: ['IN'],
            docsUrl: 'https://business.paytm.com/docs/api/initiate-transaction-api/',
            hint: 'India — UPI, cards, netbanking and Paytm wallet.',
            sort: 51,
        );
    }
}
