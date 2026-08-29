<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\SiteSetting;
use App\Support\Currencies;

/**
 * The single authoritative place where a currency rate is applied to money.
 *
 * Prices are stored and computed in the store's base currency. The storefront converts
 * for display only; anything that will actually be charged is converted here, server-side,
 * and the rate used is frozen onto the order so the figures stay reconstructible after
 * rates move.
 */
class PriceBook
{
    /** @var array<string, float>|null */
    private ?array $rates = null;

    public function baseCurrency(): string
    {
        return strtoupper((string) SiteSetting::get('general.currency', 'BDT'));
    }

    /** Whether the merchant has switched multi-currency on at all. */
    public function multiCurrencyEnabled(): bool
    {
        return (bool) SiteSetting::get('currencies.enabled', '0');
    }

    /**
     * Resolve the currency the customer is being quoted in, falling back to base for
     * anything unknown or inactive. Never trust the requested code without this check —
     * it arrives from a cookie.
     */
    public function presentmentCurrency(?string $requested): string
    {
        $base = $this->baseCurrency();

        if (! $this->multiCurrencyEnabled() || $requested === null) {
            return $base;
        }

        $code = strtoupper($requested);

        return isset($this->rates()[$code]) ? $code : $base;
    }

    /** How far a rate may drift between page render and submit before we re-quote. */
    public const DRIFT_TOLERANCE = 0.005;

    /** How long a browsing session keeps the rate it was first quoted at. */
    private const PIN_MINUTES = 45;

    /**
     * The rate this visitor is being quoted at, pinned for the session.
     *
     * Without pinning, a scheduled refresh landing mid-visit would change totals while
     * the customer is still browsing. The pin expires so a session cannot hold a stale
     * rate indefinitely.
     */
    public function sessionRate(string $currency): float
    {
        $currency = strtoupper($currency);
        $pinned   = session('fx');

        if (is_array($pinned)
            && ($pinned['code'] ?? null) === $currency
            && isset($pinned['at'], $pinned['rate'])
            && now()->diffInMinutes(\Illuminate\Support\Carbon::parse($pinned['at'])) < self::PIN_MINUTES) {
            return (float) $pinned['rate'];
        }

        $rate = $this->rate($currency);
        session(['fx' => ['code' => $currency, 'rate' => $rate, 'at' => now()->toIso8601String()]]);

        return $rate;
    }

    /**
     * Whether a rate the browser rendered with is still close enough to charge at.
     *
     * This is the "saw $X, charged $Y" guard: if the rate moved between render and
     * submit, the customer must be re-quoted rather than silently charged the new one.
     */
    public function rateHasDrifted(string $currency, ?float $quoted): bool
    {
        if ($quoted === null || $quoted <= 0) {
            return false; // nothing was quoted, so nothing can have drifted
        }

        $authoritative = $this->rate($currency);

        if ($authoritative <= 0) {
            return false;
        }

        return abs($authoritative - $quoted) / $authoritative > self::DRIFT_TOLERANCE;
    }

    /** Presentment units per one base unit. 1.0 for the base currency itself. */
    public function rate(string $currency): float
    {
        $currency = strtoupper($currency);

        if ($currency === $this->baseCurrency()) {
            return 1.0;
        }

        return $this->rates()[$currency] ?? 1.0;
    }

    /** Convert a base-currency amount, rounded to the target currency's own precision. */
    public function convert(int|float $baseAmount, string $currency, ?float $rate = null): float
    {
        $rate ??= $this->rate($currency);

        return round(((float) $baseAmount) * $rate, Currencies::exponent($currency));
    }

    /**
     * Convert a whole set of base-currency totals in one pass.
     *
     * @param  array<string, int|float>  $amounts
     * @return array<string, float>
     */
    public function convertAll(array $amounts, string $currency, ?float $rate = null): array
    {
        $rate ??= $this->rate($currency);

        return array_map(fn ($amount) => $this->convert($amount, $currency, $rate), $amounts);
    }

    /** @return array<string, float> */
    private function rates(): array
    {
        return $this->rates ??= Currency::where('is_active', true)
            ->pluck('rate', 'code')
            ->map(fn ($rate) => (float) $rate)
            ->all();
    }
}
