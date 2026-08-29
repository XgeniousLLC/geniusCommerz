<?php

namespace App\Models\Concerns;

use App\Support\Countries;

/**
 * Country → state → postal matching, shared by tax and shipping zones.
 *
 * Both resolve a destination the same way and must agree on what "most specific" means,
 * so the rule lives in one place rather than being written twice.
 *
 * Requires: country, state, postal_pattern, priority.
 */
trait MatchesAddress
{
    /**
     * How specific this zone is. A postal-level zone must beat a state-level one, which
     * must beat a country-wide one, regardless of priority.
     */
    public function specificity(): int
    {
        return ($this->postal_pattern ? 4 : 0) + ($this->state ? 2 : 0) + 1;
    }

    public function matches(?string $country, ?string $state, ?string $postal): bool
    {
        if (! $country || strcasecmp($this->country, $country) !== 0) {
            return false;
        }

        if ($this->state && strcasecmp($this->state, (string) $state) !== 0) {
            return false;
        }

        if ($this->postal_pattern) {
            $pattern = '/^'.str_replace(['%', '_'], ['.*', '.'], preg_quote($this->postal_pattern, '/')).'$/i';

            // Compared without spacing so "SW1A 2AA" still matches a "SW1A2%" pattern.
            if (! preg_match($pattern, str_replace(' ', '', (string) $postal))) {
                return false;
            }
        }

        return true;
    }

    public function describe(): string
    {
        return implode(' · ', array_filter([
            Countries::name($this->country),
            $this->state ? Countries::subdivisionName($this->country, $this->state) : null,
            $this->postal_pattern,
        ]));
    }

    /** @return \Illuminate\Support\Collection<int, static> */
    public static function matching(array $address)
    {
        $country = $address['country'] ?? null;

        if (! $country) {
            return collect();
        }

        return static::where('is_active', true)
            ->where('country', strtoupper($country))
            ->get()
            ->filter(fn ($zone) => $zone->matches($country, $address['state'] ?? null, $address['postal_code'] ?? null))
            ->sortByDesc(fn ($zone) => [$zone->specificity(), $zone->priority]);
    }
}
