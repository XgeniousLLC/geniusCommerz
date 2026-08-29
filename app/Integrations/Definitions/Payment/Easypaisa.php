<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Easypaisa implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'easypaisa',
            group: 'payment',
            label: 'Easypaisa',
            driver: \App\Services\Payments\EasypaisaGateway::class,
            fields: [
                CredentialField::text('store_id', 'Store ID'),
                CredentialField::text('username', 'API Username'),
                CredentialField::secret('password', 'API Password'),
                CredentialField::secret('callback_token', 'Callback Token', 'Any long random string; append it as ?token=… on the callback URL'),
            ],
            capabilities: [
                Capability::DirectCharge,
                Capability::Webhook,
            ],
            currencies: ['PKR'],
            countries: ['PK'],
            docsUrl: 'https://easypaisa.com.pk/easypaisa-payment-gateway/',
            hint: 'Pakistan — wallet charge approved on the customer\'s handset. Not a redirect.',
            sort: 38,
        );
    }
}
