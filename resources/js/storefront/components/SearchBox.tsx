import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { usePrice } from '../usePrice';

interface Suggestion {
  id: number;
  name: string;
  slug: string;
  price: number;
  compare_at_price: number | null;
  image: string | null;
}

interface Props {
  placeholder?: string;
  className?: string;
  inputClassName?: string;
}

export default function SearchBox({ placeholder = 'Search products…', className = '', inputClassName = '' }: Props) {
  const [query, setQuery]           = useState('');
  const [suggestions, setSuggestions] = useState<Suggestion[]>([]);
  const [open, setOpen]             = useState(false);
  const [loading, setLoading]       = useState(false);
  const [activeIdx, setActiveIdx]   = useState(-1);
  const debounceRef                 = useRef<ReturnType<typeof setTimeout> | null>(null);
  const containerRef                = useRef<HTMLDivElement>(null);
  const formatPrice                 = usePrice();

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  useEffect(() => {
    if (debounceRef.current) clearTimeout(debounceRef.current);

    if (query.trim().length < 2) {
      setSuggestions([]);
      setOpen(false);
      return;
    }

    debounceRef.current = setTimeout(async () => {
      setLoading(true);
      try {
        const res = await fetch(`/shop/suggest?q=${encodeURIComponent(query.trim())}`, {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
          const data: Suggestion[] = await res.json();
          setSuggestions(data);
          setOpen(data.length > 0);
        }
      } catch (err) {
        console.error('Search suggest failed:', err);
      } finally {
        setLoading(false);
      }
    }, 300);

    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, [query]);

  function navigate(slug: string) {
    setOpen(false);
    setQuery('');
    router.visit(`/shop/${slug}`);
  }

  function submit(e: React.FormEvent) {
    e.preventDefault();
    setOpen(false);
    if (activeIdx >= 0 && suggestions[activeIdx]) {
      navigate(suggestions[activeIdx].slug);
    } else if (query.trim()) {
      router.visit(`/shop?q=${encodeURIComponent(query.trim())}`);
    }
  }

  function handleKey(e: React.KeyboardEvent) {
    if (!open) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActiveIdx(i => Math.min(i + 1, suggestions.length - 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActiveIdx(i => Math.max(i - 1, -1));
    } else if (e.key === 'Escape') {
      setOpen(false);
    }
  }

  return (
    <div ref={containerRef} className={`relative ${className}`}>
      <form onSubmit={submit}>
        <div className="relative w-full">
          <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          {loading && (
            <svg className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/>
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3V4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"/>
            </svg>
          )}
          <input
            type="text"
            placeholder={placeholder}
            value={query}
            onChange={e => { setQuery(e.target.value); setActiveIdx(-1); }}
            onKeyDown={handleKey}
            onFocus={() => suggestions.length > 0 && setOpen(true)}
            className={`kb-input pl-9 pr-4 w-full text-sm h-9 ${inputClassName}`}
            style={{ borderRadius: 9999 }}
            autoComplete="off"
          />
        </div>
      </form>

      {open && suggestions.length > 0 && (
        <div className="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden">
          {suggestions.map((s, i) => (
            <button
              key={s.id}
              type="button"
              onMouseDown={() => navigate(s.slug)}
              className={`w-full flex items-center gap-3 px-3 py-2.5 text-left transition-colors ${i === activeIdx ? 'bg-slate-100' : 'hover:bg-slate-50'} ${i > 0 ? 'border-t border-slate-100' : ''}`}
              style={{ background: 'none', border: i > 0 ? '1px solid #f1f5f9' : 'none', borderLeft: 'none', borderRight: 'none', cursor: 'pointer' }}
            >
              {s.image
                ? <img src={s.image} alt={s.name} className="w-9 h-9 rounded-lg object-cover shrink-0 border border-slate-100" />
                : <div className="w-9 h-9 rounded-lg bg-slate-100 shrink-0 flex items-center justify-center">
                    <svg className="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>
              }
              <div className="flex-1 min-w-0">
                <p className="text-sm text-slate-800 font-medium truncate">{s.name}</p>
                <div className="flex items-center gap-1.5 mt-0.5">
                  <span className="text-xs font-semibold" style={{ color: 'var(--kb-primary)' }}>{formatPrice(s.price)}</span>
                  {s.compare_at_price && s.compare_at_price > s.price && (
                    <span className="text-xs text-slate-400 line-through">{formatPrice(s.compare_at_price)}</span>
                  )}
                </div>
              </div>
              <svg className="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          ))}
          <div className="px-4 py-2 border-t border-slate-100 bg-slate-50/80">
            <button type="button" onMouseDown={() => { setOpen(false); router.visit(`/shop?q=${encodeURIComponent(query.trim())}`); }}
              className="text-xs text-slate-500 hover:text-slate-700 w-full text-left"
              style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
              See all results for <strong className="text-slate-700">"{query}"</strong>
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
