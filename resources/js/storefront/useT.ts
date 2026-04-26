import { usePage } from '@inertiajs/react';
import type { SharedProps } from './types';

/**
 * Returns a translation function `t(key, vars?)` that looks up the
 * current locale's strings (shared via Inertia) and replaces {token}
 * placeholders with the given vars map.
 *
 * Falls back to the raw key if no string is found.
 */
export function useT() {
  const { strings } = usePage<SharedProps>().props;

  return function t(key: string, vars?: Record<string, string | number>): string {
    let value = strings?.[key] ?? key;
    if (vars) {
      for (const [k, v] of Object.entries(vars)) {
        value = value.replace(new RegExp(`\\{${k}\\}`, 'g'), String(v));
      }
    }
    return value;
  };
}
