import { Head, Link } from '@inertiajs/react';
import Layout from '../../layouts/Layout';
import type { BlogCategory, BlogPost } from '../../types';

interface Props {
  featured: BlogPost | null;
  posts: BlogPost[];
  categories: (BlogCategory & { blogs_count?: number })[];
  activeCategory: BlogCategory | null;
  totalCount: number;
}

const GRADIENTS: [string, string][] = [
  ['#DDE4F4', '#EEF2FB'],
  ['#D4EBE2', '#E6F5EE'],
  ['#F5E6CC', '#FBF2E5'],
  ['#E5D4F5', '#F2E6FB'],
  ['#D4E5F5', '#E6EFF9'],
  ['#F5D4D4', '#FBE6E6'],
];

function gradientFor(text: string): [string, string] {
  let hash = 0;
  for (let i = 0; i < text.length; i++) hash = (hash * 31 + text.charCodeAt(i)) >>> 0;
  return GRADIENTS[hash % GRADIENTS.length];
}

function CoverPlaceholder({ text, featured: isFeatured = false }: { text: string; featured?: boolean }) {
  const [c1, c2] = gradientFor(text);
  return (
    <div style={{
      width: '100%', height: '100%',
      background: `linear-gradient(135deg, ${c1}, ${c2})`,
      display: 'grid', placeItems: 'center',
      color: 'rgba(14,19,32,.4)',
      fontSize: isFeatured ? 88 : 48,
      fontWeight: 800, letterSpacing: '-0.04em',
    }}>
      {text.charAt(0)}
    </div>
  );
}

function FeaturedCard({ post, isActive }: { post: BlogPost; isActive: boolean }) {
  return (
    <Link href={`/blog/${post.slug}`} style={{ textDecoration: 'none' }}>
      <div className="grid sm:grid-cols-[1.3fr_1fr] hover:shadow-lg"
        style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 16, overflow: 'hidden', transition: 'box-shadow .2s' }}>
        <div className="aspect-[16/9] sm:aspect-auto" style={{ overflow: 'hidden', minHeight: 0 }}>
          {post.cover_url
            ? <img src={post.cover_url} alt={post.title} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            : <CoverPlaceholder text={post.category_name || post.title} featured />
          }
        </div>
        <div className="p-5 sm:p-[40px_44px]" style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
          {post.category_name && (
            <span style={{
              display: 'inline-flex', alignItems: 'center',
              background: 'var(--kb-primary-50)', color: 'var(--kb-primary)',
              padding: '5px 12px', borderRadius: 999,
              fontSize: 12, fontWeight: 700, alignSelf: 'flex-start',
            }}>
              {isActive ? 'Latest' : 'Featured'} · {post.category_name}
            </span>
          )}
          <h2 className="text-[22px] sm:text-[28px]" style={{ margin: '12px 0 8px', fontWeight: 800, letterSpacing: '-0.02em', lineHeight: 1.15, color: 'var(--kb-ink)' }}>
            {post.title}
          </h2>
          {post.excerpt && (
            <p style={{ color: 'var(--kb-ink-muted)', fontSize: 15, lineHeight: 1.55, margin: '0 0 16px' }}>
              {post.excerpt}
            </p>
          )}
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 13, color: 'var(--kb-ink-soft)', marginBottom: 16, flexWrap: 'wrap' }}>
            <div style={{ width: 32, height: 32, borderRadius: '50%', background: 'var(--kb-primary)', color: '#fff', display: 'grid', placeItems: 'center', fontSize: 13, fontWeight: 700, flexShrink: 0 }}>
              {post.author_display_name[0]?.toUpperCase()}
            </div>
            <span style={{ fontWeight: 600, color: 'var(--kb-ink-muted)' }}>{post.author_display_name}</span>
            <span>·</span>
            <span>{post.published_at}</span>
            <span>·</span>
            <span>{post.read_time}</span>
          </div>
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'var(--kb-primary)', fontWeight: 700, fontSize: 14 }}>
            Read article →
          </span>
        </div>
      </div>
    </Link>
  );
}

function PostCard({ post }: { post: BlogPost }) {
  return (
    <Link href={`/blog/${post.slug}`} style={{ textDecoration: 'none' }}>
      <div style={{
        background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, overflow: 'hidden',
        display: 'flex', flexDirection: 'column', height: '100%',
        transition: 'border-color .15s, box-shadow .15s, transform .15s',
      }} className="hover:shadow-md hover:-translate-y-0.5">
        <div style={{ aspectRatio: '16/10', overflow: 'hidden' }}>
          {post.cover_url
            ? <img src={post.cover_url} alt={post.title} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            : <CoverPlaceholder text={post.category_name || post.title} />
          }
        </div>
        <div style={{ padding: '18px 20px', flex: 1, display: 'flex', flexDirection: 'column' }}>
          {post.category_name && (
            <span style={{
              display: 'inline-block',
              background: 'var(--kb-primary-50)', color: 'var(--kb-primary)',
              padding: '3px 8px', borderRadius: 4,
              fontSize: 11, fontWeight: 700, letterSpacing: '0.04em', textTransform: 'uppercase',
              alignSelf: 'flex-start',
            }}>
              {post.category_name}
            </span>
          )}
          <h3 style={{ fontSize: 17, fontWeight: 700, letterSpacing: '-0.01em', lineHeight: 1.3, margin: '10px 0 6px', color: 'var(--kb-ink)' }}>
            {post.title}
          </h3>
          {post.excerpt && (
            <p style={{
              color: 'var(--kb-ink-muted)', fontSize: 13, lineHeight: 1.55, margin: 0, flex: 1,
              display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden',
            }}>
              {post.excerpt}
            </p>
          )}
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 14, fontSize: 12, color: 'var(--kb-ink-soft)' }}>
            <div style={{ width: 24, height: 24, borderRadius: '50%', background: 'var(--kb-primary)', color: '#fff', display: 'grid', placeItems: 'center', fontSize: 11, fontWeight: 700, flexShrink: 0 }}>
              {post.author_display_name[0]?.toUpperCase()}
            </div>
            <span>{post.author_display_name}</span>
            <span>·</span>
            <span>{post.published_at}</span>
            <span>·</span>
            <span>{post.read_time}</span>
          </div>
        </div>
      </div>
    </Link>
  );
}

export default function BlogIndex({ featured, posts, categories, activeCategory, totalCount }: Props) {
  return (
    <Layout>
      <Head title={activeCategory ? `${activeCategory.name} — Journal` : 'The klixbd Journal'} />

      <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>

        {/* Header */}
        <div style={{ padding: '32px 0 16px', borderBottom: '1px solid var(--kb-border)' }}>
          <h1 style={{ fontSize: 36, fontWeight: 800, letterSpacing: '-0.02em', margin: 0, color: 'var(--kb-ink)' }}>
            The klixbd Journal
          </h1>
          <p style={{ color: 'var(--kb-ink-muted)', margin: '6px 0 16px', fontSize: 15 }}>
            Buying guides, reviews, and shopping wisdom — written by people who actually use the stuff.
          </p>
          <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
            <Link href="/blog"
              style={{
                display: 'inline-flex', alignItems: 'center',
                padding: '7px 14px', borderRadius: 999,
                background: !activeCategory ? 'var(--kb-primary)' : '#fff',
                border: `1px solid ${!activeCategory ? 'var(--kb-primary)' : 'var(--kb-border)'}`,
                color: !activeCategory ? '#fff' : 'var(--kb-ink-muted)',
                fontSize: 13, fontWeight: 500, textDecoration: 'none',
              }}>
              All
              <span style={{
                marginLeft: 6, padding: '1px 6px', borderRadius: 999, fontSize: 11,
                background: !activeCategory ? 'rgba(255,255,255,.2)' : 'var(--kb-surface-2)',
                color: !activeCategory ? '#fff' : 'var(--kb-ink-soft)',
              }}>{totalCount}</span>
            </Link>
            {categories.map((cat) => (
              <Link key={cat.id} href={`/blog/c/${cat.slug}`}
                style={{
                  display: 'inline-flex', alignItems: 'center',
                  padding: '7px 14px', borderRadius: 999,
                  background: activeCategory?.id === cat.id ? 'var(--kb-primary)' : '#fff',
                  border: `1px solid ${activeCategory?.id === cat.id ? 'var(--kb-primary)' : 'var(--kb-border)'}`,
                  color: activeCategory?.id === cat.id ? '#fff' : 'var(--kb-ink-muted)',
                  fontSize: 13, fontWeight: 500, textDecoration: 'none',
                }}>
                {cat.name}
                {cat.blogs_count !== undefined && (
                  <span style={{
                    marginLeft: 6, padding: '1px 6px', borderRadius: 999, fontSize: 11,
                    background: activeCategory?.id === cat.id ? 'rgba(255,255,255,.2)' : 'var(--kb-surface-2)',
                    color: activeCategory?.id === cat.id ? '#fff' : 'var(--kb-ink-soft)',
                  }}>{cat.blogs_count}</span>
                )}
              </Link>
            ))}
          </div>
        </div>

        {/* Featured */}
        {featured && (
          <div style={{ marginTop: 24 }}>
            <FeaturedCard post={featured} isActive={!!activeCategory} />
          </div>
        )}

        {/* Posts grid */}
        {posts.length > 0 && (
          <section style={{ padding: '24px 0 16px' }}>
            <div style={{ marginBottom: 20 }}>
              <h2 style={{ fontSize: 22, fontWeight: 800, letterSpacing: '-0.01em', margin: '0 0 4px', color: 'var(--kb-ink)' }}>
                {activeCategory ? `${activeCategory.name} articles` : 'Latest articles'}
              </h2>
              <div style={{ fontSize: 13, color: 'var(--kb-ink-soft)' }}>
                {posts.length} article{posts.length !== 1 ? 's' : ''}
              </div>
            </div>
            <div className="grid md:grid-cols-2 lg:grid-cols-3" style={{ gap: 24 }}>
              {posts.map((post) => <PostCard key={post.id} post={post} />)}
            </div>
          </section>
        )}

        {!featured && posts.length === 0 && (
          <div style={{ padding: '80px 0', textAlign: 'center', color: 'var(--kb-ink-soft)' }}>
            <p style={{ fontSize: 18, fontWeight: 600 }}>
              {activeCategory ? `No articles in "${activeCategory.name}" yet.` : 'No articles published yet.'}
            </p>
            {activeCategory && (
              <Link href="/blog" style={{ display: 'inline-flex', marginTop: 16, color: 'var(--kb-primary)', fontWeight: 600, textDecoration: 'none' }}>
                ← View all articles
              </Link>
            )}
          </div>
        )}

      </div>
    </Layout>
  );
}
