import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import type { SharedProps } from '../types';

const PAYMENT_METHODS = ['bKash', 'Nagad', 'Rocket', 'SSLCOMMERZ', 'Visa', 'Mastercard', 'Cash on Delivery'];

const SOCIAL_ICONS: Record<string, string> = {
  facebook:  'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z',
  instagram: 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M7.5 2h9A5.5 5.5 0 0122 7.5v9A5.5 5.5 0 0116.5 22h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2z',
  youtube:   'M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z',
  tiktok:    'M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.17 8.17 0 004.77 1.52V6.74a4.85 4.85 0 01-1-.05z',
  twitter:   'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z',
  linkedin:  'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z',
};

export default function Footer() {
  const { site } = usePage<SharedProps>().props;
  const [email, setEmail] = useState('');
  const [done,  setDone]  = useState(false);

  function handleNewsletter(e: React.FormEvent) {
    e.preventDefault();
    if (email.includes('@')) setDone(true);
  }

  const activeSocials = Object.entries(site.socialLinks ?? {})
    .filter(([, url]) => url)
    .map(([platform, url]) => ({ platform, url, icon: SOCIAL_ICONS[platform] }))
    .filter(s => s.icon);

  return (
    <footer style={{ background: 'var(--av-ink)', color: 'var(--av-paper)', marginTop: 0 }}>

      {/* Newsletter section */}
      {site.newsletterEnabled && (
        <div style={{ borderBottom: '1px solid rgba(244,239,229,.1)' }}>
          <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: 'clamp(40px,7vw,60px) var(--av-gutter)', textAlign: 'center' }}>
              <div style={{ fontSize: 10.5, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.34em', color: 'var(--av-gold)', marginBottom: 14 }}>
                The Journal
              </div>
              <h3 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(26px,3vw,38px)', fontWeight: 400, margin: '0 0 14px', lineHeight: 1.04 }}>
                {site.newsletterHeading || 'Join the maison'}
              </h3>
              <p style={{ margin: '0 0 28px', color: 'rgba(244,239,229,.62)', fontSize: 14.5, lineHeight: 1.7, maxWidth: 440, marginLeft: 'auto', marginRight: 'auto' }}>
                Early access to new arrivals, seasonal promotions, and nothing else.
              </p>
              {done ? (
                <div style={{ display: 'inline-flex', alignItems: 'center', gap: 10, color: 'var(--av-gold)', fontSize: 14, letterSpacing: '0.06em' }}>
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"><path d="m5 12 4.5 4.5L19 7"/></svg>
                  Welcome. Check your inbox.
                </div>
              ) : (
                <form onSubmit={handleNewsletter} style={{ display: 'flex', gap: 10, maxWidth: 440, margin: '0 auto', flexWrap: 'wrap' }}>
                  <input
                    type="email" required value={email} onChange={e => setEmail(e.target.value)}
                    placeholder="Email address"
                    style={{ flex: 1, minWidth: 200, border: '1px solid rgba(244,239,229,.2)', background: 'rgba(244,239,229,.06)', padding: '13px 16px', fontSize: 14, color: 'var(--av-paper)', outline: 'none', borderRadius: 2 }}
                  />
                  <button type="submit" style={{ padding: '13px 30px', background: 'var(--av-cognac)', color: '#fff', border: '1px solid var(--av-cognac)', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', borderRadius: 2, cursor: 'pointer' }}>
                    Subscribe
                  </button>
                </form>
              )}
          </div>
        </div>
      )}

      {/* Main grid */}
      <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: 'clamp(48px,9vw,72px) var(--av-gutter) 40px' }}>
        <div style={{ display: 'grid', gridTemplateColumns: '1.4fr 1fr 1fr 1fr', gap: 40 }} className="av-ft-grid">

          {/* Brand column */}
          <div>
            {site.logoUrl
              ? <img src={site.logoUrl} alt={site.name} style={{ height: 36, filter: 'brightness(0) invert(1)', marginBottom: 20 }} />
              : <span style={{ fontFamily: 'var(--av-display)', fontSize: 22, fontWeight: 500, letterSpacing: '0.26em', color: 'var(--av-paper)', display: 'block', marginBottom: 20 }}>{site.name.toUpperCase()}</span>
            }
            {site.tagline && (
              <p style={{ maxWidth: 280, color: 'rgba(244,239,229,.62)', fontSize: 13.5, lineHeight: 1.7, margin: '0 0 20px' }}>{site.tagline}</p>
            )}
            {(site.phone || site.email || site.address) && (
              <p style={{ color: 'rgba(244,239,229,.5)', fontSize: 13, lineHeight: 1.75, margin: '0 0 22px' }}>
                {site.phone && <>{site.phone}<br /></>}
                {site.email && <>{site.email}<br /></>}
                {site.address}
              </p>
            )}
            {activeSocials.length > 0 && (
              <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                {activeSocials.map(s => (
                  <a key={s.platform} href={s.url} title={s.platform} target="_blank" rel="noopener noreferrer"
                    style={{ fontSize: 10.5, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'rgba(244,239,229,.55)', border: '1px solid rgba(244,239,229,.2)', padding: '6px 12px', borderRadius: 2, textDecoration: 'none', transition: 'color .2s, border-color .2s' }}
                    onMouseEnter={e => { const el = e.currentTarget as HTMLElement; el.style.color = 'var(--av-paper)'; el.style.borderColor = 'rgba(244,239,229,.5)'; }}
                    onMouseLeave={e => { const el = e.currentTarget as HTMLElement; el.style.color = 'rgba(244,239,229,.55)'; el.style.borderColor = 'rgba(244,239,229,.2)'; }}>
                    {s.platform.charAt(0).toUpperCase() + s.platform.slice(1)}
                  </a>
                ))}
              </div>
            )}
          </div>

          {/* Nav columns */}
          {[site.footerCol1, site.footerCol2].filter(Boolean).map((col, ci) => (
            <div key={ci}>
              <h4 style={{ margin: '0 0 18px', fontSize: 11, letterSpacing: '0.24em', textTransform: 'uppercase', color: 'var(--av-gold)', fontWeight: 500 }}>{col.title}</h4>
              <ul style={{ listStyle: 'none', margin: 0, padding: 0, display: 'flex', flexDirection: 'column', gap: 12 }}>
                {col.links.map((l, i) => (
                  <li key={i}>
                    <Link href={l.url} style={{ fontSize: 13, color: 'rgba(244,239,229,.72)', letterSpacing: '0.02em', textDecoration: 'none', transition: 'color .2s' }}
                      onMouseEnter={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-paper)'; }}
                      onMouseLeave={e => { (e.currentTarget as HTMLElement).style.color = 'rgba(244,239,229,.72)'; }}>
                      {l.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}

          {/* Quick links */}
          <div>
            <h4 style={{ margin: '0 0 18px', fontSize: 11, letterSpacing: '0.24em', textTransform: 'uppercase', color: 'var(--av-gold)', fontWeight: 500 }}>Shop</h4>
            <ul style={{ listStyle: 'none', margin: 0, padding: 0, display: 'flex', flexDirection: 'column', gap: 12 }}>
              {[['All Products', '/shop'], ['New Arrivals', '/shop?sort=newest'], ['Sale', '/shop?sale=1'], ['Track Order', '/track']].map(([l, href]) => (
                <li key={href}>
                  <Link href={href} style={{ fontSize: 13, color: 'rgba(244,239,229,.72)', textDecoration: 'none', transition: 'color .2s' }}
                    onMouseEnter={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-paper)'; }}
                    onMouseLeave={e => { (e.currentTarget as HTMLElement).style.color = 'rgba(244,239,229,.72)'; }}>
                    {l}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Bottom bar */}
        <div style={{ display: 'flex', justifyContent: 'space-between', flexWrap: 'wrap', gap: 18, marginTop: 56, paddingTop: 26, borderTop: '1px solid rgba(244,239,229,.1)', fontSize: 12, color: 'rgba(244,239,229,.42)', letterSpacing: '0.04em' }}>
          <span>
            {site.copyright
              ? site.copyright.replace('{year}', String(new Date().getFullYear()))
              : `© ${new Date().getFullYear()} ${site.name}. All rights reserved.`}
          </span>
          <div style={{ display: 'flex', gap: 18, flexWrap: 'wrap', alignItems: 'center' }}>
            <span>We accept:</span>
            {PAYMENT_METHODS.map(pm => (
              <span key={pm} style={{ fontSize: 10.5, letterSpacing: '0.06em', opacity: 0.7 }}>{pm}</span>
            ))}
          </div>
        </div>
      </div>

      <style>{`@media(max-width:760px){.av-ft-grid{grid-template-columns:1fr 1fr!important;gap:32px!important}}`}</style>
    </footer>
  );
}
