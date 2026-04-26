import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useCallback } from 'react';
import Layout from '../../layouts/Layout';
import type { Brand, Paginated, ProductCard, SharedProps, ShopCategory } from '../../types';

interface Filters {
  q?: string;
  min_price?: string;
  max_price?: string;
  sort?: string;
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

function ProductCardItem({ product }: { product: ProductCard }) {
  const discount = product.compare_at_price
    ? Math.round((1 - product.price / product.compare_at_price) * 100)
    : null;

  return (
    <Link href={`/shop/${product.slug}`} className="group block" style={{ textDecoration: 'none' }}>
      <div className="kb-card overflow-hidden hover:shadow-md transition-shadow h-full flex flex-col" style={{ borderRadius: 10 }}>
        <div className="relative overflow-hidden" style={{ aspectRatio: '1/1', background: '#f3f4f6' }}>
          {product.image_url
            ? <img src={product.image_url} alt={product.name}
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                loading="lazy" />
            : <div className="w-full h-full flex items-center justify-center">
                <svg className="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
          }
          {discount !== null && discount > 0 && (
            <span className="absolute top-1.5 left-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white"
              style={{ background: '#ef4444' }}>-{discount}%</span>
          )}
          {product.is_featured && !(discount !== null && discount > 0) && (
            <span className="absolute top-1.5 left-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white"
              style={{ background: 'var(--kb-accent)' }}>Featured</span>
          )}
          {!product.in_stock && (
            <div className="absolute inset-0 flex items-center justify-center" style={{ background: 'rgba(255,255,255,.7)' }}>
              <span className="text-xs font-semibold text-gray-500 bg-white px-2 py-0.5 rounded-full shadow-sm">Out of stock</span>
            </div>
          )}
        </div>
        <div className="p-2 sm:p-3 flex flex-col flex-1">
          <p className="text-xs sm:text-sm font-medium line-clamp-2 leading-snug flex-1" style={{ color: 'var(--kb-ink)' }}>
            {product.name}
          </p>
          <div className="mt-1.5 flex items-center gap-1.5 flex-wrap">
            <span className="text-sm font-bold" style={{ color: 'var(--kb-primary)' }}>৳{product.price.toLocaleString()}</span>
            {product.compare_at_price && (
              <span className="text-xs line-through" style={{ color: 'var(--kb-ink-soft)' }}>৳{product.compare_at_price.toLocaleString()}</span>
            )}
          </div>
        </div>
      </div>
    </Link>
  );
}

function Pagination({ data }: { data: Paginated<ProductCard> }) {
  if (data.last_page <= 1) return null;
  return (
    <div className="flex items-center justify-center gap-1 mt-8 flex-wrap">
      {data.links.map((link, i) => (
        <button key={i} disabled={!link.url}
          onClick={() => link.url && router.get(link.url, {}, { preserveScroll: false })}
          className={`min-w-[38px] h-10 px-3 rounded-lg text-sm font-medium transition-colors ${
            link.active ? 'bg-blue-600 text-white'
            : link.url ? 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
            : 'bg-white border border-gray-100 text-gray-300 cursor-default'
          }`}
          dangerouslySetInnerHTML={{ __html: link.label }}
        />
      ))}
    </div>
  );
}

export default function ShopIndex({
  products, categories, brands, filters,
  activeCategorySlug, activeCategoryName,
  activeBrandSlug, activeBrandName,
}: Props) {
  const { site } = usePage<SharedProps>().props;
  const showFilters   = site.shopShowFilters !== false;
  const defaultSort   = site.shopDefaultSort ?? 'newest';
  const [localFilters, setLocalFilters] = useState<Filters>({ sort: defaultSort, ...filters });
  const [sidebarOpen, setSidebarOpen]   = useState(false);

  const baseUrl = activeCategorySlug
    ? `/shop/c/${activeCategorySlug}`
    : activeBrandSlug ? `/shop/b/${activeBrandSlug}` : '/shop';

  const applyFilters = useCallback((updated: Filters) => {
    const clean: Record<string, string> = {};
    Object.entries(updated).forEach(([k, v]) => { if (v) clean[k] = v; });
    router.get(baseUrl, clean, { preserveScroll: false });
  }, [baseUrl]);

  const update = (key: keyof Filters, value: string) => {
    const next = { ...localFilters, [key]: value };
    setLocalFilters(next);
    if (key === 'sort') applyFilters(next);
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    applyFilters(localFilters);
  };

  const pageTitle = activeCategoryName ?? activeBrandName ?? 'Shop';

  const SidebarContent = () => (
    <div className="space-y-6">
      {/* Categories */}
      <div>
        <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Category</h3>
        <ul className="space-y-0.5">
          <li>
            <Link href="/shop"
              className={`flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-colors ${!activeCategorySlug && !activeBrandSlug ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'}`}>
              All Products
            </Link>
          </li>
          {categories.map(c => (
            <li key={c.id}>
              <Link href={`/shop/c/${c.slug}`}
                className={`flex items-center justify-between px-2.5 py-2 rounded-lg text-sm transition-colors ${activeCategorySlug === c.slug ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'}`}>
                {c.name}
              </Link>
            </li>
          ))}
        </ul>
      </div>

      {/* Brands */}
      {brands.length > 0 && (
        <div>
          <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Brand</h3>
          <ul className="space-y-0.5">
            <li>
              <Link href="/shop"
                className={`flex items-center px-2.5 py-2 rounded-lg text-sm transition-colors ${!activeBrandSlug && !activeCategorySlug ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'}`}>
                All Brands
              </Link>
            </li>
            {brands.map(b => (
              <li key={b.id}>
                <Link href={`/shop/b/${b.slug}`}
                  className={`flex items-center px-2.5 py-2 rounded-lg text-sm transition-colors ${activeBrandSlug === b.slug ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50'}`}>
                  {b.name}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Price Range */}
      <div>
        <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Price Range</h3>
        <form onSubmit={handleSearch} className="space-y-2">
          <div className="flex items-center gap-2">
            <input type="number" placeholder="Min ৳" min="0"
              value={localFilters.min_price ?? ''}
              onChange={e => setLocalFilters(f => ({ ...f, min_price: e.target.value }))}
              className="kb-input w-full text-sm" />
            <span className="text-gray-400 shrink-0 text-xs">—</span>
            <input type="number" placeholder="Max ৳" min="0"
              value={localFilters.max_price ?? ''}
              onChange={e => setLocalFilters(f => ({ ...f, max_price: e.target.value }))}
              className="kb-input w-full text-sm" />
          </div>
          <button type="submit" className="kb-btn kb-btn-primary w-full text-sm py-2">Apply</button>
        </form>
      </div>
    </div>
  );

  return (
    <Layout>
      <Head title={pageTitle} />

      <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-5">

        {/* Breadcrumb */}
        <nav className="flex items-center gap-1.5 text-xs text-gray-400 mb-4">
          <Link href="/" className="hover:text-gray-600">Home</Link>
          <span>/</span>
          {activeCategorySlug || activeBrandSlug
            ? <><Link href="/shop" className="hover:text-gray-600">Shop</Link><span>/</span><span className="text-gray-700 font-medium">{pageTitle}</span></>
            : <span className="text-gray-700 font-medium">Shop</span>
          }
        </nav>

        {(activeCategoryName || activeBrandName) && (
          <h1 className="text-xl font-bold text-gray-900 mb-4">{pageTitle}</h1>
        )}

        {/* ── Mobile: search + filter row ── */}
        <div className="flex gap-2 mb-4">
          <form onSubmit={handleSearch} className="flex-1 flex gap-2">
            <div className="relative flex-1">
              <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input type="text" placeholder="Search…"
                value={localFilters.q ?? ''}
                onChange={e => setLocalFilters(f => ({ ...f, q: e.target.value }))}
                className="kb-input pl-9 w-full text-sm" />
            </div>
            <button type="submit" className="kb-btn kb-btn-primary text-sm px-4 hidden sm:block">Search</button>
          </form>

          {/* Sort — always visible */}
          <select value={localFilters.sort ?? defaultSort}
            onChange={e => update('sort', e.target.value)}
            className="kb-input text-xs sm:text-sm shrink-0 max-w-[110px] sm:max-w-[160px]">
            <option value="newest">Newest</option>
            <option value="featured">Featured</option>
            <option value="price_asc">Price ↑</option>
            <option value="price_desc">Price ↓</option>
          </select>

          {/* Filter drawer toggle — mobile */}
          {showFilters && (
            <button onClick={() => setSidebarOpen(o => !o)}
              className="lg:hidden kb-btn text-sm flex items-center gap-1.5 shrink-0 px-3">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4h18M7 8h10M10 12h4"/>
              </svg>
              <span className="hidden sm:inline">Filter</span>
            </button>
          )}
        </div>

        <div className="flex gap-6">
          {/* Sidebar desktop */}
          {showFilters && (
            <div className="hidden lg:block w-52 shrink-0">
              <SidebarContent />
            </div>
          )}

          {/* Mobile filter drawer */}
          {showFilters && sidebarOpen && (
            <div className="lg:hidden fixed inset-0 z-50 flex">
              <div className="fixed inset-0 bg-black/40" onClick={() => setSidebarOpen(false)} />
              <div className="relative bg-white w-72 h-full overflow-y-auto p-5 shadow-xl ml-auto">
                <div className="flex items-center justify-between mb-5">
                  <h2 className="font-bold text-gray-900">Filters</h2>
                  <button onClick={() => setSidebarOpen(false)} className="text-gray-400 hover:text-gray-600 p-1">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </div>
                <SidebarContent />
              </div>
            </div>
          )}

          {/* Product grid */}
          <div className="flex-1 min-w-0">
            <p className="text-xs text-gray-500 mb-3">
              {products.total} product{products.total !== 1 ? 's' : ''}
              {products.from && products.to ? ` · showing ${products.from}–${products.to}` : ''}
            </p>

            {products.data.length === 0 ? (
              <div className="text-center py-16 text-gray-400">
                <svg className="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p className="font-medium text-sm">No products found</p>
                <p className="text-xs mt-1">Try adjusting your filters</p>
                <Link href="/shop" className="kb-btn kb-btn-ghost text-sm mt-4">Clear filters</Link>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-4">
                {products.data.map(p => <ProductCardItem key={p.id} product={p} />)}
              </div>
            )}

            <Pagination data={products} />
          </div>
        </div>
      </div>
    </Layout>
  );
}
