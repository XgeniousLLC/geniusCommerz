<?php

namespace App\Integrations\Definitions\Ai;

use App\Integrations\CredentialField;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProvidesDefinition;

class DeepSeek implements ProvidesDefinition
{
    public static function definition(): ProviderDefinition
    {
        return new ProviderDefinition(
            slug: 'deepseek',
            group: 'ai',
            label: 'DeepSeek',
            driver: \App\Services\Ai\DeepSeekDriver::class,
            fields: [
                CredentialField::secret('api_key', 'API Key'),
                CredentialField::text('model', 'Model', 'e.g. deepseek-chat, deepseek-reasoner'),
            ],
            sort: 40,
        );
    }
}
