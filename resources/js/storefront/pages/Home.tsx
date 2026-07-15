import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { useCartStore } from '../store/cartStore';
import { useWishlistStore } from '../store/wishlistStore';
import { usePrice } from '../usePrice';
import Layout from '../layouts/Layout';
import type { BlogPost, Brand, ProductCard, SharedProps, ShopCategory } from '../types';

interface Props {
  latestPosts: BlogPost[];
  shopCategories: ShopCategory[];
  featuredProducts: ProductCard[];
  newArrivals: ProductCard[];
  brands: Brand[];
  heroTitle: string;
  heroSub: string;
  heroImageUrl?: string | null;
}

const stroke = { fill: 'none', stroke: 'currentColor', strokeWidth: 1.4, strokeLinecap: 'round', strokeLinejoin: 'round' } as React.SVGProps<SVGSVGElement>;

const VALUE_PROPS = [
  {
    icon: <svg viewBox="0 0 24 24" width="22" height="22" {...stroke}><path d="M5 12h14M12 5l7 7-7 7"/></svg>,
    title: 'Worldwide Shipping',
    sub: 'Fast & tracked delivery',
  },
  {
    icon: <svg viewBox="0 0 24 24" width="22" height="22" {...stroke}><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>,
    title: 'Secure Payment',
    sub: 'SSL encrypted checkout',
  },
  {
    icon: <svg viewBox="0 0 24 24" width="22" height="22" {...stroke}><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>,
    title: 'Easy Returns',
    sub: '7-day no-questions policy',
  },
  {
    icon: <svg viewBox="0 0 24 24" width="22" height="22" {...stroke}><path d="M18 20V10M12 20V4M6 20v-6"/></svg>,
    title: 'Quality Assured',
    sub: 'Curated for discerning taste',
  },
];

function Stars({ rating }: { rating: number }) {
  return (
    <span style={{ color: 'var(--av-gold)', fontSize: 12, letterSpacing: 2 }}>
      {'★'.repeat(Math.floor(rating))}
      {rating % 1 >= 0.5 ? '½' : ''}
      {'☆'.repeat(5 - Math.ceil(rating))}
    </span>
  );
}

function AvProductCard({ product }: { product: ProductCard }) {
  const fmt = usePrice();
  const [hovered, setHovered] = useState(false);
  const addItem  = useCartStore(s => s.addItem);
  const openCart = useCartStore(s => s.openCart);
  const toggleWishlist = useWishlistStore(s => s.toggle);
  const inWishlist     = useWishlistStore(s => s.has(product.id));

  const discount = product.compare_at_price
    ? Math.round((1 - product.price / product.compare_at_price) * 100)
    : null;

  function handleAddToCart(e: React.MouseEvent) {
    e.preventDefault();
    if (product.has_variants) { router.visit(`/shop/${product.slug}`); return; }
    addItem({
      product_id: product.id,
      variant_id: null,
      name: product.name,
      variant_label: null,
      price: product.price,
      image_url: product.image_url ?? null,
      slug: product.slug,
      shipping_included: false,
    });
    openCart();
  }

  function handleWishlist(e: React.MouseEvent) {
    e.preventDefault();
    toggleWishlist({ id: product.id, slug: product.slug, name: product.name, price: product.price, compare_at_price: product.compare_at_price, image_url: product.image_url, category: product.category_name ?? null });
  }

  return (
    <Link href={`/shop/${product.slug}`}
      style={{ textDecoration: 'none', color: 'inherit', display: 'block' }}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}>
      {/* Image */}
      <div style={{ aspectRatio: '4/5', position: 'relative', overflow: 'hidden', background: 'var(--av-paper-2)', marginBottom: 14 }}>
        {product.image_url ? (
          <img src={product.image_url} alt={product.name} loading="lazy"
            style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block', transition: 'transform .55s cubic-bezier(.2,.7,.3,1)', transform: hovered ? 'scale(1.04)' : 'scale(1)' }} />
        ) : (
          <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center' }}>
            <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="var(--av-muted)" strokeWidth="1">
              <path strokeLinecap="round" strokeLinejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
        )}

        {/* Top badge */}
        {product.is_featured && !discount && (
          <span style={{ position: 'absolute', top: 12, left: 12, background: 'var(--av-ink)', color: 'var(--av-paper)', fontSize: 9.5, letterSpacing: '0.18em', textTransform: 'uppercase', padding: '5px 10px', fontFamily: 'var(--av-sans)' }}>
            Curated
          </span>
        )}
        {discount && discount > 0 && (
          <span style={{ position: 'absolute', top: 12, left: 12, background: 'var(--av-cognac)', color: '#fff', fontSize: 9.5, letterSpacing: '0.18em', textTransform: 'uppercase', padding: '5px 10px' }}>
            -{discount}%
          </span>
        )}

        {/* Wishlist */}
        <button onClick={handleWishlist}
          style={{ position: 'absolute', top: 10, right: 10, width: 34, height: 34, borderRadius: '50%', background: 'rgba(251,248,241,.88)', border: '1px solid var(--av-line)', color: inWishlist ? 'var(--av-cognac)' : 'var(--av-ink)', display: 'grid', placeItems: 'center', cursor: 'pointer', backdropFilter: 'blur(4px)', opacity: hovered ? 1 : 0, transition: 'opacity .2s' }}
          title="Wishlist">
          <svg viewBox="0 0 24 24" width="15" height="15" fill={inWishlist ? 'currentColor' : 'none'} stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
          </svg>
        </button>

        {/* OOS overlay */}
        {!product.in_stock && (
          <div style={{ position: 'absolute', inset: 0, background: 'rgba(244,239,229,.72)', display: 'grid', placeItems: 'center' }}>
            <span style={{ fontSize: 10, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>Sold out</span>
          </div>
        )}

        {/* Quick-add strip */}
        {product.in_stock && (
          <button onClick={handleAddToCart}
            style={{ position: 'absolute', bottom: 0, left: 0, right: 0, background: 'var(--av-ink)', color: 'var(--av-paper)', padding: '14px 16px', fontSize: 11, letterSpacing: '0.2em', textTransform: 'uppercase', border: 'none', cursor: 'pointer', fontFamily: 'var(--av-sans)', fontWeight: 500, opacity: hovered ? 1 : 0, transform: hovered ? 'translateY(0)' : 'translateY(14px)', transition: 'opacity .25s, transform .25s' }}>
            Quick Add
          </button>
        )}
      </div>

      {/* Info */}
      <div>
        {product.category_name && (
          <div style={{ fontSize: 10, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 5, fontFamily: 'var(--av-sans)' }}>
            {product.category_name}
          </div>
        )}
        <div style={{ fontFamily: 'var(--av-display)', fontSize: 16, fontWeight: 400, lineHeight: 1.2, color: 'var(--av-ink)', marginBottom: 6, letterSpacing: '-0.01em' }}>
          {product.name}
        </div>
        {product.avg_rating !== null && product.avg_rating > 0 && product.reviews_count > 0 && (
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 5 }}>
            <Stars rating={product.avg_rating} />
            <span style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>({product.reviews_count})</span>
          </div>
        )}
        <div style={{ display: 'flex', alignItems: 'baseline', gap: 8 }}>
          <span style={{ fontSize: 14, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>{fmt(product.price)}</span>
          {product.compare_at_price && (
            <span style={{ fontSize: 12.5, color: 'var(--av-muted)', textDecoration: 'line-through', fontFamily: 'var(--av-sans)' }}>{fmt(product.compare_at_price)}</span>
          )}
        </div>
      </div>
    </Link>
  );
}

function AvBlogCard({ post }: { post: BlogPost }) {
  const [hovered, setHovered] = useState(false);
  return (
    <Link href={`/blog/${post.slug}`} style={{ textDecoration: 'none', color: 'inherit', display: 'block' }}
      onMouseEnter={() => setHovered(true)} onMouseLeave={() => setHovered(false)}>
      {/* Cover */}
      <div style={{ aspectRatio: '3/2', overflow: 'hidden', background: 'var(--av-paper-2)', marginBottom: 16 }}>
        {post.cover_url ? (
          <img src={post.cover_url} alt={post.title} loading="lazy"
            style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block', transition: 'transform .55s cubic-bezier(.2,.7,.3,1)', transform: hovered ? 'scale(1.04)' : 'scale(1)' }} />
        ) : (
          <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center' }}>
            <span style={{ fontFamily: 'var(--av-display)', fontSize: 48, fontWeight: 300, color: 'var(--av-line)', letterSpacing: '-0.04em' }}>
              {post.title.slice(0, 1)}
            </span>
          </div>
        )}
      </div>
      {post.category_name && (
        <div style={{ fontSize: 10, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 8 }}>
          {post.category_name}
        </div>
      )}
      <h3 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(17px,2vw,21px)', fontWeight: 400, lineHeight: 1.2, margin: '0 0 8px', color: 'var(--av-ink)', letterSpacing: '-0.01em' }}>
        {post.title}
      </h3>
      {post.excerpt && (
        <p style={{ fontSize: 13.5, color: 'var(--av-muted)', margin: '0 0 12px', lineHeight: 1.6, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden', fontFamily: 'var(--av-sans)' }}>
          {post.excerpt}
        </p>
      )}
      <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
        <span>{post.author_display_name}</span>
        {post.published_at && <><span style={{ opacity: 0.5 }}>·</span><span>{post.published_at}</span></>}
        {post.read_time && <><span style={{ opacity: 0.5 }}>·</span><span>{post.read_time}</span></>}
      </div>
    </Link>
  );
}

function Eyebrow({ children }: { children: React.ReactNode }) {
  return (
    <div style={{ fontSize: 10.5, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.34em', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 14 }}>
      {children}
    </div>
  );
}

function SectionHead({ title, sub, href, light }: { title: string; sub?: string; href?: string; light?: boolean }) {
  const color = light ? 'var(--av-paper)' : 'var(--av-ink)';
  const mutedColor = light ? 'rgba(244,239,229,.6)' : 'var(--av-muted)';
  return (
    <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: 36, flexWrap: 'wrap', gap: 12 }}>
      <div>
        <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(26px,3vw,38px)', fontWeight: 400, margin: '0 0 8px', color, letterSpacing: '-0.012em', lineHeight: 1.04 }}>
          {title}
        </h2>
        {sub && <p style={{ margin: 0, color: mutedColor, fontSize: 14, fontFamily: 'var(--av-sans)', lineHeight: 1.6 }}>{sub}</p>}
      </div>
      {href && (
        <Link href={href} style={{ fontSize: 11, letterSpacing: '0.2em', textTransform: 'uppercase', color: light ? 'var(--av-paper)' : 'var(--av-ink)', textDecoration: 'none', borderBottom: `1px solid ${light ? 'rgba(244,239,229,.4)' : 'var(--av-line)'}`, paddingBottom: 2, fontFamily: 'var(--av-sans)', fontWeight: 500, flexShrink: 0 }}>
          View all
        </Link>
      )}
    </div>
  );
}

export default function Home({ latestPosts, shopCategories, featuredProducts, newArrivals, brands, heroTitle, heroSub, heroImageUrl }: Props) {
  const { site } = usePage<SharedProps>().props;
  const [trackQ, setTrackQ] = useState('');
  const [heroLoaded, setHeroLoaded] = useState(false);
  const revealRefs = useRef<(HTMLElement | null)[]>([]);

  const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };

  useEffect(() => {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          (e.target as HTMLElement).style.transform = 'none';
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    revealRefs.current.forEach(el => { if (el) obs.observe(el); });
    return () => obs.disconnect();
  }, []);

  function addReveal(el: HTMLElement | null, i: number) {
    revealRefs.current[i] = el;
    if (el) {
      el.style.transition = `transform .85s cubic-bezier(.2,.7,.3,1) ${i * 60}ms`;
      el.style.transform = 'translateY(22px)';
    }
  }

  function handleTrack(e: React.FormEvent) {
    e.preventDefault();
    if (trackQ.trim()) router.visit(`/track?order=${encodeURIComponent(trackQ.trim())}`);
  }

  let revealIdx = 0;

  return (
    <Layout>
      <Head title={site.name} />

      {/* ══ HERO ══════════════════════════════════════════════════════════════ */}
      <div style={{ position: 'relative', width: '100%', height: 'clamp(480px, 72vh, 760px)', overflow: 'hidden', background: 'var(--av-ink)' }}>
        {heroImageUrl ? (
          <img src={heroImageUrl} alt={heroTitle} loading="eager" onLoad={() => setHeroLoaded(true)}
            style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover', display: 'block', opacity: heroLoaded ? 1 : 0, transition: 'opacity .5s' }} />
        ) : (
          <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(135deg, var(--av-ink) 0%, var(--av-ink-soft) 100%)' }} />
        )}
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(90deg, rgba(20,16,12,.72) 0%, rgba(20,16,12,.04) 72%)' }} />

        <div style={{ ...W, position: 'relative', zIndex: 2, height: '100%', display: 'flex', alignItems: 'center', gap: 'clamp(32px,6vw,80px)', paddingBlock: 'clamp(40px,8vh,80px)' }}>

          {/* Left: text */}
          <div style={{ flex: 1, minWidth: 0 }}>
            <Eyebrow>New Collection</Eyebrow>
            <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(36px,5.5vw,80px)', fontWeight: 400, color: 'var(--av-paper)', margin: '0 0 20px', lineHeight: 1.0, letterSpacing: '-0.018em' }}>
              {heroTitle || 'Crafted for the\nDiscerning Few'}
            </h1>
            {heroSub && (
              <p style={{ fontFamily: 'var(--av-sans)', fontSize: 'clamp(14px,1.4vw,17px)', color: 'rgba(244,239,229,.72)', margin: '0 0 32px', maxWidth: 480, lineHeight: 1.65 }}>
                {heroSub}
              </p>
            )}
            <div style={{ display: 'flex', gap: 14, flexWrap: 'wrap' }}>
              <Link href="/shop"
                style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '14px 32px', background: 'var(--av-paper)', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', textDecoration: 'none', borderRadius: 2, transition: 'background .2s' }}>
                Shop Collection
              </Link>
              <Link href="/shop?sort=newest"
                style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '14px 32px', border: '1px solid rgba(244,239,229,.34)', color: 'var(--av-paper)', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', textDecoration: 'none', borderRadius: 2, background: 'transparent', transition: 'border-color .2s' }}>
                New Arrivals
              </Link>
            </div>
          </div>

          {/* Right: image placeholder */}
          <div className="av-hero-img-placeholder" style={{ flexShrink: 0, width: 'clamp(200px,32%,420px)', aspectRatio: '3/4', border: '1px dashed rgba(244,239,229,.22)', borderRadius: 4, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 12, color: 'rgba(244,239,229,.28)' }}>
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>
            </svg>
            <span style={{ fontFamily: 'var(--av-sans)', fontSize: 11, letterSpacing: '0.16em', textTransform: 'uppercase' }}>Hero Image</span>
          </div>

        </div>
      </div>

      {/* ══ VALUE PROPS ════════════════════════════════════════════════════════ */}
      <div style={{ borderBottom: '1px solid var(--av-line)', overflow: 'hidden' }}>
        <div style={{ ...W, display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)' }} className="av-vp-grid">
          {VALUE_PROPS.map((vp, i) => (
            <div key={i} style={{ padding: 'clamp(22px,4vw,32px) 24px', display: 'flex', alignItems: 'center', gap: 14, borderRight: i < 3 ? '1px solid var(--av-line-soft)' : 'none' }}>
              <span style={{ color: 'var(--av-cognac)', flexShrink: 0 }}>{vp.icon}</span>
              <div>
                <div style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', marginBottom: 2 }}>{vp.title}</div>
                <div style={{ fontSize: 12, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{vp.sub}</div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* ══ FEATURED PRODUCTS ══════════════════════════════════════════════════ */}
      {site.showFeaturedProducts !== false && featuredProducts.length > 0 && (
        <section style={{ padding: 'clamp(56px,10vw,96px) 0' }}>
          <div style={W}>
            <div style={{ textAlign: 'center', marginBottom: 48 }}>
              <Eyebrow>Curated Selection</Eyebrow>
              <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(28px,4vw,48px)', fontWeight: 400, margin: '0 0 10px', color: 'var(--av-ink)', letterSpacing: '-0.012em', lineHeight: 1.04 }}>
                Featured Pieces
              </h2>
              <p style={{ color: 'var(--av-muted)', fontSize: 14.5, fontFamily: 'var(--av-sans)', maxWidth: 400, margin: '0 auto' }}>
                Hand-picked for quality, craft, and lasting appeal.
              </p>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 28 }} className="av-prod-grid">
              {featuredProducts.map(p => <AvProductCard key={p.id} product={p} />)}
            </div>
            <div style={{ textAlign: 'center', marginTop: 44 }}>
              <Link href="/shop?sort=featured" style={{ display: 'inline-flex', alignItems: 'center', gap: 10, fontSize: 11.5, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--av-ink)', textDecoration: 'none', borderBottom: '1px solid var(--av-line)', paddingBottom: 3, fontFamily: 'var(--av-sans)', fontWeight: 500 }}>
                View All Featured
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* ══ EDITORIAL SPLIT ════════════════════════════════════════════════════ */}
      {site.showCategories !== false && shopCategories.length > 0 && (
        <section style={{ padding: 'clamp(40px,8vw,72px) 0', background: 'var(--av-paper)' }}>
          <div style={{ ...W, display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 'clamp(28px,5vw,60px)', alignItems: 'center' }} className="av-split-grid">
            <div style={{ aspectRatio: '3/4', background: 'var(--av-paper-2)', overflow: 'hidden' }}>
              {heroImageUrl && (
                <img src={heroImageUrl} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
              )}
            </div>
            <div>
              <Eyebrow>Shop by Category</Eyebrow>
              <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(26px,3.5vw,48px)', fontWeight: 400, margin: '0 0 20px', color: 'var(--av-ink)', lineHeight: 1.04, letterSpacing: '-0.012em' }}>
                Explore the collection
              </h2>
              <p style={{ color: 'var(--av-muted)', fontSize: 14.5, fontFamily: 'var(--av-sans)', lineHeight: 1.7, margin: '0 0 32px', maxWidth: 380 }}>
                Each category is carefully curated to offer pieces that stand the test of time.
              </p>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 0, borderTop: '1px solid var(--av-line)' }}>
                {shopCategories.slice(0, 5).map(cat => (
                  <Link key={cat.id} href={`/shop/c/${cat.slug}`}
                    style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 0', borderBottom: '1px solid var(--av-line-soft)', textDecoration: 'none', color: 'var(--av-ink)', fontSize: 14, fontFamily: 'var(--av-sans)', letterSpacing: '0.02em', transition: 'color .18s' }}
                    onMouseEnter={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-cognac)'; }}
                    onMouseLeave={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-ink)'; }}>
                    {cat.name}
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                  </Link>
                ))}
              </div>
            </div>
          </div>
        </section>
      )}

      {/* ══ DARK BAND / CLEARANCE ═══════════════════════════════════════════════ */}
      {site.showSaleBanner !== false && (
      <section style={{ background: 'var(--av-ink)', padding: 'clamp(56px,10vw,96px) 0', position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', pointerEvents: 'none', overflow: 'hidden' }}>
          <span style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(100px,20vw,220px)', fontWeight: 600, color: 'rgba(244,239,229,.045)', letterSpacing: '-0.04em', userSelect: 'none', whiteSpace: 'nowrap', fontStyle: 'italic' }}>
            Sale
          </span>
        </div>
        <div style={{ ...W, position: 'relative', zIndex: 1, textAlign: 'center' }}>
          <Eyebrow>Limited Time</Eyebrow>
          <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(30px,5vw,64px)', fontWeight: 400, color: 'var(--av-paper)', margin: '0 0 18px', lineHeight: 1.04, letterSpacing: '-0.018em', fontStyle: 'italic' }}>
            Shop the season's finest
          </h2>
          <p style={{ color: 'rgba(244,239,229,.62)', fontFamily: 'var(--av-sans)', fontSize: 15, maxWidth: 460, margin: '0 auto 36px', lineHeight: 1.65 }}>
            Select pieces marked down. Quality unchanged.
          </p>
          <Link href="/shop?sale=1"
            style={{ display: 'inline-flex', alignItems: 'center', gap: 10, padding: '14px 40px', border: '1px solid rgba(244,239,229,.34)', color: 'var(--av-paper)', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.22em', textTransform: 'uppercase', textDecoration: 'none', borderRadius: 2, background: 'transparent', transition: 'border-color .2s' }}>
            View Sale Items
          </Link>
        </div>
      </section>
      )}

      {/* ══ NEW ARRIVALS ════════════════════════════════════════════════════════ */}
      {site.showNewArrivals !== false && newArrivals.length > 0 && (
        <section style={{ padding: 'clamp(56px,10vw,96px) 0' }}>
          <div style={W}>
            <SectionHead title="New Arrivals" sub="Just landed in the collection." href="/shop?sort=newest" />
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 28 }} className="av-prod-grid">
              {newArrivals.map(p => <AvProductCard key={p.id} product={p} />)}
            </div>
          </div>
        </section>
      )}

      {/* ══ BRANDS STRIP ════════════════════════════════════════════════════════ */}
      {site.showBrands !== false && brands.length > 0 && (
        <div style={{ borderTop: '1px solid var(--av-line)', borderBottom: '1px solid var(--av-line)', background: 'var(--av-paper)' }}>
          <div style={{ ...W, display: 'flex', justifyContent: 'center', gap: 'clamp(24px,5vw,64px)', padding: 'clamp(24px,4vw,36px) var(--av-gutter)', flexWrap: 'wrap', alignItems: 'center' }}>
            {brands.slice(0, 6).map(brand => (
              <Link key={brand.id} href={`/shop?brand=${brand.slug}`}
                style={{ fontSize: 12, letterSpacing: '0.26em', textTransform: 'uppercase', color: 'var(--av-muted)', textDecoration: 'none', fontFamily: 'var(--av-sans)', fontWeight: 500, transition: 'color .2s' }}
                onMouseEnter={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-ink)'; }}
                onMouseLeave={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-muted)'; }}>
                {brand.name}
              </Link>
            ))}
          </div>
        </div>
      )}

      {/* ══ BLOG / JOURNAL ══════════════════════════════════════════════════════ */}
      {site.showBlog !== false && latestPosts.length > 0 && (
        <section style={{ padding: 'clamp(56px,10vw,96px) 0', background: 'var(--av-paper)' }}>
          <div style={W}>
            <SectionHead title="From the Journal" sub="Essays, guides, and notes on craft." href="/blog" />
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 40 }} className="av-blog-grid">
              {latestPosts.map(post => <AvBlogCard key={post.id} post={post} />)}
            </div>
          </div>
        </section>
      )}

      {/* ══ TRACK ORDER ═════════════════════════════════════════════════════════ */}
      {site.showTrackOrder !== false && (
      <section style={{ padding: 'clamp(40px,8vw,72px) 0', background: 'var(--av-ivory)' }}>
        <div style={{ ...W, textAlign: 'center' }}>
          <Eyebrow>Your Order</Eyebrow>
          <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(24px,3vw,38px)', fontWeight: 400, margin: '0 0 10px', color: 'var(--av-ink)', letterSpacing: '-0.012em' }}>
            Track a shipment
          </h2>
          <p style={{ color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 14.5, margin: '0 0 30px', lineHeight: 1.6 }}>
            Enter your order number to check live delivery status.
          </p>
          <form onSubmit={handleTrack} style={{ display: 'flex', gap: 10, maxWidth: 480, margin: '0 auto', flexWrap: 'wrap' }}>
            <input type="text" value={trackQ} onChange={e => setTrackQ(e.target.value)}
              placeholder="e.g. KLX-10248" required
              style={{ flex: 1, minWidth: 200, border: '1px solid var(--av-line)', background: 'var(--av-paper)', padding: '13px 16px', fontSize: 14, color: 'var(--av-ink)', outline: 'none', borderRadius: 2, fontFamily: 'var(--av-sans)', transition: 'border-color .2s' }}
              onFocus={e => { (e.currentTarget as HTMLElement).style.borderColor = 'var(--av-ink)'; }}
              onBlur={e => { (e.currentTarget as HTMLElement).style.borderColor = 'var(--av-line)'; }}
            />
            <button type="submit"
              style={{ padding: '13px 30px', background: 'var(--av-ink)', color: 'var(--av-paper)', border: 'none', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', borderRadius: 2, cursor: 'pointer', flexShrink: 0 }}>
              Track
            </button>
          </form>
        </div>
      </section>
      )}

      <style>{`
        @media(max-width:920px){.av-prod-grid{grid-template-columns:1fr 1fr!important;gap:20px!important}}
        @media(max-width:600px){.av-prod-grid{grid-template-columns:1fr 1fr!important;gap:14px!important}}
        @media(max-width:860px){.av-vp-grid{grid-template-columns:1fr 1fr!important}}
        @media(max-width:860px){.av-split-grid{grid-template-columns:1fr!important}}
        @media(max-width:760px){.av-blog-grid{grid-template-columns:1fr!important;gap:32px!important}}
      `}</style>
    </Layout>
  );
}
