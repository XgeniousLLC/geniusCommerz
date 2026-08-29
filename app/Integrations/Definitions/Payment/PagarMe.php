<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class PagarMe implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'pagarme',
            group: 'payment',
            label: 'Pagar.me',
            driver: \App\Services\Payments\PagarMeGateway::class,
            fields: [
                CredentialField::secret('secret_key', 'Secret Key', 'sk_test_… or sk_…'),
                CredentialField::secret('webhook_secret', 'Webhook Secret'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
                Capability::Refund,
            ],
            currencies: ['BRL'],
            countries: ['BR'],
            docsUrl: 'https://docs.pagar.me/reference',
            hint: 'Brazil — Pix, boleto and cards. Pix is the default method.',
            sort: 35,
        );
    }
}
