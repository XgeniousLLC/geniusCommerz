import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Layout from '../../layouts/Layout';
import type { BlogCategory, BlogPost, Comment, SharedProps } from '../../types';

interface Props {
  blog: BlogPost;
  related: BlogPost[];
  categories: BlogCategory[];
  comments: Comment[];
}

function CommentItem({ comment, blogId }: { comment: Comment; blogId: number }) {
  const { auth } = usePage<SharedProps>().props;
  const [replyOpen, setReplyOpen] = useState(false);
  const { data, setData, post, processing, reset } = useForm({ commentable_type: 'blog', commentable_id: blogId, parent_id: comment.id, body: '' });

  function submitReply(e: React.FormEvent) {
    e.preventDefault();
    post('/comments', { onSuccess: () => { reset(); setReplyOpen(false); } });
  }

  return (
    <div className="flex gap-4" id={`comment-${comment.id}`}>
      <div className="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
        style={{ background: 'var(--kb-primary)' }}>
        {comment.name[0].toUpperCase()}
      </div>
      <div className="flex-1">
        <div className="kb-card p-4" style={{ borderRadius: 12 }}>
          <div className="flex items-center justify-between gap-2 mb-2">
            <span className="font-semibold text-sm" style={{ color: 'var(--kb-ink)' }}>{comment.name}</span>
            <span className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>{comment.created_at}</span>
          </div>
          <p className="text-sm leading-relaxed" style={{ color: 'var(--kb-ink-muted)' }}>{comment.body}</p>
        </div>

        {auth.user ? (
          <div className="mt-2">
            <button onClick={() => setReplyOpen((o) => !o)} type="button"
              className="text-xs font-medium hover:underline flex items-center gap-1"
              style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--kb-primary)' }}>
              ↳ Reply
            </button>
            {replyOpen && (
              <form onSubmit={submitReply} className="mt-3 space-y-3">
                <textarea name="body" rows={2} placeholder="Write a reply…" required
                  value={data.body} onChange={(e) => setData('body', e.target.value)}
                  className="w-full text-sm px-3 py-2 rounded-lg border outline-none resize-none"
                  style={{ borderColor: '#e2e8f0', background: '#f8fafc', color: 'var(--kb-ink)' }} />
                <button type="submit" disabled={processing}
                  className="kb-btn kb-btn-primary text-sm px-4 py-1.5">
                  Post reply
                </button>
              </form>
            )}
          </div>
        ) : (
          <p className="mt-2 text-xs" style={{ color: 'var(--kb-ink-soft)' }}>
            <Link href="/login" className="font-medium hover:underline" style={{ color: 'var(--kb-primary)' }}>Sign in</Link> to reply
          </p>
        )}

        {comment.replies.length > 0 && (
          <div className="mt-4 space-y-4 pl-4" style={{ borderLeft: '2px solid var(--kb-border)' }}>
            {comment.replies.map((reply) => (
              <div key={reply.id} className="flex gap-3" id={`comment-${reply.id}`}>
                <div className="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                  style={{ background: 'var(--kb-accent)' }}>
                  {reply.name[0].toUpperCase()}
                </div>
                <div className="kb-card p-3 flex-1" style={{ borderRadius: 10 }}>
                  <div className="flex items-center justify-between gap-2 mb-1.5">
                    <span className="font-semibold text-xs" style={{ color: 'var(--kb-ink)' }}>{reply.name}</span>
                    <span className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>{reply.created_at}</span>
                  </div>
                  <p className="text-sm leading-relaxed" style={{ color: 'var(--kb-ink-muted)' }}>{reply.body}</p>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

function TocBox({ contentRef }: { contentRef: React.RefObject<HTMLDivElement | null> }) {
  const [items, setItems] = useState<{ id: string; text: string; level: number }[]>([]);
  const [active, setActive] = useState('');

  useEffect(() => {
    const el = contentRef.current;
    if (!el) return;
    const headings = el.querySelectorAll('h2, h3');
    if (!headings.length) return;
    const list = Array.from(headings).map((h, i) => {
      if (!h.id) h.id = `toc-h-${i}`;
      return { id: h.id, text: h.textContent?.trim() ?? '', level: h.tagName === 'H3' ? 3 : 2 };
    });
    setItems(list);
    const onScroll = () => {
      let cur = '';
      list.forEach(({ id }) => {
        const el = document.getElementById(id);
        if (el && window.scrollY >= el.offsetTop - 120) cur = id;
      });
      setActive(cur);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, [contentRef]);

  if (!items.length) return null;

  return (
    <div className="kb-card p-4">
      <h3 className="font-semibold text-sm mb-3" style={{ color: 'var(--kb-ink)' }}>Contents</h3>
      <nav className="space-y-1 text-sm" style={{ color: 'var(--kb-ink-soft)' }}>
        {items.map((item) => (
          <a key={item.id} href={`#${item.id}`}
            className="block hover:opacity-80 transition-colors"
            style={{
              color: active === item.id ? 'var(--kb-primary)' : 'inherit',
              paddingLeft: item.level === 3 ? 14 : 0,
              fontSize: item.level === 3 ? '0.79rem' : '0.83rem',
              fontWeight: item.level === 2 ? 600 : 400,
              lineHeight: 1.6,
            }}>
            {item.text}
          </a>
        ))}
      </nav>
    </div>
  );
}

export default function BlogShow({ blog, related, categories, comments }: Props) {
  const { auth, flash } = usePage<SharedProps>().props;
  const contentRef = useRef<HTMLDivElement>(null);
  const { data, setData, post, processing, errors, reset } = useForm({ commentable_type: 'blog', commentable_id: blog.id, body: '' });

  function submitComment(e: React.FormEvent) {
    e.preventDefault();
    post('/comments', { onSuccess: () => reset() });
  }

  const meta = blog.metaInformation;
  const pageTitle = meta?.meta_title || blog.title;

  return (
    <Layout>
      <Head>
        <title>{pageTitle}</title>
        {meta?.meta_description && <meta name="description" content={meta.meta_description} />}
        {meta?.robots && <meta name="robots" content={meta.robots} />}
        <meta property="og:title" content={blog.title} />
        <meta property="og:type" content="article" />
        {blog.cover_url && <meta property="og:image" content={blog.cover_url} />}
        {blog.faqs && blog.faqs.length > 0 && (
          <script type="application/ld+json">{JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            mainEntity: blog.faqs.map((f) => ({
              '@type': 'Question',
              name: f.question,
              acceptedAnswer: { '@type': 'Answer', text: f.answer },
            })),
          })}</script>
        )}
      </Head>

      <div className="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-10">

        {/* Breadcrumb */}
        <nav className="text-xs mb-4 flex items-center gap-1.5" style={{ color: 'var(--kb-ink-soft)' }}>
          <Link href="/" className="hover:text-slate-900">Home</Link>
          <span>›</span>
          <Link href="/blog" className="hover:text-slate-900">Blog</Link>
          <span>›</span>
          <span style={{ color: 'var(--kb-ink)' }}>{blog.title.substring(0, 50)}{blog.title.length > 50 ? '…' : ''}</span>
        </nav>

        <div className="grid lg:grid-cols-[1fr_280px] gap-10">

          <article>
            {blog.blogCategory ? (
              <Link href={`/blog/c/${blog.blogCategory.slug}`} className="kb-chip kb-chip-new mb-3 hover:opacity-80" style={{ textDecoration: 'none' }}>
                {blog.category_name}
              </Link>
            ) : blog.category_name ? (
              <span className="kb-chip kb-chip-new mb-3">{blog.category_name}</span>
            ) : null}

            <h1 className="text-3xl md:text-5xl font-extrabold leading-tight">{blog.title}</h1>

            <div className="flex flex-wrap items-center gap-3 text-sm mt-4" style={{ color: 'var(--kb-ink-soft)' }}>
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold"
                  style={{ background: 'var(--kb-primary)' }}>
                  {blog.author_display_name[0].toUpperCase()}
                </div>
                <span className="font-semibold" style={{ color: 'var(--kb-ink)' }}>{blog.author_display_name}</span>
              </div>
              <span>·</span>
              <span>{blog.published_at}</span>
              <span>·</span>
              <span>{blog.read_time}</span>
            </div>

            {blog.cover_url && (
              <img className="w-full rounded-2xl mt-6 object-cover kb-shadow-card" style={{ maxHeight: 480 }}
                src={blog.cover_url} alt={blog.title} />
            )}

            <div ref={contentRef}
              className="mt-7 kb-prose leading-relaxed"
              style={{ color: '#334155' }}
              dangerouslySetInnerHTML={{ __html: blog.content ?? '' }}
            />

            {/* FAQs */}
            {blog.faqs && blog.faqs.length > 0 && (
              <div className="mt-10">
                <h2 className="text-xl font-bold mb-4" style={{ color: 'var(--kb-ink)' }}>Frequently Asked Questions</h2>
                <div className="space-y-3">
                  {blog.faqs.map((faq, i) => (
                    <FaqItem key={i} question={faq.question} answer={faq.answer} />
                  ))}
                </div>
              </div>
            )}

            {/* Share */}
            <div className="mt-8 flex items-center gap-3 text-sm">
              <span style={{ color: 'var(--kb-ink-soft)' }}>Share:</span>
              <a href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`}
                target="_blank" rel="noopener"
                className="w-9 h-9 rounded-full grid place-items-center hover:opacity-80"
                style={{ background: '#f1f5f9' }}>
                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                </svg>
              </a>
              <button onClick={() => navigator.clipboard.writeText(window.location.href)}
                className="w-9 h-9 rounded-full grid place-items-center hover:opacity-80"
                style={{ background: '#f1f5f9', border: 'none', cursor: 'pointer' }} title="Copy link">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
              </button>
            </div>
          </article>

          {/* Sidebar */}
          <aside className="space-y-5">
            {blog.enable_toc && <TocBox contentRef={contentRef} />}

            {categories.length > 0 && (
              <div className="kb-card p-4">
                <h3 className="font-semibold text-sm mb-3" style={{ color: 'var(--kb-ink)' }}>Categories</h3>
                <div className="space-y-1">
                  {categories.map((cat) => (
                    <Link key={cat.id} href={`/blog/c/${cat.slug}`}
                      className="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition hover:opacity-80"
                      style={blog.blog_category_id === cat.id
                        ? { background: 'var(--kb-primary-50)', color: 'var(--kb-primary)', fontWeight: 600 }
                        : { color: 'var(--kb-ink-muted)' }}>
                      <span>{cat.name}</span>
                      <span className="text-xs px-1.5 py-0.5 rounded-full" style={{ background: '#f1f5f9', color: 'var(--kb-ink-soft)' }}>
                        {cat.blogs_count}
                      </span>
                    </Link>
                  ))}
                </div>
              </div>
            )}
          </aside>
        </div>

        {/* Related articles */}
        {related.length > 0 && (
          <div className="mt-14">
            <h2 className="text-xl font-bold mb-6" style={{ color: 'var(--kb-ink)' }}>Related Articles</h2>
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {related.map((rel) => (
                <Link key={rel.id} href={`/blog/${rel.slug}`}
                  className="kb-card group flex flex-col overflow-hidden kb-shadow-card hover:shadow-lg transition-shadow"
                  style={{ borderRadius: 16, textDecoration: 'none' }}>
                  {rel.cover_url
                    ? <img className="w-full object-cover" style={{ height: 180 }} src={rel.cover_url} alt={rel.title} />
                    : <div style={{ height: 180, background: 'var(--kb-primary-50)' }} />
                  }
                  <div className="p-4 flex flex-col gap-2 flex-1">
                    {rel.category_name && <span className="kb-chip kb-chip-new text-xs self-start">{rel.category_name}</span>}
                    <div className="font-semibold text-sm leading-snug group-hover:underline line-clamp-2" style={{ color: 'var(--kb-ink)' }}>{rel.title}</div>
                    <div className="flex items-center gap-2 text-xs mt-auto pt-2" style={{ color: 'var(--kb-ink-soft)' }}>
                      <span>{rel.published_at}</span>
                      <span>·</span>
                      <span>{rel.read_time}</span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        )}

        {/* Comments */}
        <div className="mt-14" id="comments">
          {flash.comment_success && (
            <div className="mb-6 px-4 py-3 rounded-xl text-sm font-medium" style={{ background: '#f0fdf4', color: '#15803d', border: '1px solid #bbf7d0' }}>
              {flash.comment_success}
            </div>
          )}

          {comments.length > 0 && (
            <>
              <h2 className="text-xl font-bold mb-6" style={{ color: 'var(--kb-ink)' }}>
                {comments.length} Comment{comments.length !== 1 ? 's' : ''}
              </h2>
              <div className="space-y-6 mb-12">
                {comments.map((c) => <CommentItem key={c.id} comment={c} blogId={blog.id} />)}
              </div>
            </>
          )}

          {auth.user ? (
            <div className="kb-card p-6 lg:p-8" style={{ borderRadius: 20 }}>
              <div className="flex items-center gap-3 mb-6">
                <div className="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold"
                  style={{ background: 'var(--kb-primary)' }}>
                  {auth.user.name[0].toUpperCase()}
                </div>
                <div>
                  <div className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>{auth.user.name}</div>
                  <div className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>Commenting as yourself</div>
                </div>
              </div>
              <form onSubmit={submitComment} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Comment <span style={{ color: '#ef4444' }}>*</span></label>
                  <textarea name="body" rows={5} placeholder="Share your thoughts…" required
                    value={data.body} onChange={(e) => setData('body', e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border text-sm outline-none resize-none"
                    style={{ borderColor: '#e2e8f0', background: '#f8fafc', color: 'var(--kb-ink)' }} />
                  {errors.body && <div className="text-xs text-red-600 mt-1">{errors.body}</div>}
                </div>
                <button type="submit" disabled={processing} className="kb-btn kb-btn-primary px-6 py-2.5">Post Comment</button>
              </form>
            </div>
          ) : (
            <p className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>
              <Link href="/login" className="font-semibold hover:underline" style={{ color: 'var(--kb-primary)' }}>Sign in</Link>
              {' or '}
              <Link href="/register" className="font-semibold hover:underline" style={{ color: 'var(--kb-primary)' }}>create an account</Link>
              {' to leave a comment.'}
            </p>
          )}
        </div>

      </div>
    </Layout>
  );
}

function FaqItem({ question, answer }: { question: string; answer: string }) {
  const [open, setOpen] = useState(false);
  return (
    <div className="kb-card overflow-hidden" style={{ borderRadius: 12 }}>
      <button type="button" onClick={() => setOpen((o) => !o)}
        className="w-full flex items-center justify-between p-4 text-left"
        style={{ background: 'transparent', border: 'none', cursor: 'pointer' }}>
        <span className="font-semibold text-sm pr-4" style={{ color: 'var(--kb-ink)' }}>{question}</span>
        <svg className="w-4 h-4 flex-shrink-0 transition-transform duration-200" style={{ transform: open ? 'rotate(180deg)' : 'none', color: 'var(--kb-ink-soft)' }}
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      {open && (
        <div className="px-4 pb-4 text-sm leading-relaxed" style={{ color: 'var(--kb-ink-muted)' }}>{answer}</div>
      )}
    </div>
  );
}
