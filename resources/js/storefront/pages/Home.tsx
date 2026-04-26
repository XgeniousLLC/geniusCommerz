import { Head, Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useCartStore } from '../store/cartStore';
import Layout from '../layouts/Layout';
import type { BlogPost, ProductCard, SharedProps, ShopCategory } from '../types';

interface Props {
  latestPosts: BlogPost[];
  shopCategories: ShopCategory[];
  featuredProducts: ProductCard[];
  newArrivals: ProductCard[];
  heroTitle: string;
  heroSub: string;
}

/* ── Geometric triangle pattern overlay ─────────────────────────────── */
function TrianglePattern({ color = 'rgba(255,255,255,0.07)' }: { color?: string }) {
  return (
    <svg className="absolute inset-0 w-full h-full pointer-events-none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="tri" x="0" y="0" width="80" height="80" patternUnits="userSpaceOnUse">
          <polygon points="40,4 76,76 4,76" fill={color}/>
          <polygon points="4,4 76,4 40,72" fill={color} opacity="0.5"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#tri)"/>
    </svg>
  );
}

/* ── Slide definitions ───────────────────────────────────────────────── */
interface SlideData {
  bg: string;
  patternColor: string;
  textDark: boolean;
  eyebrow: string;
  titleLines: string[];
  sub: string;
  ctaLabel: string;
  ctaHref: string;
  ctaStyle: React.CSSProperties;
  badge?: string;
  badgeStyle?: React.CSSProperties;
  icon: React.ReactNode;
}

function buildSlides(heroTitle: string, heroSub: string, siteName: string): SlideData[] {
  return [
    {
      bg: 'linear-gradient(135deg,#b2f5ea 0%,#c6f6d5 40%,#fefcbf 100%)',
      patternColor: 'rgba(20,120,100,0.07)',
      textDark: true,
      eyebrow: '🛍️ Welcome',
      titleLines: heroTitle ? [heroTitle] : ['Shop Smart,', 'Save More'],
      sub: heroSub || 'Thousands of quality products with fast delivery across Bangladesh.',
      ctaLabel: 'Shop Now',
      ctaHref: '/shop',
      ctaStyle: { background: '#1F3A8A', color: '#fff' },
      badge: '4.8 ★',
      badgeStyle: { background: '#1F3A8A', color: '#fff' },
      icon: (
        <svg viewBox="0 0 120 120" className="w-full h-full" fill="none">
          <circle cx="60" cy="60" r="58" fill="rgba(31,58,138,0.08)"/>
          <path d="M30 45h60l-8 40H38L30 45z" stroke="#1F3A8A" strokeWidth="4" fill="rgba(31,58,138,0.12)" strokeLinejoin="round"/>
          <path d="M45 45c0-8.28 6.72-15 15-15s15 6.72 15 15" stroke="#1F3A8A" strokeWidth="4" strokeLinecap="round"/>
          <circle cx="48" cy="87" r="5" fill="#1F3A8A"/>
          <circle cx="72" cy="87" r="5" fill="#1F3A8A"/>
          <path d="M44 62l8 8 16-16" stroke="#16a34a" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
      ),
    },
    {
      bg: 'linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 55%,#3b82f6 100%)',
      patternColor: 'rgba(255,255,255,0.06)',
      textDark: false,
      eyebrow: '⚡ Flash Sale',
      titleLines: ['Up to 40% Off', 'Selected Items'],
      sub: "Limited time deals on featured products — don't miss out!",
      ctaLabel: 'View Deals',
      ctaHref: '/shop?sort=featured',
      ctaStyle: { background: '#f97316', color: '#fff' },
      badge: 'HOT 🔥',
      badgeStyle: { background: '#f97316', color: '#fff' },
      icon: (
        <svg viewBox="0 0 120 120" className="w-full h-full" fill="none">
          <circle cx="60" cy="60" r="58" fill="rgba(255,255,255,0.08)"/>
          <circle cx="60" cy="60" r="42" stroke="rgba(255,255,255,0.2)" strokeWidth="2" strokeDasharray="6 4"/>
          <text x="60" y="52" textAnchor="middle" fill="#fff" fontSize="11" fontWeight="600">UP TO</text>
          <text x="60" y="82" textAnchor="middle" fill="#fbbf24" fontSize="38" fontWeight="900">40%</text>
          <text x="60" y="98" textAnchor="middle" fill="rgba(255,255,255,0.8)" fontSize="11" fontWeight="600">OFF</text>
        </svg>
      ),
    },
    {
      bg: 'linear-gradient(135deg,#7c1d1d 0%,#dc2626 55%,#f97316 100%)',
      patternColor: 'rgba(255,255,255,0.06)',
      textDark: false,
      eyebrow: '🚚 Free Delivery',
      titleLines: ['Free Shipping', 'Over ৳999'],
      sub: 'Fast & reliable delivery across Bangladesh on every qualifying order.',
      ctaLabel: 'Explore Now',
      ctaHref: '/shop',
      ctaStyle: { background: '#fff', color: '#dc2626' },
      badge: 'FREE',
      badgeStyle: { background: '#fff', color: '#dc2626' },
      icon: (
        <svg viewBox="0 0 120 120" className="w-full h-full" fill="none">
          <circle cx="60" cy="60" r="58" fill="rgba(255,255,255,0.08)"/>
          <rect x="10" y="42" width="72" height="44" rx="6" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.4)" strokeWidth="2"/>
          <path d="M82 58h18l10 18v10H82V58z" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.4)" strokeWidth="2"/>
          <circle cx="30" cy="90" r="8" fill="#fff" stroke="rgba(220,38,38,0.5)" strokeWidth="2"/>
          <circle cx="90" cy="90" r="8" fill="#fff" stroke="rgba(220,38,38,0.5)" strokeWidth="2"/>
          <path d="M22 68h56" stroke="rgba(255,255,255,0.5)" strokeWidth="1.5" strokeDasharray="4 3"/>
        </svg>
      ),
    },
  ];
}

/* ── Product card for grids ──────────────────────────────────────────── */
function ProductCardItem({ product }: { product: ProductCard }) {
  const openCart = useCartStore(s => s.openCart);
  const addItem  = useCartStore(s => s.addItem);
  const discount = product.compare_at_price
    ? Math.round((1 - product.price / product.compare_at_price) * 100)
    : null;

  function handleAddToCart(e: React.MouseEvent) {
    e.preventDefault();
    if (product.has_variants) { router.visit(`/shop/${product.slug}`); return; }
    addItem({ product_id: product.id, variant_id: null, name: product.name, variant_label: null, price: product.price, image_url: product.image_url ?? null, slug: product.slug, shipping_included: false });
    openCart();
  }

  return (
    <Link href={`/shop/${product.slug}`} className="group block" style={{ textDecoration: 'none' }}>
      <div className="kb-card overflow-hidden" style={{ borderRadius: 12 }}>
        <div className="relative overflow-hidden" style={{ aspectRatio: '1/1', background: '#f3f4f6' }}>
          {product.image_url
            ? <img src={product.image_url} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy"/>
            : <div className="w-full h-full flex items-center justify-center">
                <svg className="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
          }
          {discount !== null && discount > 0 && (
            <span className="absolute top-1.5 left-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white" style={{ background: '#ef4444' }}>-{discount}%</span>
          )}
          {!product.in_stock && (
            <div className="absolute inset-0 flex items-center justify-center" style={{ background: 'rgba(255,255,255,.75)' }}>
              <span className="text-xs font-semibold text-gray-500 bg-white px-2 py-1 rounded-full shadow-sm">Out of stock</span>
            </div>
          )}
          {product.in_stock && (
            <button onClick={handleAddToCart}
              className="absolute bottom-2 right-2 w-8 h-8 rounded-full flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition-all translate-y-1 group-hover:translate-y-0"
              style={{ background: 'var(--kb-primary)', color: '#fff' }} title="Add to cart">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4"/></svg>
            </button>
          )}
        </div>
        <div className="p-2.5">
          <p className="text-xs font-medium line-clamp-2 leading-snug" style={{ color: 'var(--kb-ink)' }}>{product.name}</p>
          <div className="mt-1.5 flex items-center gap-1.5">
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

function SectionHeader({ title, href }: { title: string; href: string }) {
  return (
    <div className="flex items-center justify-between mb-4">
      <h2 className="text-lg font-extrabold md:text-2xl" style={{ color: 'var(--kb-ink)' }}>{title}</h2>
      <Link href={href} className="text-xs font-semibold hover:underline flex items-center gap-1" style={{ color: 'var(--kb-primary)' }}>
        View all <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7"/></svg>
      </Link>
    </div>
  );
}

/* ═══════════════════════════════════════════════════════════════════════
   MAIN COMPONENT
══════════════════════════════════════════════════════════════════════ */
export default function Home({ latestPosts, shopCategories, featuredProducts, newArrivals, heroTitle, heroSub }: Props) {
  const { site } = usePage<SharedProps>().props;
  const [searchQ, setSearchQ]     = useState('');
  const [activeSlide, setActiveSlide] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const SLIDES = buildSlides(heroTitle, heroSub, site.name);

  const startTimer = useCallback(() => {
    if (timerRef.current) clearInterval(timerRef.current);
    timerRef.current = setInterval(() => setActiveSlide(s => (s + 1) % SLIDES.length), 5000);
  }, [SLIDES.length]);

  useEffect(() => {
    startTimer();
    return () => { if (timerRef.current) clearInterval(timerRef.current); };
  }, [startTimer]);

  const goTo = (i: number) => { setActiveSlide(i); startTimer(); };
  const prev = () => goTo((activeSlide - 1 + SLIDES.length) % SLIDES.length);
  const next = () => goTo((activeSlide + 1) % SLIDES.length);

  function handleSearch(e: React.FormEvent) {
    e.preventDefault();
    if (searchQ.trim()) router.visit(`/shop?q=${encodeURIComponent(searchQ.trim())}`);
  }

  const slide = SLIDES[activeSlide];

  return (
    <Layout>
      <Head title={site.name} />

      {/* ══════════════════════════════════════════════════════════════════
          HERO: CATEGORY SIDEBAR + BANNER SLIDER
      ══════════════════════════════════════════════════════════════════ */}
      <section style={{ background: '#f8fafc', borderBottom: '1px solid var(--kb-border)' }}>
        <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-3 sm:py-4">

          <div className="flex rounded-2xl overflow-hidden shadow-md" style={{ minHeight: 380 }}>

            {/* ── Category Sidebar (desktop only) ───────────────────── */}
            {site.showCategories !== false && <div className="hidden lg:flex flex-col shrink-0 bg-white border-r border-gray-100" style={{ width: 240 }}>

              {/* Header */}
              <div className="flex items-center gap-3 px-4 py-3.5 font-bold text-sm uppercase tracking-widest text-white select-none"
                style={{ background: 'var(--kb-primary)' }}>
                <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Categories
              </div>

              {/* Category list */}
              <ul className="flex-1 overflow-y-auto py-1">
                {shopCategories.length === 0 && (
                  <li>
                    <Link href="/shop" className="flex items-center justify-between px-4 py-2.5 text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                      All Products
                      <svg className="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/></svg>
                    </Link>
                  </li>
                )}
                {shopCategories.map(cat => (
                  <li key={cat.id}>
                    <Link href={`/shop/c/${cat.slug}`}
                      className="flex items-center justify-between px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors border-b border-gray-50">
                      <span className="flex items-center gap-2.5">
                        <svg className="w-3.5 h-3.5 shrink-0" style={{ color: 'var(--kb-primary)', opacity: 0.5 }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {cat.name}
                      </span>
                      <svg className="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/>
                      </svg>
                    </Link>
                  </li>
                ))}
              </ul>

              {/* Footer link */}
              <Link href="/shop"
                className="flex items-center justify-center gap-1.5 px-4 py-3 text-xs font-semibold border-t transition-colors hover:bg-blue-50"
                style={{ color: 'var(--kb-primary)', borderColor: 'var(--kb-border)' }}>
                View All Products
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7"/></svg>
              </Link>
            </div>}

            {/* ── Banner Slider ──────────────────────────────────────── */}
            <div className="flex-1 relative overflow-hidden" style={{ minHeight: 380 }}>

              {SLIDES.map((s, i) => (
                <div key={i}
                  className="absolute inset-0 transition-all duration-700 ease-in-out"
                  style={{
                    background: s.bg,
                    opacity: i === activeSlide ? 1 : 0,
                    transform: i === activeSlide ? 'translateX(0)' : i < activeSlide ? 'translateX(-6%)' : 'translateX(6%)',
                    pointerEvents: i === activeSlide ? 'auto' : 'none',
                  }}>

                  {/* Geometric pattern */}
                  <TrianglePattern color={s.patternColor}/>

                  {/* Slide content */}
                  <div className="relative h-full flex items-center px-6 sm:px-10 md:px-14 gap-4">

                    {/* Left: text + CTA */}
                    <div className="flex-1 min-w-0 py-8">
                      {/* Eyebrow */}
                      <span className="inline-block text-xs font-bold px-3 py-1 rounded-full mb-3"
                        style={s.textDark
                          ? { background: 'rgba(31,58,138,0.12)', color: '#1F3A8A' }
                          : { background: 'rgba(255,255,255,0.2)', color: '#fff' }}>
                        {s.eyebrow}
                      </span>

                      {/* Title */}
                      <h1 className="font-extrabold leading-none mb-3" style={{ color: s.textDark ? '#0f172a' : '#fff', fontSize: 'clamp(1.7rem,4vw,3rem)' }}>
                        {s.titleLines.map((line, li) => (
                          <span key={li} className="block">{line}</span>
                        ))}
                      </h1>

                      {/* Subtitle */}
                      <p className="text-sm max-w-sm mb-5 leading-relaxed" style={{ color: s.textDark ? '#475569' : 'rgba(255,255,255,0.82)' }}>
                        {s.sub}
                      </p>

                      {/* Search (slide 0 only) */}
                      {i === 0 && (
                        <form onSubmit={handleSearch} className="flex gap-2 max-w-sm mb-5">
                          <div className="relative flex-1">
                            <svg className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" style={{ color: '#94a3b8' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input value={searchQ} onChange={e => setSearchQ(e.target.value)}
                              className="w-full rounded-xl pl-9 pr-3 py-2.5 text-sm outline-none border"
                              style={{ background: 'rgba(255,255,255,0.9)', borderColor: 'rgba(255,255,255,0.5)' }}
                              placeholder="Search products, brands…"/>
                          </div>
                          <button type="submit" className="kb-btn text-sm font-semibold px-4" style={{ background: '#1F3A8A', color: '#fff', borderRadius: 12 }}>
                            Go
                          </button>
                        </form>
                      )}

                      {/* CTA */}
                      <div className="flex flex-wrap items-center gap-3">
                        <Link href={s.ctaHref}
                          className="kb-btn font-bold px-6 py-2.5 text-sm"
                          style={{ ...s.ctaStyle, borderRadius: 999 }}>
                          {s.ctaLabel} →
                        </Link>
                        {s.badge && (
                          <span className="text-xs font-bold px-3 py-1.5 rounded-full shadow-sm" style={s.badgeStyle}>
                            {s.badge}
                          </span>
                        )}
                      </div>
                    </div>

                    {/* Right: decorative SVG icon */}
                    <div className="hidden sm:block shrink-0" style={{ width: 140, height: 140, opacity: 0.92 }}>
                      {s.icon}
                    </div>
                  </div>
                </div>
              ))}

              {/* ── Prev / Next arrows ── */}
              <button onClick={prev}
                className="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center transition-all hover:scale-110"
                style={{ background: 'rgba(255,255,255,0.25)', backdropFilter: 'blur(4px)' }}>
                <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M15 19l-7-7 7-7"/></svg>
              </button>
              <button onClick={next}
                className="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full flex items-center justify-center transition-all hover:scale-110"
                style={{ background: 'rgba(255,255,255,0.25)', backdropFilter: 'blur(4px)' }}>
                <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7"/></svg>
              </button>

              {/* ── Dot navigation ── */}
              <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-1.5">
                {SLIDES.map((_, i) => (
                  <button key={i} onClick={() => goTo(i)}
                    className="rounded-full transition-all duration-300"
                    style={{
                      width: i === activeSlide ? 22 : 8,
                      height: 8,
                      background: i === activeSlide ? '#fff' : 'rgba(255,255,255,0.45)',
                    }}/>
                ))}
              </div>

              {/* ── Progress bar ── */}
              <div className="absolute bottom-0 left-0 right-0 h-0.5" style={{ background: 'rgba(255,255,255,0.15)' }}>
                <div key={activeSlide} className="h-full" style={{ background: 'rgba(255,255,255,0.6)', animation: 'slideProgress 5s linear' }}/>
              </div>
            </div>

          </div>{/* end flex */}

          {/* ── Mobile: category pills below banner ──────────────────── */}
          {site.showCategories !== false && <div className="lg:hidden mt-3 -mx-3 sm:-mx-4 px-3 sm:px-4 overflow-x-auto">
            <div className="flex gap-2 pb-1 min-w-max">
              <Link href="/shop"
                className="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold whitespace-nowrap border transition-colors hover:bg-blue-50"
                style={{ background: 'var(--kb-primary)', color: '#fff', border: 'none' }}>
                All Products
              </Link>
              {shopCategories.map(cat => (
                <Link key={cat.id} href={`/shop/c/${cat.slug}`}
                  className="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold whitespace-nowrap border transition-colors hover:bg-blue-50 hover:text-blue-700 bg-white"
                  style={{ color: 'var(--kb-ink)', borderColor: 'var(--kb-border)' }}>
                  {cat.name}
                </Link>
              ))}
            </div>
          </div>}

        </div>
      </section>

      {/* progress bar animation */}
      <style>{`@keyframes slideProgress { from{width:0} to{width:100%} }`}</style>

      {/* ═══ TRUST BADGES ═══════════════════════════════════════════════════ */}
      <section style={{ background: '#fff', borderBottom: '1px solid var(--kb-border)' }}>
        <div className="max-w-7xl mx-auto px-4 lg:px-6">
          <div className="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0" style={{ borderColor: 'var(--kb-border)' } as React.CSSProperties}>
            {[
              { icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', label: 'Fast Delivery', sub: '2-3 business days' },
              { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', label: 'Secure Payment', sub: '100% protected' },
              { icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', label: 'Easy Returns', sub: '7-day return policy' },
              { icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', label: '24/7 Support', sub: 'Always here for you' },
            ].map(b => (
              <div key={b.label} className="flex items-center gap-3 px-4 py-4">
                <div className="w-9 h-9 rounded-full flex items-center justify-center shrink-0" style={{ background: 'var(--kb-primary-50)' }}>
                  <svg className="w-4 h-4" style={{ color: 'var(--kb-primary)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={b.icon}/>
                  </svg>
                </div>
                <div>
                  <p className="text-xs font-semibold" style={{ color: 'var(--kb-ink)' }}>{b.label}</p>
                  <p className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>{b.sub}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ═══ FEATURED PRODUCTS ═══════════════════════════════════════════════ */}
      {site.showFeaturedProducts !== false && featuredProducts.length > 0 && (
        <section style={{ background: 'var(--kb-primary-50)' }}>
          <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-8 lg:py-12">
            <SectionHeader title="⚡ Featured Products" href="/shop?sort=featured"/>
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 lg:gap-4">
              {featuredProducts.map(p => <ProductCardItem key={p.id} product={p}/>)}
            </div>
          </div>
        </section>
      )}

      {/* ═══ PROMOTIONAL BANNERS ═════════════════════════════════════════════ */}
      <section className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-6">
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <Link href="/shop?sort=newest" className="block rounded-2xl overflow-hidden p-5 md:p-7 relative group" style={{ background: 'linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%)', minHeight: 130, textDecoration: 'none' }}>
            <p className="text-[10px] font-bold uppercase tracking-widest" style={{ color: 'rgba(255,255,255,.6)' }}>New Season</p>
            <h3 className="text-lg md:text-xl font-extrabold text-white mt-1 leading-tight">Fresh Arrivals<br/>Just Dropped</h3>
            <span className="mt-3 inline-flex items-center gap-1 text-xs font-semibold" style={{ color: '#fbbf24' }}>
              Shop Now <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7"/></svg>
            </span>
            <svg className="absolute right-4 top-1/2 -translate-y-1/2 w-16 h-16 opacity-10 group-hover:scale-110 transition-transform text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M5 3l14 9-14 9V3z"/></svg>
          </Link>
          <Link href="/shop" className="block rounded-2xl overflow-hidden p-5 md:p-7 relative group" style={{ background: 'linear-gradient(135deg,#f97316 0%,#fb923c 100%)', minHeight: 130, textDecoration: 'none' }}>
            <p className="text-[10px] font-bold uppercase tracking-widest" style={{ color: 'rgba(255,255,255,.6)' }}>Limited Time</p>
            <h3 className="text-lg md:text-xl font-extrabold text-white mt-1 leading-tight">Free Shipping<br/>Over ৳999</h3>
            <span className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-white">
              Explore <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7"/></svg>
            </span>
            <svg className="absolute right-4 top-1/2 -translate-y-1/2 w-16 h-16 opacity-10 group-hover:scale-110 transition-transform text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
          </Link>
        </div>
      </section>

      {/* ═══ NEW ARRIVALS ════════════════════════════════════════════════════ */}
      {site.showNewArrivals !== false && newArrivals.length > 0 && (
        <section className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pb-8 lg:pb-12">
          <SectionHeader title="🆕 New Arrivals" href="/shop?sort=newest"/>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 lg:gap-4">
            {newArrivals.map(p => <ProductCardItem key={p.id} product={p}/>)}
          </div>
          <div className="mt-6 text-center">
            <Link href="/shop" className="kb-btn kb-btn-ghost kb-btn-md font-semibold" style={{ borderRadius: 12 }}>
              View All Products →
            </Link>
          </div>
        </section>
      )}

      {/* ═══ BLOG POSTS ══════════════════════════════════════════════════════ */}
      {site.showBlog !== false && latestPosts.length > 0 && (
        <section style={{ background: '#F8FAFF' }}>
          <div className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-8 lg:py-12">
            <SectionHeader title="From the Blog" href="/blog"/>
            <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
              {latestPosts.map(post => (
                <Link key={post.id} href={`/blog/${post.slug}`} className="kb-card overflow-hidden block group" style={{ textDecoration: 'none' }}>
                  <div className="overflow-hidden" style={{ aspectRatio: '16/9' }}>
                    {post.cover_url
                      ? <img className="w-full h-full object-cover group-hover:scale-105 transition duration-300" src={post.cover_url} alt={post.title} loading="lazy"/>
                      : <div className="w-full h-full" style={{ background: 'var(--kb-primary-50)', minHeight: 140 }}/>
                    }
                  </div>
                  <div className="p-4">
                    {post.category_name && <span className="kb-chip kb-chip-new text-xs">{post.category_name}</span>}
                    <h3 className="text-sm font-bold mt-2 leading-snug line-clamp-2" style={{ color: 'var(--kb-ink)' }}>{post.title}</h3>
                    <p className="text-xs mt-1.5" style={{ color: 'var(--kb-ink-soft)' }}>{post.published_at} · {post.read_time}</p>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* ═══ TRACK ORDER CTA ═════════════════════════════════════════════════ */}
      <section className="max-w-7xl mx-auto px-4 lg:px-6 py-8 lg:py-12">
        <div className="kb-card p-6 md:p-10 flex flex-col md:flex-row items-center justify-between gap-4"
          style={{ background: 'linear-gradient(135deg,var(--kb-primary-50) 0%,#fff 100%)' }}>
          <div>
            <h2 className="text-xl font-extrabold mb-1" style={{ color: 'var(--kb-ink)' }}>Track Your Order</h2>
            <p className="text-sm" style={{ color: 'var(--kb-ink-muted)' }}>Enter your order number to see real-time delivery status.</p>
          </div>
          <Link href="/track" className="kb-btn kb-btn-primary kb-btn-md flex-shrink-0 font-semibold w-full md:w-auto text-center" style={{ borderRadius: 12 }}>
            Track Order →
          </Link>
        </div>
      </section>
    </Layout>
  );
}
