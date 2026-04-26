<?php

namespace App\Contracts;

interface AiInterface
{
    /**
     * Send a prompt and return the text response.
     */
    public function complete(string $prompt, array $options = []): string;

    /**
     * Human-readable provider name.
     */
    public function name(): string;
}
