<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Tap implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'tap',
            group: 'payment',
            label: 'Tap Payments',
            driver: \App\Services\Payments\TapGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'sk_test_… or sk_live_…'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['KWD', 'SAR', 'AED', 'BHD', 'QAR', 'OMR', 'EGP', 'USD'],
            docsUrl: 'https://developers.tap.company/reference/create-a-charge',
            hint: 'Gulf — KNET, mada, Benefit, Apple Pay and cards.',
            sort: 32,
        );
    }
}
