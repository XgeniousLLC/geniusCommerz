<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Termii implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'termii',
            group: 'sms',
            label: 'Termii',
            driver: \App\Services\Sms\TermiiGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('sender_id', 'Sender ID'),
                CredentialField::optional('channel', 'Channel', 'generic, dnd or whatsapp'),
                CredentialField::optional('base_url', 'Endpoint', 'Regional host, e.g. https://api.ng.termii.com'),
            ],
            environments: ['live'],
            countries: ['NG', 'GH', 'KE', 'ZA'],
            hint: 'Nigeria and West Africa — includes a DND channel.',
            sort: 121,
        );
    }
}
