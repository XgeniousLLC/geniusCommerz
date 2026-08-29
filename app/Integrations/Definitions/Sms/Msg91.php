<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Msg91 implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'msg91',
            group: 'sms',
            label: 'MSG91',
            driver: \App\Services\Sms\Msg91Gateway::class,
            fields: [
                CredentialField::secret('auth_key', 'Auth Key'),
                CredentialField::text('sender_id', 'Sender ID', '6-character approved sender'),
                CredentialField::optional('route', 'Route', '4 = transactional, 1 = promotional'),
            ],
            environments: ['live'],
            countries: ['IN'],
            hint: 'India — transactional and OTP SMS.',
            sort: 100,
        );
    }
}
