<?php

namespace App\Integrations\Definitions\Ai;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class Claude implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'claude',
            group: 'ai',
            label: 'Anthropic Claude',
            driver: \App\Services\Ai\ClaudeDriver::class,
            fields: [
                CredentialField::secret('api_key', 'API Key', 'Starts with sk-ant-…'),
                CredentialField::text('model', 'Model', 'e.g. claude-haiku-4-5-20251001, claude-sonnet-4-6'),
            ],
            sort: 30,
        );
    }
}
