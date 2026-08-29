<?php

namespace App\Contracts;

interface FraudInterface
{
    /**
     * Score a customer.
     *
     * Returns the normalised shape every provider is reduced to:
     * ['provider', 'risk_level' (safe|low_risk|mid_risk|high_risk|unknown), 'risk_score' 0-100, …]
     * or ['error' => '…'] when the check could not be made.
     *
     * @param  array{email?: string, ip?: string, country?: string}  $context
     */
    public function check(string $phone, array $context = []): array;

    public function isConfigured(): bool;

    public function name(): string;
}
