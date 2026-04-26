import { usePage } from '@inertiajs/react';
import type { SharedProps } from './types';

/**
 * Returns a `formatPrice(amount)` function that:
 * - Converts `amount` from base currency to the active currency (amount * rate)
 * - Formats it with the active currency symbol
 */
export function usePrice() {
  const { activeCurrency } = usePage<SharedProps>().props;
  const { symbol, rate } = activeCurrency ?? { symbol: '৳', rate: 1 };

  return function formatPrice(amount: number): string {
    const converted = amount * (rate ?? 1);
    const formatted = converted % 1 === 0
      ? converted.toLocaleString()
      : converted.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return `${symbol}${formatted}`;
  };
}
