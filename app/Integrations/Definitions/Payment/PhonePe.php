<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class PhonePe implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'phonepe',
            group: 'payment',
            label: 'PhonePe',
            driver: \App\Services\Payments\PhonePeGateway::class,
            fields: [
                CredentialField::text('merchant_id', 'Merchant ID'),
                CredentialField::secret('salt_key', 'Salt Key'),
                CredentialField::optional('salt_index', 'Salt Index', 'Usually 1'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['INR'],
            countries: ['IN'],
            docsUrl: 'https://developer.phonepe.com/v1/reference/pay-api',
            hint: 'India — UPI, cards and wallet.',
            sort: 50,
        );
    }
}
