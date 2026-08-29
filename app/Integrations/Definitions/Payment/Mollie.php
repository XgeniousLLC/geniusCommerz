<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Mollie implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'mollie',
            group: 'payment',
            label: 'Mollie',
            driver: \App\Services\Payments\MollieGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'test_… or live_…'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
                Capability::PartialRefund,
            ],
            currencies: ['EUR', 'GBP', 'CHF', 'DKK', 'NOK', 'SEK', 'PLN', 'CZK', 'HUF', 'USD', 'CAD', 'AUD'],
            docsUrl: 'https://docs.mollie.com/reference/v2/payments-api/create-payment',
            hint: 'iDEAL, Bancontact, SEPA and cards across Europe.',
            sort: 21,
        );
    }
}
