<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Paystack implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'paystack',
            group: 'payment',
            label: 'Paystack',
            driver: \App\Services\Payments\PaystackGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'sk_test_… or sk_live_…; also signs webhooks'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
                Capability::PartialRefund,
            ],
            currencies: ['NGN', 'GHS', 'ZAR', 'KES', 'USD'],
            countries: ['NG', 'GH', 'ZA', 'KE'],
            docsUrl: 'https://paystack.com/docs/api/transaction/',
            hint: 'Nigeria, Ghana, South Africa and Kenya.',
            sort: 22,
        );
    }
}
