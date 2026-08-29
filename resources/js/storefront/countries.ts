/**
 * Shared country-shape helpers. The country list itself is passed per-page as an Inertia
 * prop rather than shared globally — 213 countries with their subdivisions would bloat
 * every page payload on the site.
 */

export interface Country {
  code: string;
  name: string;
  dial: string;
  currency: string;
  /** Whether the country uses postal codes at all. */
  postal: 'required' | 'optional' | 'none';
  /** Subdivision code => name. Empty when the country has no structured list. */
  states: Record<string, string>;
}

export function findCountry(countries: Country[], code: string): Country | undefined {
  return countries.find(c => c.code === code);
}

/** What this country actually calls its postal code. */
export function postalLabel(code: string): string {
  switch (code) {
    case 'US': case 'PR': case 'GU': case 'AS': case 'VI': return 'ZIP code';
    case 'IN': return 'PIN code';
    case 'GB': case 'AU': case 'NZ': case 'IE': case 'ZA': case 'SG': case 'MY':
      return 'Postcode';
    default: return 'Postal code';
  }
}

/** What this country actually calls its first-level subdivision. */
export function stateLabel(code: string): string {
  switch (code) {
    case 'CA': return 'Province';
    case 'GB': case 'IE': return 'County';
    case 'US': case 'AU': case 'BR': case 'MX': case 'MY': case 'NG': case 'IN': case 'AR':
      return 'State';
    case 'BD': return 'Division';
    case 'ZA': return 'Province';
    default: return 'State / Region';
  }
}

/** Subdivisions as a sorted list, ready for a <select>. */
export function stateOptions(country?: Country): Array<{ code: string; name: string }> {
  if (!country) return [];
  return Object.entries(country.states)
    .map(([code, name]) => ({ code, name }))
    .sort((a, b) => a.name.localeCompare(b.name));
}

/** Dial codes for a phone prefix picker, deduped and sorted by country name. */
export function dialOptions(countries: Country[]): Array<{ code: string; name: string; dial: string }> {
  return countries
    .filter(c => c.dial)
    .map(c => ({ code: c.code, name: c.name, dial: c.dial }))
    .sort((a, b) => a.name.localeCompare(b.name));
}
