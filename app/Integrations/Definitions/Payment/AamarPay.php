<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class AamarPay implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'aamarpay',
            group: 'payment',
            label: 'aamarPay',
            driver: \App\Services\Payments\AamarPayGateway::class,
            fields: [
                CredentialField::text('store_id', 'Store ID'),
                CredentialField::secret('signature_key', 'Signature Key'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['BDT', 'USD'],
            countries: ['BD'],
            docsUrl: 'https://aamarpay.readme.io/',
            hint: 'Bangladesh — cards, bKash, Nagad, Rocket and net banking.',
            sort: 6,
        );
    }
}
