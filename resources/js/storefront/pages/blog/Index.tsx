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
  ['#efe9dc', '#fbf8f1'],
  ['#e8ddd0', '#f4efe5'],
  ['#ddd5c5', '#efe9dc'],
  ['#d6ccb8', '#e8ddd0'],
  ['#cfc3ad', '#ddd5c5'],
  ['#c9baa8', '#d6ccb8'],
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
      color: 'var(--av-muted)',
      fontSize: isFeatured ? 72 : 36,
      fontWeight: 400, letterSpacing: '-0.04em', fontFamily: 'var(--av-display)',
    }}>
      {text.charAt(0)}
    </div>
  );
}

function FeaturedCard({ post, isActive }: { post: BlogPost; isActive: boolean }) {
  return (
    <Link href={`/blog/${post.slug}`} style={{ textDecoration: 'none' }}>
      <div className="grid sm:grid-cols-[1.3fr_1fr]"
        style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', overflow: 'hidden' }}>
        <div className="aspect-[16/9] sm:aspect-auto" style={{ overflow: 'hidden', minHeight: 0, background: 'var(--av-paper-2)' }}>
          {post.cover_url
            ? <img src={post.cover_url} alt={post.title} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            : <CoverPlaceholder text={post.category_name || post.title} featured />
          }
        </div>
        <div className="p-5 sm:p-[36px_32px]" style={{ display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
          {post.category_name && (
            <span style={{
              display: 'inline-flex', alignItems: 'center',
              background: 'var(--av-paper-2)', color: 'var(--av-cognac)',
              padding: '4px 10px', fontSize: 10, fontWeight: 500, letterSpacing: '0.14em', textTransform: 'uppercase', fontFamily: 'var(--av-sans)', alignSelf: 'flex-start',
            }}>
              {isActive ? 'Latest' : 'Featured'} · {post.category_name}
            </span>
          )}
          <h2 style={{ margin: '12px 0 8px', fontWeight: 400, letterSpacing: '-0.012em', lineHeight: 1.12, color: 'var(--av-ink)', fontFamily: 'var(--av-display)', fontSize: 'clamp(22px,3vw,28px)' }}>
            {post.title}
          </h2>
          {post.excerpt && (
            <p style={{ color: 'var(--av-muted)', fontSize: 13.5, lineHeight: 1.6, margin: '0 0 14px', fontFamily: 'var(--av-sans)' }}>
              {post.excerpt}
            </p>
          )}
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 11.5, color: 'var(--av-muted)', marginBottom: 14, flexWrap: 'wrap', fontFamily: 'var(--av-sans)' }}>
            <div style={{ width: 28, height: 28, borderRadius: '50%', background: 'var(--av-ink)', color: 'var(--av-paper)', display: 'grid', placeItems: 'center', fontSize: 11, fontWeight: 500, flexShrink: 0 }}>
              {post.author_display_name[0]?.toUpperCase()}
            </div>
            <span style={{ fontWeight: 500, color: 'var(--av-ink)' }}>{post.author_display_name}</span>
            <span style={{ color: 'var(--av-line)' }}>·</span>
            <span>{post.published_at}</span>
            <span style={{ color: 'var(--av-line)' }}>·</span>
            <span>{post.read_time}</span>
          </div>
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6, color: 'var(--av-ink)', fontWeight: 500, fontSize: 11.5, letterSpacing: '0.1em', textTransform: 'uppercase', fontFamily: 'var(--av-sans)' }}>
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
        background: 'var(--av-paper)', border: '1px solid var(--av-line-soft)', overflow: 'hidden',
        display: 'flex', flexDirection: 'column', height: '100%',
      }}>
        <div style={{ aspectRatio: '16/10', overflow: 'hidden', background: 'var(--av-paper-2)' }}>
          {post.cover_url
            ? <img src={post.cover_url} alt={post.title} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
            : <CoverPlaceholder text={post.category_name || post.title} />
          }
        </div>
        <div style={{ padding: '16px 18px', flex: 1, display: 'flex', flexDirection: 'column' }}>
          {post.category_name && (
            <span style={{
              display: 'inline-block',
              background: 'var(--av-paper-2)', color: 'var(--av-cognac)',
              padding: '3px 8px', fontSize: 10, fontWeight: 500, letterSpacing: '0.12em', textTransform: 'uppercase', fontFamily: 'var(--av-sans)',
              alignSelf: 'flex-start',
            }}>
              {post.category_name}
            </span>
          )}
          <h3 style={{ fontSize: 16, fontWeight: 400, letterSpacing: '-0.01em', lineHeight: 1.3, margin: '10px 0 6px', color: 'var(--av-ink)', fontFamily: 'var(--av-display)' }}>
            {post.title}
          </h3>
          {post.excerpt && (
            <p style={{
              color: 'var(--av-muted)', fontSize: 12.5, lineHeight: 1.6, margin: 0, flex: 1, fontFamily: 'var(--av-sans)',
              display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden',
            }}>
              {post.excerpt}
            </p>
          )}
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 12, fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
            <div style={{ width: 20, height: 20, borderRadius: '50%', background: 'var(--av-ink)', color: 'var(--av-paper)', display: 'grid', placeItems: 'center', fontSize: 10, fontWeight: 500, flexShrink: 0 }}>
              {post.author_display_name[0]?.toUpperCase()}
            </div>
            <span>{post.author_display_name}</span>
            <span style={{ color: 'var(--av-line)' }}>·</span>
            <span>{post.published_at}</span>
          </div>
        </div>
      </div>
    </Link>
  );
}

export default function BlogIndex({ featured, posts, categories, activeCategory, totalCount }: Props) {
  const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };
  return (
    <Layout>
      <Head title={activeCategory ? `${activeCategory.name} — Journal` : 'Journal'} />

      <div style={{ ...W }}>

        <div style={{ padding: '32px 0 18px', borderBottom: '1px solid var(--av-line)' }}>
          <div style={{ fontSize: 10.5, letterSpacing: '0.28em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>The Journal</div>
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(28px,4vw,38px)', fontWeight: 400, letterSpacing: '-0.012em', margin: 0, color: 'var(--av-ink)', lineHeight: 1.04 }}>
            {activeCategory ? activeCategory.name : 'The Journal'}
          </h1>
          <p style={{ color: 'var(--av-muted)', margin: '8px 0 18px', fontSize: 13.5, fontFamily: 'var(--av-sans)', lineHeight: 1.6 }}>
            Guides, reviews, and notes on craft.
          </p>
          <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
            <Link href="/blog"
              style={{
                display: 'inline-flex', alignItems: 'center',
                padding: '7px 14px', fontSize: 11, letterSpacing: '0.08em', textTransform: 'uppercase', fontWeight: 500, fontFamily: 'var(--av-sans)', textDecoration: 'none',
                background: !activeCategory ? 'var(--av-ink)' : 'var(--av-paper)',
                border: `1px solid ${!activeCategory ? 'var(--av-ink)' : 'var(--av-line)'}`,
                color: !activeCategory ? 'var(--av-paper)' : 'var(--av-muted)',
              }}>
              All
              <span style={{
                marginLeft: 6, padding: '1px 6px', fontSize: 10,
                background: !activeCategory ? 'rgba(244,239,229,.18)' : 'var(--av-paper-2)',
                color: !activeCategory ? 'var(--av-paper)' : 'var(--av-muted)',
              }}>{totalCount}</span>
            </Link>
            {categories.map((cat) => (
              <Link key={cat.id} href={`/blog/c/${cat.slug}`}
                style={{
                  display: 'inline-flex', alignItems: 'center',
                  padding: '7px 14px', fontSize: 11, letterSpacing: '0.08em', textTransform: 'uppercase', fontWeight: 500, fontFamily: 'var(--av-sans)', textDecoration: 'none',
                  background: activeCategory?.id === cat.id ? 'var(--av-ink)' : 'var(--av-paper)',
                  border: `1px solid ${activeCategory?.id === cat.id ? 'var(--av-ink)' : 'var(--av-line)'}`,
                  color: activeCategory?.id === cat.id ? 'var(--av-paper)' : 'var(--av-muted)',
                }}>
                {cat.name}
                {cat.blogs_count !== undefined && (
                  <span style={{
                    marginLeft: 6, padding: '1px 6px', fontSize: 10,
                    background: activeCategory?.id === cat.id ? 'rgba(244,239,229,.18)' : 'var(--av-paper-2)',
                    color: activeCategory?.id === cat.id ? 'var(--av-paper)' : 'var(--av-muted)',
                  }}>{cat.blogs_count}</span>
                )}
              </Link>
            ))}
          </div>
        </div>

        {featured && (
          <div style={{ marginTop: 24 }}>
            <FeaturedCard post={featured} isActive={!!activeCategory} />
          </div>
        )}

        {posts.length > 0 && (
          <section style={{ padding: '28px 0 16px' }}>
            <div style={{ marginBottom: 18 }}>
              <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, letterSpacing: '-0.01em', margin: '0 0 4px', color: 'var(--av-ink)' }}>
                {activeCategory ? `${activeCategory.name}` : 'Latest articles'}
              </h2>
              <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                {posts.length} {posts.length === 1 ? 'article' : 'articles'}
              </div>
            </div>
            <div className="grid md:grid-cols-2 lg:grid-cols-3" style={{ gap: 20 }}>
              {posts.map((post) => <PostCard key={post.id} post={post} />)}
            </div>
          </section>
        )}

        {!featured && posts.length === 0 && (
          <div style={{ padding: '64px 0', textAlign: 'center' }}>
            <p style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 8px' }}>
              {activeCategory ? `No articles in "${activeCategory.name}" yet.` : 'No articles published yet.'}
            </p>
            {activeCategory && (
              <Link href="/blog" style={{ display: 'inline-flex', marginTop: 12, color: 'var(--av-cognac)', fontWeight: 500, textDecoration: 'none', fontFamily: 'var(--av-sans)', fontSize: 13 }}>
                ← View all
              </Link>
            )}
          </div>
        )}

      </div>
    </Layout>
  );
}
