import { Head, Link, usePage } from '@inertiajs/react';
import Layout from '../layouts/Layout';
import type { BlogPost, SharedProps, ShopCategory } from '../types';

interface Props {
  latestPosts: BlogPost[];
  shopCategories: ShopCategory[];
  heroTitle: string;
  heroSub: string;
}

const TRUST_BADGES = [
  { icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4', label: 'Fast Delivery', sub: '2-3 business days' },
  { icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', label: 'Secure Payment', sub: '100% protected' },
  { icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', label: 'Easy Returns', sub: '7-day return policy' },
  { icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', label: '24/7 Support', sub: 'Always here for you' },
];

export default function Home({ latestPosts, shopCategories, heroTitle, heroSub }: Props) {
  const { site } = usePage<SharedProps>().props;

  return (
    <Layout>
      <Head title={site.name} />

      {/* Hero */}
      <section style={{ background: 'linear-gradient(135deg,var(--kb-primary) 0%,#2649B8 100%)', color: '#fff' }}>
        <div className="max-w-7xl mx-auto px-4 lg:px-6 py-16 md:py-24 flex flex-col items-center text-center">
          <span className="kb-chip mb-5" style={{ background: 'rgba(255,255,255,.18)', color: '#fff', fontSize: '0.8rem' }}>
            Free delivery on orders over ৳999
          </span>
          <h1 className="text-4xl md:text-6xl font-extrabold leading-tight max-w-3xl">{heroTitle || site.name}</h1>
          {heroSub && <p className="mt-4 text-lg md:text-xl max-w-xl" style={{ color: 'rgba(255,255,255,.85)' }}>{heroSub}</p>}
          <div className="mt-8 flex flex-wrap justify-center gap-3">
            <Link href="/shop" className="kb-btn kb-btn-accent kb-btn-lg" style={{ borderRadius: 12 }}>
              Shop Now
            </Link>
            <Link href="/track" className="kb-btn kb-btn-md" style={{ background: 'rgba(255,255,255,.15)', color: '#fff', border: '1px solid rgba(255,255,255,.3)', borderRadius: 12 }}>
              Track Order
            </Link>
          </div>
        </div>
      </section>

      {/* Trust badges */}
      <section style={{ background: '#fff', borderBottom: '1px solid var(--kb-border)' }}>
        <div className="max-w-7xl mx-auto px-4 lg:px-6 py-5">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center text-sm" style={{ color: 'var(--kb-ink-muted)' }}>
            {TRUST_BADGES.map((b) => (
              <div key={b.label} className="flex flex-col items-center gap-1.5 py-2">
                <div className="w-10 h-10 rounded-full flex items-center justify-center" style={{ background: 'var(--kb-primary-50)' }}>
                  <svg className="w-5 h-5" style={{ color: 'var(--kb-primary)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={b.icon} />
                  </svg>
                </div>
                <p className="font-semibold text-xs" style={{ color: 'var(--kb-ink)' }}>{b.label}</p>
                <p className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>{b.sub}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Shop categories */}
      {shopCategories.length > 0 && (
        <section className="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-14">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>Shop by Category</h2>
            <Link href="/shop" className="text-sm font-semibold hover:underline" style={{ color: 'var(--kb-primary)' }}>View all →</Link>
          </div>
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
            {shopCategories.map((cat) => (
              <Link key={cat.id} href={`/shop?category=${cat.slug}`}
                className="kb-card flex flex-col items-center gap-2 p-4 text-center hover:shadow-md transition group"
                style={{ textDecoration: 'none' }}>
                <div className="w-14 h-14 rounded-full flex items-center justify-center" style={{ background: 'var(--kb-primary-50)' }}>
                  <svg className="w-6 h-6" style={{ color: 'var(--kb-primary)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                </div>
                <span className="text-sm font-semibold group-hover:text-blue-700" style={{ color: 'var(--kb-ink)' }}>{cat.name}</span>
              </Link>
            ))}
          </div>
        </section>
      )}

      {/* Latest blog posts */}
      {latestPosts.length > 0 && (
        <section style={{ background: '#F8FAFF' }}>
          <div className="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-14">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-2xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>From the Blog</h2>
              <Link href="/blog" className="text-sm font-semibold hover:underline" style={{ color: 'var(--kb-primary)' }}>All posts →</Link>
            </div>
            <div className="grid md:grid-cols-3 gap-6">
              {latestPosts.map((post) => (
                <Link key={post.id} href={`/blog/${post.slug}`}
                  className="kb-card overflow-hidden block group"
                  style={{ textDecoration: 'none' }}>
                  <div className="overflow-hidden" style={{ aspectRatio: '16/9' }}>
                    {post.cover_url
                      ? <img className="w-full h-full object-cover group-hover:scale-105 transition duration-300" src={post.cover_url} alt={post.title} />
                      : <div className="w-full h-full flex items-center justify-center" style={{ background: 'var(--kb-primary-50)', minHeight: 160 }} />
                    }
                  </div>
                  <div className="p-5">
                    {post.category_name && (
                      <span className="kb-chip kb-chip-new">{post.category_name}</span>
                    )}
                    <h3 className="text-base font-bold mt-2 leading-snug line-clamp-2" style={{ color: 'var(--kb-ink)' }}>{post.title}</h3>
                    {post.excerpt && <p className="text-sm mt-2 line-clamp-2" style={{ color: 'var(--kb-ink-muted)' }}>{post.excerpt}</p>}
                    <div className="flex items-center gap-2 text-xs mt-3" style={{ color: 'var(--kb-ink-soft)' }}>
                      <span>{post.author_display_name}</span>
                      <span>·</span>
                      <span>{post.published_at}</span>
                      <span>·</span>
                      <span>{post.read_time}</span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Track order CTA */}
      <section className="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-14">
        <div className="kb-card p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6"
          style={{ background: 'linear-gradient(135deg,var(--kb-primary-50) 0%,#fff 100%)' }}>
          <div>
            <h2 className="text-2xl font-extrabold mb-2" style={{ color: 'var(--kb-ink)' }}>Track Your Order</h2>
            <p style={{ color: 'var(--kb-ink-muted)' }}>Enter your order number to see real-time status and delivery updates.</p>
          </div>
          <Link href="/track" className="kb-btn kb-btn-primary kb-btn-lg flex-shrink-0" style={{ borderRadius: 12 }}>
            Track Order
          </Link>
        </div>
      </section>
    </Layout>
  );
}
