import { Head, Link, router, usePage } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import { useCartStore } from '../../store/cartStore';
import { useWishlistStore } from '../../store/wishlistStore';
import Layout from '../../layouts/Layout';
import { usePrice } from '../../usePrice';
import type { Brand, Paginated, ProductCard, SharedProps, ShopCategory } from '../../types';

interface Filters {
  q?: string; min_price?: string; max_price?: string;
  sort?: string; in_stock?: string; on_sale?: string; min_rating?: string;
}
interface Props {
  products: Paginated<ProductCard>;
  categories: ShopCategory[];
  brands: Brand[];
  filters: Filters;
  activeCategorySlug?: string | null;
  activeCategoryName?: string | null;
  activeBrandSlug?: string | null;
  activeBrandName?: string | null;
}

const S = { fill: 'none', stroke: 'currentColor', strokeWidth: 1.4, strokeLinecap: 'round', strokeLinejoin: 'round' } as React.SVGProps<SVGSVGElement>;

function Stars({ rating }: { rating: number }) {
  return (
    <span style={{ color: 'var(--av-gold)', fontSize: 11, letterSpacing: 1.5 }}>
      {[1,2,3,4,5].map(i => rating >= i ? '★' : rating >= i - 0.5 ? '½' : '☆').join('')}
    </span>
  );
}

function AvProductCard({ product }: { product: ProductCard }) {
  const fmt = usePrice();
  const [hovered, setHovered] = useState(false);
  const addItem  = useCartStore(s => s.addItem);
  const openCart = useCartStore(s => s.openCart);
  const { has, toggle } = useWishlistStore();
  const wished = has(product.id);
  const discount = product.compare_at_price ? Math.round((1 - product.price / product.compare_at_price) * 100) : null;

  function addToCart(e: React.MouseEvent) {
    e.preventDefault();
    if (!product.in_stock || product.has_variants) { router.visit(`/shop/${product.slug}`); return; }
    addItem({ product_id: product.id, variant_id: null, variant_label: null, name: product.name, price: product.price, image_url: product.image_url, slug: product.slug, shipping_included: false });
    openCart();
  }

  function toggleWish(e: React.MouseEvent) {
    e.preventDefault();
    toggle({ id: product.id, slug: product.slug, name: product.name, price: product.price, compare_at_price: product.compare_at_price, image_url: product.image_url, category: product.category_name });
  }

  return (
    <Link href={`/shop/${product.slug}`} style={{ textDecoration: 'none', color: 'inherit', display: 'block' }}
      onMouseEnter={() => setHovered(true)} onMouseLeave={() => setHovered(false)}>
      <div style={{ aspectRatio: '4/5', position: 'relative', overflow: 'hidden', background: 'var(--av-paper-2)', marginBottom: 12 }}>
        {product.image_url
          ? <img src={product.image_url} alt={product.name} loading="lazy" style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block', transition: 'transform .55s cubic-bezier(.2,.7,.3,1)', transform: hovered ? 'scale(1.04)' : 'scale(1)' }} />
          : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center' }}>
              <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="var(--av-line)" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round">
                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
        }
        {discount && discount > 0 && (
          <span style={{ position: 'absolute', top: 10, left: 10, background: 'var(--av-cognac)', color: '#fff', fontSize: 9.5, letterSpacing: '0.18em', textTransform: 'uppercase', padding: '4px 9px', fontFamily: 'var(--av-sans)' }}>−{discount}%</span>
        )}
        {!product.in_stock && (
          <div style={{ position: 'absolute', inset: 0, background: 'rgba(244,239,229,.7)', display: 'grid', placeItems: 'center' }}>
            <span style={{ fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>Sold out</span>
          </div>
        )}
        <button onClick={toggleWish} title="Wishlist"
          style={{ position: 'absolute', top: 10, right: 10, width: 32, height: 32, borderRadius: '50%', background: 'rgba(251,248,241,.88)', border: '1px solid var(--av-line)', color: wished ? 'var(--av-cognac)' : 'var(--av-ink)', display: 'grid', placeItems: 'center', cursor: 'pointer', backdropFilter: 'blur(4px)', opacity: hovered ? 1 : 0, transition: 'opacity .2s' }}>
          <svg viewBox="0 0 24 24" width="14" height="14" fill={wished ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
          </svg>
        </button>
        {product.in_stock && (
          <button onClick={addToCart}
            style={{ position: 'absolute', bottom: 0, left: 0, right: 0, background: 'var(--av-ink)', color: 'var(--av-paper)', padding: '13px 16px', fontSize: 10.5, letterSpacing: '0.2em', textTransform: 'uppercase', border: 'none', cursor: 'pointer', fontFamily: 'var(--av-sans)', fontWeight: 500, opacity: hovered ? 1 : 0, transform: hovered ? 'translateY(0)' : 'translateY(12px)', transition: 'opacity .22s, transform .22s' }}>
            {product.has_variants ? 'Select Options' : 'Quick Add'}
          </button>
        )}
      </div>
      {product.category_name && (
        <div style={{ fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 4, fontFamily: 'var(--av-sans)' }}>{product.category_name}</div>
      )}
      <div style={{ fontFamily: 'var(--av-display)', fontSize: 15, fontWeight: 400, lineHeight: 1.2, color: 'var(--av-ink)', marginBottom: 5, letterSpacing: '-0.01em', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>{product.name}</div>
      {product.avg_rating !== null && product.avg_rating > 0 && (
        <div style={{ display: 'flex', alignItems: 'center', gap: 5, marginBottom: 5 }}>
          <Stars rating={product.avg_rating} />
          <span style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>({product.reviews_count})</span>
        </div>
      )}
      <div style={{ display: 'flex', alignItems: 'baseline', gap: 8 }}>
        <span style={{ fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>{fmt(product.price)}</span>
        {product.compare_at_price && <span style={{ fontSize: 12, color: 'var(--av-muted)', textDecoration: 'line-through', fontFamily: 'var(--av-sans)' }}>{fmt(product.compare_at_price)}</span>}
      </div>
    </Link>
  );
}

function AvListCard({ product }: { product: ProductCard }) {
  const fmt = usePrice();
  const addItem  = useCartStore(s => s.addItem);
  const openCart = useCartStore(s => s.openCart);
  const { has, toggle } = useWishlistStore();
  const wished = has(product.id);
  const discount = product.compare_at_price ? Math.round((1 - product.price / product.compare_at_price) * 100) : null;

  function addToCart(e: React.MouseEvent) {
    e.preventDefault();
    if (!product.in_stock || product.has_variants) { router.visit(`/shop/${product.slug}`); return; }
    addItem({ product_id: product.id, variant_id: null, variant_label: null, name: product.name, price: product.price, image_url: product.image_url, slug: product.slug, shipping_included: false });
    openCart();
  }

  return (
    <Link href={`/shop/${product.slug}`} style={{ textDecoration: 'none', color: 'inherit', display: 'flex', gap: 20, paddingBottom: 20, marginBottom: 20, borderBottom: '1px solid var(--av-line-soft)' }}>
      <div style={{ width: 120, height: 150, flexShrink: 0, background: 'var(--av-paper-2)', overflow: 'hidden' }}>
        {product.image_url && <img src={product.image_url} alt={product.name} loading="lazy" style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />}
      </div>
      <div style={{ flex: 1 }}>
        {product.category_name && <div style={{ fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 6, fontFamily: 'var(--av-sans)' }}>{product.category_name}</div>}
        <div style={{ fontFamily: 'var(--av-display)', fontSize: 18, fontWeight: 400, color: 'var(--av-ink)', marginBottom: 8, lineHeight: 1.2 }}>{product.name}</div>
        {product.avg_rating !== null && product.avg_rating > 0 && (
          <div style={{ display: 'flex', alignItems: 'center', gap: 5, marginBottom: 8 }}>
            <Stars rating={product.avg_rating} />
            <span style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>({product.reviews_count})</span>
          </div>
        )}
        <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginBottom: 14 }}>
          <span style={{ fontSize: 15, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>{fmt(product.price)}</span>
          {product.compare_at_price && <span style={{ fontSize: 12.5, color: 'var(--av-muted)', textDecoration: 'line-through', fontFamily: 'var(--av-sans)' }}>{fmt(product.compare_at_price)}</span>}
          {discount && discount > 0 && <span style={{ fontSize: 11, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, letterSpacing: '0.04em' }}>−{discount}%</span>}
        </div>
        <button onClick={addToCart}
          style={{ padding: '10px 22px', background: product.in_stock ? 'var(--av-ink)' : 'transparent', color: product.in_stock ? 'var(--av-paper)' : 'var(--av-muted)', border: `1px solid ${product.in_stock ? 'var(--av-ink)' : 'var(--av-line)'}`, fontFamily: 'var(--av-sans)', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', borderRadius: 2, cursor: product.in_stock ? 'pointer' : 'not-allowed' }}>
          {product.in_stock ? (product.has_variants ? 'Select Options' : 'Add to Bag') : 'Sold Out'}
        </button>
      </div>
      <button onClick={e => { e.preventDefault(); toggle({ id: product.id, slug: product.slug, name: product.name, price: product.price, compare_at_price: product.compare_at_price, image_url: product.image_url, category: product.category_name }); }}
        style={{ alignSelf: 'flex-start', background: 'transparent', border: 'none', cursor: 'pointer', color: wished ? 'var(--av-cognac)' : 'var(--av-muted)', padding: 4 }}>
        <svg viewBox="0 0 24 24" width="16" height="16" fill={wished ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round">
          <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
        </svg>
      </button>
    </Link>
  );
}

function Pagination({ data }: { data: Paginated<ProductCard> }) {
  if (data.last_page <= 1) return null;
  function go(url: string | null) {
    if (!url) return;
    router.get(url, {}, { preserveScroll: false });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  const btn = (active: boolean): React.CSSProperties => ({
    minWidth: 38, height: 38, border: `1px solid ${active ? 'var(--av-ink)' : 'var(--av-line)'}`,
    background: active ? 'var(--av-ink)' : 'transparent',
    color: active ? 'var(--av-paper)' : 'var(--av-ink)',
    fontSize: 13, fontFamily: 'var(--av-sans)', cursor: 'pointer',
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', padding: '0 12px',
  });
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 4, padding: '40px 0 0' }}>
      <button style={{ ...btn(false), opacity: !data.prev_page_url ? .35 : 1 }} disabled={!data.prev_page_url} onClick={() => go(data.prev_page_url)}>←</button>
      {data.links.filter(l => !l.label.includes('Previous') && !l.label.includes('Next')).map((link, i) => (
        <button key={i} style={btn(link.active)} disabled={!link.url} onClick={() => go(link.url)}
          dangerouslySetInnerHTML={{ __html: link.label }} />
      ))}
      <button style={{ ...btn(false), opacity: !data.next_page_url ? .35 : 1 }} disabled={!data.next_page_url} onClick={() => go(data.next_page_url)}>→</button>
    </div>
  );
}

export default function ShopIndex({ products, categories, brands, filters, activeCategorySlug, activeCategoryName, activeBrandSlug, activeBrandName }: Props) {
  const { site } = usePage<SharedProps>().props;
  const showFilters = site.shopShowFilters !== false;
  const defaultSort = site.shopDefaultSort ?? 'newest';

  const [localFilters, setLocalFilters] = useState<Filters>({ sort: defaultSort, ...filters });
  const [priceMin, setPriceMin] = useState(filters.min_price ?? '');
  const [priceMax, setPriceMax] = useState(filters.max_price ?? '');
  const [view, setView] = useState<'grid' | 'list'>('grid');
  const [mobileOpen, setMobileOpen] = useState(false);

  const baseUrl = activeCategorySlug ? `/shop/c/${activeCategorySlug}` : activeBrandSlug ? `/shop/b/${activeBrandSlug}` : '/shop';

  const applyFilters = useCallback((updated: Filters) => {
    const clean: Record<string, string> = {};
    Object.entries(updated).forEach(([k, v]) => { if (v) clean[k] = String(v); });
    router.get(baseUrl, clean, { preserveScroll: false });
  }, [baseUrl]);

  function update(key: keyof Filters, value: string) {
    const next = { ...localFilters, [key]: value };
    setLocalFilters(next);
    if (key !== 'min_price' && key !== 'max_price') applyFilters(next);
  }

  function applyPrice() {
    const next = { ...localFilters, min_price: priceMin, max_price: priceMax };
    setLocalFilters(next); applyFilters(next);
  }

  function clearAll() {
    const reset: Filters = { sort: localFilters.sort };
    setLocalFilters(reset); setPriceMin(''); setPriceMax('');
    router.get(baseUrl, reset.sort ? { sort: reset.sort } : {}, { preserveScroll: false });
  }

  const hasActiveFilters = !!(filters.q || filters.min_price || filters.max_price || filters.in_stock || filters.on_sale || filters.min_rating);
  const pageTitle = activeCategoryName ?? activeBrandName ?? 'Shop';

  const FilterPanel = () => (
    <>
      <div style={{ padding: '0 0 20px', borderBottom: '1px solid var(--av-line-soft)', marginBottom: 20 }}>
        <h4 style={{ fontSize: 10.5, letterSpacing: '0.26em', textTransform: 'uppercase', color: 'var(--av-cognac)', margin: '0 0 14px', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>Category</h4>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
          {[{ id: 0, name: 'All Products', slug: null, products_count: undefined } as ShopCategory, ...categories].map(c => {
            const active = c.slug ? activeCategorySlug === c.slug : !activeCategorySlug;
            return (
              <button key={c.id}
                onClick={() => c.slug ? router.get(`/shop/c/${c.slug}`, localFilters.sort ? { sort: localFilters.sort } : {}) : router.get('/shop', localFilters.sort ? { sort: localFilters.sort } : {})}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '7px 0', background: 'transparent', border: 'none', cursor: 'pointer', color: active ? 'var(--av-ink)' : 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 13, fontWeight: active ? 500 : 400, textAlign: 'left', letterSpacing: '0.01em' }}>
                <span style={{ borderBottom: active ? '1px solid var(--av-ink)' : '1px solid transparent' }}>{c.name}</span>
                {c.products_count !== undefined && <span style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{c.products_count}</span>}
              </button>
            );
          })}
        </div>
      </div>

      {brands.length > 0 && (
        <div style={{ padding: '0 0 20px', borderBottom: '1px solid var(--av-line-soft)', marginBottom: 20 }}>
          <h4 style={{ fontSize: 10.5, letterSpacing: '0.26em', textTransform: 'uppercase', color: 'var(--av-cognac)', margin: '0 0 14px', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>Brand</h4>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
            {brands.map(b => {
              const active = activeBrandSlug === b.slug;
              return (
                <button key={b.id}
                  onClick={() => router.get(`/shop/b/${b.slug}`, localFilters.sort ? { sort: localFilters.sort } : {})}
                  style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '7px 0', background: 'transparent', border: 'none', cursor: 'pointer', color: active ? 'var(--av-ink)' : 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 13, fontWeight: active ? 500 : 400, textAlign: 'left' }}>
                  <span style={{ borderBottom: active ? '1px solid var(--av-ink)' : '1px solid transparent' }}>{b.name}</span>
                  {b.products_count !== undefined && <span style={{ fontSize: 11, color: 'var(--av-muted)' }}>{b.products_count}</span>}
                </button>
              );
            })}
          </div>
        </div>
      )}

      <div style={{ padding: '0 0 20px', borderBottom: '1px solid var(--av-line-soft)', marginBottom: 20 }}>
        <h4 style={{ fontSize: 10.5, letterSpacing: '0.26em', textTransform: 'uppercase', color: 'var(--av-cognac)', margin: '0 0 14px', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>Price</h4>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr auto 1fr', gap: 8, alignItems: 'center', marginBottom: 10 }}>
          <input type="number" placeholder="Min" value={priceMin} onChange={e => setPriceMin(e.target.value)}
            style={{ height: 36, padding: '0 10px', border: '1px solid var(--av-line)', background: 'transparent', fontSize: 13, color: 'var(--av-ink)', outline: 'none', fontFamily: 'var(--av-sans)', boxSizing: 'border-box', width: '100%' }} />
          <span style={{ fontSize: 12, color: 'var(--av-muted)', textAlign: 'center' }}>–</span>
          <input type="number" placeholder="Max" value={priceMax} onChange={e => setPriceMax(e.target.value)}
            style={{ height: 36, padding: '0 10px', border: '1px solid var(--av-line)', background: 'transparent', fontSize: 13, color: 'var(--av-ink)', outline: 'none', fontFamily: 'var(--av-sans)', boxSizing: 'border-box', width: '100%' }} />
        </div>
        <button onClick={applyPrice} style={{ width: '100%', height: 34, background: 'var(--av-ink)', color: 'var(--av-paper)', border: 'none', cursor: 'pointer', fontFamily: 'var(--av-sans)', fontSize: 10.5, letterSpacing: '0.18em', textTransform: 'uppercase', fontWeight: 500 }}>
          Apply
        </button>
      </div>

      <div style={{ padding: '0 0 20px', borderBottom: '1px solid var(--av-line-soft)', marginBottom: 20 }}>
        <h4 style={{ fontSize: 10.5, letterSpacing: '0.26em', textTransform: 'uppercase', color: 'var(--av-cognac)', margin: '0 0 14px', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>Rating</h4>
        {[4, 3, 2, 0].map(r => {
          const active = (filters.min_rating ? Number(filters.min_rating) : 0) === r;
          return (
            <button key={r} onClick={() => update('min_rating', r > 0 ? String(r) : '')}
              style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '6px 0', background: 'transparent', border: 'none', cursor: 'pointer', color: active ? 'var(--av-ink)' : 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 13, width: '100%', textAlign: 'left', fontWeight: active ? 500 : 400 }}>
              {r === 0 ? 'Any rating' : <><span style={{ color: 'var(--av-gold)', fontSize: 12, letterSpacing: 1 }}>{'★'.repeat(r)}{'☆'.repeat(5-r)}</span> & up</>}
            </button>
          );
        })}
      </div>

      <div style={{ marginBottom: 20 }}>
        <h4 style={{ fontSize: 10.5, letterSpacing: '0.26em', textTransform: 'uppercase', color: 'var(--av-cognac)', margin: '0 0 14px', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>Availability</h4>
        {[['In stock only', 'in_stock'], ['On sale', 'on_sale']].map(([label, key]) => (
          <label key={key} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '5px 0', cursor: 'pointer', fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
            <input type="checkbox" checked={!!(filters as any)[key]} onChange={e => update(key as keyof Filters, e.target.checked ? '1' : '')}
              style={{ width: 14, height: 14, accentColor: 'var(--av-ink)', cursor: 'pointer' }} />
            {label}
          </label>
        ))}
      </div>

      {hasActiveFilters && (
        <button onClick={clearAll}
          style={{ width: '100%', height: 34, background: 'transparent', border: '1px solid var(--av-line)', color: 'var(--av-muted)', cursor: 'pointer', fontFamily: 'var(--av-sans)', fontSize: 10.5, letterSpacing: '0.14em', textTransform: 'uppercase' }}>
          Clear all
        </button>
      )}
    </>
  );

  return (
    <Layout>
      <Head title={pageTitle} />

      {/* Collection banner */}
      <div style={{ background: 'var(--av-paper)', borderBottom: '1px solid var(--av-line)', padding: 'clamp(28px,5vw,48px) var(--av-gutter)' }}>
        <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto' }}>
          <nav style={{ display: 'flex', gap: 8, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 10, flexWrap: 'wrap' }}>
            <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link>
            <span>/</span>
            {(activeCategorySlug || activeBrandSlug) ? (
              <><Link href="/shop" style={{ color: 'inherit', textDecoration: 'none' }}>Shop</Link><span>/</span><span style={{ color: 'var(--av-ink)' }}>{pageTitle}</span></>
            ) : <span style={{ color: 'var(--av-ink)' }}>Shop</span>}
          </nav>
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(24px,3.5vw,44px)', fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 6px', letterSpacing: '-0.012em', lineHeight: 1.04 }}>
            {pageTitle}
          </h1>
          <div style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{products.total} pieces</div>
        </div>
      </div>

      <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: 'clamp(28px,5vw,48px) var(--av-gutter)', paddingBottom: 64 }}>
        <div style={{ display: 'grid', gridTemplateColumns: showFilters ? '220px 1fr' : '1fr', gap: 48, alignItems: 'start' }} className="av-shop-layout">

          {/* Desktop sidebar */}
          {showFilters && (
            <aside style={{ position: 'sticky', top: 76 }} className="av-shop-sidebar">
              <FilterPanel />
            </aside>
          )}

          {/* Main */}
          <section>
            {/* Toolbar */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12, marginBottom: 28, flexWrap: 'wrap', paddingBottom: 16, borderBottom: '1px solid var(--av-line)' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                {showFilters && (
                  <button onClick={() => setMobileOpen(true)} className="av-shop-filter-btn"
                    style={{ display: 'none', alignItems: 'center', gap: 6, padding: '8px 14px', border: '1px solid var(--av-line)', background: 'transparent', cursor: 'pointer', fontFamily: 'var(--av-sans)', fontSize: 11.5, color: 'var(--av-ink)', letterSpacing: '0.1em', textTransform: 'uppercase' }}>
                    <svg viewBox="0 0 24 24" width="14" height="14" {...S}><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                    Filters
                  </button>
                )}
                <span style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                  {products.from && products.to ? `${products.from}–${products.to} of ${products.total}` : `${products.total} results`}
                </span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <select value={localFilters.sort ?? defaultSort} onChange={e => update('sort', e.target.value)}
                  style={{ height: 34, padding: '0 10px', border: '1px solid var(--av-line)', background: 'transparent', fontSize: 12.5, color: 'var(--av-ink)', outline: 'none', cursor: 'pointer', fontFamily: 'var(--av-sans)', letterSpacing: '0.04em' }}>
                  <option value="newest">Newest</option>
                  <option value="featured">Featured</option>
                  <option value="price_asc">Price: Low to High</option>
                  <option value="price_desc">Price: High to Low</option>
                  <option value="rating">Top Rated</option>
                  <option value="discount">Biggest Discount</option>
                </select>
                <div style={{ display: 'flex', border: '1px solid var(--av-line)', overflow: 'hidden' }}>
                  {(['grid', 'list'] as const).map(v => (
                    <button key={v} onClick={() => setView(v)}
                      style={{ width: 32, height: 32, border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', background: view === v ? 'var(--av-ink)' : 'transparent', color: view === v ? 'var(--av-paper)' : 'var(--av-muted)' }}>
                      {v === 'grid'
                        ? <svg viewBox="0 0 16 16" width="13" height="13" fill="currentColor"><rect x="0" y="0" width="7" height="7"/><rect x="9" y="0" width="7" height="7"/><rect x="0" y="9" width="7" height="7"/><rect x="9" y="9" width="7" height="7"/></svg>
                        : <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                      }
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {products.data.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '80px 24px' }}>
                <div style={{ fontFamily: 'var(--av-display)', fontSize: 28, fontWeight: 400, color: 'var(--av-ink)', marginBottom: 12 }}>Nothing found</div>
                <p style={{ color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 14, marginBottom: 24 }}>Try clearing some filters or browsing the full collection.</p>
                <button onClick={clearAll} style={{ padding: '12px 28px', background: 'var(--av-ink)', color: 'var(--av-paper)', border: 'none', cursor: 'pointer', fontFamily: 'var(--av-sans)', fontSize: 11, letterSpacing: '0.2em', textTransform: 'uppercase', fontWeight: 500 }}>
                  Clear filters
                </button>
              </div>
            ) : view === 'grid' ? (
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 28 }} className="av-shop-grid">
                {products.data.map(p => <AvProductCard key={p.id} product={p} />)}
              </div>
            ) : (
              <div>{products.data.map(p => <AvListCard key={p.id} product={p} />)}</div>
            )}

            <Pagination data={products} />
          </section>
        </div>
      </div>

      {/* Mobile filter drawer */}
      {showFilters && mobileOpen && (
        <div style={{ position: 'fixed', inset: 0, zIndex: 60, display: 'flex' }}>
          <div style={{ position: 'fixed', inset: 0, background: 'rgba(31,26,21,.42)' }} onClick={() => setMobileOpen(false)} />
          <div style={{ position: 'relative', background: 'var(--av-paper)', width: 300, height: '100%', overflowY: 'auto', padding: 24, boxSizing: 'border-box' }} className="av-scroll-y">
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 24 }}>
              <span style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, color: 'var(--av-ink)' }}>Filters</span>
              <button onClick={() => setMobileOpen(false)} style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', padding: 4 }}>
                <svg viewBox="0 0 24 24" width="16" height="16" {...S}><path d="M18 6 6 18M6 6l12 12"/></svg>
              </button>
            </div>
            <FilterPanel />
          </div>
        </div>
      )}

      <style>{`
        @media(max-width:860px){
          .av-shop-layout{grid-template-columns:1fr!important}
          .av-shop-sidebar{display:none!important}
          .av-shop-filter-btn{display:flex!important}
          .av-shop-grid{grid-template-columns:repeat(2,1fr)!important;gap:16px!important}
        }
        @media(max-width:480px){.av-shop-grid{grid-template-columns:repeat(2,1fr)!important;gap:12px!important}}
      `}</style>
    </Layout>
  );
}
