<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class PayTabs implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'paytabs',
            group: 'payment',
            label: 'PayTabs',
            driver: \App\Services\Payments\PayTabsGateway::class,
            fields: [
                CredentialField::text('profile_id', 'Profile ID'),
                CredentialField::secret('server_key', 'Server Key'),
                CredentialField::text('base_url', 'Regional Endpoint', 'e.g. https://secure.paytabs.sa for Saudi Arabia'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['SAR', 'AED', 'EGP', 'OMR', 'JOD', 'KWD', 'BHD', 'QAR', 'USD', 'EUR', 'GBP'],
            docsUrl: 'https://support.paytabs.com/en/support/solutions/articles/60000711548',
            hint: 'Gulf and wider MENA. Note PayTabs is region-sharded — set the endpoint for your account.',
            sort: 26,
        );
    }
}
