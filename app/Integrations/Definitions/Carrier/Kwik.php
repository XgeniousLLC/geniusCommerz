<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Kwik implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'kwik',
            group: 'carrier',
            label: 'Kwik Delivery',
            driver: \App\Services\Carriers\KwikCarrier::class,
            fields: [
                CredentialField::text('email', 'Account Email'),
                CredentialField::secret('password', 'Password'),
                CredentialField::text('domain_name', 'Domain Name'),
                CredentialField::optional('vendor_id', 'Vendor ID'),
            ],
            environments: ['live'],
            countries: ['NG'],
            docsUrl: 'https://kwik.delivery/',
            hint: 'Nigeria — same-day and on-demand.',
            sort: 32,
        );
    }
}
