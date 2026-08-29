<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Ready-made shipping zone sets.
 *
 * Unlike tax, shipping presets depend on where the store ships from, so they are built
 * against the configured store country rather than being static. Prices are placeholders
 * in the store's own currency — the point is to save the structural work of creating
 * zones and weight bands, not to guess anyone's carrier rates.
 */
class ShippingPresets
{
    /** EU member states, used for the single-zone Europe preset. */
    private const EU = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE',
        'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
    ];

    private const NORTH_AMERICA = ['US', 'CA', 'MX'];

    private const GULF = ['SA', 'AE', 'KW', 'BH', 'QA', 'OM'];

    /**
     * @return array<string, array{label: string, note: string, zones: list<array>}>
     */
    public static function all(?string $storeCountry = null, ?string $currency = null): array
    {
        $country  = strtoupper($storeCountry ?: SiteSetting::get('general.store_country', 'BD'));
        $currency = strtoupper($currency ?: SiteSetting::get('general.currency', 'USD'));
        $home     = Countries::name($country);

        return [
            'domestic_flat' => [
                'label' => "Domestic flat rate — {$home}",
                'note'  => 'One rate for every domestic order, with free shipping above a threshold.',
                'zones' => [[
                    'name' => $home, 'country' => $country,
                    'rates' => [[
                        'name' => 'Standard', 'price' => 5, 'free_above' => 50,
                        'delivery_estimate' => '2-4 business days',
                    ]],
                ]],
            ],

            'domestic_weight_bands' => [
                'label' => "Domestic weight bands — {$home}",
                'note'  => 'Three bands so heavier carts cost more: up to 1kg, 1-5kg, then a per-kg charge above 5kg.',
                'zones' => [[
                    'name' => $home, 'country' => $country,
                    'rates' => [
                        ['name' => 'Light (up to 1kg)', 'price' => 4, 'max_weight' => 1, 'priority' => 1,
                         'delivery_estimate' => '2-4 business days'],
                        ['name' => 'Standard (1-5kg)', 'price' => 8, 'min_weight' => 1, 'max_weight' => 5, 'priority' => 2,
                         'delivery_estimate' => '2-4 business days'],
                        ['name' => 'Heavy (5kg+)', 'price' => 12, 'per_kg' => 1.5, 'min_weight' => 5, 'priority' => 3,
                         'delivery_estimate' => '3-6 business days'],
                    ],
                ]],
            ],

            'europe' => [
                'label' => 'Europe — 27 EU countries',
                'note'  => 'One zone per member state so rates can be tuned individually later.',
                'zones' => self::perCountry(self::EU, 'Standard', 12, 20, '3-7 business days'),
            ],

            'north_america' => [
                'label' => 'North America — US, Canada, Mexico',
                'note'  => 'A zone per country, each with a light and a standard band.',
                'zones' => self::perCountry(self::NORTH_AMERICA, 'Standard', 10, 15, '3-8 business days'),
            ],

            'gulf' => [
                'label' => 'Gulf — GCC states',
                'note'  => 'Saudi Arabia, UAE, Kuwait, Bahrain, Qatar and Oman.',
                'zones' => self::perCountry(self::GULF, 'Standard', 8, 12, '2-5 business days'),
            ],

            'rest_of_world' => [
                'label' => 'Rest of world — single fallback zone',
                'note'  => "A catch-all so an order from anywhere still gets a price. Only matches countries you have no other zone for, since a more specific zone always wins.",
                'zones' => self::restOfWorld(),
            ],
        ];
    }

    public static function find(string $key, ?string $storeCountry = null, ?string $currency = null): ?array
    {
        return self::all($storeCountry, $currency)[$key] ?? null;
    }

    /**
     * @param  list<string>  $countries
     * @return list<array>
     */
    private static function perCountry(array $countries, string $label, float $light, float $standard, string $estimate): array
    {
        $zones = [];

        foreach ($countries as $code) {
            $zones[] = [
                'name'    => Countries::name($code),
                'country' => $code,
                'rates'   => [
                    ['name' => $label.' (up to 2kg)', 'price' => $light, 'max_weight' => 2, 'priority' => 1,
                     'delivery_estimate' => $estimate],
                    ['name' => $label, 'price' => $standard, 'per_kg' => 2, 'min_weight' => 2, 'priority' => 2,
                     'delivery_estimate' => $estimate],
                ],
            ];
        }

        return $zones;
    }

    /**
     * A zone per remaining country.
     *
     * Zones match on country, so there is no wildcard — this covers every country that
     * has no zone yet, and the apply step skips any that already do.
     *
     * @return list<array>
     */
    private static function restOfWorld(): array
    {
        $zones = [];

        foreach (array_keys(Countries::all()) as $code) {
            $zones[] = [
                'name'    => Countries::name($code),
                'country' => $code,
                // Lowest specificity and a high priority number, so any zone you add
                // later for the same country wins over this fallback.
                'priority' => 99,
                'rates'   => [[
                    'name' => 'International', 'price' => 25, 'per_kg' => 5,
                    'delivery_estimate' => '7-21 business days',
                ]],
            ];
        }

        return $zones;
    }
}
