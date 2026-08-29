<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Plivo implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'plivo',
            group: 'sms',
            label: 'Plivo',
            driver: \App\Services\Sms\PlivoGateway::class,
            fields: [
                CredentialField::text('auth_id', 'Auth ID'),
                CredentialField::secret('auth_token', 'Auth Token'),
                CredentialField::text('from', 'From Number', 'In E.164, e.g. +15551234567'),
            ],
            environments: ['live'],
            sort: 70,
        );
    }
}
