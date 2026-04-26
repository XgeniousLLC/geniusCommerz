import { Head, Link } from '@inertiajs/react';
import Layout from '../../layouts/Layout';
import type { BlogCategory, BlogPost } from '../../types';

interface Props {
  featured: BlogPost | null;
  posts: BlogPost[];
  categories: BlogCategory[];
  activeCategory: BlogCategory | null;
}

export default function BlogIndex({ featured, posts, categories, activeCategory }: Props) {
  return (
    <Layout>
      <Head title={activeCategory ? `${activeCategory.name} — Blog` : 'Blog'} />
      <div className="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-10">

        <div className="mb-6">
          {activeCategory ? (
            <>
              <div className="flex items-center gap-2 text-sm mb-2" style={{ color: 'var(--kb-ink-soft)' }}>
                <Link href="/blog" className="hover:underline">Blog</Link>
                <span>›</span>
                <span style={{ color: 'var(--kb-ink)' }}>{activeCategory.name}</span>
              </div>
              <h1 className="text-3xl md:text-4xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>{activeCategory.name}</h1>
            </>
          ) : (
            <>
              <h1 className="text-3xl md:text-4xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>Journal</h1>
              <p className="mt-1" style={{ color: 'var(--kb-ink-muted)' }}>Style, gadgets, and shopping wisdom.</p>
            </>
          )}
        </div>

        {/* Category tabs */}
        {categories.length > 0 && (
          <div className="flex flex-wrap gap-2 mb-8">
            <Link href="/blog" className="kb-chip text-sm"
              style={{ background: !activeCategory ? 'var(--kb-primary)' : '#f1f5f9', color: !activeCategory ? '#fff' : 'var(--kb-ink-muted)' }}>
              All
            </Link>
            {categories.map((cat) => (
              <Link key={cat.id} href={`/blog/c/${cat.slug}`} className="kb-chip text-sm"
                style={{ background: activeCategory?.id === cat.id ? 'var(--kb-primary)' : '#f1f5f9', color: activeCategory?.id === cat.id ? '#fff' : 'var(--kb-ink-muted)' }}>
                {cat.name}
              </Link>
            ))}
          </div>
        )}

        {/* Featured */}
        {featured && (
          <Link href={`/blog/${featured.slug}`} className="kb-card overflow-hidden block mb-10 group" style={{ textDecoration: 'none' }}>
            <div className="grid lg:grid-cols-2">
              <div className="overflow-hidden" style={{ minHeight: 220, aspectRatio: '16/10' }}>
                {featured.cover_url
                  ? <img className="w-full h-full object-cover group-hover:scale-105 transition duration-300" src={featured.cover_url} alt={featured.title} />
                  : <div className="w-full h-full" style={{ background: 'var(--kb-primary-50)', minHeight: 220 }} />
                }
              </div>
              <div className="p-6 md:p-8 flex flex-col justify-center">
                {featured.category_name && (
                  <span className="kb-chip kb-chip-new self-start mb-2">
                    {activeCategory ? 'Latest' : 'Featured'} · {featured.category_name}
                  </span>
                )}
                <h2 className="text-2xl md:text-3xl font-extrabold leading-tight" style={{ color: 'var(--kb-ink)' }}>{featured.title}</h2>
                {featured.excerpt && <p className="mt-3" style={{ color: 'var(--kb-ink-muted)' }}>{featured.excerpt}</p>}
                <div className="flex items-center gap-2 text-xs mt-4" style={{ color: 'var(--kb-ink-soft)' }}>
                  <div className="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
                    style={{ background: 'var(--kb-primary)' }}>
                    {featured.author_display_name[0].toUpperCase()}
                  </div>
                  <span className="font-semibold" style={{ color: 'var(--kb-ink-muted)' }}>{featured.author_display_name}</span>
                  <span>·</span>
                  <span>{featured.published_at}</span>
                  <span>·</span>
                  <span>{featured.read_time}</span>
                </div>
                <div className="mt-4">
                  <span className="text-sm font-semibold" style={{ color: 'var(--kb-primary)' }}>Read article →</span>
                </div>
              </div>
            </div>
          </Link>
        )}

        {/* Post grid */}
        {posts.length > 0 && (
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {posts.map((post) => (
              <Link key={post.id} href={`/blog/${post.slug}`}
                className="kb-card overflow-hidden block group" style={{ textDecoration: 'none' }}>
                <div className="overflow-hidden" style={{ aspectRatio: '16/9' }}>
                  {post.cover_url
                    ? <img className="w-full h-full object-cover group-hover:scale-105 transition duration-300" src={post.cover_url} alt={post.title} />
                    : <div className="w-full h-full flex items-center justify-center" style={{ background: 'var(--kb-primary-50)', minHeight: 160 }} />
                  }
                </div>
                <div className="p-5">
                  {post.category_name && (
                    <span className="kb-chip kb-chip-new" onClick={(e) => {
                      if (post.blogCategory) { e.preventDefault(); window.location.href = `/blog/c/${post.blogCategory.slug}`; }
                    }}>{post.category_name}</span>
                  )}
                  <h3 className="text-lg font-bold mt-2 leading-snug line-clamp-2" style={{ color: 'var(--kb-ink)' }}>{post.title}</h3>
                  {post.excerpt && <p className="text-sm mt-2 line-clamp-2" style={{ color: 'var(--kb-ink-muted)' }}>{post.excerpt}</p>}
                  <div className="flex items-center gap-2 text-xs mt-3" style={{ color: 'var(--kb-ink-soft)' }}>
                    <div className="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold"
                      style={{ background: 'var(--kb-primary)' }}>
                      {post.author_display_name[0].toUpperCase()}
                    </div>
                    <span style={{ color: 'var(--kb-ink-muted)' }}>{post.author_display_name}</span>
                    <span>·</span>
                    <span>{post.published_at}</span>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}

        {!featured && posts.length === 0 && (
          <div className="py-20 text-center" style={{ color: 'var(--kb-ink-soft)' }}>
            <p className="text-lg font-semibold">
              {activeCategory ? `No posts in "${activeCategory.name}" yet.` : 'No posts published yet.'}
            </p>
            {activeCategory && (
              <Link href="/blog" className="kb-btn kb-btn-ghost kb-btn-md mt-4 inline-flex">← View all posts</Link>
            )}
          </div>
        )}

      </div>
    </Layout>
  );
}
