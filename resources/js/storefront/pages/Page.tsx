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
      <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line-soft)', padding: '16px 20px' }}>
        <h4 style={{ fontSize: 10.5, fontWeight: 500, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: '0 0 12px' }}>On this page</h4>
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
                fontSize: item.level === 3 ? 12 : 13,
                fontWeight: item.level === 2 ? 500 : 400,
                textDecoration: 'none',
                lineHeight: 1.4,
                transition: 'color .1s, background .1s',
                fontFamily: 'var(--av-sans)',
                color: active === item.id ? 'var(--av-ink)' : 'var(--av-muted)',
                background: active === item.id ? 'var(--av-paper-2)' : 'transparent',
                borderLeft: active === item.id ? '2px solid var(--av-ink)' : '2px solid transparent',
              }}>
              {item.text}
            </a>
          ))}
        </nav>
      </div>

      <div style={{ background: 'var(--av-paper-2)', border: '1px solid var(--av-line-soft)', padding: '16px 20px' }}>
        <div style={{ fontSize: 11, fontWeight: 500, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', marginBottom: 6 }}>Questions?</div>
        <p style={{ fontSize: 13, color: 'var(--av-muted)', margin: '0 0 12px', lineHeight: 1.6, fontFamily: 'var(--av-sans)' }}>
          We reply in Bangla and English, usually within 2 hours.
        </p>
        <Link href="/page/contact" className="av-btn av-btn-primary av-btn-sm" style={{ textDecoration: 'none' }}>
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
      <div style={{ borderBottom: '1px solid var(--av-line)', background: 'var(--av-paper)', padding: '28px 0 24px' }}>
        <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' }}>
          <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 14 }}>
            <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link>
            <span>/</span>
            <span style={{ color: 'var(--av-ink)' }}>{page.title}</span>
          </nav>
          {isLegal && (
            <div style={{ fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 10 }}>
              Legal
            </div>
          )}
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(26px,3vw,36px)', fontWeight: 400, letterSpacing: '-0.012em', margin: 0, color: 'var(--av-ink)', lineHeight: 1.1 }}>
            {page.title}
          </h1>
        </div>
      </div>

      <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' }}>
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
      <div style={{ background: 'var(--av-ink)' }}>
        <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '48px var(--av-gutter)', display: 'flex', flexWrap: 'wrap', gap: 24, alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <h3 style={{ fontFamily: 'var(--av-display)', fontSize: 22, fontWeight: 400, letterSpacing: '-0.012em', margin: '0 0 6px', color: 'var(--av-paper)' }}>Have a question?</h3>
            <p style={{ color: 'rgba(244,239,229,.62)', fontSize: 13.5, margin: 0, fontFamily: 'var(--av-sans)' }}>We reply in Bangla and English, usually within 2 hours.</p>
          </div>
          <Link href="/page/contact" className="av-btn av-btn-secondary av-btn-md" style={{ background: 'var(--av-paper)', color: 'var(--av-ink)', textDecoration: 'none' }}>
            Contact us
          </Link>
        </div>
      </div>
    </Layout>
  );
}
