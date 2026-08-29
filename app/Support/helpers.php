<?php

use App\Models\SiteSetting;
use App\Support\Currencies;

if (! function_exists('money')) {
    /**
     * Format an amount in the store's base currency.
     *
     * Replaces the taka symbol that was hardcoded into ~176 places across the admin and
     * storefront blades. Those were correct only while the base currency was BDT — the
     * moment a merchant switched it, every price in the admin still read "৳".
     */
    function money(int|float|string|null $amount, ?int $decimals = null, ?string $currency = null): string
    {
        $currency ??= store_currency();

        return Currencies::format((float) $amount, $currency, $decimals);
    }
}

if (! function_exists('store_currency')) {
    /** The store's base currency code, cached per request. */
    function store_currency(): string
    {
        static $code = null;

        return $code ??= strtoupper((string) SiteSetting::get('general.currency', 'BDT'));
    }
}

if (! function_exists('currency_symbol')) {
    /** Just the symbol, for places that lay out the number themselves. */
    function currency_symbol(?string $currency = null): string
    {
        return SiteSetting::get('general.currency_symbol')
            ?: Currencies::symbol($currency ?? store_currency());
    }
}
