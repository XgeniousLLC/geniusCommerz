import { Head, Link, router, usePage } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import { useCartStore } from '../../store/cartStore';
import { useWishlistStore } from '../../store/wishlistStore';
import Layout from '../../layouts/Layout';
import { usePrice } from '../../usePrice';
import type { Brand, Paginated, ProductCard, SharedProps, ShopCategory } from '../../types';

interface Filters {
  q?: string;
  min_price?: string;
  max_price?: string;
  sort?: string;
  in_stock?: string;
  on_sale?: string;
  min_rating?: string;
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

function Stars({ rating }: { rating: number }) {
  return (
    <span style={{ color: '#E8A317', fontSize: 12, letterSpacing: 1 }}>
      {[1,2,3,4,5].map(i => {
        if (rating >= i) return '★';
        if (rating >= i - 0.5) return '½';
        return '☆';
      }).join('')}
    </span>
  );
}

function ProductCardItem({ product, view }: { product: ProductCard; view: 'grid' | 'list' }) {
  const fmt = usePrice();
  const addItem = useCartStore(s => s.addItem);
  const { has, toggle } = useWishlistStore();
  const wished = has(product.id);

  const discount = product.compare_at_price
    ? Math.round((1 - product.price / product.compare_at_price) * 100)
    : null;

  function addToCart(e: React.MouseEvent) {
    e.preventDefault();
    if (!product.in_stock || product.has_variants) {
      router.visit(`/shop/${product.slug}`);
      return;
    }
    addItem({ product_id: product.id, variant_id: null, variant_label: null, name: product.name, price: product.price, image_url: product.image_url, slug: product.slug, shipping_included: false });
  }

  function toggleWish(e: React.MouseEvent) {
    e.preventDefault();
    toggle({ id: product.id, slug: product.slug, name: product.name, price: product.price, compare_at_price: product.compare_at_price, image_url: product.image_url, category: product.category_name });
  }

  if (view === 'list') {
    return (
      <Link href={`/shop/${product.slug}`} style={{ textDecoration: 'none' }}>
        <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, overflow: 'hidden', display: 'flex', gap: 0, transition: 'box-shadow .15s' }} className="hover:shadow-md group">
          <div className="w-[110px] sm:w-[200px]" style={{ flexShrink: 0, position: 'relative', overflow: 'hidden', background: 'var(--kb-surface-2)' }}>
            {product.image_url
              ? <img src={product.image_url} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform .3s' }} className="group-hover:scale-105" />
              : <div style={{ width: '100%', height: '100%', minHeight: 110, display: 'grid', placeItems: 'center', color: 'var(--kb-border)' }}>
                  <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/><circle cx="8.5" cy="8.5" r="1.5" strokeWidth={1.5}/><path strokeWidth={1.5} d="M21 15l-5-5L5 21"/></svg>
                </div>
            }
            {discount !== null && discount > 0 && (
              <span style={{ position: 'absolute', top: 8, left: 8, background: '#EF4444', color: '#fff', fontSize: 11, fontWeight: 700, padding: '2px 7px', borderRadius: 999 }}>-{discount}%</span>
            )}
          </div>
          <div className="p-3 sm:p-[16px_20px]" style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 4 }}>
            {product.category_name && <div style={{ fontSize: 11, color: 'var(--kb-ink-soft)', textTransform: 'uppercase', letterSpacing: '0.04em', fontWeight: 600 }}>{product.category_name}</div>}
            <div className="text-[14px] sm:text-[16px]" style={{ fontWeight: 700, color: 'var(--kb-ink)', lineHeight: 1.3 }}>{product.name}</div>
            {product.avg_rating !== null && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                <Stars rating={product.avg_rating} />
                <span style={{ fontSize: 12, color: 'var(--kb-ink-soft)' }}>({product.reviews_count})</span>
              </div>
            )}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 4, flexWrap: 'wrap' }}>
              <span className="text-[15px] sm:text-[18px]" style={{ fontWeight: 800, color: 'var(--kb-ink)' }}>{fmt(product.price)}</span>
              {product.compare_at_price && <span style={{ fontSize: 12, color: 'var(--kb-ink-soft)', textDecoration: 'line-through' }}>{fmt(product.compare_at_price)}</span>}
            </div>
            {!product.in_stock && <span style={{ fontSize: 12, color: '#DC2626', fontWeight: 600 }}>Out of stock</span>}
          </div>
        </div>
      </Link>
    );
  }

  return (
    <div style={{ position: 'relative' }}>
      <Link href={`/shop/${product.slug}`} style={{ textDecoration: 'none' }}>
        <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, overflow: 'hidden', height: '100%', display: 'flex', flexDirection: 'column', transition: 'box-shadow .15s, transform .15s' }} className="hover:shadow-md hover:-translate-y-0.5 group">
          <div style={{ aspectRatio: '1', background: 'var(--kb-surface-2)', position: 'relative', overflow: 'hidden' }}>
            {product.image_url
              ? <img src={product.image_url} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform .3s' }} className="group-hover:scale-105" loading="lazy" />
              : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', color: 'var(--kb-border)' }}>
                  <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/><circle cx="8.5" cy="8.5" r="1.5" strokeWidth={1.5}/><path strokeWidth={1.5} d="M21 15l-5-5L5 21"/></svg>
                </div>
            }
            {discount !== null && discount > 0 && (
              <span style={{ position: 'absolute', top: 10, left: 10, background: '#EF4444', color: '#fff', fontSize: 11, fontWeight: 700, padding: '2px 7px', borderRadius: 999 }}>-{discount}%</span>
            )}
            {product.is_featured && !(discount !== null && discount > 0) && (
              <span style={{ position: 'absolute', top: 10, left: 10, background: 'var(--kb-primary)', color: '#fff', fontSize: 11, fontWeight: 700, padding: '2px 7px', borderRadius: 999 }}>Featured</span>
            )}
            {!product.in_stock && (
              <div style={{ position: 'absolute', inset: 0, display: 'grid', placeItems: 'center', background: 'rgba(255,255,255,.7)' }}>
                <span style={{ fontSize: 12, fontWeight: 600, color: '#6B7280', background: '#fff', padding: '3px 10px', borderRadius: 999, boxShadow: '0 1px 3px rgba(0,0,0,.1)' }}>Out of stock</span>
              </div>
            )}
            {/* Wishlist */}
            <button onClick={toggleWish}
              style={{ position: 'absolute', top: 10, right: 10, width: 32, height: 32, borderRadius: '50%', background: '#fff', border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', color: wished ? '#EF4444' : 'var(--kb-ink-soft)', boxShadow: '0 1px 4px rgba(14,19,32,.12)', transition: 'color .15s' }}>
              <svg width="16" height="16" fill={wished ? 'currentColor' : 'none'} stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </button>
            {/* Add to cart hover */}
            {product.in_stock && (
              <button onClick={addToCart}
                style={{ position: 'absolute', bottom: 0, left: 0, right: 0, height: 36, background: 'var(--kb-primary)', color: '#fff', fontWeight: 600, fontSize: 13, border: 'none', cursor: 'pointer', opacity: 0, transition: 'opacity .15s' }}
                className="group-hover:opacity-100">
                {product.has_variants ? 'Select options' : 'Add to cart'}
              </button>
            )}
          </div>
          <div style={{ padding: '12px 14px 14px', flex: 1, display: 'flex', flexDirection: 'column', gap: 4 }}>
            {product.category_name && <div style={{ fontSize: 11, color: 'var(--kb-ink-soft)', textTransform: 'uppercase', letterSpacing: '0.04em', fontWeight: 600 }}>{product.category_name}</div>}
            <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--kb-ink)', lineHeight: 1.35, flex: 1, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>{product.name}</div>
            {product.avg_rating !== null && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                <Stars rating={product.avg_rating} />
                <span style={{ fontSize: 11, color: 'var(--kb-ink-soft)' }}>({product.reviews_count})</span>
              </div>
            )}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 4 }}>
              <span style={{ fontWeight: 800, fontSize: 16, color: 'var(--kb-ink)' }}>{fmt(product.price)}</span>
              {product.compare_at_price && <span style={{ fontSize: 12, color: 'var(--kb-ink-soft)', textDecoration: 'line-through' }}>{fmt(product.compare_at_price)}</span>}
            </div>
          </div>
        </div>
      </Link>
    </div>
  );
}

function Pagination({ data }: { data: Paginated<ProductCard> }) {
  if (data.last_page <= 1) return null;

  function go(url: string | null) {
    if (!url) return;
    router.get(url, {}, { preserveScroll: false });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  const btnBase: React.CSSProperties = {
    minWidth: 36, height: 36, border: '1px solid var(--kb-border)', background: '#fff',
    borderRadius: 8, color: 'var(--kb-ink-muted)', fontWeight: 500, fontSize: 13,
    padding: '0 12px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
    cursor: 'pointer',
  };

  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 4, padding: '32px 0' }}>
      <button style={{ ...btnBase, opacity: !data.prev_page_url ? .4 : 1, cursor: !data.prev_page_url ? 'not-allowed' : 'pointer' }}
        disabled={!data.prev_page_url} onClick={() => go(data.prev_page_url)}>
        ← Previous
      </button>
      {data.links.filter(l => !l.label.includes('Previous') && !l.label.includes('Next')).map((link, i) => (
        <button key={i}
          style={{ ...btnBase, background: link.active ? 'var(--kb-primary)' : '#fff', color: link.active ? '#fff' : 'var(--kb-ink-muted)', borderColor: link.active ? 'var(--kb-primary)' : 'var(--kb-border)', cursor: link.url ? 'pointer' : 'default' }}
          disabled={!link.url} onClick={() => go(link.url)}
          dangerouslySetInnerHTML={{ __html: link.label }} />
      ))}
      <button style={{ ...btnBase, opacity: !data.next_page_url ? .4 : 1, cursor: !data.next_page_url ? 'not-allowed' : 'pointer' }}
        disabled={!data.next_page_url} onClick={() => go(data.next_page_url)}>
        Next →
      </button>
    </div>
  );
}

export default function ShopIndex({
  products, categories, brands, filters,
  activeCategorySlug, activeCategoryName,
  activeBrandSlug, activeBrandName,
}: Props) {
  const { site } = usePage<SharedProps>().props;
  const showFilters = site.shopShowFilters !== false;
  const defaultSort = site.shopDefaultSort ?? 'newest';

  const [localFilters, setLocalFilters] = useState<Filters>({ sort: defaultSort, ...filters });
  const [priceMin, setPriceMin] = useState(filters.min_price ?? '');
  const [priceMax, setPriceMax] = useState(filters.max_price ?? '');
  const [view, setView] = useState<'grid' | 'list'>('grid');
  const [mobileOpen, setMobileOpen] = useState(false);

  const baseUrl = activeCategorySlug ? `/shop/c/${activeCategorySlug}`
    : activeBrandSlug ? `/shop/b/${activeBrandSlug}` : '/shop';

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
    setLocalFilters(next);
    applyFilters(next);
  }

  function clearAll() {
    const reset: Filters = { sort: localFilters.sort };
    setLocalFilters(reset);
    setPriceMin('');
    setPriceMax('');
    router.get(baseUrl, reset.sort ? { sort: reset.sort } : {}, { preserveScroll: false });
  }

  const hasActiveFilters = !!(filters.q || filters.min_price || filters.max_price || filters.in_stock || filters.on_sale || filters.min_rating);
  const pageTitle = activeCategoryName ?? activeBrandName ?? 'Shop';

  const activeChips: { label: string; key: keyof Filters }[] = [];
  if (filters.q) activeChips.push({ label: `"${filters.q}"`, key: 'q' });
  if (filters.min_price || filters.max_price) activeChips.push({ label: `৳${filters.min_price ?? 0} – ৳${filters.max_price ?? '∞'}`, key: 'min_price' });
  if (filters.min_rating) activeChips.push({ label: `${filters.min_rating}★ & up`, key: 'min_rating' });
  if (filters.in_stock) activeChips.push({ label: 'In stock', key: 'in_stock' });
  if (filters.on_sale) activeChips.push({ label: 'On sale', key: 'on_sale' });

  function removeChip(key: keyof Filters) {
    const next = { ...localFilters };
    delete next[key];
    if (key === 'min_price') { delete next.max_price; setPriceMin(''); setPriceMax(''); }
    setLocalFilters(next);
    applyFilters(next);
  }

  const FilterSections = () => (
    <>
      {/* Category */}
      <div style={{ padding: 16, borderBottom: '1px solid var(--kb-border-2)' }}>
        <h4 style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--kb-ink)', margin: '0 0 12px' }}>Category</h4>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
          <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--kb-ink-muted)', cursor: 'pointer', padding: '4px 6px', margin: '-4px -6px', borderRadius: 6 }} className="hover:bg-[var(--kb-surface-2)]">
            <input type="radio" name="cat" checked={!activeCategorySlug} onChange={() => router.get('/shop', localFilters.sort ? { sort: localFilters.sort } : {})}
              style={{ accentColor: 'var(--kb-primary)', width: 14, height: 14, margin: 0 }} />
            All Products
          </label>
          {categories.map(c => (
            <label key={c.id} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--kb-ink-muted)', cursor: 'pointer', padding: '4px 6px', margin: '-4px -6px', borderRadius: 6 }} className="hover:bg-[var(--kb-surface-2)]">
              <input type="radio" name="cat" checked={activeCategorySlug === c.slug} onChange={() => router.get(`/shop/c/${c.slug}`, localFilters.sort ? { sort: localFilters.sort } : {})}
                style={{ accentColor: 'var(--kb-primary)', width: 14, height: 14, margin: 0 }} />
              {c.name}
              {c.products_count !== undefined && <span style={{ color: 'var(--kb-ink-soft)', fontSize: 12, marginLeft: 'auto' }}>{c.products_count}</span>}
            </label>
          ))}
        </div>
      </div>

      {/* Brand */}
      {brands.length > 0 && (
        <div style={{ padding: 16, borderBottom: '1px solid var(--kb-border-2)' }}>
          <h4 style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--kb-ink)', margin: '0 0 12px' }}>Brand</h4>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
            {brands.map(b => (
              <label key={b.id} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--kb-ink-muted)', cursor: 'pointer', padding: '4px 6px', margin: '-4px -6px', borderRadius: 6 }} className="hover:bg-[var(--kb-surface-2)]">
                <input type="radio" name="brand" checked={activeBrandSlug === b.slug} onChange={() => router.get(`/shop/b/${b.slug}`, localFilters.sort ? { sort: localFilters.sort } : {})}
                  style={{ accentColor: 'var(--kb-primary)', width: 14, height: 14, margin: 0 }} />
                {b.name}
                {b.products_count !== undefined && <span style={{ color: 'var(--kb-ink-soft)', fontSize: 12, marginLeft: 'auto' }}>{b.products_count}</span>}
              </label>
            ))}
          </div>
        </div>
      )}

      {/* Price */}
      <div style={{ padding: 16, borderBottom: '1px solid var(--kb-border-2)' }}>
        <h4 style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--kb-ink)', margin: '0 0 12px' }}>Price (৳)</h4>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr auto 1fr', gap: 6, alignItems: 'center' }}>
          <input type="number" placeholder="Min" value={priceMin} onChange={e => setPriceMin(e.target.value)}
            style={{ width: '100%', height: 36, padding: '0 10px', border: '1px solid var(--kb-border)', borderRadius: 7, fontSize: 13, outline: 'none', boxSizing: 'border-box' }} />
          <span style={{ color: 'var(--kb-ink-soft)', fontSize: 12 }}>–</span>
          <input type="number" placeholder="Max" value={priceMax} onChange={e => setPriceMax(e.target.value)}
            style={{ width: '100%', height: 36, padding: '0 10px', border: '1px solid var(--kb-border)', borderRadius: 7, fontSize: 13, outline: 'none', boxSizing: 'border-box' }} />
        </div>
        <button onClick={applyPrice}
          style={{ width: '100%', height: 36, marginTop: 10, borderRadius: 7, background: 'var(--kb-primary)', color: '#fff', fontWeight: 600, fontSize: 13, border: 'none', cursor: 'pointer' }}>
          Apply
        </button>
      </div>

      {/* Rating */}
      <div style={{ padding: 16, borderBottom: '1px solid var(--kb-border-2)' }}>
        <h4 style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--kb-ink)', margin: '0 0 12px' }}>Customer Rating</h4>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
          {[4, 3, 2, 0].map(r => (
            <label key={r} style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--kb-ink-muted)', cursor: 'pointer', padding: '4px 6px', margin: '-4px -6px', borderRadius: 6 }} className="hover:bg-[var(--kb-surface-2)]">
              <input type="radio" name="rating" checked={(filters.min_rating ? Number(filters.min_rating) : 0) === r}
                onChange={() => update('min_rating', r > 0 ? String(r) : '')}
                style={{ accentColor: 'var(--kb-primary)', width: 14, height: 14, margin: 0 }} />
              {r === 0 ? 'Any rating' : <><span style={{ color: '#E8A317' }}>{'★'.repeat(r)}{'☆'.repeat(5 - r)}</span> &amp; up</>}
            </label>
          ))}
        </div>
      </div>

      {/* Availability */}
      <div style={{ padding: 16, borderBottom: '1px solid var(--kb-border-2)' }}>
        <h4 style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--kb-ink)', margin: '0 0 12px' }}>Availability</h4>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
          <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--kb-ink-muted)', cursor: 'pointer' }}>
            <input type="checkbox" checked={!!filters.in_stock} onChange={e => update('in_stock', e.target.checked ? '1' : '')}
              style={{ accentColor: 'var(--kb-primary)', width: 14, height: 14, margin: 0 }} />
            In stock only
          </label>
          <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--kb-ink-muted)', cursor: 'pointer' }}>
            <input type="checkbox" checked={!!filters.on_sale} onChange={e => update('on_sale', e.target.checked ? '1' : '')}
              style={{ accentColor: 'var(--kb-primary)', width: 14, height: 14, margin: 0 }} />
            On sale
          </label>
        </div>
      </div>

      {/* Clear all */}
      <div style={{ padding: 16 }}>
        <button onClick={clearAll}
          style={{ width: '100%', height: 36, borderRadius: 7, background: 'transparent', border: '1px solid var(--kb-border)', color: 'var(--kb-ink-muted)', fontWeight: 600, fontSize: 13, cursor: 'pointer' }}>
          Clear all filters
        </button>
      </div>
    </>
  );

  return (
    <Layout>
      <Head title={pageTitle} />

      <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--kb-ink-soft)', padding: '16px 0 8px' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-800">Home</Link>
          <span>/</span>
          {activeCategorySlug || activeBrandSlug ? (
            <><Link href="/shop" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-800">Shop</Link><span>/</span><span style={{ color: 'var(--kb-ink)' }}>{pageTitle}</span></>
          ) : (
            <span style={{ color: 'var(--kb-ink)' }}>Shop</span>
          )}
        </nav>

        <div className="grid lg:grid-cols-[248px_1fr]" style={{ gap: 24, paddingBottom: 64, alignItems: 'start' }}>

          {/* Sidebar desktop */}
          {showFilters && (
            <aside className="hidden lg:block" style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, overflow: 'hidden', position: 'sticky', top: 80 }}>
              <FilterSections />
            </aside>
          )}

          {/* Mobile filter drawer */}
          {showFilters && mobileOpen && (
            <div className="lg:hidden" style={{ position: 'fixed', inset: 0, zIndex: 60, display: 'flex' }}>
              <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,.4)' }} onClick={() => setMobileOpen(false)} />
              <div style={{ position: 'relative', background: '#fff', width: 300, height: '100%', overflowY: 'auto', boxShadow: '4px 0 24px rgba(0,0,0,.15)' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 20px', borderBottom: '1px solid var(--kb-border)' }}>
                  <h2 style={{ fontWeight: 700, fontSize: 16, margin: 0, color: 'var(--kb-ink)' }}>Filters</h2>
                  <button onClick={() => setMobileOpen(false)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--kb-ink-soft)', padding: 4 }}>
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>
                <FilterSections />
              </div>
            </div>
          )}

          {/* Results */}
          <section>
            {/* Toolbar */}
            <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '12px 16px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12, marginBottom: 16, flexWrap: 'wrap' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                {showFilters && (
                  <button onClick={() => setMobileOpen(true)} className="lg:hidden"
                    style={{ display: 'flex', alignItems: 'center', gap: 6, height: 34, padding: '0 12px', border: '1px solid var(--kb-border)', borderRadius: 7, background: '#fff', fontSize: 13, fontWeight: 600, color: 'var(--kb-ink-muted)', cursor: 'pointer' }}>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4h18M7 8h10M10 12h4"/></svg>
                    Filters
                  </button>
                )}
                <span style={{ fontSize: 13, color: 'var(--kb-ink-soft)' }}>
                  <strong style={{ color: 'var(--kb-ink)' }}>{products.total}</strong> products
                  {products.from && products.to && ` · showing ${products.from}–${products.to}`}
                </span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                <select value={localFilters.sort ?? defaultSort} onChange={e => update('sort', e.target.value)}
                  className="flex-1 sm:flex-none sm:w-[200px]"
                  style={{ height: 36, padding: '0 10px', border: '1px solid var(--kb-border)', borderRadius: 7, background: '#fff', fontSize: 13, color: 'var(--kb-ink)', outline: 'none', cursor: 'pointer' }}>
                  <option value="newest">Newest</option>
                  <option value="featured">Featured</option>
                  <option value="price_asc">Price: Low to High</option>
                  <option value="price_desc">Price: High to Low</option>
                  <option value="rating">Top Rated</option>
                  <option value="discount">Biggest Discount</option>
                </select>
                {/* Grid/List toggle */}
                <div style={{ display: 'flex', border: '1px solid var(--kb-border)', borderRadius: 7, overflow: 'hidden' }}>
                  <button onClick={() => setView('grid')}
                    style={{ width: 34, height: 34, border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', background: view === 'grid' ? 'var(--kb-primary)' : '#fff', color: view === 'grid' ? '#fff' : 'var(--kb-ink-soft)' }}>
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><rect x="0" y="0" width="7" height="7" rx="1"/><rect x="9" y="0" width="7" height="7" rx="1"/><rect x="0" y="9" width="7" height="7" rx="1"/><rect x="9" y="9" width="7" height="7" rx="1"/></svg>
                  </button>
                  <button onClick={() => setView('list')}
                    style={{ width: 34, height: 34, border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', background: view === 'list' ? 'var(--kb-primary)' : '#fff', color: view === 'list' ? '#fff' : 'var(--kb-ink-soft)' }}>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16"/></svg>
                  </button>
                </div>
              </div>
            </div>

            {/* Active filter chips */}
            {activeChips.length > 0 && (
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginBottom: 16 }}>
                {activeChips.map(chip => (
                  <span key={chip.key} style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: '#fff', border: '1px solid var(--kb-border)', padding: '4px 8px 4px 10px', borderRadius: 999, fontSize: 12, color: 'var(--kb-ink-muted)', fontWeight: 500 }}>
                    {chip.label}
                    <button onClick={() => removeChip(chip.key)} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0, lineHeight: 0, color: 'var(--kb-ink-soft)' }} className="hover:text-red-500">
                      <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                  </span>
                ))}
              </div>
            )}

            {/* Products */}
            {products.data.length === 0 ? (
              <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '60px 24px', textAlign: 'center' }}>
                <h3 style={{ fontSize: 18, fontWeight: 700, margin: '0 0 6px', color: 'var(--kb-ink)' }}>No products match your filters</h3>
                <p style={{ color: 'var(--kb-ink-soft)', margin: '0 0 20px', fontSize: 14 }}>Try clearing some filters or searching for something else.</p>
                <button onClick={clearAll}
                  style={{ height: 40, padding: '0 20px', borderRadius: 8, background: 'var(--kb-primary)', color: '#fff', fontWeight: 600, fontSize: 14, border: 'none', cursor: 'pointer' }}>
                  Clear filters
                </button>
              </div>
            ) : view === 'grid' ? (
              <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4" style={{ gap: 16 }}>
                {products.data.map(p => <ProductCardItem key={p.id} product={p} view="grid" />)}
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                {products.data.map(p => <ProductCardItem key={p.id} product={p} view="list" />)}
              </div>
            )}

            <Pagination data={products} />
          </section>
        </div>
      </div>
    </Layout>
  );
}
