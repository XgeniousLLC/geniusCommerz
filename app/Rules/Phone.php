<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a phone number for a given country.
 *
 * Numbers were previously accepted as any string up to 50 characters, so unusable values
 * reached the SMS gateways and failed silently at delivery time.
 */
class Phone implements ValidationRule
{
    public function __construct(private readonly ?string $country = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // presence is the job of `required` / `nullable`
        }

        if (! PhoneNumber::isValid((string) $value, $this->country)) {
            $fail('The :attribute is not a valid phone number for the selected country.');
        }
    }
}
