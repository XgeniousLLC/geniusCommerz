import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCartStore } from '../../store/cartStore';
import { useWishlistStore } from '../../store/wishlistStore';
import Layout from '../../layouts/Layout';
import { usePrice } from '../../usePrice';
import type { ProductCard, ProductFull, ProductVariant, SharedProps } from '../../types';

type TabKey = 'desc' | 'specs' | 'reviews' | 'shipping';

interface Props {
  product: ProductFull;
  related: ProductCard[];
}

function Stars({ rating, size = 14 }: { rating: number; size?: number }) {
  const full = Math.round(rating);
  return (
    <span style={{ color: '#E8A317', fontSize: size }}>
      {'★'.repeat(full)}{'☆'.repeat(5 - full)}
    </span>
  );
}

function RelatedCard({ product }: { product: ProductCard }) {
  const fmt = usePrice();
  const discount = product.compare_at_price
    ? Math.round((1 - product.price / product.compare_at_price) * 100)
    : null;
  return (
    <Link href={`/shop/${product.slug}`} className="group block" style={{ textDecoration: 'none' }}>
      <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 10, overflow: 'hidden', transition: 'box-shadow .15s' }} className="hover:shadow-md">
        <div className="relative overflow-hidden" style={{ aspectRatio: '1/1', background: 'var(--kb-surface-2)' }}>
          {product.image_url
            ? <img src={product.image_url} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
            : <div className="w-full h-full flex items-center justify-center">
                <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{ color: 'var(--kb-border)' }}>
                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/>
                  <circle cx="8.5" cy="8.5" r="1.5" strokeWidth={1.5}/>
                  <path strokeWidth={1.5} d="M21 15l-5-5L5 21"/>
                </svg>
              </div>
          }
          {discount !== null && discount > 0 && (
            <span className="absolute top-1.5 left-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white" style={{ background: '#ef4444' }}>-{discount}%</span>
          )}
        </div>
        <div style={{ padding: '10px 12px 12px' }}>
          <p className="text-xs font-medium line-clamp-2 leading-snug" style={{ color: 'var(--kb-ink)' }}>{product.name}</p>
          <div className="mt-1.5 flex items-center gap-1.5">
            <span className="text-sm font-bold" style={{ color: 'var(--kb-primary)' }}>{fmt(product.price)}</span>
            {product.compare_at_price && <span className="text-xs line-through" style={{ color: 'var(--kb-ink-soft)' }}>{fmt(product.compare_at_price)}</span>}
          </div>
        </div>
      </div>
    </Link>
  );
}

export default function ShopShow({ product, related }: Props) {
  const { site } = usePage<SharedProps>().props;
  const addItem = useCartStore(s => s.addItem);
  const { toggle: toggleWishlist, has: inWishlist } = useWishlistStore();
  const fmt = usePrice();
  const wishlisted = inWishlist(product.id);
  const [activeImage, setActiveImage] = useState(0);
  const [selectedVariant, setSelectedVariant] = useState<ProductVariant | null>(
    product.has_variants && product.variants.length > 0
      ? (product.variants.find(v => v.in_stock) ?? product.variants[0])
      : null
  );
  const [qty, setQty] = useState(1);
  const [added, setAdded] = useState(false);
  const [activeTab, setActiveTab] = useState<TabKey>('desc');
  const [visitorCount] = useState(() => {
    const { visitorCounterEnabled, visitorCounterMin, visitorCounterMax } = site;
    return visitorCounterEnabled
      ? Math.floor(Math.random() * (visitorCounterMax - visitorCounterMin + 1)) + visitorCounterMin
      : 0;
  });

  const displayPrice = selectedVariant ? selectedVariant.price : product.price;
  const comparePrice = selectedVariant ? selectedVariant.compare_at_price : product.compare_at_price;
  const discount = comparePrice ? Math.round((1 - displayPrice / comparePrice) * 100) : null;
  const isInStock  = selectedVariant ? selectedVariant.in_stock : product.in_stock;
  const stockQty   = selectedVariant ? selectedVariant.stock_qty : product.stock_qty;
  const isLowStock = stockQty !== null && stockQty > 0 && stockQty <= 5;
  const isPreorder = !isInStock && product.preorder_enabled;
  const canAdd     = isInStock || isPreorder;

  const wishlistItem = {
    id: product.id, slug: product.slug, name: product.name,
    price: displayPrice, compare_at_price: comparePrice,
    image_url: product.images[0]?.url ?? null,
    category: product.categories[0]?.name ?? null,
  };

  const cartItem = {
    product_id: product.id, variant_id: selectedVariant?.id ?? null,
    name: product.name, variant_label: selectedVariant?.label ?? null,
    price: displayPrice, image_url: product.images[0]?.thumb ?? null,
    slug: product.slug, shipping_included: product.shipping_included,
  };

  const handleAddToCart = () => {
    addItem(cartItem, qty);
    setAdded(true);
    setTimeout(() => setAdded(false), 2000);
  };

  const handleBuyNow = () => {
    addItem(cartItem, qty);
    window.location.href = '/checkout';
  };

  const whatsappUrl = site.productWhatsappNumber
    ? `https://wa.me/${site.productWhatsappNumber}?text=${encodeURIComponent(
        (site.productWhatsappMessage || 'Hi, I want to order: {product}').replace('{product}', product.name)
      )}`
    : null;

  const groupedOptions = product.variants.length > 0
    ? product.variants[0].options.map(o => o.attribute)
    : [];

  const getValuesForAttribute = (attr: string) =>
    [...new Set(product.variants.map(v => v.options.find(o => o.attribute === attr)?.value).filter(Boolean))] as string[];

  const selectedOptions: Record<string, string> = selectedVariant
    ? Object.fromEntries(selectedVariant.options.map(o => [o.attribute, o.value]))
    : {};

  const handleOptionSelect = (attr: string, val: string) => {
    const newOpts = { ...selectedOptions, [attr]: val };
    const exact = product.variants.find(v => v.options.every(o => newOpts[o.attribute] === o.value));
    if (exact) { setSelectedVariant(exact); return; }
    const partial = product.variants.find(v => v.in_stock && v.options.some(o => o.attribute === attr && o.value === val))
      ?? product.variants.find(v => v.options.some(o => o.attribute === attr && o.value === val));
    if (partial) setSelectedVariant(partial);
  };

  const pageTitle = product.meta?.meta_title ?? product.name;
  const pageDesc  = product.meta?.meta_description ?? product.short_description ?? '';

  const tabs: { key: TabKey; label: string }[] = [
    { key: 'desc', label: 'Description' },
    { key: 'specs', label: 'Specifications' },
    { key: 'reviews', label: `Reviews${product.reviews_count > 0 ? ` (${product.reviews_count})` : ''}` },
    { key: 'shipping', label: 'Shipping & Returns' },
  ];

  const WishlistHeart = () => (
    <svg width="16" height="16" fill={wishlisted ? 'currentColor' : 'none'} stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
    </svg>
  );

  return (
    <Layout>
      <Head>
        <title>{pageTitle}</title>
        {pageDesc && <meta name="description" content={pageDesc} />}
      </Head>

      <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pb-[180px] md:pb-[120px] lg:pb-10">

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--kb-ink-soft)', padding: '16px 0 0', flexWrap: 'wrap' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-700">Home</Link>
          <span>/</span>
          <Link href="/shop" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-700">Shop</Link>
          {product.categories[0] && (
            <>
              <span>/</span>
              <Link href={`/shop/c/${product.categories[0].slug}`} style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-700">
                {product.categories[0].name}
              </Link>
            </>
          )}
          <span>/</span>
          <span style={{ color: 'var(--kb-ink)', fontWeight: 500 }} className="truncate max-w-[180px]">{product.name}</span>
        </nav>

        {/* PDP Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-8 py-5" style={{ alignItems: 'start' }}>

          {/* ── Gallery: Desktop (sticky thumbs + main) ── */}
          <div className="hidden lg:block lg:sticky lg:top-[116px]">
            <div style={{ display: 'grid', gridTemplateColumns: product.images.length > 1 ? '72px 1fr' : '1fr', gap: 12 }}>
              {/* Thumbs column */}
              {product.images.length > 1 && (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {product.images.map((img, i) => (
                    <button key={img.id} onClick={() => setActiveImage(i)}
                      style={{
                        width: 72, height: 72, borderRadius: 8, padding: 0,
                        border: `${i === activeImage ? 2 : 1}px solid ${i === activeImage ? 'var(--kb-primary)' : 'var(--kb-border)'}`,
                        background: 'var(--kb-surface-2)', overflow: 'hidden', cursor: 'pointer',
                      }}>
                      <img src={img.thumb} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                    </button>
                  ))}
                </div>
              )}

              {/* Main image */}
              <div style={{ aspectRatio: '1/1', background: 'var(--kb-surface-2)', borderRadius: 12, border: '1px solid var(--kb-border)', overflow: 'hidden', position: 'relative' }}>
                {product.images.length > 0
                  ? <img src={product.images[activeImage]?.url} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'contain' }} />
                  : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', background: 'linear-gradient(135deg, #F2F4F9, #E9ECF3)' }}>
                      <div style={{ textAlign: 'center' }}>
                        {product.brand && <div style={{ fontSize: 11, color: 'var(--kb-ink-soft)', textTransform: 'uppercase', letterSpacing: '0.1em', fontWeight: 700 }}>{product.brand.name}</div>}
                        <div style={{ fontSize: 18, fontWeight: 700, color: 'var(--kb-ink)', marginTop: 4 }}>{product.name}</div>
                      </div>
                    </div>
                }
                {product.images.length > 1 && (
                  <div style={{ position: 'absolute', right: 14, top: 14, background: 'rgba(15,22,37,.72)', color: '#fff', fontSize: 11, fontWeight: 600, padding: '5px 9px', borderRadius: 999, letterSpacing: '0.02em' }}>
                    {activeImage + 1} / {product.images.length}
                  </div>
                )}
                <button onClick={() => toggleWishlist(wishlistItem)}
                  style={{ position: 'absolute', top: 14, left: 14, width: 34, height: 34, borderRadius: '50%', background: wishlisted ? '#fff0f0' : 'rgba(255,255,255,.9)', border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', boxShadow: '0 1px 4px rgba(14,19,32,.12)', color: wishlisted ? '#ef4444' : '#9ca3af' }}>
                  <WishlistHeart />
                </button>
              </div>
            </div>
          </div>

          {/* ── Gallery: Mobile (main + scrollable thumbs) ── */}
          <div className="lg:hidden">
            <div style={{ aspectRatio: '1/1', background: 'var(--kb-surface-2)', borderRadius: 12, border: '1px solid var(--kb-border)', overflow: 'hidden', position: 'relative' }}>
              {product.images.length > 0
                ? <img src={product.images[activeImage]?.url} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'contain' }} />
                : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', background: 'linear-gradient(135deg, #F2F4F9, #E9ECF3)' }}>
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{ color: 'var(--kb-border)' }}>
                      <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/>
                      <circle cx="8.5" cy="8.5" r="1.5" strokeWidth={1.5}/>
                      <path strokeWidth={1.5} d="M21 15l-5-5L5 21"/>
                    </svg>
                  </div>
              }
              <button onClick={() => toggleWishlist(wishlistItem)}
                style={{ position: 'absolute', top: 12, right: 12, width: 34, height: 34, borderRadius: '50%', background: wishlisted ? '#fff0f0' : 'rgba(255,255,255,.9)', border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', boxShadow: '0 1px 4px rgba(14,19,32,.12)', color: wishlisted ? '#ef4444' : '#9ca3af' }}>
                <WishlistHeart />
              </button>
            </div>
            {product.images.length > 1 && (
              <div style={{ display: 'flex', gap: 8, marginTop: 10, overflowX: 'auto', paddingBottom: 4 }}>
                {product.images.map((img, i) => (
                  <button key={img.id} onClick={() => setActiveImage(i)}
                    style={{ flexShrink: 0, width: 60, height: 60, borderRadius: 8, padding: 0, border: `${i === activeImage ? 2 : 1}px solid ${i === activeImage ? 'var(--kb-primary)' : 'var(--kb-border)'}`, background: 'var(--kb-surface-2)', overflow: 'hidden', cursor: 'pointer' }}>
                    <img src={img.thumb} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* ── Product Info ── */}
          <div>
            {/* Brand */}
            {product.brand && (
              <Link href={`/shop/b/${product.brand.slug}`}
                style={{ display: 'block', fontSize: 12, fontWeight: 700, color: 'var(--kb-primary)', textTransform: 'uppercase', letterSpacing: '0.06em', textDecoration: 'none', marginBottom: 4 }}
                className="hover:underline">
                {product.brand.name}
              </Link>
            )}

            {/* Name */}
            <h1 className="text-[22px] sm:text-[30px]" style={{ fontWeight: 800, letterSpacing: '-0.02em', lineHeight: 1.15, margin: '4px 0 8px', color: 'var(--kb-ink)' }}>
              {product.name}
            </h1>

            {/* Rating row */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, fontSize: 13, color: 'var(--kb-ink-soft)', padding: '8px 0', borderBottom: '1px solid var(--kb-border)', marginBottom: 16, flexWrap: 'wrap' }}>
              {product.reviews_avg ? (
                <>
                  <Stars rating={product.reviews_avg} />
                  <span><strong style={{ color: 'var(--kb-ink)' }}>{Number(product.reviews_avg).toFixed(1)}</strong> · {product.reviews_count} reviews</span>
                  <span>·</span>
                  <a href="#reviews" onClick={e => { e.preventDefault(); setActiveTab('reviews'); }}
                    style={{ color: 'var(--kb-primary)', textDecoration: 'none' }} className="hover:underline">
                    Read reviews
                  </a>
                </>
              ) : (
                <span>No reviews yet</span>
              )}
            </div>

            {/* Price block */}
            <div style={{ display: 'flex', alignItems: 'baseline', gap: 12, marginBottom: 16 }}>
              <span className="text-[22px] sm:text-[28px]" style={{ fontWeight: 800, letterSpacing: '-0.02em', color: 'var(--kb-ink)' }}>{fmt(displayPrice)}</span>
              {comparePrice && <span style={{ color: 'var(--kb-ink-soft)', textDecoration: 'line-through', fontSize: 16 }}>{fmt(comparePrice)}</span>}
              {discount !== null && discount > 0 && (
                <span style={{ background: '#FEF2F2', color: '#EF4444', padding: '3px 8px', borderRadius: 4, fontSize: 12, fontWeight: 700 }}>−{discount}% off</span>
              )}
            </div>

            {/* Short description */}
            {product.short_description && (
              <p style={{ color: 'var(--kb-ink-soft)', fontSize: 14, marginBottom: 20, lineHeight: 1.65 }}>{product.short_description}</p>
            )}

            {/* Variants */}
            {product.has_variants && groupedOptions.map(attr => (
              <div key={attr} style={{ marginBottom: 16 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', fontSize: 13, fontWeight: 600, color: 'var(--kb-ink)', marginBottom: 8 }}>
                  <span>{attr}</span>
                  <span style={{ fontWeight: 400, color: 'var(--kb-ink-soft)' }}>{selectedOptions[attr]}</span>
                </div>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                  {getValuesForAttribute(attr).map(val => {
                    const combo = product.variants.find(v =>
                      Object.entries({ ...selectedOptions, [attr]: val }).every(([a, v2]) =>
                        v.options.some(o => o.attribute === a && o.value === v2)
                      )
                    );
                    const unavailable = !combo;
                    const outOfStock  = combo ? !combo.in_stock : false;
                    const isSelected  = selectedOptions[attr] === val;
                    const disabled    = unavailable || outOfStock;
                    return (
                      <button key={val}
                        onClick={() => !disabled && handleOptionSelect(attr, val)}
                        disabled={disabled}
                        style={{
                          minWidth: 56,
                          padding: isSelected ? '7px 13px' : '8px 14px',
                          border: `${isSelected ? 2 : 1}px solid ${isSelected ? 'var(--kb-primary)' : 'var(--kb-border)'}`,
                          background: isSelected ? '#EEF2FF' : '#fff',
                          borderRadius: 8, fontSize: 13, fontWeight: 600,
                          cursor: disabled ? 'not-allowed' : 'pointer',
                          color: isSelected ? 'var(--kb-primary)' : disabled ? 'var(--kb-ink-soft)' : 'var(--kb-ink)',
                          opacity: disabled ? 0.4 : 1,
                          transition: 'border-color .15s, color .15s, background .15s',
                        }}>
                        <span style={{ textDecoration: disabled ? 'line-through' : 'none' }}>{val}</span>
                      </button>
                    );
                  })}
                </div>
              </div>
            ))}

            {/* Meta strip */}
            <dl style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px 16px', padding: '12px 0', marginBottom: 16, borderTop: '1px solid var(--kb-border)', borderBottom: '1px solid var(--kb-border)', fontSize: 12 }}>
              {(selectedVariant?.sku ?? product.sku) && (
                <>
                  <dt style={{ color: 'var(--kb-ink-soft)' }}>SKU</dt>
                  <dd style={{ margin: 0, color: 'var(--kb-ink)', fontWeight: 600 }}>{selectedVariant?.sku ?? product.sku}</dd>
                </>
              )}
              {product.categories[0] && (
                <>
                  <dt style={{ color: 'var(--kb-ink-soft)' }}>Category</dt>
                  <dd style={{ margin: 0 }}>
                    <Link href={`/shop/c/${product.categories[0].slug}`}
                      style={{ color: 'var(--kb-primary)', fontWeight: 600, textDecoration: 'none' }} className="hover:underline">
                      {product.categories[0].name}
                    </Link>
                  </dd>
                </>
              )}
              <dt style={{ color: 'var(--kb-ink-soft)' }}>Availability</dt>
              <dd style={{ margin: 0, fontWeight: 600, color: isInStock ? '#16a34a' : '#ef4444' }}>
                {isInStock ? (isLowStock ? `Only ${stockQty} left!` : 'In stock') : 'Out of stock'}
              </dd>
            </dl>

            {/* Urgency */}
            {site.visitorCounterEnabled && visitorCount > 0 && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, background: '#FEF3E5', color: '#B25E09', border: '1px solid #F5DCBA', padding: '8px 12px', borderRadius: 8, fontSize: 12, fontWeight: 600, marginBottom: 16 }}>
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                {visitorCount} people are viewing this item right now
              </div>
            )}

            {/* Preorder notice */}
            {isPreorder && (product.preorder_message || product.preorder_expected_date) && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: '#c2410c', background: '#fff7ed', border: '1px solid #fed7aa', borderRadius: 10, padding: '10px 14px', marginBottom: 12 }}>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{ flexShrink: 0 }}>
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                  {product.preorder_message || 'Available for pre-order'}
                  {product.preorder_expected_date && (
                    <span style={{ marginLeft: 4, fontWeight: 600 }}>
                      · Expected: {new Date(product.preorder_expected_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                    </span>
                  )}
                </span>
              </div>
            )}

            {/* Qty + actions (desktop) */}
            <div className="hidden lg:block">
              <div style={{ display: 'flex', gap: 8, alignItems: 'center', marginBottom: 12 }}>
                <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--kb-ink)' }}>Quantity</span>
                <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--kb-border)', borderRadius: 8, overflow: 'hidden', height: 44, background: '#fff' }}>
                  <button onClick={() => setQty(q => Math.max(1, q - 1))}
                    style={{ width: 38, height: '100%', background: '#fff', border: 'none', color: 'var(--kb-ink)', cursor: 'pointer', display: 'grid', placeItems: 'center' }}
                    className="hover:bg-gray-50">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4"/></svg>
                  </button>
                  <span style={{ width: 50, textAlign: 'center', fontWeight: 600, fontSize: 14 }}>{qty}</span>
                  <button onClick={() => setQty(q => q + 1)}
                    style={{ width: 38, height: '100%', background: '#fff', border: 'none', color: 'var(--kb-ink)', cursor: 'pointer', display: 'grid', placeItems: 'center' }}
                    className="hover:bg-gray-50">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4"/></svg>
                  </button>
                </div>
                <button onClick={() => toggleWishlist(wishlistItem)}
                  style={{ height: 44, padding: '0 16px', border: '1px solid var(--kb-border)', borderRadius: 8, background: wishlisted ? '#fff0f0' : '#fff', color: wishlisted ? '#ef4444' : 'var(--kb-ink)', fontWeight: 600, fontSize: 13, display: 'flex', alignItems: 'center', gap: 6, cursor: 'pointer' }}>
                  <WishlistHeart />
                  {wishlisted ? 'Saved' : 'Save'}
                </button>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr auto', gap: 8, marginBottom: 8 }}>
                <button onClick={handleAddToCart} disabled={!canAdd}
                  style={{ height: 48, borderRadius: 10, border: 'none', cursor: canAdd ? 'pointer' : 'not-allowed', fontWeight: 700, fontSize: 15, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, background: added ? '#16a34a' : isPreorder ? '#f97316' : 'var(--kb-primary)', color: '#fff', opacity: canAdd ? 1 : 0.5, transition: 'background .15s' }}>
                  {added
                    ? <><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"/></svg>Added!</>
                    : isPreorder
                      ? 'Pre-order Now'
                      : <><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</>
                  }
                </button>
                <button onClick={handleBuyNow} disabled={!canAdd}
                  style={{ height: 48, padding: '0 24px', borderRadius: 10, border: 'none', cursor: canAdd ? 'pointer' : 'not-allowed', fontWeight: 700, fontSize: 15, background: '#0f172a', color: '#fff', opacity: canAdd ? 1 : 0.5 }}>
                  {isPreorder ? 'Pre-order & Checkout' : 'Buy Now'}
                </button>
              </div>
            </div>

            {/* Warranty strip */}
            {product.warranty && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '12px 14px', background: 'var(--kb-surface-2)', border: '1px solid var(--kb-border)', borderRadius: 8, fontSize: 13, color: 'var(--kb-ink)', marginTop: 12 }}>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{ flexShrink: 0, color: 'var(--kb-primary)' }}>
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span><strong>{product.warranty}</strong></span>
              </div>
            )}

            {/* Shipping policy strip (if free shipping) */}
            {product.shipping_included && (
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '12px 14px', background: '#f0fdf4', border: '1px solid #bbf7d0', borderRadius: 8, fontSize: 13, color: '#15803d', fontWeight: 600, marginTop: 8 }}>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style={{ flexShrink: 0 }}>
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 13h12l1-13M10 12h4"/>
                </svg>
                Free Shipping Included
              </div>
            )}

            {/* WhatsApp + Call */}
            {(site.productWhatsappEnabled || site.productCallEnabled) && (
              <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
                {site.productWhatsappEnabled && whatsappUrl && (
                  <a href={whatsappUrl} target="_blank" rel="noopener noreferrer"
                    style={{ flex: 1, height: 44, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, borderRadius: 10, background: '#25D366', color: '#fff', fontWeight: 600, fontSize: 14, textDecoration: 'none' }}>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Order on WhatsApp
                  </a>
                )}
                {site.productCallEnabled && site.productCallNumber && (
                  <a href={`tel:${site.productCallNumber}`}
                    style={{ flex: 1, height: 44, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, borderRadius: 10, background: '#2563eb', color: '#fff', fontWeight: 600, fontSize: 14, textDecoration: 'none' }}>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Call to Order
                  </a>
                )}
              </div>
            )}
          </div>
        </div>

        {/* ── Tabs ── */}
        <div style={{ marginTop: 32, borderTop: '1px solid var(--kb-border)' }}>
          <div style={{ display: 'flex', gap: 0, borderBottom: '1px solid var(--kb-border)', overflowX: 'auto' }}>
            {tabs.map(tab => (
              <button key={tab.key} onClick={() => setActiveTab(tab.key)}
                style={{
                  padding: '14px 16px', background: 'none', border: 'none',
                  fontWeight: 600, fontSize: 14, cursor: 'pointer', whiteSpace: 'nowrap',
                  marginBottom: -1,
                  borderBottom: `2px solid ${activeTab === tab.key ? 'var(--kb-primary)' : 'transparent'}`,
                  color: activeTab === tab.key ? 'var(--kb-primary)' : 'var(--kb-ink-soft)',
                  transition: 'color .15s',
                }}>
                {tab.label}
              </button>
            ))}
          </div>

          {activeTab === 'desc' && (
            <div style={{ padding: '24px 0' }}>
              {product.description
                ? <div className="kb-prose" dangerouslySetInnerHTML={{ __html: product.description }} />
                : <p style={{ color: 'var(--kb-ink-soft)', fontSize: 14 }}>No description available.</p>
              }
            </div>
          )}

          {activeTab === 'specs' && (
            <div style={{ padding: '24px 0' }}>
              {product.specifications && product.specifications.length > 0
                ? (
                  <table style={{ width: '100%', borderCollapse: 'collapse', border: '1px solid var(--kb-border)', borderRadius: 8, overflow: 'hidden', fontSize: 13 }}>
                    <tbody>
                      {product.specifications.map((spec, i) => (
                        <tr key={i} style={{ background: i % 2 === 0 ? 'var(--kb-surface-2)' : '#fff' }}>
                          <th className="w-[110px] sm:w-[200px]" style={{ textAlign: 'left', padding: '12px 16px', color: 'var(--kb-ink-soft)', fontWeight: 500, borderRight: '1px solid var(--kb-border)' }}>{spec.key}</th>
                          <td style={{ padding: '12px 16px', color: 'var(--kb-ink)', fontWeight: 500 }}>{spec.value}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                )
                : <p style={{ color: 'var(--kb-ink-soft)', fontSize: 14 }}>No specifications available.</p>
              }
            </div>
          )}

          {activeTab === 'reviews' && (
            <div style={{ padding: '24px 0' }}>
              {product.reviews_count > 0 && product.reviews_avg ? (
                <div className="grid grid-cols-1 md:grid-cols-[240px_1fr] gap-8 items-start">
                  <div style={{ textAlign: 'center', padding: 20, background: 'var(--kb-surface-2)', borderRadius: 12 }}>
                    <div style={{ fontSize: 48, fontWeight: 800, letterSpacing: '-0.03em', lineHeight: 1, color: 'var(--kb-ink)' }}>
                      {Number(product.reviews_avg).toFixed(1)}
                    </div>
                    <div style={{ margin: '4px 0' }}><Stars rating={product.reviews_avg} size={18} /></div>
                    <div style={{ fontSize: 13, color: 'var(--kb-ink-soft)' }}>Based on {product.reviews_count} reviews</div>
                  </div>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                    {product.reviews.map(r => (
                      <div key={r.id} style={{ border: '1px solid var(--kb-border)', borderRadius: 10, padding: 16 }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8, marginBottom: 6 }}>
                          <div>
                            <div style={{ fontWeight: 600, fontSize: 14, color: 'var(--kb-ink)' }}>{r.user_name}</div>
                            <Stars rating={r.rating} size={13} />
                          </div>
                          <span style={{ fontSize: 12, color: 'var(--kb-ink-soft)', whiteSpace: 'nowrap' }}>{r.created_at}</span>
                        </div>
                        {r.title && <p style={{ fontWeight: 600, fontSize: 13, color: 'var(--kb-ink)', margin: '4px 0 2px' }}>{r.title}</p>}
                        {r.body && <p style={{ fontSize: 13, color: 'var(--kb-ink-soft)', margin: 0, lineHeight: 1.6 }}>{r.body}</p>}
                      </div>
                    ))}
                  </div>
                </div>
              ) : (
                <div style={{ padding: '40px 24px', textAlign: 'center', background: 'var(--kb-surface-2)', border: '1px dashed var(--kb-border)', borderRadius: 12, color: 'var(--kb-ink-soft)' }}>
                  <div style={{ fontSize: 28, marginBottom: 8 }}>★</div>
                  <div style={{ fontWeight: 600, marginBottom: 4, color: 'var(--kb-ink)' }}>Be the first to write a detailed review</div>
                  <div style={{ fontSize: 13 }}>Share your thoughts with other shoppers.</div>
                </div>
              )}
            </div>
          )}

          {activeTab === 'shipping' && (
            <div style={{ padding: '24px 0', fontSize: 14, lineHeight: 1.65, color: 'var(--kb-ink-soft)' }}>
              {(product.return_policy || site.globalReturnPolicy) ? (
                <div className="kb-prose" dangerouslySetInnerHTML={{ __html: product.return_policy || site.globalReturnPolicy || '' }} />
              ) : (
                <p>Contact us for shipping and returns information.</p>
              )}
            </div>
          )}
        </div>

        {/* Related Products */}
        {related.length > 0 && (
          <div style={{ marginTop: 48 }}>
            <div style={{ marginBottom: 20 }}>
              <h2 style={{ fontSize: 22, fontWeight: 800, letterSpacing: '-0.02em', color: 'var(--kb-ink)', margin: 0 }}>You might also like</h2>
              <div style={{ fontSize: 14, color: 'var(--kb-ink-soft)', marginTop: 4 }}>Similar products from the same category.</div>
            </div>
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
              {related.map(p => <RelatedCard key={p.id} product={p} />)}
            </div>
          </div>
        )}
      </div>

      {/* ── Mobile Sticky Bottom Bar ── */}
      <div className="lg:hidden fixed bottom-14 md:bottom-0 left-0 right-0 z-40 bg-white border-t shadow-lg" style={{ borderColor: 'var(--kb-border)' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '10px 16px 6px' }}>
          <span style={{ fontSize: 12, color: 'var(--kb-ink-soft)', flexShrink: 0 }}>Qty:</span>
          <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--kb-border)', borderRadius: 8, overflow: 'hidden' }}>
            <button onClick={() => setQty(q => Math.max(1, q - 1))} style={{ width: 32, height: 32, background: '#fff', border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', color: 'var(--kb-ink)', fontSize: 16 }}>−</button>
            <span style={{ width: 32, textAlign: 'center', fontSize: 13, fontWeight: 600 }}>{qty}</span>
            <button onClick={() => setQty(q => q + 1)} style={{ width: 32, height: 32, background: '#fff', border: 'none', cursor: 'pointer', display: 'grid', placeItems: 'center', color: 'var(--kb-ink)', fontSize: 16 }}>+</button>
          </div>
          {isInStock
            ? isLowStock
              ? <span style={{ fontSize: 12, color: '#f97316', fontWeight: 500 }}>Only {stockQty} left!</span>
              : <span style={{ fontSize: 12, color: '#16a34a', fontWeight: 500 }}>In Stock</span>
            : <span style={{ fontSize: 12, color: '#ef4444', fontWeight: 500 }}>Out of Stock</span>
          }
          <div style={{ flex: 1 }} />
          <button onClick={() => toggleWishlist(wishlistItem)}
            style={{ width: 36, height: 36, borderRadius: '50%', border: '1px solid var(--kb-border)', background: wishlisted ? '#fff0f0' : '#fff', color: wishlisted ? '#ef4444' : '#9ca3af', cursor: 'pointer', display: 'grid', placeItems: 'center' }}>
            <WishlistHeart />
          </button>
        </div>
        <div style={{ display: 'flex', gap: 8, padding: '0 16px 16px' }}>
          <button onClick={handleAddToCart} disabled={!canAdd}
            style={{ flex: 1, height: 44, borderRadius: 10, border: 'none', cursor: canAdd ? 'pointer' : 'not-allowed', fontWeight: 700, fontSize: 14, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, background: added ? '#16a34a' : 'var(--kb-primary)', color: '#fff', opacity: canAdd ? 1 : 0.5 }}>
            {added
              ? <><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7"/></svg>Added!</>
              : <><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Add to Cart</>
            }
          </button>
          <button onClick={handleBuyNow} disabled={!canAdd}
            style={{ flex: 1, height: 44, borderRadius: 10, border: 'none', cursor: canAdd ? 'pointer' : 'not-allowed', fontWeight: 700, fontSize: 14, background: '#0f172a', color: '#fff', opacity: canAdd ? 1 : 0.5 }}>
            Buy Now
          </button>
        </div>
      </div>
    </Layout>
  );
}
