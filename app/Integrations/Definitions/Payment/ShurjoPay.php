<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class ShurjoPay implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'shurjopay',
            group: 'payment',
            label: 'ShurjoPay',
            driver: \App\Services\Payments\ShurjoPayGateway::class,
            fields: [
                CredentialField::text('username', 'Merchant Username'),
                CredentialField::secret('password', 'Merchant Password'),
                CredentialField::optional('prefix', 'Order Prefix', 'Assigned by ShurjoPay, e.g. SP'),
                CredentialField::optional('base_url', 'Endpoint', 'Override if ShurjoPay gave you a different host'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['BDT'],
            countries: ['BD'],
            docsUrl: 'https://shurjopay.com.bd/frontend-developers/direct-integration-with-api',
            hint: 'Bangladesh — cards and mobile financial services.',
            sort: 7,
        );
    }
}
