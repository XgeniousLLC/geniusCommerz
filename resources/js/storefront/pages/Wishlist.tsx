import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { useCartStore } from '../store/cartStore';
import { useWishlistStore } from '../store/wishlistStore';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';

type SortKey = 'date' | 'price-asc' | 'price-desc';

function HeartFilledIcon() {
  return (
    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
      <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/>
    </svg>
  );
}

function CartIcon() {
  return (
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
    </svg>
  );
}

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
      <Head title="My Wishlist" />

      <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--kb-ink-soft)', padding: '16px 0 0' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-800">Home</Link>
          <span>/</span>
          <span style={{ color: 'var(--kb-ink)' }}>Wishlist</span>
        </nav>

        {/* Page head */}
        <div style={{ padding: '20px 0 0' }}>
          <h1 style={{ fontSize: 28, fontWeight: 800, letterSpacing: '-0.02em', margin: 0, color: 'var(--kb-ink)' }}>My Wishlist</h1>
          <div style={{ fontSize: 14, color: 'var(--kb-ink-soft)', marginTop: 4 }}>
            {items.length > 0 ? `${items.length} item${items.length !== 1 ? 's' : ''} saved` : 'Save items you love and come back to them later.'}
          </div>
        </div>

        {items.length === 0 ? (
          /* Empty state */
          <div style={{ textAlign: 'center', padding: '80px 24px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 0 }}>
            <div style={{ width: 72, height: 72, borderRadius: '50%', background: '#FEF2F2', color: '#FDA4AF', display: 'grid', placeItems: 'center', marginBottom: 20 }}>
              <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </div>
            <h2 style={{ fontSize: 20, fontWeight: 700, margin: '0 0 8px', color: 'var(--kb-ink)' }}>Your wishlist is empty</h2>
            <p style={{ fontSize: 14, color: 'var(--kb-ink-soft)', margin: '0 0 28px' }}>Tap the heart on any product to save it here.</p>
            <Link href="/shop"
              style={{ display: 'inline-flex', alignItems: 'center', height: 48, padding: '0 28px', borderRadius: 10, background: 'var(--kb-primary)', color: '#fff', fontWeight: 700, fontSize: 15, textDecoration: 'none' }}>
              Browse products
            </Link>
          </div>
        ) : (
          <>
            {/* Toolbar */}
            <div style={{
              display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 10,
              padding: '20px 0 16px', borderBottom: '1px solid var(--kb-border)',
            }}>
              {/* Left */}
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 14, fontWeight: 500, color: 'var(--kb-ink)', cursor: 'pointer' }}>
                  <input type="checkbox" checked={allSelected} onChange={e => toggleSelAll(e.target.checked)}
                    style={{ width: 16, height: 16, accentColor: 'var(--kb-primary)', cursor: 'pointer' }} />
                  <span>Select all</span>
                </label>
                {selected.size > 0 && (
                  <>
                    <span style={{ color: 'var(--kb-border)', fontSize: 16 }}>·</span>
                    <span style={{ fontSize: 13, color: 'var(--kb-ink-muted)' }}>
                      <strong style={{ color: 'var(--kb-ink)' }}>{selected.size}</strong> selected
                    </span>
                  </>
                )}
              </div>

              {/* Right */}
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <select value={sortBy} onChange={e => setSortBy(e.target.value as SortKey)}
                  style={{ height: 36, padding: '0 10px', border: '1px solid var(--kb-border)', borderRadius: 8, background: '#fff', fontSize: 13, color: 'var(--kb-ink)', outline: 'none', cursor: 'pointer' }}>
                  <option value="date">Recently added</option>
                  <option value="price-asc">Price: Low to high</option>
                  <option value="price-desc">Price: High to low</option>
                </select>

                {selected.size > 0 && (
                  <button onClick={moveSelectedToCart}
                    style={{ display: 'flex', alignItems: 'center', gap: 7, height: 36, padding: '0 14px', borderRadius: 8, background: 'var(--kb-primary)', color: '#fff', fontWeight: 600, fontSize: 13, border: 'none', cursor: 'pointer' }}>
                    <CartIcon />
                    Move {selected.size} to cart
                  </button>
                )}

                <button onClick={shareWishlist}
                  style={{ display: 'flex', alignItems: 'center', gap: 6, height: 36, padding: '0 14px', borderRadius: 8, border: `1px solid ${copied ? 'var(--kb-success-200)' : 'var(--kb-border)'}`, background: copied ? 'var(--kb-success-50)' : '#fff', color: copied ? 'var(--kb-success)' : 'var(--kb-ink-muted)', fontWeight: 600, fontSize: 13, cursor: 'pointer', transition: 'all .15s' }}>
                  {copied ? (
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"/>
                    </svg>
                  ) : (
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.1-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                  )}
                  {copied ? 'Copied!' : 'Share'}
                </button>
              </div>
            </div>

            {/* Product grid */}
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4" style={{ gap: 16, padding: '24px 0 64px' }}>
              {sorted.map(item => {
                const pct = disc(item.price, item.compare_at_price);
                const isSelected = selected.has(item.id);

                return (
                  <div key={item.id} style={{ position: 'relative' }}>
                    {/* Checkbox */}
                    <label style={{
                      position: 'absolute', top: 14, left: 14, zIndex: 3,
                      background: '#fff', padding: '4px 8px', borderRadius: 6,
                      border: '1px solid var(--kb-border)',
                      boxShadow: '0 1px 4px rgba(14,19,32,.08)',
                      display: 'flex', alignItems: 'center', cursor: 'pointer',
                    }}>
                      <input type="checkbox" checked={isSelected} onChange={e => toggleSel(item.id, e.target.checked)}
                        style={{ width: 14, height: 14, accentColor: 'var(--kb-primary)', cursor: 'pointer' }} />
                    </label>

                    {/* Card */}
                    <div style={{
                      background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, overflow: 'hidden',
                      transition: 'box-shadow .15s',
                    }} className="hover:shadow-md group">
                      {/* Image */}
                      <Link href={`/shop/${item.slug}`} style={{ textDecoration: 'none', display: 'block' }}>
                        <div style={{ aspectRatio: '1', background: 'var(--kb-surface-2)', position: 'relative', overflow: 'hidden' }}>
                          {item.image_url
                            ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform .3s' }} className="group-hover:scale-105" />
                            : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', fontSize: 32, color: 'var(--kb-border)' }}>
                                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/>
                                  <circle cx="8.5" cy="8.5" r="1.5" strokeWidth={1.5}/>
                                  <path strokeWidth={1.5} d="M21 15l-5-5L5 21"/>
                                </svg>
                              </div>
                          }
                          {pct && (
                            <span style={{ position: 'absolute', top: 10, left: 44, background: '#EF4444', color: '#fff', fontSize: 11, fontWeight: 700, padding: '2px 7px', borderRadius: 999 }}>
                              -{pct}%
                            </span>
                          )}
                          {/* Remove button */}
                          <button onClick={() => remove(item.id)}
                            style={{ position: 'absolute', top: 10, right: 10, width: 32, height: 32, borderRadius: '50%', background: '#fff', border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', color: '#EF4444', boxShadow: '0 1px 4px rgba(14,19,32,.12)' }}>
                            <HeartFilledIcon />
                          </button>
                        </div>
                      </Link>

                      {/* Body */}
                      <div style={{ padding: '12px 14px 14px' }}>
                        {item.category && (
                          <div style={{ fontSize: 11, color: 'var(--kb-ink-soft)', marginBottom: 4 }}>{item.category}</div>
                        )}
                        <Link href={`/shop/${item.slug}`} style={{ textDecoration: 'none' }}>
                          <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--kb-ink)', lineHeight: 1.35,
                            overflow: 'hidden', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' }}>
                            {item.name}
                          </div>
                        </Link>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 8 }}>
                          <span style={{ fontWeight: 700, fontSize: 16, color: 'var(--kb-ink)' }}>{fmt(item.price)}</span>
                          {item.compare_at_price && (
                            <span style={{ fontSize: 12, color: 'var(--kb-ink-soft)', textDecoration: 'line-through' }}>{fmt(item.compare_at_price)}</span>
                          )}
                        </div>
                        <button onClick={() => moveToCart(item)}
                          style={{
                            display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 7,
                            width: '100%', height: 38, marginTop: 10,
                            borderRadius: 8, border: 'none', cursor: 'pointer',
                            background: 'var(--kb-primary)', color: '#fff',
                            fontWeight: 600, fontSize: 13,
                            transition: 'background .15s',
                          }}
                          className="hover:bg-[#102B6B]">
                          <CartIcon />
                          Move to Cart
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
    </Layout>
  );
}
