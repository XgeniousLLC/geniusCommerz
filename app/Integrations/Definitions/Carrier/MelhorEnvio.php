<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class MelhorEnvio implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'melhorenvio',
            group: 'carrier',
            label: 'Melhor Envio',
            driver: \App\Services\Carriers\MelhorEnvioCarrier::class,
            fields: [
                CredentialField::secret('access_token', 'Access Token'),
                CredentialField::optional('user_agent', 'User Agent', 'Melhor Envio requires an identifying user agent'),
            ],
            environments: ['live'],
            countries: ['BR'],
            docsUrl: 'https://docs.melhorenvio.com.br/',
            hint: 'Brazil — aggregates Correios, Jadlog, Loggi and others.',
            sort: 40,
        );
    }
}
