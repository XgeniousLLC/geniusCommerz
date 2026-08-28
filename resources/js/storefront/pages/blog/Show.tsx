import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Layout from '../../layouts/Layout';
import type { BlogCategory, BlogPost, Comment, SharedProps } from '../../types';

interface Props {
  blog: BlogPost;
  related: BlogPost[];
  recent: BlogPost[];
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
      <div style={{ width: 40, height: 40, borderRadius: '50%', background: 'var(--av-ink)', color: '#fff', display: 'grid', placeItems: 'center', fontSize: 14, fontWeight: 700, flexShrink: 0 }}>
        {comment.name[0].toUpperCase()}
      </div>
      <div style={{ flex: 1 }}>
        <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, padding: '16px 20px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8, marginBottom: 8 }}>
            <span style={{ fontWeight: 700, fontSize: 14, color: 'var(--av-ink)' }}>{comment.name}</span>
            <span style={{ fontSize: 12, color: 'var(--av-muted)' }}>{comment.created_at}</span>
          </div>
          <p style={{ fontSize: 14, color: 'var(--av-muted)', lineHeight: 1.55, margin: 0 }}>{comment.body}</p>
        </div>

        {auth.user ? (
          <div style={{ marginTop: 8 }}>
            <button type="button" onClick={() => setReplyOpen((o) => !o)}
              style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontWeight: 600, fontSize: 13, padding: 0 }}>
              ↳ Reply
            </button>
            {replyOpen && (
              <form onSubmit={submitReply} style={{ marginTop: 12, display: 'flex', flexDirection: 'column', gap: 10 }}>
                <textarea name="body" rows={2} placeholder="Write a reply…" required
                  value={data.body} onChange={(e) => setData('body', e.target.value)}
                  style={{ width: '100%', fontSize: 14, padding: '10px 12px', borderRadius: 2, border: '1px solid var(--av-line)', outline: 'none', resize: 'none', fontFamily: 'inherit', boxSizing: 'border-box' }} />
                <button type="submit" disabled={processing}
                  style={{ alignSelf: 'flex-start', height: 34, padding: '0 16px', borderRadius: 2, background: 'var(--av-ink)', color: '#fff', fontWeight: 600, fontSize: 13, border: 'none', cursor: 'pointer' }}>
                  Post reply
                </button>
              </form>
            )}
          </div>
        ) : (
          <p style={{ marginTop: 8, fontSize: 12, color: 'var(--av-muted)' }}>
            <Link href="/login" style={{ fontWeight: 600, color: 'var(--av-ink)', textDecoration: 'none' }}>Sign in</Link> to reply
          </p>
        )}

        {comment.replies.length > 0 && (
          <div style={{ marginTop: 16, paddingLeft: 16, borderLeft: '2px solid var(--av-line)', display: 'flex', flexDirection: 'column', gap: 12 }}>
            {comment.replies.map((reply) => (
              <div key={reply.id} style={{ display: 'flex', gap: 12 }} id={`comment-${reply.id}`}>
                <div style={{ width: 32, height: 32, borderRadius: '50%', background: 'var(--av-paper-2)', color: 'var(--av-ink)', display: 'grid', placeItems: 'center', fontSize: 12, fontWeight: 700, flexShrink: 0 }}>
                  {reply.name[0].toUpperCase()}
                </div>
                <div style={{ flex: 1, background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, padding: '12px 16px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8, marginBottom: 6 }}>
                    <span style={{ fontWeight: 700, fontSize: 13, color: 'var(--av-ink)' }}>{reply.name}</span>
                    <span style={{ fontSize: 11, color: 'var(--av-muted)' }}>{reply.created_at}</span>
                  </div>
                  <p style={{ fontSize: 13, color: 'var(--av-muted)', lineHeight: 1.5, margin: 0 }}>{reply.body}</p>
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
      let cur = list[0]?.id ?? '';
      list.forEach(({ id }) => {
        const el2 = document.getElementById(id);
        if (el2 && window.scrollY >= el2.offsetTop - 120) cur = id;
      });
      setActive(cur);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, [contentRef]);

  if (!items.length) return null;

  return (
    <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, padding: '16px 20px' }}>
      <h4 style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--av-muted)', margin: '0 0 12px' }}>Contents</h4>
      <nav style={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
        {items.map((item) => (
          <a key={item.id} href={`#${item.id}`}
            style={{
              display: 'block', padding: '6px 10px',
              paddingLeft: item.level === 3 ? 20 : 10,
              borderRadius: 6,
              fontSize: item.level === 3 ? 12 : 13,
              fontWeight: item.level === 2 ? 600 : 400,
              textDecoration: 'none', lineHeight: 1.4,
              color: active === item.id ? 'var(--av-ink)' : 'var(--av-muted)',
              background: active === item.id ? 'var(--av-paper-2)' : 'transparent',
            }}>
            {item.text}
          </a>
        ))}
      </nav>
    </div>
  );
}

function FaqItem({ question, answer }: { question: string; answer: string }) {
  const [open, setOpen] = useState(false);
  return (
    <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, overflow: 'hidden' }}>
      <button type="button" onClick={() => setOpen((o) => !o)}
        style={{ width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: 16, textAlign: 'left', background: 'transparent', border: 'none', cursor: 'pointer' }}>
        <span style={{ fontWeight: 600, fontSize: 14, color: 'var(--av-ink)', paddingRight: 16 }}>{question}</span>
        <svg style={{ width: 16, height: 16, flexShrink: 0, color: 'var(--av-muted)', transition: 'transform .2s', transform: open ? 'rotate(180deg)' : 'none' }}
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      {open && (
        <div style={{ padding: '0 16px 16px', fontSize: 14, color: 'var(--av-muted)', lineHeight: 1.6 }}>{answer}</div>
      )}
    </div>
  );
}

function NewsletterCard() {
  const [email, setEmail] = useState('');
  const [done, setDone] = useState(false);

  function submit(e: React.FormEvent) {
    e.preventDefault();
    if (email) setDone(true);
  }

  return (
    <div style={{ background: 'linear-gradient(135deg, var(--av-ink) 0%, var(--av-cognac) 100%)', color: '#fff', borderRadius: 2, padding: '16px 20px', border: 'none' }}>
      <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'rgba(255,255,255,.7)', marginBottom: 6 }}>Newsletter</div>
      <div style={{ fontWeight: 700, fontSize: 14, marginBottom: 6 }}>Get the best of klixbd</div>
      <div style={{ fontSize: 12, color: 'rgba(255,255,255,.8)', marginBottom: 14, lineHeight: 1.5 }}>
        Weekly buying guides, sales, and reviews.
      </div>
      {done ? (
        <div style={{ fontSize: 13, fontWeight: 600, color: '#A3D9C0' }}>Subscribed — thanks!</div>
      ) : (
        <form onSubmit={submit}>
          <input type="email" placeholder="your@email.com" value={email} onChange={(e) => setEmail(e.target.value)}
            required
            style={{ width: '100%', height: 38, padding: '0 12px', borderRadius: 2, border: '1px solid rgba(255,255,255,.25)', background: 'rgba(255,255,255,.12)', color: '#fff', outline: 'none', marginBottom: 8, fontSize: 13, boxSizing: 'border-box' }} />
          <button type="submit"
            style={{ width: '100%', height: 36, borderRadius: 2, border: 'none', background: 'var(--av-paper)', color: 'var(--av-ink)', fontWeight: 700, fontSize: 13, cursor: 'pointer' }}>
            Subscribe
          </button>
        </form>
      )}
    </div>
  );
}

export default function BlogShow({ blog, related, recent, categories, comments }: Props) {
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

      <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--av-muted)', padding: '16px 0 8px' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-800">Home</Link>
          <span>/</span>
          <Link href="/blog" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-800">Journal</Link>
          <span>/</span>
          <span style={{ color: 'var(--av-ink)' }}>{blog.title.length > 50 ? blog.title.substring(0, 50) + '…' : blog.title}</span>
        </nav>

        <div className="grid lg:grid-cols-[1fr_280px]" style={{ gap: 48, padding: '16px 0', alignItems: 'start' }}>

          {/* Article */}
          <article>
            {blog.blogCategory ? (
              <Link href={`/blog/c/${blog.blogCategory.slug}`}
                style={{ display: 'inline-block', background: 'var(--av-paper-2)', color: 'var(--av-ink)', padding: '5px 12px', borderRadius: 999, fontSize: 12, fontWeight: 700, textDecoration: 'none', marginBottom: 16 }}>
                {blog.category_name}
              </Link>
            ) : blog.category_name ? (
              <span style={{ display: 'inline-block', background: 'var(--av-paper-2)', color: 'var(--av-ink)', padding: '5px 12px', borderRadius: 999, fontSize: 12, fontWeight: 700, marginBottom: 16 }}>
                {blog.category_name}
              </span>
            ) : null}

            <h1 style={{ fontSize: 36, fontWeight: 400, fontFamily: 'var(--av-display)', letterSpacing: '-0.025em', lineHeight: 1.08, margin: '0 0 16px', color: 'var(--av-ink)' }}>
              {blog.title}
            </h1>

            <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'center', gap: 12, padding: '16px 0', borderBottom: '1px solid var(--av-line)', fontSize: 13, color: 'var(--av-muted)' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                <div style={{ width: 36, height: 36, borderRadius: '50%', background: 'var(--av-ink)', color: '#fff', display: 'grid', placeItems: 'center', fontSize: 14, fontWeight: 700, flexShrink: 0 }}>
                  {blog.author_display_name[0]?.toUpperCase()}
                </div>
                <div>
                  <div style={{ fontWeight: 700, fontSize: 14, color: 'var(--av-ink)' }}>{blog.author_display_name}</div>
                </div>
              </div>
              <span>·</span>
              <span>{blog.published_at}</span>
              <span>·</span>
              <span>{blog.read_time}</span>
            </div>

            {blog.cover_url && (
              <img style={{ width: '100%', borderRadius: 2, marginTop: 24, objectFit: 'cover', maxHeight: 480 }}
                src={blog.cover_url} alt={blog.title} />
            )}

            <div ref={contentRef} className="kb-prose" style={{ marginTop: 28, color: '#334155' }}
              dangerouslySetInnerHTML={{ __html: blog.content ?? '' }} />

            {/* FAQs */}
            {blog.faqs && blog.faqs.length > 0 && (
              <div style={{ marginTop: 40 }}>
                <h2 style={{ fontSize: 20, fontWeight: 700, marginBottom: 16, color: 'var(--av-ink)' }}>Frequently Asked Questions</h2>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {blog.faqs.map((faq, i) => (
                    <FaqItem key={i} question={faq.question} answer={faq.answer} />
                  ))}
                </div>
              </div>
            )}

            {/* Share */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '20px 0', borderTop: '1px solid var(--av-line)', borderBottom: '1px solid var(--av-line)', margin: '28px 0' }}>
              <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--av-muted)' }}>Share this article</span>
              <a href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(typeof window !== 'undefined' ? window.location.href : '')}`}
                target="_blank" rel="noopener"
                style={{ width: 36, height: 36, borderRadius: '50%', border: '1px solid var(--av-line)', background: 'var(--av-paper)', display: 'grid', placeItems: 'center', color: '#1877F2', textDecoration: 'none' }}>
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                </svg>
              </a>
              <button
                onClick={() => typeof navigator !== 'undefined' && navigator.clipboard?.writeText(window.location.href)}
                style={{ width: 36, height: 36, borderRadius: '50%', border: '1px solid var(--av-line)', background: 'var(--av-paper)', display: 'grid', placeItems: 'center', color: 'var(--av-muted)', cursor: 'pointer' }}
                title="Copy link">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
              </button>
            </div>

            {/* Author card */}
            <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, padding: 24, display: 'grid', gridTemplateColumns: '56px 1fr', gap: 16, alignItems: 'center', marginBottom: 32 }}>
              <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'var(--av-ink)', color: '#fff', display: 'grid', placeItems: 'center', fontSize: 22, fontWeight: 700 }}>
                {blog.author_display_name[0]?.toUpperCase()}
              </div>
              <div>
                <h4 style={{ margin: 0, fontSize: 15, fontWeight: 700, color: 'var(--av-ink)' }}>Written by {blog.author_display_name}</h4>
                <p style={{ margin: '2px 0 0', fontSize: 13, color: 'var(--av-muted)' }}>Writes about the things they actually use.</p>
              </div>
            </div>

            {/* Comments */}
            <div id="comments">
              {flash.comment_success && (
                <div style={{ marginBottom: 24, padding: '12px 16px', borderRadius: 2, background: '#f0fdf4', color: '#15803d', border: '1px solid #bbf7d0', fontSize: 14, fontWeight: 500 }}>
                  {flash.comment_success}
                </div>
              )}

              {comments.length > 0 && (
                <>
                  <h3 style={{ fontSize: 20, fontWeight: 700, letterSpacing: '-0.01em', margin: '0 0 16px', color: 'var(--av-ink)' }}>
                    {comments.length} Comment{comments.length !== 1 ? 's' : ''}
                  </h3>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: 20, marginBottom: 32 }}>
                    {comments.map((c) => <CommentItem key={c.id} comment={c} blogId={blog.id} />)}
                  </div>
                </>
              )}

              {auth.user ? (
                <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, padding: 28 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                    <div style={{ width: 36, height: 36, borderRadius: '50%', background: 'var(--av-ink)', color: '#fff', display: 'grid', placeItems: 'center', fontSize: 14, fontWeight: 700 }}>
                      {auth.user.name[0].toUpperCase()}
                    </div>
                    <div>
                      <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--av-ink)' }}>{auth.user.name}</div>
                      <div style={{ fontSize: 12, color: 'var(--av-muted)' }}>Commenting as yourself</div>
                    </div>
                  </div>
                  <form onSubmit={submitComment} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                    <div>
                      <label style={{ display: 'block', fontSize: 12, fontWeight: 600, marginBottom: 6, color: 'var(--av-ink)' }}>
                        Comment <span style={{ color: '#ef4444' }}>*</span>
                      </label>
                      <textarea name="body" rows={5} placeholder="Share your thoughts…" required
                        value={data.body} onChange={(e) => setData('body', e.target.value)}
                        style={{ width: '100%', padding: '10px 14px', borderRadius: 2, border: '1px solid var(--av-line)', fontSize: 14, outline: 'none', resize: 'none', fontFamily: 'inherit', boxSizing: 'border-box', background: '#f8fafc', color: 'var(--av-ink)' }} />
                      {errors.body && <div style={{ fontSize: 12, color: '#dc2626', marginTop: 4 }}>{errors.body}</div>}
                    </div>
                    <div>
                      <button type="submit" disabled={processing}
                        style={{ height: 44, padding: '0 24px', borderRadius: 2, background: processing ? 'var(--av-muted)' : 'var(--av-ink)', color: '#fff', fontWeight: 600, fontSize: 15, border: 'none', cursor: processing ? 'not-allowed' : 'pointer' }}>
                        Post Comment
                      </button>
                    </div>
                  </form>
                </div>
              ) : (
                <p style={{ fontSize: 14, color: 'var(--av-muted)' }}>
                  <Link href="/login" style={{ fontWeight: 700, color: 'var(--av-ink)', textDecoration: 'none' }}>Sign in</Link>
                  {' or '}
                  <Link href="/register" style={{ fontWeight: 700, color: 'var(--av-ink)', textDecoration: 'none' }}>create an account</Link>
                  {' to leave a comment.'}
                </p>
              )}
            </div>
          </article>

          {/* Sidebar */}
          <aside style={{ position: 'sticky', top: 96, display: 'flex', flexDirection: 'column', gap: 16 }}>
            {blog.enable_toc && <TocBox contentRef={contentRef} />}

            {categories.length > 0 && (
              <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, padding: '16px 20px' }}>
                <h4 style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--av-muted)', margin: '0 0 12px' }}>Categories</h4>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                  {categories.map((cat) => (
                    <Link key={cat.id} href={`/blog/c/${cat.slug}`}
                      style={{
                        display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                        padding: '8px 10px', borderRadius: 2, fontSize: 13, textDecoration: 'none',
                        ...(blog.blog_category_id === cat.id
                          ? { background: 'var(--av-paper-2)', color: 'var(--av-ink)', fontWeight: 600 }
                          : { color: 'var(--av-muted)' }),
                      }}>
                      <span>{cat.name}</span>
                      <span style={{ fontSize: 11, padding: '1px 7px', borderRadius: 999, background: 'var(--av-paper-2)', color: 'var(--av-muted)' }}>
                        {cat.blogs_count}
                      </span>
                    </Link>
                  ))}
                </div>
              </div>
            )}

            {recent.length > 0 && (
              <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, padding: '16px 20px' }}>
                <h4 style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--av-muted)', margin: '0 0 12px' }}>Recent Posts</h4>
                <div>
                  {recent.map((b, i) => (
                    <Link key={b.id} href={`/blog/${b.slug}`}
                      style={{
                        display: 'flex', gap: 10, alignItems: 'flex-start', textDecoration: 'none',
                        padding: '8px 0',
                        borderBottom: i < recent.length - 1 ? '1px solid var(--av-line-soft)' : 'none',
                      }}>
                      <div style={{ width: 56, height: 56, borderRadius: 2, flexShrink: 0, overflow: 'hidden', background: 'var(--av-paper-2)' }}>
                        {b.cover_url
                          ? <img src={b.cover_url} alt={b.title} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                          : null
                        }
                      </div>
                      <div>
                        <div style={{ fontSize: 13, fontWeight: 600, lineHeight: 1.35, color: 'var(--av-ink)',
                          display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                          {b.title}
                        </div>
                        <div style={{ fontSize: 11, color: 'var(--av-muted)', marginTop: 4 }}>
                          {b.published_at} · {b.read_time}
                        </div>
                      </div>
                    </Link>
                  ))}
                </div>
              </div>
            )}

            <NewsletterCard />
          </aside>
        </div>

        {/* Related articles */}
        {related.length > 0 && (
          <div style={{ marginTop: 48, paddingTop: 32, borderTop: '1px solid var(--av-line)', paddingBottom: 64 }}>
            <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 24 }}>
              <div>
                <h2 style={{ fontSize: 22, fontWeight: 700, letterSpacing: '-0.01em', margin: 0, color: 'var(--av-ink)' }}>Related Articles</h2>
                <div style={{ fontSize: 13, color: 'var(--av-muted)', marginTop: 4 }}>More from the Journal you might like.</div>
              </div>
              <Link href="/blog" style={{ fontSize: 13, fontWeight: 600, color: 'var(--av-ink)', textDecoration: 'none' }}>All articles →</Link>
            </div>
            <div className="grid sm:grid-cols-2 lg:grid-cols-3" style={{ gap: 20 }}>
              {related.map((rel) => (
                <Link key={rel.id} href={`/blog/${rel.slug}`}
                  style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 2, overflow: 'hidden', display: 'flex', flexDirection: 'column', textDecoration: 'none' }}
                  className="hover:shadow-md hover:-translate-y-0.5 transition-all">
                  <div style={{ height: 160, background: 'var(--av-paper-2)', overflow: 'hidden' }}>
                    {rel.cover_url
                      ? <img src={rel.cover_url} alt={rel.title} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                      : null
                    }
                  </div>
                  <div style={{ padding: '16px 18px', flex: 1, display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {rel.category_name && (
                      <span style={{ display: 'inline-block', background: 'var(--av-paper-2)', color: 'var(--av-ink)', padding: '2px 8px', borderRadius: 4, fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em', alignSelf: 'flex-start' }}>
                        {rel.category_name}
                      </span>
                    )}
                    <div style={{ fontWeight: 700, fontSize: 15, lineHeight: 1.3, color: 'var(--av-ink)',
                      display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                      {rel.title}
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--av-muted)', marginTop: 'auto' }}>
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
      </div>
    </Layout>
  );
}
