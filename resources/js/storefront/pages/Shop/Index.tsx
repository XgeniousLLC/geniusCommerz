import { Head, Link, router } from '@inertiajs/react';
import { useState, useCallback } from 'react';
import Layout from '../../layouts/Layout';
import type { Brand, Paginated, ProductCard, ShopCategory } from '../../types';

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
    <Link href={`/shop/${product.slug}`} className="group block">
      <div className="kb-card overflow-hidden hover:shadow-md transition-shadow">
        <div className="relative aspect-square bg-gray-100 overflow-hidden">
          {product.image_url
            ? <img src={product.image_url} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            : <div className="w-full h-full flex items-center justify-center">
                <svg className="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
          }
          {discount && (
            <span className="absolute top-2 left-2 kb-chip text-xs font-bold" style={{ background: '#ef4444', color: '#fff' }}>
              -{discount}%
            </span>
          )}
          {!product.in_stock && (
            <div className="absolute inset-0 bg-white/60 flex items-center justify-center">
              <span className="text-sm font-semibold text-gray-500">Out of stock</span>
            </div>
          )}
        </div>
        <div className="p-3">
          <p className="text-sm font-medium text-gray-800 line-clamp-2 leading-snug">{product.name}</p>
          <div className="mt-2 flex items-center gap-2">
            <span className="font-bold text-gray-900">৳{product.price.toLocaleString()}</span>
            {product.compare_at_price && (
              <span className="text-xs text-gray-400 line-through">৳{product.compare_at_price.toLocaleString()}</span>
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
    <div className="flex items-center justify-center gap-1 mt-10">
      {data.links.map((link, i) => (
        <button
          key={i}
          disabled={!link.url}
          onClick={() => link.url && router.get(link.url, {}, { preserveScroll: false })}
          className={`min-w-[36px] h-9 px-3 rounded-lg text-sm font-medium transition-colors ${
            link.active
              ? 'bg-blue-600 text-white'
              : link.url
              ? 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
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
  const [localFilters, setLocalFilters] = useState<Filters>(filters);
  const [sidebarOpen, setSidebarOpen]   = useState(false);

  const baseUrl = activeCategorySlug
    ? `/shop/c/${activeCategorySlug}`
    : activeBrandSlug
    ? `/shop/b/${activeBrandSlug}`
    : '/shop';

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

  const Sidebar = () => (
    <aside className="space-y-6">
      {/* Categories */}
      <div>
        <h3 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Category</h3>
        <ul className="space-y-1.5">
          <li>
            <Link
              href="/shop"
              className={`text-sm block px-2 py-1 rounded transition-colors ${!activeCategorySlug && !activeBrandSlug ? 'font-semibold text-blue-600' : 'text-gray-600 hover:text-gray-900'}`}
            >
              All Products
            </Link>
          </li>
          {categories.map(c => (
            <li key={c.id}>
              <Link
                href={`/shop/c/${c.slug}`}
                className={`text-sm block px-2 py-1 rounded transition-colors ${activeCategorySlug === c.slug ? 'font-semibold text-blue-600' : 'text-gray-600 hover:text-gray-900'}`}
              >
                {c.name}
              </Link>
            </li>
          ))}
        </ul>
      </div>

      {/* Brands */}
      {brands.length > 0 && (
        <div>
          <h3 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Brand</h3>
          <ul className="space-y-1.5">
            <li>
              <Link href="/shop" className={`text-sm block px-2 py-1 rounded ${!activeBrandSlug && !activeCategorySlug ? 'font-semibold text-blue-600' : 'text-gray-600 hover:text-gray-900'}`}>
                All Brands
              </Link>
            </li>
            {brands.map(b => (
              <li key={b.id}>
                <Link
                  href={`/shop/b/${b.slug}`}
                  className={`text-sm block px-2 py-1 rounded ${activeBrandSlug === b.slug ? 'font-semibold text-blue-600' : 'text-gray-600 hover:text-gray-900'}`}
                >
                  {b.name}
                </Link>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Price Range */}
      <div>
        <h3 className="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Price Range</h3>
        <form onSubmit={handleSearch} className="flex items-center gap-2">
          <input
            type="number" placeholder="Min" min="0"
            value={localFilters.min_price ?? ''}
            onChange={e => setLocalFilters(f => ({ ...f, min_price: e.target.value }))}
            className="kb-input w-full text-sm"
          />
          <span className="text-gray-400 shrink-0">—</span>
          <input
            type="number" placeholder="Max" min="0"
            value={localFilters.max_price ?? ''}
            onChange={e => setLocalFilters(f => ({ ...f, max_price: e.target.value }))}
            className="kb-input w-full text-sm"
          />
          <button type="submit" className="kb-btn kb-btn-primary shrink-0 px-3 py-2 text-sm">Go</button>
        </form>
      </div>
    </aside>
  );

  return (
    <Layout>
      <Head title={pageTitle} />

      <div className="max-w-7xl mx-auto px-4 lg:px-6 py-8">

        {/* Breadcrumb */}
        <nav className="flex items-center gap-2 text-sm text-gray-400 mb-5">
          <Link href="/" className="hover:text-gray-600">Home</Link>
          <span>/</span>
          {activeCategorySlug || activeBrandSlug
            ? <><Link href="/shop" className="hover:text-gray-600">Shop</Link><span>/</span><span className="text-gray-700 font-medium">{pageTitle}</span></>
            : <span className="text-gray-700 font-medium">Shop</span>
          }
        </nav>

        {/* Page heading */}
        {(activeCategoryName || activeBrandName) && (
          <h1 className="text-2xl font-bold text-gray-900 mb-5">{pageTitle}</h1>
        )}

        {/* Top bar */}
        <div className="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
          <div className="flex-1">
            <form onSubmit={handleSearch} className="flex gap-2">
              <div className="relative flex-1 max-w-sm">
                <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                  type="text" placeholder="Search products…"
                  value={localFilters.q ?? ''}
                  onChange={e => setLocalFilters(f => ({ ...f, q: e.target.value }))}
                  className="kb-input pl-9 w-full"
                />
              </div>
              <button type="submit" className="kb-btn kb-btn-primary">Search</button>
            </form>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => setSidebarOpen(o => !o)}
              className="lg:hidden kb-btn text-sm flex items-center gap-2"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4h18M7 8h10M10 12h4"/></svg>
              Filters
            </button>
            <select
              value={localFilters.sort ?? 'newest'}
              onChange={e => update('sort', e.target.value)}
              className="kb-input text-sm"
            >
              <option value="newest">Newest</option>
              <option value="featured">Featured</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
            </select>
          </div>
        </div>

        <div className="flex gap-8">
          {/* Sidebar desktop */}
          <div className="hidden lg:block w-52 shrink-0">
            <Sidebar />
          </div>

          {/* Mobile sidebar drawer */}
          {sidebarOpen && (
            <div className="lg:hidden fixed inset-0 z-40 flex">
              <div className="fixed inset-0 bg-black/40" onClick={() => setSidebarOpen(false)} />
              <div className="relative bg-white w-72 h-full overflow-y-auto p-5 shadow-xl">
                <button onClick={() => setSidebarOpen(false)} className="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h2 className="font-semibold text-gray-900 mb-4">Filters</h2>
                <Sidebar />
              </div>
            </div>
          )}

          {/* Product grid */}
          <div className="flex-1 min-w-0">
            <p className="text-sm text-gray-500 mb-4">
              {products.total} product{products.total !== 1 ? 's' : ''}
              {products.from && products.to ? ` — showing ${products.from}–${products.to}` : ''}
            </p>

            {products.data.length === 0 ? (
              <div className="text-center py-20 text-gray-400">
                <svg className="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p className="font-medium">No products found</p>
                <p className="text-sm mt-1">Try adjusting your filters</p>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
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
