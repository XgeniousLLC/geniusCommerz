<?php

namespace App\Services\Ai;

use App\Contracts\AiInterface;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class OpenAiDriver implements AiInterface
{
    private Integration $integration;

    public function __construct()
    {
        $this->integration = Integration::forProvider('openai')
            ?? new Integration(['credentials' => []]);
    }

    public function complete(string $prompt, array $options = []): string
    {
        $model = $this->integration->getCredential('model', 'gpt-4o-mini');

        $response = Http::withToken($this->integration->getCredential('api_key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $model,
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'max_tokens'  => $options['max_tokens'] ?? 1024,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('OpenAI error: ' . $response->json('error.message', $response->body()));
        }

        return $response->json('choices.0.message.content', '');
    }

    public function name(): string
    {
        return 'OpenAI';
    }
}
