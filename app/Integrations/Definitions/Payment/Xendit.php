<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Xendit implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'xendit',
            group: 'payment',
            label: 'Xendit',
            driver: \App\Services\Payments\XenditGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'xnd_development_… or xnd_production_…'),
                CredentialField::secret('callback_token', 'Callback Verification Token', 'From Settings → Webhooks in Xendit'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['IDR', 'PHP', 'USD'],
            countries: ['ID', 'PH'],
            docsUrl: 'https://developers.xendit.co/api-reference/#create-invoice',
            hint: 'Indonesia and the Philippines.',
            sort: 25,
        );
    }
}
