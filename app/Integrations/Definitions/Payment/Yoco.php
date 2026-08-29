<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Yoco implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'yoco',
            group: 'payment',
            label: 'Yoco',
            driver: \App\Services\Payments\YocoGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'sk_test_… or sk_live_…'),
                CredentialField::secret('webhook_secret', 'Webhook Secret', 'whsec_…'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['ZAR'],
            countries: ['ZA'],
            docsUrl: 'https://developer.yoco.com/online/api-reference',
            hint: 'South Africa — hosted checkout.',
            sort: 47,
        );
    }
}
