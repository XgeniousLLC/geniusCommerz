<?php

namespace App\Payments;

class PaymentResult
{
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $transactionId = null,
        public readonly ?string $error = null,
        public readonly array $raw = [],
    ) {}

    public static function redirect(string $url, ?string $transactionId = null, array $raw = []): self
    {
        return new self(PaymentStatus::Redirect, redirectUrl: $url, transactionId: $transactionId, raw: $raw);
    }

    /**
     * For gateways that require the browser to POST a signed form rather than following
     * a URL. The fields are stored on the attempt and replayed by an auto-submitting page,
     * because a signed form cannot be turned into a GET redirect without breaking the
     * signature.
     */
    public static function formPost(string $url, array $fields, ?string $transactionId = null): self
    {
        return new self(
            PaymentStatus::Redirect,
            transactionId: $transactionId,
            raw: ['_form' => ['url' => $url, 'fields' => $fields]],
        );
    }

    /** @return array{url: string, fields: array<string, mixed>}|null */
    public function formPayload(): ?array
    {
        return $this->raw['_form'] ?? null;
    }

    public static function paid(?string $transactionId = null, array $raw = []): self
    {
        return new self(PaymentStatus::Paid, transactionId: $transactionId, raw: $raw);
    }

    public static function pending(?string $transactionId = null, array $raw = []): self
    {
        return new self(PaymentStatus::Pending, transactionId: $transactionId, raw: $raw);
    }

    public static function deferred(array $raw = []): self
    {
        return new self(PaymentStatus::Deferred, raw: $raw);
    }

    public static function failed(string $error, array $raw = []): self
    {
        return new self(PaymentStatus::Failed, error: $error, raw: $raw);
    }

    public static function cancelled(array $raw = []): self
    {
        return new self(PaymentStatus::Cancelled, raw: $raw);
    }
}
