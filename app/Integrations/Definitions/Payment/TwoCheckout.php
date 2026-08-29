<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class TwoCheckout implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'twocheckout',
            group: 'payment',
            label: '2Checkout',
            driver: \App\Services\Payments\TwoCheckoutGateway::class,
            fields: [
                CredentialField::text('merchant_code', 'Merchant Code'),
                CredentialField::secret('secret_key', 'Secret Key'),
                CredentialField::secret('secret_word', 'Secret Word', 'Used to verify the IPN'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['*'],
            docsUrl: 'https://verifone.cloud/docs/2checkout',
            hint: 'Merchant of record, like Paddle — handles global tax on your behalf.',
            sort: 44,
        );
    }
}
