<?php

namespace App\Integrations;

/**
 * One input on a provider's credential form. Replaces the per-provider $fields match
 * that used to live inside admin/integrations/edit.blade.php.
 */
class CredentialField
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        /** text|password|textarea|url|select */
        public readonly string $type = 'text',
        public readonly ?string $hint = null,
        public readonly bool $required = true,
        /** @var array<string, string> value => label, for type=select */
        public readonly array $options = [],
        /**
         * null = one value shared across environments. 'sandbox'/'live' = the value is
         * stored per environment, so test keys and live keys can coexist.
         */
        public readonly ?string $environment = null,
    ) {}

    public function isSecret(): bool
    {
        return $this->type === 'password';
    }

    public static function text(string $key, string $label, ?string $hint = null): self
    {
        return new self($key, $label, 'text', $hint);
    }

    public static function secret(string $key, string $label, ?string $hint = null): self
    {
        return new self($key, $label, 'password', $hint);
    }

    public static function textarea(string $key, string $label, ?string $hint = null): self
    {
        return new self($key, $label, 'textarea', $hint);
    }

    public static function optional(string $key, string $label, ?string $hint = null): self
    {
        return new self($key, $label, 'text', $hint, required: false);
    }
}
