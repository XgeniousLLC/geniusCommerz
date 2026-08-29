<?php

namespace App\Services\Ai;

use App\Contracts\AiInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

class DeepSeekDriver extends ProviderDriver implements AiInterface
{

    public function complete(string $prompt, array $options = []): string
    {
        $model = $this->integration->getCredential('model', 'deepseek-chat');

        $response = Http::withToken($this->integration->getCredential('api_key'))
            ->post('https://api.deepseek.com/chat/completions', [
                'model'       => $model,
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'max_tokens'  => $options['max_tokens'] ?? 1024,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('DeepSeek error: ' . $response->json('error.message', $response->body()));
        }

        return $response->json('choices.0.message.content', '');
    }

    public function name(): string
    {
        return 'DeepSeek';
    }
}
