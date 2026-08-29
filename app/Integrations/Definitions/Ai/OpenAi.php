<?php

namespace App\Integrations\Definitions\Ai;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class OpenAi implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'openai',
            group: 'ai',
            label: 'OpenAI',
            driver: \App\Services\Ai\OpenAiDriver::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'Starts with sk-…'),
                CredentialField::text('model', 'Model', 'e.g. gpt-4o, gpt-4o-mini'),
            ],
            sort: 10,
        );
    }
}
