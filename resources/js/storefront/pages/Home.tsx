import { Head, Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
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

interface SlideData {
  kicker: string;
  title: string;
  sub: string;
  cta: string;
  ctaHref: string;
  cta2: string;
  stat: string;
  hue1: string;
  hue2: string;
}

const BASE_SLIDES: SlideData[] = [
  {
    kicker: 'Hot Deal',
    title: 'Up to 40% off selected items',
    sub: 'Limited time. Hand-picked from electronics, fashion, and home.',
    cta: 'Shop deals',
    ctaHref: '/shop',
    cta2: 'View all',
    stat: '40%',
    hue1: '#0B1F4F',
    hue2: '#1B3380',
  },
  {
    kicker: 'New Arrivals',
    title: 'Fresh styles, just landed',
    sub: 'The first drops of the new season — straight from our editors.',
    cta: 'Browse new in',
    ctaHref: '/shop?sort=newest',
    cta2: 'Lookbook',
    stat: 'NEW',
    hue1: '#0E2A55',
    hue2: '#264E94',
  },
  {
    kicker: 'Free Shipping',
    title: 'Free delivery over ৳999',
    sub: 'Country-wide. No coupon needed at checkout.',
    cta: 'Shop now',
    ctaHref: '/shop',
    cta2: 'Track order',
    stat: '৳999',
    hue1: '#102B6B',
    hue2: '#1A47AC',
  },
];

const VALUE_PROPS = [
  {
    icon: (
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
      </svg>
    ),
    title: 'Fast Delivery',
    sub: 'Same-day in select cities',
  },
  {
    icon: (
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
    ),
    title: 'Secure Payment',
    sub: 'Trusted by 10k+ shoppers',
  },
  {
    icon: (
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
      </svg>
    ),
    title: 'Easy Returns',
    sub: '7-day no-questions return',
  },
  {
    icon: (
      <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
      </svg>
    ),
    title: '24/7 Support',
    sub: 'Always here, always fast',
  },
];

function Stars({ rating }: { rating: number }) {
  const stars: string[] = [];
  for (let i = 1; i <= 5; i++) {
    if (rating >= i) stars.push('★');
    else if (rating >= i - 0.5) stars.push('½');
    else stars.push('☆');
  }
  return (
    <span style={{ color: '#E8A317', fontSize: 12, letterSpacing: 1 }}>
      {stars.join('')}
    </span>
  );
}

function ProductCardItem({ product }: { product: ProductCard }) {
  const fmt = usePrice();
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
    toggleWishlist({
      id: product.id,
      slug: product.slug,
      name: product.name,
      price: product.price,
      compare_at_price: product.compare_at_price,
      image_url: product.image_url,
      category: product.category_name ?? null,
    });
  }

  const showDiscount = discount !== null && discount > 0;
  const showFeatured = product.is_featured && !showDiscount;

  return (
    <Link href={`/shop/${product.slug}`} className="group block" style={{ textDecoration: 'none' }}>
      <div className="group-hover:border-[#D4D9E3] group-hover:shadow-[0_1px_2px_rgba(14,19,32,.06),0_8px_24px_rgba(14,19,32,.06)]"
        style={{ background: '#fff', border: '1px solid #E6E8EE', borderRadius: 12, overflow: 'hidden', display: 'flex', flexDirection: 'column', transition: 'border-color .15s, box-shadow .15s', position: 'relative' }}>

        {/* ── Image area ── */}
        <div style={{ aspectRatio: '1/1', position: 'relative', overflow: 'hidden' }}>
          {product.image_url ? (
            <img
              src={product.image_url}
              alt={product.name}
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              loading="lazy"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center"
              style={{ background: 'linear-gradient(135deg, #F2F4F9 0%, #E9ECF3 100%)' }}>
              <svg width="40" height="40" fill="none" stroke="#97A0B5" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          )}

          {/* Badge */}
          {showDiscount && (
            <div style={{ position: 'absolute', top: 8, left: 8 }}>
              <span style={{ background: '#C43030', color: '#fff', fontSize: 11, fontWeight: 700, padding: '3px 8px', borderRadius: 4 }}>
                -{discount}%
              </span>
            </div>
          )}
          {showFeatured && (
            <div style={{ position: 'absolute', top: 8, left: 8 }}>
              <span style={{ background: '#fff', color: '#0E1320', border: '1px solid #E6E8EE', fontSize: 11, fontWeight: 700, padding: '3px 8px', borderRadius: 4 }}>
                Featured
              </span>
            </div>
          )}

          {/* Wishlist quick action */}
          <div className="opacity-0 group-hover:opacity-100 translate-x-1 group-hover:translate-x-0 transition-all duration-150"
            style={{ position: 'absolute', top: 8, right: 8 }}>
            <button onClick={handleWishlist}
              style={{ width: 32, height: 32, borderRadius: 999, background: '#fff', border: '1px solid #E6E8EE', color: inWishlist ? '#C43030' : '#2A3142', display: 'grid', placeItems: 'center', boxShadow: '0 1px 2px rgba(14,19,32,.04)', cursor: 'pointer' }}
              title="Add to wishlist">
              <svg width="15" height="15" fill={inWishlist ? 'currentColor' : 'none'} stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </button>
          </div>

          {/* OOS overlay */}
          {!product.in_stock && (
            <div style={{ position: 'absolute', inset: 0, background: 'rgba(255,255,255,.7)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <span style={{ background: 'rgba(14,19,32,.7)', color: '#fff', fontSize: 11, fontWeight: 700, padding: '3px 8px', borderRadius: 4 }}>
                Out of Stock
              </span>
            </div>
          )}
        </div>

        {/* ── Info ── */}
        <div style={{ padding: 12, display: 'flex', flexDirection: 'column', gap: 5, flex: 1 }}>
          {product.category_name && (
            <span style={{ fontSize: 11, color: '#5A6478', textTransform: 'uppercase', letterSpacing: '0.06em', fontWeight: 600 }}>
              {product.category_name}
            </span>
          )}
          <div style={{ fontSize: 13, fontWeight: 600, color: '#0E1320', lineHeight: 1.35, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden', minHeight: '2.7em' }}>
            {product.name}
          </div>
          {product.avg_rating !== null && product.avg_rating > 0 && (
            <div style={{ display: 'flex', alignItems: 'center', gap: 4, fontSize: 12, color: '#5A6478' }}>
              <Stars rating={product.avg_rating} />
              <span>{product.avg_rating}</span>
              {product.reviews_count > 0 && <span>({product.reviews_count})</span>}
            </div>
          )}
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 6, marginTop: 2 }}>
            <span style={{ fontWeight: 700, color: '#0E1320', fontSize: 14 }}>{fmt(product.price)}</span>
            {product.compare_at_price && (
              <span style={{ color: '#5A6478', textDecoration: 'line-through', fontSize: 12 }}>{fmt(product.compare_at_price)}</span>
            )}
          </div>
          <span style={{ fontSize: 11, fontWeight: 600, color: product.in_stock ? '#0F8A5F' : '#C43030' }}>
            {product.in_stock ? 'In stock' : 'Out of stock'}
          </span>
          {product.in_stock && (
            <button onClick={handleAddToCart}
              className="hover:bg-[#0B1F4F] hover:text-white hover:border-[#0B1F4F] hover:-translate-y-px hover:shadow-[0_6px_16px_rgba(11,31,79,.18)]"
              style={{ marginTop: 4, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, height: 32, padding: '0 12px', borderRadius: 6, fontSize: 13, fontWeight: 600, border: '1px solid #E6E8EE', background: '#fff', color: '#0E1320', transition: 'background .15s, color .15s, transform .12s, box-shadow .15s', cursor: 'pointer', width: '100%' }}>
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Add to cart
            </button>
          )}
        </div>
      </div>
    </Link>
  );
}

function SectionHead({ title, sub, href }: { title: string; sub?: string; href: string }) {
  return (
    <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: 16 }}>
      <div>
        <h2 style={{ fontSize: 20, fontWeight: 700, letterSpacing: '-0.01em', margin: 0, color: '#0E1320' }}>{title}</h2>
        {sub && <div style={{ color: '#5A6478', fontSize: 13, marginTop: 4 }}>{sub}</div>}
      </div>
      <Link href={href} style={{ fontSize: 13, color: '#0B1F4F', fontWeight: 600, display: 'inline-flex', alignItems: 'center', gap: 4, textDecoration: 'none' }}
        className="hover:underline">
        View all
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/>
        </svg>
      </Link>
    </div>
  );
}

function BlogCardItem({ post }: { post: BlogPost }) {
  const coverColors = [
    ['#DDE4F4', '#EEF2FB'],
    ['#F1ECE3', '#E6E0D2'],
    ['#E6E0F4', '#F4F0FB'],
    ['#E0EBE3', '#F1F6F2'],
    ['#F4E0E0', '#FBF1F1'],
  ];
  const idx = post.id % coverColors.length;
  const [c1, c2] = coverColors[idx];

  return (
    <Link href={`/blog/${post.slug}`} className="group block" style={{ textDecoration: 'none' }}>
      <div className="group-hover:border-[#D4D9E3] group-hover:shadow-[0_1px_2px_rgba(14,19,32,.06),0_8px_24px_rgba(14,19,32,.06)]"
        style={{ background: '#fff', border: '1px solid #E6E8EE', borderRadius: 12, overflow: 'hidden', transition: 'border-color .15s, box-shadow .15s' }}>
        {/* Cover */}
        <div style={{ aspectRatio: '16/10', position: 'relative', overflow: 'hidden' }}>
          {post.cover_url ? (
            <img src={post.cover_url} alt={post.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
          ) : (
            <div style={{ width: '100%', height: '100%', background: `linear-gradient(135deg, ${c1}, ${c2})`, display: 'grid', placeItems: 'center' }}>
              <span style={{ fontSize: 32, fontWeight: 800, letterSpacing: '-0.02em', color: 'rgba(14,19,32,.4)' }}>
                {(post.category_name ?? post.title).slice(0, 2).toUpperCase()}
              </span>
            </div>
          )}
        </div>
        {/* Body */}
        <div style={{ padding: 16 }}>
          {post.category_name && (
            <span style={{ display: 'inline-block', background: '#EEF2FB', color: '#0B1F4F', padding: '3px 8px', borderRadius: 4, fontSize: 11, fontWeight: 700, letterSpacing: '0.04em', textTransform: 'uppercase' }}>
              {post.category_name}
            </span>
          )}
          <h3 style={{ margin: '8px 0 6px', fontSize: 15, fontWeight: 700, letterSpacing: '-0.01em', lineHeight: 1.3, color: '#0E1320' }}
            className="line-clamp-2">
            {post.title}
          </h3>
          {post.excerpt && (
            <p className="line-clamp-2" style={{ color: '#5A6478', fontSize: 13, margin: 0, lineHeight: 1.5 }}>
              {post.excerpt}
            </p>
          )}
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 12, fontSize: 12, color: '#5A6478' }}>
            <div style={{ width: 24, height: 24, borderRadius: '50%', background: '#0B1F4F', color: '#fff', display: 'grid', placeItems: 'center', fontSize: 11, fontWeight: 700, flexShrink: 0 }}>
              {post.author_display_name.slice(0, 1).toUpperCase()}
            </div>
            <span>{post.author_display_name}</span>
            {post.published_at && <><span>·</span><span>{post.published_at}</span></>}
            {post.read_time && <><span>·</span><span>{post.read_time}</span></>}
          </div>
        </div>
      </div>
    </Link>
  );
}

export default function Home({ latestPosts, shopCategories, featuredProducts, newArrivals, brands, heroTitle, heroSub, heroImageUrl }: Props) {
  const { site } = usePage<SharedProps>().props;
  const [activeSlide, setActiveSlide] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const [trackQ, setTrackQ] = useState('');

  const slides = BASE_SLIDES.map((s, i) =>
    i === 0 ? { ...s, title: heroTitle || s.title, sub: heroSub || s.sub } : s
  );

  const startTimer = useCallback(() => {
    if (timerRef.current) clearInterval(timerRef.current);
    timerRef.current = setInterval(() => setActiveSlide(s => (s + 1) % slides.length), 5000);
  }, [slides.length]);

  useEffect(() => {
    startTimer();
    return () => { if (timerRef.current) clearInterval(timerRef.current); };
  }, [startTimer]);

  const goTo = (i: number) => { setActiveSlide(i); startTimer(); };
  const prev = () => goTo((activeSlide - 1 + slides.length) % slides.length);
  const next = () => goTo((activeSlide + 1) % slides.length);

  function handleTrack(e: React.FormEvent) {
    e.preventDefault();
    if (trackQ.trim()) router.visit(`/track?order=${encodeURIComponent(trackQ.trim())}`);
  }

  const C = 'max-w-[1280px] mx-auto px-3 sm:px-6';

  return (
    <Layout>
      <Head title={site.name} />

      {/* ══ HERO ═══════════════════════════════════════════════════════════ */}
      <div className="pt-2 sm:pt-4">
        <div className={C}>

          {/* Grid: sidebar + slider */}
          <div className="grid gap-4 lg:grid-cols-[240px_1fr]">

            {/* ── Category sidebar ── */}
            {site.showCategories !== false && (
              <aside style={{ background: '#fff', border: '1px solid #E6E8EE', borderRadius: 12, overflow: 'hidden', flexDirection: 'column' }}
                className="hidden lg:flex">
                <div style={{ padding: '14px 16px', background: '#0B1F4F', color: '#fff', fontSize: 13, fontWeight: 700, letterSpacing: '0.04em', textTransform: 'uppercase', display: 'flex', alignItems: 'center', gap: 8 }}>
                  <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M4 6h16M4 12h16M4 18h16"/>
                  </svg>
                  All Categories
                </div>
                <ul style={{ listStyle: 'none', padding: 0, margin: 0, flex: 1 }}>
                  {shopCategories.map(cat => (
                    <li key={cat.id}>
                      <Link href={`/shop/c/${cat.slug}`}
                        className="group/cat hover:bg-[#EEF2FB] hover:text-[#0B1F4F]"
                        style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '11px 16px', fontSize: 13, color: '#2A3142', fontWeight: 500, borderBottom: '1px solid #EFF1F5', textDecoration: 'none', transition: 'background .1s, color .1s' }}>
                        {cat.name}
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/>
                        </svg>
                      </Link>
                    </li>
                  ))}
                  <li>
                    <Link href="/shop?sale=1"
                      className="hover:bg-[#EEF2FB]"
                      style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '11px 16px', fontSize: 13, color: '#C43030', fontWeight: 600, borderBottom: '1px solid #EFF1F5', textDecoration: 'none', transition: 'background .1s' }}>
                      Today's Deals
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/>
                      </svg>
                    </Link>
                  </li>
                  <li>
                    <Link href="/shop?sort=newest"
                      className="hover:bg-[#EEF2FB] hover:text-[#0B1F4F]"
                      style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '11px 16px', fontSize: 13, color: '#2A3142', fontWeight: 500, textDecoration: 'none', transition: 'background .1s, color .1s' }}>
                      New Arrivals
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/>
                      </svg>
                    </Link>
                  </li>
                </ul>
              </aside>
            )}

            {/* ── Hero slider ── */}
            <div
              className="h-[200px] sm:h-[280px] lg:h-[360px]"
              style={{ position: 'relative', borderRadius: 12, overflow: 'hidden', background: '#0B1F4F', gridColumn: site.showCategories === false ? '1 / -1' : undefined }}
              onMouseEnter={() => { if (timerRef.current) clearInterval(timerRef.current); }}
              onMouseLeave={() => startTimer()}>

              {slides.map((s, i) => {
                const isImageSlide = i === 0 && !!heroImageUrl;
                return (
                <div key={i}
                  className={isImageSlide ? '' : 'flex flex-col justify-center px-5 py-5 sm:grid sm:items-center sm:px-10 sm:py-8 lg:px-12 lg:py-10'}
                  style={{
                    position: 'absolute', inset: 0,
                    gridTemplateColumns: '1.6fr 1fr',
                    color: '#fff',
                    opacity: i === activeSlide ? 1 : 0,
                    transition: 'opacity .4s ease',
                    pointerEvents: i === activeSlide ? 'auto' : 'none',
                    background: isImageSlide ? undefined : `radial-gradient(circle at 75% 50%, rgba(255,255,255,.12), transparent 60%), linear-gradient(135deg, ${s.hue1} 0%, ${s.hue2} 100%)`,
                  }}>

                  {isImageSlide ? (
                    /* ── Image banner mode ── */
                    <img
                      src={heroImageUrl!}
                      alt={s.title}
                      style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}
                    />
                  ) : (
                    <>
                      {/* Left: text */}
                      <div style={{ position: 'relative', zIndex: 1 }}>
                        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6, background: 'rgba(255,255,255,.16)', border: '1px solid rgba(255,255,255,.2)', padding: '4px 10px', borderRadius: 999, fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', marginBottom: 10 }}>
                          {s.kicker}
                        </span>
                        <h1 className="text-[18px] sm:text-[30px] lg:text-[40px] mb-2 sm:mb-3" style={{ fontWeight: 800, letterSpacing: '-0.02em', lineHeight: 1.1, margin: 0 }}>
                          {s.title}
                        </h1>
                        <p className="hidden sm:block text-sm sm:text-[15px]" style={{ opacity: 0.9, maxWidth: 420, margin: '0 0 22px' }}>
                          {s.sub}
                        </p>
                        <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginTop: 15 }}>
                          <Link href={s.ctaHref}
                            className="hover:bg-[#F2F4F9] h-[28px] sm:h-[36px] px-[10px] sm:px-[14px] text-[12px] sm:text-[13px]"
                            style={{ display: 'inline-flex', alignItems: 'center', borderRadius: 8, fontWeight: 600, background: '#fff', color: '#0B1F4F', textDecoration: 'none', transition: 'background .15s' }}>
                            {s.cta}
                          </Link>
                          <Link href={s.ctaHref === '/shop' ? '/shop?sort=newest' : '/track'}
                            className="hidden sm:inline-flex hover:border-white"
                            style={{ alignItems: 'center', height: 36, padding: '0 14px', borderRadius: 8, fontWeight: 600, fontSize: 13, background: 'transparent', color: '#fff', border: '1px solid rgba(255,255,255,.4)', textDecoration: 'none', transition: 'border-color .15s' }}>
                            {s.cta2}
                          </Link>
                        </div>
                      </div>

                      {/* Right: stat bubble — hidden on mobile */}
                      <div className="hidden sm:grid" style={{ justifySelf: 'end', width: 140, height: 140, borderRadius: '50%', background: 'rgba(255,255,255,.1)', border: '1px solid rgba(255,255,255,.18)', placeItems: 'center', fontSize: s.stat.length > 3 ? 28 : 44, fontWeight: 800, letterSpacing: '-0.03em', backdropFilter: 'blur(10px)', position: 'relative', zIndex: 1 }}>
                        {s.stat}
                      </div>
                    </>
                  )}
                </div>
                );
              })}

              {/* Arrows */}
              <button onClick={prev}
                style={{ position: 'absolute', top: '50%', left: 16, transform: 'translateY(-50%)', width: 36, height: 36, borderRadius: '50%', background: 'rgba(255,255,255,.16)', border: '1px solid rgba(255,255,255,.2)', color: '#fff', placeItems: 'center', cursor: 'pointer', backdropFilter: 'blur(10px)', zIndex: 5 }}
                className="hidden sm:grid hover:bg-[rgba(255,255,255,.28)]">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <button onClick={next}
                style={{ position: 'absolute', top: '50%', right: 16, transform: 'translateY(-50%)', width: 36, height: 36, borderRadius: '50%', background: 'rgba(255,255,255,.16)', border: '1px solid rgba(255,255,255,.2)', color: '#fff', placeItems: 'center', cursor: 'pointer', backdropFilter: 'blur(10px)', zIndex: 5 }}
                className="hidden sm:grid hover:bg-[rgba(255,255,255,.28)]">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/>
                </svg>
              </button>

              {/* Dots */}
              <div className="flex" style={{ position: 'absolute', bottom: 16, left: '50%', transform: 'translateX(-50%)', gap: 6, zIndex: 5 }}>
                {slides.map((_, i) => (
                  <button key={i} onClick={() => goTo(i)}
                    style={{ height: 4, borderRadius: 2, border: 'none', cursor: 'pointer', background: i === activeSlide ? '#fff' : 'rgba(255,255,255,.4)', width: i === activeSlide ? 32 : 24, transition: 'background .2s, width .2s' }}/>
                ))}
              </div>
            </div>
          </div>

          {/* Mobile category pills */}
          {site.showCategories !== false && (
            <div className="hidden">
              <div style={{ display: 'flex', gap: 8, paddingBottom: 4, minWidth: 'max-content' }}>
                <Link href="/shop"
                  style={{ display: 'inline-flex', alignItems: 'center', borderRadius: 999, padding: '6px 14px', fontSize: 12, fontWeight: 600, background: '#0B1F4F', color: '#fff', textDecoration: 'none', whiteSpace: 'nowrap' }}>
                  All Products
                </Link>
                {shopCategories.map(cat => (
                  <Link key={cat.id} href={`/shop/c/${cat.slug}`}
                    style={{ display: 'inline-flex', alignItems: 'center', borderRadius: 999, padding: '6px 14px', fontSize: 12, fontWeight: 600, background: '#fff', color: '#2A3142', border: '1px solid #E6E8EE', textDecoration: 'none', whiteSpace: 'nowrap' }}
                    className="hover:bg-[#EEF2FB] hover:text-[#0B1F4F]">
                    {cat.name}
                  </Link>
                ))}
              </div>
            </div>
          )}

          {/* ── Value props ── */}
          <div className="grid grid-cols-2 md:grid-cols-4 mt-3 sm:mt-6"
            style={{ gap: 1, background: '#E6E8EE', border: '1px solid #E6E8EE', borderRadius: 12, overflow: 'hidden' }}>
            {VALUE_PROPS.map((vp, i) => (
              <div key={i} className="p-3 sm:p-5" style={{ display: 'flex', alignItems: 'center', gap: 10, background: '#fff' }}>
                <div className="hidden sm:grid" style={{ width: 38, height: 38, borderRadius: 10, background: '#EEF2FB', color: '#0B1F4F', placeItems: 'center', flexShrink: 0 }}>
                  {vp.icon}
                </div>
                <div>
                  <div style={{ fontWeight: 700, fontSize: 13, color: '#0E1320' }}>{vp.title}</div>
                  <div className="hidden sm:block" style={{ color: '#5A6478', fontSize: 12 }}>{vp.sub}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* ══ FEATURED PRODUCTS ══════════════════════════════════════════════ */}
      {site.showFeaturedProducts !== false && featuredProducts.length > 0 && (
        <section className="py-5 sm:py-8">
          <div className={C}>
            <SectionHead title="Featured Products" sub="Hand-picked by our editors this week." href="/shop?sort=featured" />
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
              {featuredProducts.map(p => <ProductCardItem key={p.id} product={p} />)}
            </div>
          </div>
        </section>
      )}

      {/* ══ PROMO STRIP ════════════════════════════════════════════════════ */}
      <div className={C}>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
          <Link href="/shop?sort=newest"
            className="p-5 sm:p-[28px_32px]"
            style={{ borderRadius: 12, color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'space-between', minHeight: 140, overflow: 'hidden', position: 'relative', background: 'linear-gradient(135deg, #0E1320, #2A3142)', textDecoration: 'none' }}>
            <div style={{ position: 'relative', zIndex: 1 }}>
              <span style={{ display: 'inline-block', background: 'rgba(255,255,255,.16)', padding: '3px 10px', borderRadius: 999, fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', marginBottom: 10 }}>
                New season
              </span>
              <h3 className="text-[18px] sm:text-[22px]" style={{ margin: '0 0 10px', fontWeight: 800, letterSpacing: '-0.01em', lineHeight: 1.15 }}>
                Fresh arrivals,<br/>just dropped
              </h3>
              <span style={{ color: '#fff', fontWeight: 600, fontSize: 13, display: 'inline-flex', alignItems: 'center', gap: 4 }}>
                Shop now →
              </span>
            </div>
            <div style={{ width: 100, height: 100, borderRadius: '50%', background: 'rgba(255,255,255,.08)', border: '1px solid rgba(255,255,255,.14)', flexShrink: 0 }}/>
          </Link>
          <Link href="/shop"
            className="p-5 sm:p-[28px_32px]"
            style={{ borderRadius: 12, color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'space-between', minHeight: 140, overflow: 'hidden', position: 'relative', background: 'linear-gradient(135deg, #0B1F4F, #102B6B)', textDecoration: 'none' }}>
            <div style={{ position: 'relative', zIndex: 1 }}>
              <span style={{ display: 'inline-block', background: 'rgba(255,255,255,.16)', padding: '3px 10px', borderRadius: 999, fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', marginBottom: 10 }}>
                Limited time
              </span>
              <h3 className="text-[18px] sm:text-[22px]" style={{ margin: '0 0 10px', fontWeight: 800, letterSpacing: '-0.01em', lineHeight: 1.15 }}>
                Free shipping<br/>over ৳999
              </h3>
              <span style={{ color: '#fff', fontWeight: 600, fontSize: 13, display: 'inline-flex', alignItems: 'center', gap: 4 }}>
                Rules apply →
              </span>
            </div>
            <div style={{ width: 100, height: 100, borderRadius: '50%', background: 'rgba(255,255,255,.08)', border: '1px solid rgba(255,255,255,.14)', flexShrink: 0 }}/>
          </Link>
        </div>
      </div>

      {/* ══ NEW ARRIVALS ═══════════════════════════════════════════════════ */}
      {site.showNewArrivals !== false && newArrivals.length > 0 && (
        <section className="py-4 sm:py-7">
          <div className={C}>
            <SectionHead title="New Arrivals" sub="Just landed in the store." href="/shop?sort=newest" />
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
              {newArrivals.map(p => <ProductCardItem key={p.id} product={p} />)}
            </div>
          </div>
        </section>
      )}

      {/* ══ BRANDS STRIP ═══════════════════════════════════════════════════ */}
      {brands.length > 0 && (
        <div className={C} style={{ marginTop: 24 }}>
          <div className="grid grid-cols-3 sm:grid-cols-5" style={{ border: '1px solid #E6E8EE', borderRadius: 12, overflow: 'hidden', background: '#E6E8EE', gap: 1 }}>
            {brands.slice(0, 5).map((brand) => (
              <Link key={brand.id} href={`/shop?brand=${brand.slug}`}
                style={{ padding: '12px 8px', textAlign: 'center', background: '#fff', color: '#5A6478', fontWeight: 700, fontSize: 13, letterSpacing: '0.02em', textDecoration: 'none', transition: 'color .15s, background .15s' }}
                className="hover:text-[#0B1F4F] hover:bg-[#EEF2FB]">
                {brand.name}
              </Link>
            ))}
          </div>
        </div>
      )}

      {/* ══ BLOG TEASER ════════════════════════════════════════════════════ */}
      {site.showBlog !== false && latestPosts.length > 0 && (
        <section className="py-5 sm:py-8">
          <div className={C}>
            <SectionHead title="From the Journal" sub="Buying guides, reviews, and shopping wisdom." href="/blog" />
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {latestPosts.map(post => <BlogCardItem key={post.id} post={post} />)}
            </div>
          </div>
        </section>
      )}

      {/* ══ TRACK ORDER BANNER ═════════════════════════════════════════════ */}
      <div className={C} style={{ paddingBottom: 40 }}>
        <div className="p-4 sm:p-[24px_32px]" style={{ background: 'linear-gradient(135deg, #EEF2FB, #F7F8FB)', border: '1px solid #E6E8EE', borderRadius: 12, display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap' }}>
          <div>
            <h3 style={{ margin: 0, fontSize: 18, fontWeight: 700, letterSpacing: '-0.01em', color: '#0E1320' }}>Track your order</h3>
            <p style={{ margin: '4px 0 0', color: '#5A6478', fontSize: 13 }}>Enter your order number to see live delivery status.</p>
          </div>
          <form onSubmit={handleTrack} style={{ display: 'flex', gap: 8, flex: '0 0 480px', maxWidth: '100%' }}>
            <input
              type="text"
              value={trackQ}
              onChange={e => setTrackQ(e.target.value)}
              placeholder="e.g. KLX-10248"
              required
              style={{ flex: 1, height: 44, padding: '0 14px', border: '1px solid #E6E8EE', background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14, color: '#0E1320', transition: 'border-color .15s, box-shadow .15s' }}
              onFocus={e => { e.currentTarget.style.borderColor = '#0B1F4F'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(11,31,79,.12)'; }}
              onBlur={e => { e.currentTarget.style.borderColor = '#E6E8EE'; e.currentTarget.style.boxShadow = 'none'; }}
            />
            <button type="submit"
              style={{ height: 44, padding: '0 20px', borderRadius: 8, background: '#0B1F4F', color: '#fff', fontWeight: 600, fontSize: 14, border: 'none', cursor: 'pointer', transition: 'background .15s', flexShrink: 0 }}
              className="hover:bg-[#102B6B]">
              Track
            </button>
          </form>
        </div>
      </div>

    </Layout>
  );
}
