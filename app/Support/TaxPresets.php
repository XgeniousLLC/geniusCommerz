<?php

namespace App\Support;

/**
 * Ready-made tax zone sets.
 *
 * Configuring tax country by country is the slowest part of going live, so these seed a
 * whole region in one action. They are a starting point, not advice: rates change, and
 * the merchant is responsible for confirming them against their own registrations.
 *
 * Applying a preset never overwrites an existing zone for the same country and state —
 * it skips it, so a preset can be re-applied safely after new countries are added.
 */
class TaxPresets
{
    /**
     * @return array<string, array{label: string, note: string, zones: list<array{name: string, country: string, state?: string, rates: list<array{name: string, rate: float, tax_class: string}>}>}>
     */
    public static function all(): array
    {
        return [
            'eu_vat' => [
                'label' => 'EU VAT — 27 member states',
                'note'  => 'Standard rates only. Reduced rates for food, books and similar vary by country and are not included.',
                'zones' => self::euVat(),
            ],
            'uk_vat' => [
                'label' => 'UK VAT',
                'note'  => 'Standard 20% plus the 5% reduced rate, applied to products in the reduced tax class.',
                'zones' => self::ukVat(),
            ],
            'us_sales_tax' => [
                'label' => 'US sales tax — state base rates',
                'note'  => 'State-level rates only. Counties and cities add their own on top, and nexus rules decide where you must collect at all.',
                'zones' => self::usSalesTax(),
            ],
            'canada_gst' => [
                'label' => 'Canada GST / HST / PST',
                'note'  => 'HST provinces get one combined rate; GST+PST provinces get two stacked rates, which is how they are actually charged.',
                'zones' => self::canada(),
            ],
            'single_rate_countries' => [
                'label' => 'Single-rate countries — Gulf, APAC, Africa and more',
                'note'  => 'One standard rate per country: Australia, New Zealand, Singapore, UAE, Saudi Arabia, UK, South Africa, Bangladesh, Nigeria, India, Japan, Switzerland, Norway, Türkiye and Mexico.',
                'zones' => self::singleRateCountries(),
            ],
        ];
    }

    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    private static function euVat(): array
    {
        return [
            ['name' => 'Austria VAT', 'country' => 'AT', 'rates' => [['name' => 'VAT', 'rate' => 20.0, 'tax_class' => 'standard']]],
            ['name' => 'Belgium VAT', 'country' => 'BE', 'rates' => [['name' => 'VAT', 'rate' => 21.0, 'tax_class' => 'standard']]],
            ['name' => 'Bulgaria VAT', 'country' => 'BG', 'rates' => [['name' => 'VAT', 'rate' => 20.0, 'tax_class' => 'standard']]],
            ['name' => 'Croatia VAT', 'country' => 'HR', 'rates' => [['name' => 'VAT', 'rate' => 25.0, 'tax_class' => 'standard']]],
            ['name' => 'Cyprus VAT', 'country' => 'CY', 'rates' => [['name' => 'VAT', 'rate' => 19.0, 'tax_class' => 'standard']]],
            ['name' => 'Czechia VAT', 'country' => 'CZ', 'rates' => [['name' => 'VAT', 'rate' => 21.0, 'tax_class' => 'standard']]],
            ['name' => 'Denmark VAT', 'country' => 'DK', 'rates' => [['name' => 'VAT', 'rate' => 25.0, 'tax_class' => 'standard']]],
            ['name' => 'Estonia VAT', 'country' => 'EE', 'rates' => [['name' => 'VAT', 'rate' => 22.0, 'tax_class' => 'standard']]],
            ['name' => 'Finland VAT', 'country' => 'FI', 'rates' => [['name' => 'VAT', 'rate' => 25.5, 'tax_class' => 'standard']]],
            ['name' => 'France VAT', 'country' => 'FR', 'rates' => [['name' => 'VAT', 'rate' => 20.0, 'tax_class' => 'standard']]],
            ['name' => 'Germany VAT', 'country' => 'DE', 'rates' => [['name' => 'VAT', 'rate' => 19.0, 'tax_class' => 'standard']]],
            ['name' => 'Greece VAT', 'country' => 'GR', 'rates' => [['name' => 'VAT', 'rate' => 24.0, 'tax_class' => 'standard']]],
            ['name' => 'Hungary VAT', 'country' => 'HU', 'rates' => [['name' => 'VAT', 'rate' => 27.0, 'tax_class' => 'standard']]],
            ['name' => 'Ireland VAT', 'country' => 'IE', 'rates' => [['name' => 'VAT', 'rate' => 23.0, 'tax_class' => 'standard']]],
            ['name' => 'Italy VAT', 'country' => 'IT', 'rates' => [['name' => 'VAT', 'rate' => 22.0, 'tax_class' => 'standard']]],
            ['name' => 'Latvia VAT', 'country' => 'LV', 'rates' => [['name' => 'VAT', 'rate' => 21.0, 'tax_class' => 'standard']]],
            ['name' => 'Lithuania VAT', 'country' => 'LT', 'rates' => [['name' => 'VAT', 'rate' => 21.0, 'tax_class' => 'standard']]],
            ['name' => 'Luxembourg VAT', 'country' => 'LU', 'rates' => [['name' => 'VAT', 'rate' => 17.0, 'tax_class' => 'standard']]],
            ['name' => 'Malta VAT', 'country' => 'MT', 'rates' => [['name' => 'VAT', 'rate' => 18.0, 'tax_class' => 'standard']]],
            ['name' => 'Netherlands VAT', 'country' => 'NL', 'rates' => [['name' => 'VAT', 'rate' => 21.0, 'tax_class' => 'standard']]],
            ['name' => 'Poland VAT', 'country' => 'PL', 'rates' => [['name' => 'VAT', 'rate' => 23.0, 'tax_class' => 'standard']]],
            ['name' => 'Portugal VAT', 'country' => 'PT', 'rates' => [['name' => 'VAT', 'rate' => 23.0, 'tax_class' => 'standard']]],
            ['name' => 'Romania VAT', 'country' => 'RO', 'rates' => [['name' => 'VAT', 'rate' => 19.0, 'tax_class' => 'standard']]],
            ['name' => 'Slovakia VAT', 'country' => 'SK', 'rates' => [['name' => 'VAT', 'rate' => 23.0, 'tax_class' => 'standard']]],
            ['name' => 'Slovenia VAT', 'country' => 'SI', 'rates' => [['name' => 'VAT', 'rate' => 22.0, 'tax_class' => 'standard']]],
            ['name' => 'Spain VAT', 'country' => 'ES', 'rates' => [['name' => 'VAT', 'rate' => 21.0, 'tax_class' => 'standard']]],
            ['name' => 'Sweden VAT', 'country' => 'SE', 'rates' => [['name' => 'VAT', 'rate' => 25.0, 'tax_class' => 'standard']]],
        ];
    }

    private static function ukVat(): array
    {
        return [
            ['name' => 'United Kingdom VAT', 'country' => 'GB', 'rates' => [['name' => 'VAT', 'rate' => 20.0, 'tax_class' => 'standard'], ['name' => 'VAT (reduced)', 'rate' => 5.0, 'tax_class' => 'reduced']]],
        ];
    }

    private static function usSalesTax(): array
    {
        return [
            ['name' => 'US — AL sales tax', 'country' => 'US', 'state' => 'AL', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.0, 'tax_class' => 'standard']]],
            ['name' => 'US — AR sales tax', 'country' => 'US', 'state' => 'AR', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.5, 'tax_class' => 'standard']]],
            ['name' => 'US — AZ sales tax', 'country' => 'US', 'state' => 'AZ', 'rates' => [['name' => 'State Sales Tax', 'rate' => 5.6, 'tax_class' => 'standard']]],
            ['name' => 'US — CA sales tax', 'country' => 'US', 'state' => 'CA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 7.25, 'tax_class' => 'standard']]],
            ['name' => 'US — CO sales tax', 'country' => 'US', 'state' => 'CO', 'rates' => [['name' => 'State Sales Tax', 'rate' => 2.9, 'tax_class' => 'standard']]],
            ['name' => 'US — CT sales tax', 'country' => 'US', 'state' => 'CT', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.35, 'tax_class' => 'standard']]],
            ['name' => 'US — DC sales tax', 'country' => 'US', 'state' => 'DC', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — FL sales tax', 'country' => 'US', 'state' => 'FL', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — GA sales tax', 'country' => 'US', 'state' => 'GA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.0, 'tax_class' => 'standard']]],
            ['name' => 'US — HI sales tax', 'country' => 'US', 'state' => 'HI', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.0, 'tax_class' => 'standard']]],
            ['name' => 'US — IA sales tax', 'country' => 'US', 'state' => 'IA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — ID sales tax', 'country' => 'US', 'state' => 'ID', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — IL sales tax', 'country' => 'US', 'state' => 'IL', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.25, 'tax_class' => 'standard']]],
            ['name' => 'US — IN sales tax', 'country' => 'US', 'state' => 'IN', 'rates' => [['name' => 'State Sales Tax', 'rate' => 7.0, 'tax_class' => 'standard']]],
            ['name' => 'US — KS sales tax', 'country' => 'US', 'state' => 'KS', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.5, 'tax_class' => 'standard']]],
            ['name' => 'US — KY sales tax', 'country' => 'US', 'state' => 'KY', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — LA sales tax', 'country' => 'US', 'state' => 'LA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.45, 'tax_class' => 'standard']]],
            ['name' => 'US — MA sales tax', 'country' => 'US', 'state' => 'MA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.25, 'tax_class' => 'standard']]],
            ['name' => 'US — MD sales tax', 'country' => 'US', 'state' => 'MD', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — ME sales tax', 'country' => 'US', 'state' => 'ME', 'rates' => [['name' => 'State Sales Tax', 'rate' => 5.5, 'tax_class' => 'standard']]],
            ['name' => 'US — MI sales tax', 'country' => 'US', 'state' => 'MI', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — MN sales tax', 'country' => 'US', 'state' => 'MN', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.875, 'tax_class' => 'standard']]],
            ['name' => 'US — MO sales tax', 'country' => 'US', 'state' => 'MO', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.225, 'tax_class' => 'standard']]],
            ['name' => 'US — MS sales tax', 'country' => 'US', 'state' => 'MS', 'rates' => [['name' => 'State Sales Tax', 'rate' => 7.0, 'tax_class' => 'standard']]],
            ['name' => 'US — NC sales tax', 'country' => 'US', 'state' => 'NC', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.75, 'tax_class' => 'standard']]],
            ['name' => 'US — ND sales tax', 'country' => 'US', 'state' => 'ND', 'rates' => [['name' => 'State Sales Tax', 'rate' => 5.0, 'tax_class' => 'standard']]],
            ['name' => 'US — NE sales tax', 'country' => 'US', 'state' => 'NE', 'rates' => [['name' => 'State Sales Tax', 'rate' => 5.5, 'tax_class' => 'standard']]],
            ['name' => 'US — NJ sales tax', 'country' => 'US', 'state' => 'NJ', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.625, 'tax_class' => 'standard']]],
            ['name' => 'US — NM sales tax', 'country' => 'US', 'state' => 'NM', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.875, 'tax_class' => 'standard']]],
            ['name' => 'US — NV sales tax', 'country' => 'US', 'state' => 'NV', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.85, 'tax_class' => 'standard']]],
            ['name' => 'US — NY sales tax', 'country' => 'US', 'state' => 'NY', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.0, 'tax_class' => 'standard']]],
            ['name' => 'US — OH sales tax', 'country' => 'US', 'state' => 'OH', 'rates' => [['name' => 'State Sales Tax', 'rate' => 5.75, 'tax_class' => 'standard']]],
            ['name' => 'US — OK sales tax', 'country' => 'US', 'state' => 'OK', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.5, 'tax_class' => 'standard']]],
            ['name' => 'US — PA sales tax', 'country' => 'US', 'state' => 'PA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — RI sales tax', 'country' => 'US', 'state' => 'RI', 'rates' => [['name' => 'State Sales Tax', 'rate' => 7.0, 'tax_class' => 'standard']]],
            ['name' => 'US — SC sales tax', 'country' => 'US', 'state' => 'SC', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — SD sales tax', 'country' => 'US', 'state' => 'SD', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.2, 'tax_class' => 'standard']]],
            ['name' => 'US — TN sales tax', 'country' => 'US', 'state' => 'TN', 'rates' => [['name' => 'State Sales Tax', 'rate' => 7.0, 'tax_class' => 'standard']]],
            ['name' => 'US — TX sales tax', 'country' => 'US', 'state' => 'TX', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.25, 'tax_class' => 'standard']]],
            ['name' => 'US — UT sales tax', 'country' => 'US', 'state' => 'UT', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.1, 'tax_class' => 'standard']]],
            ['name' => 'US — VA sales tax', 'country' => 'US', 'state' => 'VA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 5.3, 'tax_class' => 'standard']]],
            ['name' => 'US — VT sales tax', 'country' => 'US', 'state' => 'VT', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — WA sales tax', 'country' => 'US', 'state' => 'WA', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.5, 'tax_class' => 'standard']]],
            ['name' => 'US — WI sales tax', 'country' => 'US', 'state' => 'WI', 'rates' => [['name' => 'State Sales Tax', 'rate' => 5.0, 'tax_class' => 'standard']]],
            ['name' => 'US — WV sales tax', 'country' => 'US', 'state' => 'WV', 'rates' => [['name' => 'State Sales Tax', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'US — WY sales tax', 'country' => 'US', 'state' => 'WY', 'rates' => [['name' => 'State Sales Tax', 'rate' => 4.0, 'tax_class' => 'standard']]],
        ];
    }

    private static function canada(): array
    {
        return [
            ['name' => 'Canada — Alberta', 'country' => 'CA', 'state' => 'AB', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — British Columbia', 'country' => 'CA', 'state' => 'BC', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard'], ['name' => 'PST', 'rate' => 7.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Manitoba', 'country' => 'CA', 'state' => 'MB', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard'], ['name' => 'RST', 'rate' => 7.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — New Brunswick', 'country' => 'CA', 'state' => 'NB', 'rates' => [['name' => 'HST', 'rate' => 15.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Newfoundland and Labrador', 'country' => 'CA', 'state' => 'NL', 'rates' => [['name' => 'HST', 'rate' => 15.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Northwest Territories', 'country' => 'CA', 'state' => 'NT', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Nova Scotia', 'country' => 'CA', 'state' => 'NS', 'rates' => [['name' => 'HST', 'rate' => 14.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Nunavut', 'country' => 'CA', 'state' => 'NU', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Ontario', 'country' => 'CA', 'state' => 'ON', 'rates' => [['name' => 'HST', 'rate' => 13.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Prince Edward Island', 'country' => 'CA', 'state' => 'PE', 'rates' => [['name' => 'HST', 'rate' => 15.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Quebec', 'country' => 'CA', 'state' => 'QC', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard'], ['name' => 'QST', 'rate' => 9.975, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Saskatchewan', 'country' => 'CA', 'state' => 'SK', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard'], ['name' => 'PST', 'rate' => 6.0, 'tax_class' => 'standard']]],
            ['name' => 'Canada — Yukon', 'country' => 'CA', 'state' => 'YT', 'rates' => [['name' => 'GST', 'rate' => 5.0, 'tax_class' => 'standard']]],
        ];
    }

    private static function singleRateCountries(): array
    {
        return [
            ['name' => 'Australia GST', 'country' => 'AU', 'rates' => [['name' => 'GST', 'rate' => 10.0, 'tax_class' => 'standard']]],
            ['name' => 'New Zealand GST', 'country' => 'NZ', 'rates' => [['name' => 'GST', 'rate' => 15.0, 'tax_class' => 'standard']]],
            ['name' => 'Singapore GST', 'country' => 'SG', 'rates' => [['name' => 'GST', 'rate' => 9.0, 'tax_class' => 'standard']]],
            ['name' => 'United Arab Emirates VAT', 'country' => 'AE', 'rates' => [['name' => 'VAT', 'rate' => 5.0, 'tax_class' => 'standard']]],
            ['name' => 'Saudi Arabia VAT', 'country' => 'SA', 'rates' => [['name' => 'VAT', 'rate' => 15.0, 'tax_class' => 'standard']]],
            ['name' => 'United Kingdom VAT', 'country' => 'GB', 'rates' => [['name' => 'VAT', 'rate' => 20.0, 'tax_class' => 'standard']]],
            ['name' => 'South Africa VAT', 'country' => 'ZA', 'rates' => [['name' => 'VAT', 'rate' => 15.0, 'tax_class' => 'standard']]],
            ['name' => 'Bangladesh VAT', 'country' => 'BD', 'rates' => [['name' => 'VAT', 'rate' => 15.0, 'tax_class' => 'standard']]],
            ['name' => 'Nigeria VAT', 'country' => 'NG', 'rates' => [['name' => 'VAT', 'rate' => 7.5, 'tax_class' => 'standard']]],
            ['name' => 'India GST', 'country' => 'IN', 'rates' => [['name' => 'GST', 'rate' => 18.0, 'tax_class' => 'standard']]],
            ['name' => 'Japan Consumption Tax', 'country' => 'JP', 'rates' => [['name' => 'Consumption Tax', 'rate' => 10.0, 'tax_class' => 'standard']]],
            ['name' => 'Switzerland VAT', 'country' => 'CH', 'rates' => [['name' => 'VAT', 'rate' => 8.1, 'tax_class' => 'standard']]],
            ['name' => 'Norway VAT', 'country' => 'NO', 'rates' => [['name' => 'VAT', 'rate' => 25.0, 'tax_class' => 'standard']]],
            ['name' => 'Türkiye VAT', 'country' => 'TR', 'rates' => [['name' => 'VAT', 'rate' => 20.0, 'tax_class' => 'standard']]],
            ['name' => 'Mexico IVA', 'country' => 'MX', 'rates' => [['name' => 'IVA', 'rate' => 16.0, 'tax_class' => 'standard']]],
        ];
    }
}
