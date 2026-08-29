<?php

namespace App\Integrations\Definitions\Sms;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Fast2Sms implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'fast2sms',
            group: 'sms',
            label: 'Fast2SMS',
            driver: \App\Services\Sms\Fast2SmsGateway::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::optional('route', 'Route', 'q = quick transactional, dlt = DLT-approved'),
            ],
            environments: ['live'],
            countries: ['IN'],
            hint: 'India only — takes a bare 10-digit number.',
            sort: 102,
        );
    }
}
