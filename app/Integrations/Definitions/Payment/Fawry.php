<?php

namespace App\Integrations\Definitions\Payment;

use App\Integrations\Capability;
use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Fawry implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'fawry',
            group: 'payment',
            label: 'Fawry',
            driver: \App\Services\Payments\FawryGateway::class,
            fields: [
                CredentialField::text('merchant_code', 'Merchant Code'),
                CredentialField::secret('security_key', 'Security Key'),
            ],
            capabilities: [
                Capability::HostedRedirect,
                Capability::Webhook,
            ],
            currencies: ['EGP'],
            countries: ['EG'],
            docsUrl: 'https://developer.fawrystaging.com/docs',
            hint: 'Egypt — cards plus cash at Fawry outlets, which stay pending until paid in person.',
            sort: 46,
        );
    }
}
