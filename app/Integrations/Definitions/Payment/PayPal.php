<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class PayPal implements ProvidesDefinition
{
    /** PayPal settles in this fixed list; anything else is rejected at their API. */
    private const CURRENCIES = [
        'AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS',
        'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD',
    ];

    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'paypal',
            group: 'payment',
            label: 'PayPal',
            driver: \App\Services\Payments\PayPalGateway::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
                CredentialField::secret('webhook_id', 'Webhook ID', 'From Developer Dashboard → Webhooks'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
                Capability::PartialRefund,
            ],
            currencies: self::CURRENCIES,
            docsUrl: 'https://developer.paypal.com/docs/api/orders/v2/',
            sort: 3,
        );
    }
}
