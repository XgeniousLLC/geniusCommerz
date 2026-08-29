<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Razorpay implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'razorpay',
            group: 'payment',
            label: 'Razorpay',
            driver: \App\Services\Payments\RazorpayGateway::class,
            fields: [
                CredentialField::text('key_id', 'Key ID', 'rzp_test_… or rzp_live_…'),
                CredentialField::secret('key_secret', 'Key Secret'),
                CredentialField::secret('webhook_secret', 'Webhook Secret', 'Set when you add the webhook in Razorpay'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
                Capability::PartialRefund,
            ],
            currencies: ['INR', 'USD', 'EUR', 'GBP', 'SGD', 'AED', 'MYR', 'AUD', 'CAD'],
            docsUrl: 'https://razorpay.com/docs/api/payment-links/',
            hint: 'India and cross-border cards, UPI and netbanking.',
            sort: 20,
        );
    }
}
