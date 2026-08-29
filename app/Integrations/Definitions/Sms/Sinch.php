<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Sinch implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'sinch',
            group: 'sms',
            label: 'Sinch',
            driver: \App\Services\Sms\SinchGateway::class,
            fields: [
                CredentialField::secret('api_token', 'API Token'),
                CredentialField::text('service_plan_id', 'Service Plan ID'),
                CredentialField::optional('sender_id', 'Sender ID / From Number'),
                CredentialField::optional('region', 'Region', 'us, eu, au, br or ca'),
            ],
            environments: ['live'],
            hint: 'Global coverage, region-sharded.',
            sort: 131,
        );
    }
}
