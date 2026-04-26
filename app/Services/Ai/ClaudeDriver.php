<?php

namespace App\Services\Ai;

use App\Contracts\AiInterface;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;

class ClaudeDriver implements AiInterface
{
    private Integration $integration;

    public function __construct()
    {
        $this->integration = Integration::forProvider('claude')
            ?? new Integration(['credentials' => []]);
    }

    public function complete(string $prompt, array $options = []): string
    {
        $model = $this->integration->getCredential('model', 'claude-haiku-4-5-20251001');

        $response = Http::withHeaders([
            'x-api-key'         => $this->integration->getCredential('api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => $model,
            'max_tokens' => $options['max_tokens'] ?? 1024,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Claude error: ' . $response->json('error.message', $response->body()));
        }

        return $response->json('content.0.text', '');
    }

    public function name(): string
    {
        return 'Claude';
    }
}
