import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { useCartStore } from '../store/cartStore';
import { useWishlistStore } from '../store/wishlistStore';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';

type SortKey = 'date' | 'price-asc' | 'price-desc';

const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };

export default function Wishlist() {
  const { items, remove, clear } = useWishlistStore();
  const addItem = useCartStore(s => s.addItem);
  const fmt = usePrice();

  const [sortBy, setSortBy] = useState<SortKey>('date');
  const [selected, setSelected] = useState<Set<number>>(new Set());
  const [copied, setCopied] = useState(false);

  const sorted = [...items].sort((a, b) => {
    if (sortBy === 'price-asc') return a.price - b.price;
    if (sortBy === 'price-desc') return b.price - a.price;
    return 0;
  });

  const allSelected = sorted.length > 0 && selected.size === sorted.length;

  function toggleSel(id: number, on: boolean) {
    setSelected(prev => {
      const next = new Set(prev);
      if (on) next.add(id); else next.delete(id);
      return next;
    });
  }

  function toggleSelAll(on: boolean) {
    setSelected(on ? new Set(sorted.map(i => i.id)) : new Set());
  }

  function moveToCart(item: typeof items[0]) {
    addItem({
      product_id: item.id,
      variant_id: null,
      variant_label: null,
      name: item.name,
      price: item.price,
      image_url: item.image_url,
      slug: item.slug,
      shipping_included: false,
    });
    remove(item.id);
    setSelected(prev => { const n = new Set(prev); n.delete(item.id); return n; });
  }

  function moveSelectedToCart() {
    const ids = [...selected];
    ids.forEach(id => {
      const item = items.find(i => i.id === id);
      if (item) moveToCart(item);
    });
    setSelected(new Set());
  }

  function shareWishlist() {
    navigator.clipboard?.writeText(window.location.href).then(() => {
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }).catch(() => {
      const ta = document.createElement('textarea');
      ta.value = window.location.href;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    });
  }

  const disc = (price: number, compare: number | null) =>
    compare ? Math.round((1 - price / compare) * 100) : null;

  return (
    <Layout>
      <Head title="Wishlist" />

      <div style={{ ...W, paddingBottom: 64 }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 11.5, color: 'var(--av-muted)', padding: '18px 0 0', fontFamily: 'var(--av-sans)' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link>
          <span>/</span>
          <span style={{ color: 'var(--av-ink)', fontWeight: 500 }}>Wishlist</span>
        </nav>

        {/* Page head */}
        <div style={{ padding: '20px 0 0' }}>
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(26px,3vw,38px)', fontWeight: 400, letterSpacing: '-0.012em', margin: 0, color: 'var(--av-ink)', lineHeight: 1.04 }}>Wishlist</h1>
          <div style={{ fontSize: 13.5, color: 'var(--av-muted)', marginTop: 8, fontFamily: 'var(--av-sans)' }}>
            {items.length > 0 ? `${items.length} piece${items.length !== 1 ? 's' : ''} saved` : 'Save pieces you love and return to them later.'}
          </div>
        </div>

        {items.length === 0 ? (
          <div className="av-empty" style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', marginTop: 28 }}>
            <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'var(--av-paper-2)', color: 'var(--av-muted)', display: 'grid', placeItems: 'center', margin: '0 auto 18px' }}>
              <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </div>
            <h2>Your wishlist is empty</h2>
            <p>Tap the heart on any product to save it here.</p>
            <Link href="/shop" className="av-btn av-btn-primary av-btn-md" style={{ textDecoration: 'none' }}>
              Browse collection
            </Link>
          </div>
        ) : (
          <>
            {/* Toolbar */}
            <div style={{
              display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12,
              padding: '20px 0 16px', borderBottom: '1px solid var(--av-line)', marginTop: 8
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', cursor: 'pointer', fontFamily: 'var(--av-sans)' }}>
                  <input type="checkbox" checked={allSelected} onChange={e => toggleSelAll(e.target.checked)}
                    style={{ width: 14, height: 14, accentColor: 'var(--av-ink)', cursor: 'pointer' }} />
                  <span style={{ letterSpacing: '0.06em', textTransform: 'uppercase', fontSize: 11 }}>Select all</span>
                </label>
                {selected.size > 0 && (
                  <>
                    <span style={{ color: 'var(--av-line)', fontSize: 12 }}>·</span>
                    <span style={{ fontSize: 12.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                      <strong style={{ color: 'var(--av-ink)' }}>{selected.size}</strong> selected
                    </span>
                  </>
                )}
              </div>

              <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                <select value={sortBy} onChange={e => setSortBy(e.target.value as SortKey)}
                  className="av-select" style={{ height: 36, width: 'auto', fontSize: 12.5 }}>
                  <option value="date">Recently added</option>
                  <option value="price-asc">Price: Low to high</option>
                  <option value="price-desc">Price: High to low</option>
                </select>

                {selected.size > 0 && (
                  <button onClick={moveSelectedToCart} className="av-btn av-btn-primary av-btn-sm">
                    Move {selected.size} to bag
                  </button>
                )}

                <button onClick={shareWishlist} className="av-btn av-btn-secondary av-btn-sm">
                  {copied ? 'Copied' : 'Share'}
                </button>
                {items.length > 0 && (
                  <button onClick={() => { if (confirm('Clear wishlist?')) { clear(); setSelected(new Set()); } }} className="av-btn av-btn-secondary av-btn-sm">
                    Clear
                  </button>
                )}
              </div>
            </div>

            {/* Product grid */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 20, padding: '24px 0 0' }} className="av-wl-grid">
              {sorted.map(item => {
                const pct = disc(item.price, item.compare_at_price);
                const isSelected = selected.has(item.id);

                return (
                  <div key={item.id} style={{ position: 'relative' }}>
                    <label style={{
                      position: 'absolute', top: 10, left: 10, zIndex: 2,
                      background: 'var(--av-paper)', padding: '3px 6px',
                      border: '1px solid var(--av-line)', display: 'flex', alignItems: 'center', cursor: 'pointer',
                    }}>
                      <input type="checkbox" checked={isSelected} onChange={e => toggleSel(item.id, e.target.checked)}
                        style={{ width: 13, height: 13, accentColor: 'var(--av-ink)', cursor: 'pointer' }} />
                    </label>

                    <div style={{
                      background: 'var(--av-paper)', border: '1px solid var(--av-line-soft)', overflow: 'hidden',
                    }}>
                      <Link href={`/shop/${item.slug}`} style={{ textDecoration: 'none', display: 'block' }}>
                        <div style={{ aspectRatio: '4/5', background: 'var(--av-paper-2)', position: 'relative', overflow: 'hidden' }}>
                          {item.image_url
                            ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
                            : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', color: 'var(--av-muted)' }}>
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.2}/>
                                </svg>
                              </div>
                          }
                          {pct !== null && pct > 0 && (
                            <span style={{ position: 'absolute', top: 10, right: 10, background: 'var(--av-cognac)', color: '#fff', fontSize: 9.5, fontWeight: 500, letterSpacing: '0.14em', textTransform: 'uppercase', padding: '4px 7px', fontFamily: 'var(--av-sans)' }}>
                              −{pct}%
                            </span>
                          )}
                          <button onClick={e => { e.preventDefault(); remove(item.id); }}
                            style={{ position: 'absolute', top: 10, right: pct ? 54 : 10, width: 30, height: 30, background: 'rgba(251,248,241,.92)', border: '1px solid var(--av-line)', cursor: 'pointer', display: 'grid', placeItems: 'center', color: 'var(--av-cognac)' }}
                            title="Remove" aria-label="Remove from wishlist">
                            <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/>
                            </svg>
                          </button>
                        </div>
                      </Link>

                      <div style={{ padding: '14px 14px 16px' }}>
                        {item.category && (
                          <div style={{ fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 6, fontFamily: 'var(--av-sans)' }}>{item.category}</div>
                        )}
                        <Link href={`/shop/${item.slug}`} style={{ textDecoration: 'none' }}>
                          <div style={{ fontFamily: 'var(--av-display)', fontSize: 15, fontWeight: 400, color: 'var(--av-ink)', lineHeight: 1.3,
                            overflow: 'hidden', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' }}>
                            {item.name}
                          </div>
                        </Link>
                        <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginTop: 8 }}>
                          <span style={{ fontWeight: 500, fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{fmt(item.price)}</span>
                          {item.compare_at_price && (
                            <span style={{ fontSize: 11.5, color: 'var(--av-muted)', textDecoration: 'line-through', fontFamily: 'var(--av-sans)' }}>{fmt(item.compare_at_price)}</span>
                          )}
                        </div>
                        <button onClick={() => moveToCart(item)} className="av-btn av-btn-primary av-btn-sm av-btn-block" style={{ marginTop: 12 }}>
                          Move to bag
                        </button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </>
        )}
      </div>

      <style>{`
        @media(max-width: 960px){ .av-wl-grid{ grid-template-columns: repeat(3, 1fr) !important; gap: 14px !important; } }
        @media(max-width: 640px){ .av-wl-grid{ grid-template-columns: repeat(2, 1fr) !important; gap: 12px !important; } }
      `}</style>
    </Layout>
  );
}
