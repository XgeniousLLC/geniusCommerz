<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Twilio implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'twilio',
            group: 'sms',
            label: 'Twilio',
            driver: \App\Services\Sms\TwilioGateway::class,
            fields: [
                CredentialField::text('account_sid', 'Account SID'),
                CredentialField::secret('auth_token', 'Auth Token'),
                CredentialField::text('from_number', 'From Number', 'e.g. +15551234567'),
            ],
            docsUrl: 'https://www.twilio.com/docs/sms',
            sort: 40,
        );
    }
}
