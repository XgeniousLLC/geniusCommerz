<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Paddle implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'paddle',
            group: 'payment',
            label: 'Paddle',
            driver: \App\Services\Payments\PaddleGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::secret('webhook_secret', 'Webhook Secret'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['*'],
            docsUrl: 'https://developer.paddle.com/api-reference/transactions/create-transaction',
            hint: 'Merchant of record — Paddle becomes the seller and remits EU VAT and US sales tax itself.',
            sort: 43,
        );
    }
}
