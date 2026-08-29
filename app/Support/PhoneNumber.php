<?php

namespace App\Support;

/**
 * Phone numbers in E.164.
 *
 * There was no phone handling at all before this — every rule was a bare `max:50` and
 * numbers reached gateways in whatever shape the customer typed. Twilio and the other
 * international gateways require E.164; the Bangladeshi gateways want a local
 * `01XXXXXXXXX`. Normalising once here means each driver converts from a known shape
 * instead of guessing.
 *
 * This is deliberately not a full numbering-plan validator: it checks the country's
 * dialling code and E.164's own length limits. If you need per-carrier correctness,
 * giggsey/libphonenumber-for-php is the right tool.
 */
class PhoneNumber
{
    /** E.164 allows at most 15 digits including the country code, and needs at least 8 to be plausible. */
    private const MIN_DIGITS = 8;

    private const MAX_DIGITS = 15;

    /**
     * Convert user input to E.164 (`+8801711111111`), or null when it cannot be read
     * as a number for that country.
     */
    public static function toE164(?string $raw, ?string $country = null): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        $hasPlus = str_starts_with($raw, '+');
        $digits  = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return null;
        }

        // 00 is the international prefix in most of the world; treat it as a leading +.
        if (! $hasPlus && str_starts_with($digits, '00')) {
            $digits  = substr($digits, 2);
            $hasPlus = true;
        }

        if (! $hasPlus) {
            $dial = $country ? Countries::dial($country) : null;

            if (! $dial) {
                return null;
            }

            // A leading 0 is a national trunk prefix and is dropped when going international.
            $national = ltrim($digits, '0');

            // Already carries its own country code (e.g. "8801711111111" typed without +).
            $digits = str_starts_with($digits, $dial) && strlen($digits) > strlen($dial)
                ? $digits
                : $dial.$national;
        }

        $length = strlen($digits);

        if ($length < self::MIN_DIGITS || $length > self::MAX_DIGITS) {
            return null;
        }

        return '+'.$digits;
    }

    public static function isValid(?string $raw, ?string $country = null): bool
    {
        return self::toE164($raw, $country) !== null;
    }

    /**
     * The local form a domestic gateway expects, e.g. `+8801711111111` → `01711111111`.
     *
     * Countries on the North American Numbering Plan have no trunk prefix; everywhere
     * else here uses a leading 0.
     */
    public static function national(?string $e164, ?string $country = null): ?string
    {
        $e164 = self::toE164($e164, $country);

        if ($e164 === null) {
            return null;
        }

        $digits = substr($e164, 1);
        // Fall back to reading the dialling code off the number itself, so callers that
        // only have an E.164 string still get a correct local form.
        $dial   = ($country ? Countries::dial($country) : null) ?? self::dialOf($e164);

        if ($dial === null || ! str_starts_with($digits, $dial)) {
            return $digits;
        }

        $national = substr($digits, strlen($dial));

        return $dial === '1' ? $national : '0'.$national;
    }

    /** The dialling code a number carries, without the leading "+". */
    public static function dialOf(?string $e164): ?string
    {
        $e164 = trim((string) $e164);

        if (! str_starts_with($e164, '+')) {
            return null;
        }

        $digits = substr($e164, 1);

        // Longest dialling code first, so +1 does not shadow +1268.
        $codes = array_unique(array_column(Countries::all(), 'dial'));
        usort($codes, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $code) {
            if (str_starts_with($digits, $code)) {
                return $code;
            }
        }

        return null;
    }
}
