<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class PayUIndia implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'payu_india',
            group: 'payment',
            label: 'PayU India',
            driver: \App\Services\Payments\PayUIndiaGateway::class,
            fields: [
                CredentialField::text('merchant_key', 'Merchant Key'),
                CredentialField::secret('salt', 'Merchant Salt'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['INR'],
            countries: ['IN'],
            docsUrl: 'https://devguide.payu.in/',
            hint: 'India — cards, UPI and netbanking.',
            sort: 31,
        );
    }
}
