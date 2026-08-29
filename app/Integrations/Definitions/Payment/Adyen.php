<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Adyen implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'adyen',
            group: 'payment',
            label: 'Adyen',
            driver: \App\Services\Payments\AdyenGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('merchant_account', 'Merchant Account'),
                CredentialField::secret('hmac_key', 'HMAC Key', 'Hex key from the standard notification setup'),
                CredentialField::optional('live_url_prefix', 'Live URL Prefix', 'Issued by Adyen; required in live mode'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['*'],
            docsUrl: 'https://docs.adyen.com/unified-commerce/pay-by-link/',
            hint: 'Global enterprise coverage — cards, wallets and local methods via Pay by Link.',
            sort: 40,
        );
    }
}
