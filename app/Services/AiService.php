<?php

namespace App\Services;

use App\Contracts\AiInterface;
use App\Models\Integration;
use App\Services\Ai\ClaudeDriver;
use App\Services\Ai\DeepSeekDriver;
use App\Services\Ai\GeminiDriver;
use App\Services\Ai\OpenAiDriver;

class AiService
{
    public function driver(?string $provider = null): AiInterface
    {
        if ($provider === null) {
            $integration = Integration::defaultAi();
            $provider    = $integration?->provider;
        }

        return match ($provider) {
            'openai'   => app(OpenAiDriver::class),
            'gemini'   => app(GeminiDriver::class),
            'claude'   => app(ClaudeDriver::class),
            'deepseek' => app(DeepSeekDriver::class),
            default    => throw new \RuntimeException('No active default AI provider configured. Go to Admin → Integrations to set one.'),
        };
    }

    public function complete(string $prompt, array $options = []): string
    {
        return $this->driver()->complete($prompt, $options);
    }

    public function hasDefault(): bool
    {
        return Integration::defaultAi() !== null;
    }
}
