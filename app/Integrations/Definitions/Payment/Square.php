<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Square implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'square',
            group: 'payment',
            label: 'Square',
            driver: \App\Services\Payments\SquareGateway::class,
            fields: [
                CredentialField::secret('access_token', 'Access Token'),
                CredentialField::text('location_id', 'Location ID'),
                CredentialField::secret('webhook_signature_key', 'Webhook Signature Key'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['USD', 'CAD', 'GBP', 'AUD', 'JPY', 'EUR'],
            countries: ['US', 'CA', 'GB', 'AU', 'JP', 'IE'],
            docsUrl: 'https://developer.squareup.com/docs/checkout-api',
            hint: 'US, Canada, UK, Australia, Japan and Ireland — strong if you also sell in person.',
            sort: 41,
        );
    }
}
