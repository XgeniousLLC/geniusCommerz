import { Head, Link, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { useCartStore } from '../../store/cartStore';
import { useWishlistStore } from '../../store/wishlistStore';
import Layout from '../../layouts/Layout';
import { usePrice } from '../../usePrice';
import type { ProductCard, ProductFull, ProductVariant, SharedProps } from '../../types';

const S = { fill: 'none', stroke: 'currentColor', strokeWidth: 1.4, strokeLinecap: 'round', strokeLinejoin: 'round' } as React.SVGProps<SVGSVGElement>;

type TabKey = 'desc' | 'specs' | 'reviews' | 'shipping';

interface Props { product: ProductFull; related: ProductCard[]; }

function Stars({ rating, size = 12 }: { rating: number; size?: number }) {
  const full = Math.round(rating);
  return <span style={{ color: 'var(--av-gold)', fontSize: size, letterSpacing: 1.5 }}>{'★'.repeat(full)}{'☆'.repeat(5 - full)}</span>;
}

function RelatedCard({ product }: { product: ProductCard }) {
  const fmt = usePrice();
  const [hovered, setHovered] = useState(false);
  const discount = product.compare_at_price ? Math.round((1 - product.price / product.compare_at_price) * 100) : null;
  return (
    <Link href={`/shop/${product.slug}`} style={{ textDecoration: 'none', color: 'inherit', display: 'block' }}
      onMouseEnter={() => setHovered(true)} onMouseLeave={() => setHovered(false)}>
      <div style={{ aspectRatio: '4/5', background: 'var(--av-paper-2)', overflow: 'hidden', marginBottom: 10, position: 'relative' }}>
        {product.image_url
          ? <img src={product.image_url} alt={product.name} loading="lazy"
              style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block', transition: 'transform .55s cubic-bezier(.2,.7,.3,1)', transform: hovered ? 'scale(1.04)' : 'scale(1)' }} />
          : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center' }}>
              <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="var(--av-line)" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round">
                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
              </svg>
            </div>
        }
        {discount && discount > 0 && (
          <span style={{ position: 'absolute', top: 10, left: 10, background: 'var(--av-cognac)', color: '#fff', fontSize: 9.5, letterSpacing: '0.18em', textTransform: 'uppercase', padding: '4px 9px' }}>−{discount}%</span>
        )}
      </div>
      <div style={{ fontFamily: 'var(--av-display)', fontSize: 14.5, fontWeight: 400, color: 'var(--av-ink)', marginBottom: 4, lineHeight: 1.2, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>{product.name}</div>
      <div style={{ display: 'flex', alignItems: 'baseline', gap: 8 }}>
        <span style={{ fontSize: 13, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>{fmt(product.price)}</span>
        {product.compare_at_price && <span style={{ fontSize: 12, color: 'var(--av-muted)', textDecoration: 'line-through', fontFamily: 'var(--av-sans)' }}>{fmt(product.compare_at_price)}</span>}
      </div>
    </Link>
  );
}

function Accordion({ title, children, defaultOpen = false }: { title: string; children: React.ReactNode; defaultOpen?: boolean }) {
  const [open, setOpen] = useState(defaultOpen);
  return (
    <div style={{ borderBottom: '1px solid var(--av-line)' }}>
      <button onClick={() => setOpen(o => !o)}
        style={{ width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '18px 0', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontSize: 12, fontWeight: 500, letterSpacing: '0.16em', textTransform: 'uppercase', textAlign: 'left' }}>
        {title}
        <svg viewBox="0 0 24 24" width="14" height="14" {...S} style={{ flexShrink: 0, transform: open ? 'rotate(180deg)' : 'none', transition: 'transform .2s' }}>
          <path d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      {open && <div style={{ paddingBottom: 20 }}>{children}</div>}
    </div>
  );
}

export default function ShopShow({ product, related }: Props) {
  const { site } = usePage<SharedProps>().props;
  const addItem = useCartStore(s => s.addItem);
  const openCart = useCartStore(s => s.openCart);
  const { toggle: toggleWishlist, has: inWishlist } = useWishlistStore();
  const fmt = usePrice();
  const wishlisted = inWishlist(product.id);
  const [activeImage, setActiveImage] = useState(0);
  const [mediaTab, setMediaTab] = useState<'images' | 'video'>('images');
  const THUMB_VISIBLE = 5;
  const [thumbStart, setThumbStart] = useState(0);
  useEffect(() => {
    if (activeImage < thumbStart) setThumbStart(activeImage);
    else if (activeImage >= thumbStart + THUMB_VISIBLE) setThumbStart(activeImage - THUMB_VISIBLE + 1);
  }, [activeImage]);
  const [selectedVariant, setSelectedVariant] = useState<ProductVariant | null>(
    product.has_variants && product.variants.length > 0
      ? (product.variants.find(v => v.in_stock) ?? product.variants[0]) : null
  );
  const [qty, setQty] = useState(1);
  const [added, setAdded] = useState(false);
  const [visitorCount] = useState(() => {
    const { visitorCounterEnabled, visitorCounterMin, visitorCounterMax } = site;
    return visitorCounterEnabled ? Math.floor(Math.random() * (visitorCounterMax - visitorCounterMin + 1)) + visitorCounterMin : 0;
  });

  const displayPrice = selectedVariant ? selectedVariant.price : product.price;
  const comparePrice = selectedVariant ? selectedVariant.compare_at_price : product.compare_at_price;
  const discount = comparePrice ? Math.round((1 - displayPrice / comparePrice) * 100) : null;
  const isInStock  = selectedVariant ? selectedVariant.in_stock : product.in_stock;
  const stockQty   = selectedVariant ? selectedVariant.stock_qty : product.stock_qty;
  const isLowStock = stockQty !== null && stockQty > 0 && stockQty <= 5;
  const isPreorder = !isInStock && product.preorder_enabled;
  const canAdd     = isInStock || isPreorder;

  const wishlistItem = { id: product.id, slug: product.slug, name: product.name, price: displayPrice, compare_at_price: comparePrice, image_url: product.images[0]?.url ?? null, category: product.categories[0]?.name ?? null };
  const cartItem = { product_id: product.id, variant_id: selectedVariant?.id ?? null, name: product.name, variant_label: selectedVariant?.label ?? null, price: displayPrice, image_url: product.images[0]?.thumb ?? null, slug: product.slug, shipping_included: product.shipping_included };

  function handleAddToCart() {
    addItem(cartItem, qty); setAdded(true); openCart();
    setTimeout(() => setAdded(false), 2200);
  }
  function handleBuyNow() { addItem(cartItem, qty); window.location.href = '/checkout'; }

  const whatsappUrl = site.productWhatsappNumber
    ? `https://wa.me/${site.productWhatsappNumber}?text=${encodeURIComponent((site.productWhatsappMessage || 'Hi, I want to order: {product}').replace('{product}', product.name))}`
    : null;

  const groupedOptions = product.variants.length > 0 ? product.variants[0].options.map(o => o.attribute) : [];
  const getValuesForAttribute = (attr: string) => [...new Set(product.variants.map(v => v.options.find(o => o.attribute === attr)?.value).filter(Boolean))] as string[];
  const selectedOptions: Record<string, string> = selectedVariant ? Object.fromEntries(selectedVariant.options.map(o => [o.attribute, o.value])) : {};
  function handleOptionSelect(attr: string, val: string) {
    const newOpts = { ...selectedOptions, [attr]: val };
    const exact = product.variants.find(v => v.options.every(o => newOpts[o.attribute] === o.value));
    if (exact) { setSelectedVariant(exact); return; }
    const partial = product.variants.find(v => v.in_stock && v.options.some(o => o.attribute === attr && o.value === val)) ?? product.variants.find(v => v.options.some(o => o.attribute === attr && o.value === val));
    if (partial) setSelectedVariant(partial);
  }

  const pageTitle = product.meta?.meta_title ?? product.name;
  const pageDesc  = product.meta?.meta_description ?? product.short_description ?? '';

  const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };

  const AddBtn = ({ full }: { full?: boolean }) => (
    <button onClick={handleAddToCart} disabled={!canAdd}
      style={{ flex: full ? undefined : 1, width: full ? '100%' : undefined, height: 50, background: added ? 'var(--av-cognac-deep)' : 'var(--av-ink)', color: 'var(--av-paper)', border: 'none', cursor: canAdd ? 'pointer' : 'not-allowed', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.22em', textTransform: 'uppercase', opacity: canAdd ? 1 : 0.4, transition: 'background .2s', boxSizing: 'border-box' }}>
      {added ? 'Added to Bag' : isPreorder ? 'Pre-order' : 'Add to Bag'}
    </button>
  );

  const InfoPanel = () => (
    <>
      {/* Breadcrumb */}
      <nav style={{ display: 'flex', gap: 8, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 18, flexWrap: 'wrap' }}>
        <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link><span>/</span>
        <Link href="/shop" style={{ color: 'inherit', textDecoration: 'none' }}>Shop</Link>
        {product.categories[0] && (<><span>/</span><Link href={`/shop/c/${product.categories[0].slug}`} style={{ color: 'inherit', textDecoration: 'none' }}>{product.categories[0].name}</Link></>)}
        <span>/</span><span style={{ color: 'var(--av-ink)' }}>{product.name}</span>
      </nav>

      {/* Eyebrow */}
      {(product.brand || product.categories[0]) && (
        <div style={{ fontSize: 10.5, letterSpacing: '0.28em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>
          {product.brand?.name ?? product.categories[0]?.name}
        </div>
      )}

      {/* Name */}
      <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(24px,3vw,38px)', fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 14px', lineHeight: 1.06, letterSpacing: '-0.012em' }}>
        {product.name}
      </h1>

      {/* Rating */}
      {(product.reviews_avg || product.reviews_count > 0) && (
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 18, paddingBottom: 16, borderBottom: '1px solid var(--av-line-soft)' }}>
          {product.reviews_avg && <Stars rating={product.reviews_avg} size={13} />}
          <span style={{ fontSize: 12.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{product.reviews_count} reviews</span>
        </div>
      )}

      {/* Price */}
      <div style={{ display: 'flex', alignItems: 'baseline', gap: 12, marginBottom: 20 }}>
        <span style={{ fontFamily: 'var(--av-sans)', fontSize: 22, fontWeight: 600, color: 'var(--av-ink)' }}>{fmt(displayPrice)}</span>
        {comparePrice && <span style={{ fontSize: 15, color: 'var(--av-muted)', textDecoration: 'line-through', fontFamily: 'var(--av-sans)' }}>{fmt(comparePrice)}</span>}
        {discount && discount > 0 && <span style={{ fontSize: 11.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, letterSpacing: '0.06em' }}>−{discount}%</span>}
      </div>

      {/* Short description */}
      {product.short_description && (
        <p style={{ color: 'var(--av-muted)', fontSize: 14, marginBottom: 20, lineHeight: 1.7, fontFamily: 'var(--av-sans)' }}>{product.short_description}</p>
      )}

      {/* Variants */}
      {product.has_variants && groupedOptions.map(attr => (
        <div key={attr} style={{ marginBottom: 18 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 11.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: 10 }}>
            <span>{attr}</span>
            <span style={{ fontWeight: 400, color: 'var(--av-muted)' }}>{selectedOptions[attr]}</span>
          </div>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
            {getValuesForAttribute(attr).map(val => {
              const combo = product.variants.find(v => Object.entries({ ...selectedOptions, [attr]: val }).every(([a, v2]) => v.options.some(o => o.attribute === a && o.value === v2)));
              const unavailable = !combo;
              const outOfStock  = combo ? !combo.in_stock : false;
              const isSelected  = selectedOptions[attr] === val;
              const disabled    = unavailable || outOfStock;
              return (
                <button key={val} onClick={() => !disabled && handleOptionSelect(attr, val)} disabled={disabled}
                  style={{ minWidth: 52, padding: '9px 14px', border: `1px solid ${isSelected ? 'var(--av-ink)' : 'var(--av-line)'}`, background: isSelected ? 'var(--av-ink)' : 'transparent', borderRadius: 2, fontSize: 13, cursor: disabled ? 'not-allowed' : 'pointer', color: isSelected ? 'var(--av-paper)' : disabled ? 'var(--av-line)' : 'var(--av-ink)', fontFamily: 'var(--av-sans)', opacity: disabled ? 0.4 : 1, transition: 'all .15s' }}>
                  <span style={{ textDecoration: disabled ? 'line-through' : 'none' }}>{val}</span>
                </button>
              );
            })}
          </div>
        </div>
      ))}

      {/* Stock status */}
      <div style={{ fontSize: 12.5, fontFamily: 'var(--av-sans)', color: isInStock ? 'var(--av-cognac)' : 'var(--av-muted)', letterSpacing: '0.06em', marginBottom: 18 }}>
        {isInStock ? (isLowStock ? `Only ${stockQty} left` : 'In stock') : isPreorder ? 'Available to pre-order' : 'Out of stock'}
        {site.visitorCounterEnabled && visitorCount > 0 && (
          <span style={{ marginLeft: 14, color: 'var(--av-muted)' }}>· {visitorCount} viewing now</span>
        )}
      </div>

      {/* Preorder notice */}
      {isPreorder && (product.preorder_message || product.preorder_expected_date) && (
        <div style={{ padding: '12px 14px', border: '1px solid var(--av-line)', background: 'var(--av-paper)', fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 16, lineHeight: 1.6 }}>
          {product.preorder_message || 'Available for pre-order'}
          {product.preorder_expected_date && <strong style={{ color: 'var(--av-ink)', marginLeft: 4 }}>· Ships {new Date(product.preorder_expected_date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' })}</strong>}
        </div>
      )}

      {/* Qty + CTA */}
      <div style={{ display: 'flex', gap: 10, marginBottom: 10 }}>
        <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--av-line)', height: 50, flexShrink: 0 }}>
          <button onClick={() => setQty(q => Math.max(1, q - 1))} style={{ width: 44, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 18, display: 'grid', placeItems: 'center' }}>−</button>
          <span style={{ width: 36, textAlign: 'center', fontSize: 14, fontFamily: 'var(--av-sans)', fontWeight: 500, color: 'var(--av-ink)' }}>{qty}</span>
          <button onClick={() => setQty(q => q + 1)} style={{ width: 44, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 18, display: 'grid', placeItems: 'center' }}>+</button>
        </div>
        <AddBtn />
        <button onClick={() => toggleWishlist(wishlistItem)}
          style={{ width: 50, height: 50, border: '1px solid var(--av-line)', background: 'transparent', cursor: 'pointer', color: wishlisted ? 'var(--av-cognac)' : 'var(--av-muted)', display: 'grid', placeItems: 'center', flexShrink: 0 }}>
          <svg viewBox="0 0 24 24" width="16" height="16" fill={wishlisted ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
          </svg>
        </button>
      </div>

      <button onClick={handleBuyNow} disabled={!canAdd}
        style={{ width: '100%', height: 46, background: 'transparent', border: '1px solid var(--av-line)', color: 'var(--av-ink)', cursor: canAdd ? 'pointer' : 'not-allowed', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.22em', textTransform: 'uppercase', opacity: canAdd ? 1 : 0.4, marginBottom: 20, boxSizing: 'border-box' }}>
        Buy Now
      </button>

      {/* WA + Call */}
      {(site.productWhatsappEnabled || site.productCallEnabled) && (
        <div style={{ display: 'flex', gap: 10, marginBottom: 20 }}>
          {site.productWhatsappEnabled && whatsappUrl && (
            <a href={whatsappUrl} target="_blank" rel="noopener noreferrer"
              style={{ flex: 1, height: 42, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, border: '1px solid rgba(37,211,102,.5)', color: '#1a8a42', fontFamily: 'var(--av-sans)', fontSize: 12, fontWeight: 500, letterSpacing: '0.08em', textDecoration: 'none', background: 'rgba(37,211,102,.06)' }}>
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              WhatsApp
            </a>
          )}
          {site.productCallEnabled && site.productCallNumber && (
            <a href={`tel:${site.productCallNumber}`}
              style={{ flex: 1, height: 42, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, border: '1px solid var(--av-line)', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 12, fontWeight: 500, letterSpacing: '0.08em', textDecoration: 'none' }}>
              <svg viewBox="0 0 24 24" width="14" height="14" {...S}><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.63A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92v2z"/></svg>
              Call
            </a>
          )}
        </div>
      )}

      {/* Reassurance — product-level override or site-level default */}
      {((product.trust_badges ?? site.trustBadges).length > 0) && (
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginBottom: 20 }}>
          {(product.trust_badges ?? site.trustBadges).map((badge, i) => (
            <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px', border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)' }}>
              <span style={{ color: 'var(--av-cognac)', flexShrink: 0 }}>
                <svg viewBox="0 0 24 24" width="16" height="16" {...S}><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </span>
              <div>
                <div style={{ fontSize: 12, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{badge.title}</div>
                <div style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{badge.sub}</div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Meta chips */}
      {((selectedVariant?.sku ?? product.sku) || product.brand) && (
        <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap', marginBottom: 20 }}>
          {(selectedVariant?.sku ?? product.sku) && (
            <span style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', letterSpacing: '0.08em' }}>
              SKU: {selectedVariant?.sku ?? product.sku}
            </span>
          )}
          {product.brand && (
            <><span style={{ color: 'var(--av-line)' }}>·</span>
            <Link href={`/shop/b/${product.brand.slug}`} style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', textDecoration: 'none', letterSpacing: '0.08em' }}>
              Brand: {product.brand.name}
            </Link></>
          )}
        </div>
      )}

      {(product.warranty || product.shipping_included) && (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {product.warranty && (
            <div style={{ fontSize: 12.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', letterSpacing: '0.04em' }}>
              Warranty: {product.warranty}
            </div>
          )}
          {product.shipping_included && (
            <div style={{ fontSize: 12.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', letterSpacing: '0.04em' }}>
              Free shipping included
            </div>
          )}
        </div>
      )}
    </>
  );

  return (
    <Layout>
      <Head>
        <title>{pageTitle}</title>
        {pageDesc && <meta name="description" content={pageDesc} />}
      </Head>

      <div style={{ ...W, paddingTop: 'clamp(24px,4vw,48px)', paddingBottom: 'clamp(60px,8vw,96px)' }}>
        {/* PDP grid */}
        <div style={{ display: 'grid', gridTemplateColumns: '1.15fr 1fr', gap: 'clamp(32px,5vw,64px)', alignItems: 'start' }} className="av-pdp-grid">

          {/* Gallery */}
          <div style={{ position: 'sticky', top: 80 }} className="av-pdp-gallery">
            {/* Image / Video tab switcher */}
            {product.videos.length > 0 && product.images.length > 0 && (
              <div style={{ display: 'flex', gap: 0, marginBottom: 12, border: '1px solid var(--av-line)', width: 'fit-content', marginLeft: 'auto', marginRight: 'auto' }}>
                {(['images', 'video'] as const).map(tab => (
                  <button key={tab} onClick={() => setMediaTab(tab)}
                    style={{ padding: '6px 18px', fontSize: 11, fontFamily: 'var(--av-sans)', fontWeight: 500, letterSpacing: '0.14em', textTransform: 'uppercase', border: 'none', cursor: 'pointer', background: mediaTab === tab ? 'var(--av-ink)' : 'transparent', color: mediaTab === tab ? '#fff' : 'var(--av-muted)', transition: 'background .15s,color .15s' }}>
                    {tab === 'images' ? 'Photos' : 'Video'}
                  </button>
                ))}
              </div>
            )}
            <div style={{ display: 'grid', gridTemplateColumns: (mediaTab === 'images' && product.images.length > 1) ? '72px 1fr' : '1fr', gap: 14 }} className="av-gallery-inner">
              {/* Video player */}
              {mediaTab === 'video' && product.videos.length > 0 && (
                <video controls style={{ width: '100%', aspectRatio: '4/5', background: '#000', display: 'block', objectFit: 'contain' }}>
                  <source src={product.videos[0].url} />
                </video>
              )}
              {/* Thumbs */}
              {mediaTab === 'images' && product.images.length > 1 && (
                <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
                  {/* Up arrow */}
                  <button
                    onClick={() => setThumbStart(s => Math.max(0, s - 1))}
                    disabled={thumbStart === 0}
                    style={{ width: 72, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'transparent', border: '1px solid var(--av-line)', cursor: thumbStart === 0 ? 'default' : 'pointer', opacity: thumbStart === 0 ? 0.3 : 1, flexShrink: 0, transition: 'opacity .15s' }}
                    aria-label="Scroll thumbnails up"
                  >
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M18 15l-6-6-6 6"/>
                    </svg>
                  </button>
                  {/* Visible thumbs */}
                  {product.images.slice(thumbStart, thumbStart + THUMB_VISIBLE).map((img, j) => {
                    const i = thumbStart + j;
                    return (
                      <button key={img.id} onClick={() => setActiveImage(i)}
                        style={{ width: 72, height: 88, padding: 0, border: `1px solid ${i === activeImage ? 'var(--av-ink)' : 'var(--av-line)'}`, background: 'var(--av-paper-2)', overflow: 'hidden', cursor: 'pointer', flexShrink: 0 }}>
                        <img src={img.thumb} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
                      </button>
                    );
                  })}
                  {/* Down arrow */}
                  <button
                    onClick={() => setThumbStart(s => Math.min(product.images.length - THUMB_VISIBLE, s + 1))}
                    disabled={thumbStart + THUMB_VISIBLE >= product.images.length}
                    style={{ width: 72, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'transparent', border: '1px solid var(--av-line)', cursor: thumbStart + THUMB_VISIBLE >= product.images.length ? 'default' : 'pointer', opacity: thumbStart + THUMB_VISIBLE >= product.images.length ? 0.3 : 1, flexShrink: 0, transition: 'opacity .15s' }}
                    aria-label="Scroll thumbnails down"
                  >
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M6 9l6 6 6-6"/>
                    </svg>
                  </button>
                </div>
              )}
              {/* Main image */}
              {mediaTab === 'images' && <div style={{ aspectRatio: '4/5', background: 'var(--av-paper-2)', overflow: 'hidden', position: 'relative' }}>
                {product.images.length > 0
                  ? <img src={product.images[activeImage]?.url} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'contain', display: 'block' }} />
                  : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center' }}>
                      <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="var(--av-line)" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                    </div>
                }
                {product.images.length > 1 && (
                  <div style={{ position: 'absolute', right: 12, bottom: 12, fontSize: 10.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', letterSpacing: '0.1em', background: 'rgba(251,248,241,.88)', padding: '4px 8px', backdropFilter: 'blur(4px)' }}>
                    {activeImage + 1} / {product.images.length}
                  </div>
                )}
              </div>}
            </div>
          </div>

          {/* Info */}
          <div>
            <InfoPanel />
          </div>
        </div>

        {/* Accordion tabs */}
        <div style={{ marginTop: 56, borderTop: '1px solid var(--av-line)', maxWidth: 760 }}>
          <Accordion title="Description" defaultOpen>
            {product.description
              ? <div className="kb-prose" style={{ color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontSize: 14, lineHeight: 1.7 }} dangerouslySetInnerHTML={{ __html: product.description }} />
              : <p style={{ color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 14 }}>No description available.</p>
            }
          </Accordion>

          {product.specifications && product.specifications.length > 0 && (
            <Accordion title="Specifications">
              <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13 }}>
                <tbody>
                  {product.specifications.map((spec, i) => (
                    <tr key={i} style={{ borderBottom: '1px solid var(--av-line-soft)' }}>
                      <th style={{ textAlign: 'left', padding: '10px 0 10px 0', color: 'var(--av-muted)', fontWeight: 400, width: '40%', fontFamily: 'var(--av-sans)', fontSize: 13 }}>{spec.key}</th>
                      <td style={{ padding: '10px 0', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, fontSize: 13 }}>{spec.value}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </Accordion>
          )}

          {product.reviews_count > 0 && <Accordion title={`Reviews (${product.reviews_count})`}>
            {product.reviews_avg ? (
              <div style={{ display: 'grid', gridTemplateColumns: '180px 1fr', gap: 32 }} className="av-reviews-grid">
                <div style={{ textAlign: 'center', padding: '20px 0' }}>
                  <div style={{ fontFamily: 'var(--av-display)', fontSize: 64, fontWeight: 300, lineHeight: 1, color: 'var(--av-ink)', letterSpacing: '-0.04em' }}>
                    {Number(product.reviews_avg).toFixed(1)}
                  </div>
                  <div style={{ margin: '6px 0' }}><Stars rating={product.reviews_avg} size={16} /></div>
                  <div style={{ fontSize: 12, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{product.reviews_count} reviews</div>
                </div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                  {product.reviews.map(r => (
                    <div key={r.id} style={{ paddingBottom: 16, borderBottom: '1px solid var(--av-line-soft)' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8, marginBottom: 6 }}>
                        <div>
                          <div style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', marginBottom: 3 }}>{r.user_name}</div>
                          <Stars rating={r.rating} size={12} />
                        </div>
                        <span style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', whiteSpace: 'nowrap' }}>{r.created_at}</span>
                      </div>
                      {r.title && <p style={{ fontWeight: 500, fontSize: 13, color: 'var(--av-ink)', margin: '6px 0 3px', fontFamily: 'var(--av-sans)' }}>{r.title}</p>}
                      {r.body && <p style={{ fontSize: 13.5, color: 'var(--av-muted)', margin: 0, lineHeight: 1.65, fontFamily: 'var(--av-sans)' }}>{r.body}</p>}
                    </div>
                  ))}
                </div>
              </div>
            ) : null}
          </Accordion>}

          {(product.return_policy || site.globalReturnPolicy) && (
            <Accordion title="Shipping & Returns">
              <div className="kb-prose" style={{ color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 14, lineHeight: 1.7 }} dangerouslySetInnerHTML={{ __html: product.return_policy || site.globalReturnPolicy || '' }} />
            </Accordion>
          )}

          {product.faqs && product.faqs.length > 0 && (
            <Accordion title="FAQ">
              <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
                {product.faqs.map((faq, i) => (
                  <div key={i} style={{ paddingBottom: 16, borderBottom: i < product.faqs.length - 1 ? '1px solid var(--av-line-soft)' : 'none' }}>
                    <div style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', marginBottom: 6 }}>{faq.question}</div>
                    <div style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', lineHeight: 1.65 }}>{faq.answer}</div>
                  </div>
                ))}
              </div>
            </Accordion>
          )}
        </div>

        {/* FAQ JSON-LD schema */}
        {product.faqs && product.faqs.length > 0 && (
          <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            mainEntity: product.faqs.map(faq => ({
              '@type': 'Question',
              name: faq.question,
              acceptedAnswer: { '@type': 'Answer', text: faq.answer },
            })),
          })}} />
        )}

        {/* Related */}
        {related.length > 0 && (
          <section style={{ marginTop: 72 }}>
            <div style={{ marginBottom: 32 }}>
              <div style={{ fontSize: 10.5, letterSpacing: '0.28em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>You may also like</div>
              <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(22px,3vw,34px)', fontWeight: 400, color: 'var(--av-ink)', margin: 0, letterSpacing: '-0.012em' }}>Complete the look</h2>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 28 }} className="av-related-grid">
              {related.map(p => <RelatedCard key={p.id} product={p} />)}
            </div>
          </section>
        )}
      </div>

      {/* Mobile sticky bar */}
      <div className="av-pdp-mobile-bar" style={{ display: 'none', position: 'fixed', bottom: 56, left: 0, right: 0, zIndex: 40, background: 'var(--av-paper)', borderTop: '1px solid var(--av-line)', padding: '12px 16px', gap: 10 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 10, flexWrap: 'wrap' }}>
          <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--av-line)', height: 40 }}>
            <button onClick={() => setQty(q => Math.max(1, q - 1))} style={{ width: 36, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 18, display: 'grid', placeItems: 'center' }}>−</button>
            <span style={{ width: 30, textAlign: 'center', fontSize: 13, fontFamily: 'var(--av-sans)', fontWeight: 500 }}>{qty}</span>
            <button onClick={() => setQty(q => q + 1)} style={{ width: 36, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 18, display: 'grid', placeItems: 'center' }}>+</button>
          </div>
          <span style={{ fontSize: 12, color: isInStock ? 'var(--av-cognac)' : 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
            {isInStock ? (isLowStock ? `Only ${stockQty} left` : 'In stock') : 'Out of stock'}
          </span>
          <button onClick={() => toggleWishlist(wishlistItem)} style={{ marginLeft: 'auto', background: 'transparent', border: 'none', cursor: 'pointer', color: wishlisted ? 'var(--av-cognac)' : 'var(--av-muted)', padding: 6 }}>
            <svg viewBox="0 0 24 24" width="18" height="18" fill={wishlisted ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
            </svg>
          </button>
        </div>
        <div style={{ display: 'flex', gap: 8 }}>
          <AddBtn />
          <button onClick={handleBuyNow} disabled={!canAdd}
            style={{ flex: 1, height: 50, background: 'transparent', border: '1px solid var(--av-line)', color: 'var(--av-ink)', cursor: canAdd ? 'pointer' : 'not-allowed', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase', opacity: canAdd ? 1 : 0.4 }}>
            Buy Now
          </button>
        </div>
      </div>

      <style>{`
        @media(max-width:860px){
          .av-pdp-grid{grid-template-columns:1fr!important}
          .av-pdp-gallery{position:static!important}
          .av-gallery-inner{grid-template-columns:1fr!important}
          .av-pdp-mobile-bar{display:flex!important;flex-direction:column}
        }
        @media(max-width:640px){
          .av-related-grid{grid-template-columns:1fr 1fr!important;gap:16px!important}
          .av-reviews-grid{grid-template-columns:1fr!important}
        }
      `}</style>
    </Layout>
  );
}
