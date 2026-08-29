<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class MtnMomo implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'mtn_momo',
            group: 'payment',
            label: 'MTN MoMo',
            driver: \App\Services\Payments\MtnMomoGateway::class,
            fields: [
                CredentialField::text('api_user', 'API User ID'),
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::secret('subscription_key', 'Subscription Key', 'From the MoMo developer portal'),
                CredentialField::optional('target_environment', 'Target Environment', 'e.g. mtnghana, mtnuganda'),
                CredentialField::secret('callback_token', 'Callback Token', 'Any long random string; append it as ?token=… on the callback URL'),
            ],
            capabilities: [
                Capability::DirectCharge,
                Capability::Webhook,
            ],
            currencies: ['GHS', 'UGX', 'XAF', 'XOF', 'RWF', 'ZMW', 'EUR'],
            docsUrl: 'https://momodeveloper.mtn.com/api-documentation',
            hint: 'Ghana, Uganda, Cameroon and more — push to the customer\'s handset.',
            sort: 37,
        );
    }
}
