<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class MercadoPago implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'mercadopago',
            group: 'payment',
            label: 'MercadoPago',
            driver: \App\Services\Payments\MercadoPagoGateway::class,
            fields: [
                CredentialField::secret('access_token', 'Access Token', 'APP_USR-… or TEST-…'),
                CredentialField::secret('webhook_secret', 'Webhook Secret', 'From Your integrations → Webhooks'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['ARS', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'UYU'],
            countries: ['AR', 'BR', 'CL', 'CO', 'MX', 'PE', 'UY'],
            docsUrl: 'https://www.mercadopago.com/developers/en/reference/preferences/_checkout_preferences/post',
            hint: 'Brazil, Argentina, Mexico and wider LatAm.',
            sort: 27,
        );
    }
}
