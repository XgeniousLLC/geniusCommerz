<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Mpesa implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'mpesa',
            group: 'payment',
            label: 'M-Pesa',
            driver: \App\Services\Payments\MpesaGateway::class,
            fields: [
                CredentialField::text('consumer_key', 'Consumer Key'),
                CredentialField::secret('consumer_secret', 'Consumer Secret'),
                CredentialField::text('shortcode', 'Business Shortcode'),
                CredentialField::secret('passkey', 'Passkey'),
                CredentialField::secret('callback_token', 'Callback Token', 'Any long random string; append it as ?token=… on the callback URL'),
            ],
            capabilities: [
                Capability::DirectCharge,
                Capability::Webhook,
            ],
            currencies: ['KES'],
            countries: ['KE'],
            docsUrl: 'https://developer.safaricom.co.ke/APIs/MpesaExpressSimulate',
            hint: 'Kenya — STK push to the customer\'s handset. Not a redirect.',
            sort: 36,
        );
    }
}
