import { useEffect, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import type { SharedProps } from '../types';

export default function CurrencySwitcher() {
  const { currencies, activeCurrency } = usePage<SharedProps>().props;
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!open) return;
    const handler = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [open]);

  if (!currencies || currencies.length <= 1) return null;

  function switchCurrency(code: string) {
    setOpen(false);
    window.location.href = `/currency/${code}`;
  }

  return (
    <div ref={ref} className="relative inline-block">
      <button
        onClick={() => setOpen(o => !o)}
        className="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 px-2 py-1.5 rounded hover:bg-gray-100 transition-colors"
      >
        <span className="font-medium">{activeCurrency.symbol} {activeCurrency.code}</span>
        <svg className={`w-3 h-3 transition-transform duration-150 ${open ? 'rotate-180' : ''}`} fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
        </svg>
      </button>

      {open && (
        <div className="absolute right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-1 min-w-full w-max">
          {currencies.map((c) => (
            <button
              key={c.code}
              onClick={() => switchCurrency(c.code)}
              className={`flex items-center gap-2 w-full text-left px-4 py-2 text-sm whitespace-nowrap hover:bg-gray-50 transition-colors
                ${c.code === activeCurrency.code ? 'font-semibold text-blue-600 bg-blue-50' : 'text-gray-700'}`}
            >
              <span className="font-mono w-4 text-center">{c.symbol}</span>
              <span>{c.code}</span>
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
