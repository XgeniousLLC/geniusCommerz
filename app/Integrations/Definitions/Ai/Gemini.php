<?php

namespace App\Integrations\Definitions\Ai;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Gemini implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'gemini',
            group: 'ai',
            label: 'Google Gemini',
            driver: \App\Services\Ai\GeminiDriver::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'From Google AI Studio'),
                CredentialField::text('model', 'Model', 'e.g. gemini-1.5-flash, gemini-1.5-pro'),
            ],
            sort: 20,
        );
    }
}
