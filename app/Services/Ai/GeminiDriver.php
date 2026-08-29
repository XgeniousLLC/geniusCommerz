<?php

namespace App\Services\Ai;

use App\Contracts\AiInterface;
use App\Services\ProviderDriver;
use Illuminate\Support\Facades\Http;

class GeminiDriver extends ProviderDriver implements AiInterface
{

    public function complete(string $prompt, array $options = []): string
    {
        $model  = $this->integration->getCredential('model', 'gemini-1.5-flash');
        $apiKey = $this->integration->getCredential('api_key');

        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'maxOutputTokens' => $options['max_tokens'] ?? 1024,
                    'temperature'     => $options['temperature'] ?? 0.7,
                ],
            ]
        );

        if (! $response->successful()) {
            throw new \RuntimeException('Gemini error: ' . $response->json('error.message', $response->body()));
        }

        return $response->json('candidates.0.content.parts.0.text', '');
    }

    public function name(): string
    {
        return 'Gemini';
    }
}
