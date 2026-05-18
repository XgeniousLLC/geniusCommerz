import { Head, Link } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Layout from '../layouts/Layout';
import type { StaticPage } from '../types';

const LEGAL_SLUGS = ['privacy', 'privacy-policy', 'refund', 'refund-policy', 'terms', 'terms-and-conditions', 'shipping-policy'];

function TocBox({ contentRef }: { contentRef: React.RefObject<HTMLDivElement | null> }) {
  const [items, setItems] = useState<{ id: string; text: string; level: number }[]>([]);
  const [active, setActive] = useState('');

  useEffect(() => {
    const el = contentRef.current;
    if (!el) return;
    const headings = el.querySelectorAll('h2, h3');
    if (!headings.length) return;
    const list = Array.from(headings).map((h, i) => {
      if (!h.id) h.id = `toc-s-${i}`;
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
    onScroll();
    return () => window.removeEventListener('scroll', onScroll);
  }, [contentRef]);

  if (!items.length) return null;

  return (
    <div style={{ position: 'sticky', top: 96, display: 'flex', flexDirection: 'column', gap: 12 }}>
      <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '16px 20px' }}>
        <h4 style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--kb-ink-soft)', margin: '0 0 12px' }}>On this page</h4>
        <nav style={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
          {items.map((item) => (
            <a key={item.id} href={`#${item.id}`}
              onClick={(e) => {
                e.preventDefault();
                const t = document.getElementById(item.id);
                if (t) window.scrollTo({ top: t.offsetTop - 72, behavior: 'smooth' });
              }}
              style={{
                display: 'block',
                padding: '6px 10px',
                paddingLeft: item.level === 3 ? 22 : 10,
                borderRadius: 6,
                fontSize: item.level === 3 ? 12 : 13,
                fontWeight: item.level === 2 ? 600 : 400,
                textDecoration: 'none',
                lineHeight: 1.4,
                transition: 'color .1s, background .1s',
                color: active === item.id ? 'var(--kb-primary)' : 'var(--kb-ink-muted)',
                background: active === item.id ? 'var(--kb-primary-50)' : 'transparent',
              }}>
              {item.text}
            </a>
          ))}
        </nav>
      </div>

      <div style={{ background: 'var(--kb-primary-50)', border: '1px solid #C8D6EF', borderRadius: 12, padding: '16px 20px' }}>
        <div style={{ fontSize: 12, fontWeight: 700, color: 'var(--kb-primary)', marginBottom: 6 }}>Questions?</div>
        <p style={{ fontSize: 13, color: 'var(--kb-ink-muted)', margin: '0 0 12px', lineHeight: 1.5 }}>
          Our team replies in Bangla and English, usually within 2 hours.
        </p>
        <Link href="/page/contact"
          style={{ display: 'inline-flex', alignItems: 'center', height: 34, padding: '0 14px', borderRadius: 7, background: 'var(--kb-primary)', color: '#fff', fontWeight: 600, fontSize: 12, textDecoration: 'none' }}>
          Contact us
        </Link>
      </div>
    </div>
  );
}

interface Props {
  page: StaticPage;
}

export default function Page({ page }: Props) {
  const meta = page.metaInformation;
  const contentRef = useRef<HTMLDivElement>(null);
  const isLegal = LEGAL_SLUGS.includes(page.slug);
  const hasHeadings = (page.content.match(/<h[23]/gi) ?? []).length >= 3;

  return (
    <Layout>
      <Head>
        <title>{meta?.meta_title || page.title}</title>
        {meta?.meta_description && <meta name="description" content={meta.meta_description} />}
      </Head>

      {/* Hero */}
      <div style={{ borderBottom: '1px solid var(--kb-border)', background: '#fff', padding: '28px 0 24px' }}>
        <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
          <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--kb-ink-soft)', marginBottom: 14 }}>
            <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-800">Home</Link>
            <span>/</span>
            <span style={{ color: 'var(--kb-ink)' }}>{page.title}</span>
          </nav>
          {isLegal && (
            <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--kb-primary)', marginBottom: 10 }}>
              Legal · klixbd
            </div>
          )}
          <h1 style={{ fontSize: 34, fontWeight: 800, letterSpacing: '-0.02em', margin: 0, color: 'var(--kb-ink)', lineHeight: 1.1 }}>
            {page.title}
          </h1>
        </div>
      </div>

      <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px' }}>
        {hasHeadings ? (
          <div className="grid lg:grid-cols-[260px_1fr]" style={{ gap: 48, padding: '40px 0 80px', alignItems: 'start' }}>
            <TocBox contentRef={contentRef} />
            <div ref={contentRef} className="kb-prose" dangerouslySetInnerHTML={{ __html: page.content }} />
          </div>
        ) : (
          <div style={{ maxWidth: 760, margin: '0 auto', padding: '40px 0 80px' }}>
            <div ref={contentRef} className="kb-prose" dangerouslySetInnerHTML={{ __html: page.content }} />
          </div>
        )}
      </div>

      {/* Footer CTA */}
      <div style={{ background: 'var(--kb-ink)' }}>
        <div style={{ maxWidth: 1280, margin: '0 auto', padding: '48px 24px', display: 'flex', flexWrap: 'wrap', gap: 24, alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <h3 style={{ fontSize: 22, fontWeight: 800, letterSpacing: '-0.02em', margin: '0 0 6px', color: '#fff' }}>Have a question we didn't answer?</h3>
            <p style={{ color: '#B5C4E5', fontSize: 14, margin: 0 }}>Our team replies in Bangla and English, usually within 2 hours.</p>
          </div>
          <Link href="/page/contact"
            style={{ display: 'inline-flex', alignItems: 'center', height: 44, padding: '0 24px', borderRadius: 10, background: '#fff', color: 'var(--kb-ink)', fontWeight: 700, fontSize: 14, textDecoration: 'none', whiteSpace: 'nowrap' }}>
            Contact us
          </Link>
        </div>
      </div>
    </Layout>
  );
}
