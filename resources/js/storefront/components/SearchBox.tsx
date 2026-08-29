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
          <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style={{ color: 'var(--av-muted)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          {loading && (
            <svg className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 animate-spin" style={{ color: 'var(--av-muted)' }} fill="none" viewBox="0 0 24 24">
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
            className={`av-input pl-9 pr-4 w-full text-sm ${inputClassName}`}
            style={{ height: 38, borderRadius: 2, fontFamily: 'var(--av-sans)' }}
            autoComplete="off"
            aria-label="Search products"
          />
        </div>
      </form>

      {open && suggestions.length > 0 && (
        <div className="absolute left-0 right-0 top-full mt-1.5 z-50 overflow-hidden" style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', boxShadow: '0 12px 40px rgba(31,26,21,.12)' }}>
          {suggestions.map((s, i) => (
            <button
              key={s.id}
              type="button"
              onMouseDown={() => navigate(s.slug)}
              style={{
                width: '100%', display: 'flex', alignItems: 'center', gap: 12, padding: '10px 12px', textAlign: 'left', cursor: 'pointer',
                background: i === activeIdx ? 'var(--av-paper-2)' : 'transparent', border: 'none', borderTop: i > 0 ? '1px solid var(--av-line-soft)' : 'none', transition: 'background .15s'
              }}
              onMouseEnter={e => { if (i !== activeIdx) (e.currentTarget as HTMLElement).style.background = 'var(--av-paper-2)'; }}
              onMouseLeave={e => { if (i !== activeIdx) (e.currentTarget as HTMLElement).style.background = 'transparent'; }}
            >
              {s.image
                ? <img src={s.image} alt={s.name} style={{ width: 36, height: 36, objectFit: 'cover', flexShrink: 0, border: '1px solid var(--av-line-soft)' }} />
                : <div style={{ width: 36, height: 36, background: 'var(--av-paper-2)', flexShrink: 0, display: 'grid', placeItems: 'center', border: '1px solid var(--av-line-soft)' }}>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{ color: 'var(--av-muted)' }}>
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                    </svg>
                  </div>
              }
              <div style={{ flex: 1, minWidth: 0 }}>
                <p style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis', margin: 0 }}>{s.name}</p>
                <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginTop: 2 }}>
                  <span style={{ fontSize: 12, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{formatPrice(s.price)}</span>
                  {s.compare_at_price && s.compare_at_price > s.price && (
                    <span style={{ fontSize: 11, color: 'var(--av-muted)', textDecoration: 'line-through', fontFamily: 'var(--av-sans)' }}>{formatPrice(s.compare_at_price)}</span>
                  )}
                </div>
              </div>
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{ color: 'var(--av-muted)', flexShrink: 0 }}>
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          ))}
          <div style={{ padding: '8px 12px', borderTop: '1px solid var(--av-line-soft)', background: 'var(--av-paper-2)' }}>
            <button type="button" onMouseDown={() => { setOpen(false); router.visit(`/shop?q=${encodeURIComponent(query.trim())}`); }}
              style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', background: 'transparent', border: 'none', cursor: 'pointer', width: '100%', textAlign: 'left', padding: 0 }}>
              See all results for <strong style={{ color: 'var(--av-ink)' }}>"{query}"</strong>
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
