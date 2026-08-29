<?php

namespace App\Integrations\Definitions\Carrier;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class GigLogistics implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'gigl',
            group: 'carrier',
            label: 'GIG Logistics',
            driver: \App\Services\Carriers\GigLogisticsCarrier::class,
            fields: [
                CredentialField::text('username', 'Username'),
                CredentialField::secret('password', 'Password'),
                CredentialField::optional('sender_station_id', 'Sender Station ID'),
                CredentialField::optional('receiver_station_id', 'Receiver Station ID'),
            ],
            environments: ['sandbox', 'live'],
            countries: ['NG'],
            docsUrl: 'https://giglogistics.com/',
            hint: 'Nigeria — nationwide delivery.',
            sort: 31,
        );
    }
}
