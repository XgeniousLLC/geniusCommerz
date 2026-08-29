<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class VippsMobilePay implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'vipps',
            group: 'payment',
            label: 'Vipps MobilePay',
            driver: \App\Services\Payments\VippsMobilePayGateway::class,
            fields: [
                CredentialField::text('client_id', 'Client ID'),
                CredentialField::secret('client_secret', 'Client Secret'),
                CredentialField::secret('subscription_key', 'Subscription Key'),
                CredentialField::text('merchant_serial_number', 'Merchant Serial Number'),
                CredentialField::secret('callback_token', 'Callback Token', 'Any long random string; append it as ?token=… on the webhook URL'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['NOK', 'DKK', 'EUR', 'SEK'],
            countries: ['NO', 'DK', 'FI', 'SE'],
            docsUrl: 'https://developer.vippsmobilepay.com/docs/APIs/epayment-api/',
            hint: 'Nordics — one integration covers both the Vipps and MobilePay wallets.',
            sort: 49,
        );
    }
}
