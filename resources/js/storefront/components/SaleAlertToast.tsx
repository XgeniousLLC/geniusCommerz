import { useEffect, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import type { SharedProps } from '../types';

interface Alert {
  id: number;
  name: string;
  image: string | null;
}

export default function SaleAlertToast() {
  const { site } = usePage<SharedProps>().props;
  const { saleAlertEnabled, saleAlertIntervalMin, saleAlertIntervalMax, saleAlertProducts } = site;

  const [visible, setVisible] = useState(false);
  const [current, setCurrent] = useState<Alert | null>(null);
  const indexRef = useRef(0);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (!saleAlertEnabled || saleAlertProducts.length === 0) return;

    const schedule = () => {
      const delay =
        (Math.floor(Math.random() * (saleAlertIntervalMax - saleAlertIntervalMin + 1)) +
          saleAlertIntervalMin) *
        1000;

      timerRef.current = setTimeout(() => {
        const product = saleAlertProducts[indexRef.current % saleAlertProducts.length];
        indexRef.current += 1;
        setCurrent(product);
        setVisible(true);

        // Auto-dismiss after 4 seconds
        setTimeout(() => setVisible(false), 4000);

        schedule();
      }, delay);
    };

    schedule();

    return () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    };
  }, [saleAlertEnabled, saleAlertIntervalMin, saleAlertIntervalMax, saleAlertProducts]);

  if (!saleAlertEnabled || !current) return null;

  return (
    <div
      className={`fixed bottom-20 left-4 z-50 transition-all duration-500 md:bottom-6 ${
        visible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none'
      }`}
    >
      <div className="flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-lg border border-gray-100 max-w-[280px]">
        {current.image ? (
          <img
            src={current.image}
            alt={current.name}
            className="h-12 w-12 rounded-lg object-cover flex-shrink-0"
          />
        ) : (
          <div className="h-12 w-12 rounded-lg bg-gray-100 flex-shrink-0 flex items-center justify-center">
            <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
        )}
        <div className="min-w-0">
          <p className="text-xs font-semibold text-gray-900 truncate">{current.name}</p>
          <p className="text-xs text-gray-500 mt-0.5">ordered just now!</p>
          <div className="flex items-center gap-1 mt-1">
            <div className="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse" />
            <span className="text-[10px] text-green-600 font-medium">Live</span>
          </div>
        </div>
        <button
          onClick={() => setVisible(false)}
          className="ml-auto flex-shrink-0 text-gray-400 hover:text-gray-600 -mr-1"
          aria-label="Dismiss"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  );
}
