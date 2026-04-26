import { usePage } from '@inertiajs/react';
import type { SharedProps } from '../types';

export default function CurrencySwitcher() {
  const { currencies, activeCurrency } = usePage<SharedProps>().props;

  if (!currencies || currencies.length <= 1) return null;

  function switchCurrency(code: string) {
    window.location.href = `/currency/${code}`;
  }

  return (
    <div className="relative group inline-block">
      <button className="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 px-2 py-1 rounded">
        <span>{activeCurrency.symbol}</span>
        <span>{activeCurrency.code}</span>
        <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
        </svg>
      </button>
      <div className="absolute right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden group-hover:block min-w-[100px]">
        {currencies.map((c) => (
          <button
            key={c.code}
            onClick={() => switchCurrency(c.code)}
            className={`flex items-center gap-2 w-full text-left px-3 py-2 text-sm whitespace-nowrap hover:bg-gray-50 transition-colors
              ${c.code === activeCurrency.code ? 'font-semibold text-blue-600' : 'text-gray-700'}`}
          >
            <span className="font-mono">{c.symbol}</span>
            <span>{c.code}</span>
          </button>
        ))}
      </div>
    </div>
  );
}
