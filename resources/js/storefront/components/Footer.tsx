import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import type { SharedProps } from '../types';

const PAYMENT_METHODS = ['bKash', 'Nagad', 'Rocket', 'SSLCOMMERZ', 'Visa / Mastercard', 'Cash on Delivery'];

const SOCIAL_LINKS = [
  {
    label: 'Facebook',
    href: '#',
    icon: 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z',
  },
  {
    label: 'Instagram',
    href: '#',
    icon: 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M7.5 2h9A5.5 5.5 0 0122 7.5v9A5.5 5.5 0 0116.5 22h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2z',
  },
  {
    label: 'YouTube',
    href: '#',
    icon: 'M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z',
  },
];

export default function Footer() {
  const { site } = usePage<SharedProps>().props;
  const [email, setEmail] = useState('');

  function handleNewsletter(e: React.FormEvent) {
    e.preventDefault();
    setEmail('');
  }

  return (
    <footer className="kb-footer mt-20">

      {/* Newsletter strip */}
      <div style={{ background: 'rgba(255,255,255,.04)', borderBottom: '1px solid rgba(255,255,255,.08)' }}>
        <div className="max-w-7xl mx-auto px-4 lg:px-6 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="text-white text-center sm:text-left">
            <p className="font-bold">Subscribe to our newsletter</p>
            <p className="text-xs text-slate-400 mt-0.5">Deals, new arrivals, and updates — no spam.</p>
          </div>
          <form onSubmit={handleNewsletter} className="flex gap-2 w-full sm:w-auto max-w-sm">
            <input
              type="email"
              required
              value={email}
              onChange={e => setEmail(e.target.value)}
              className="flex-1 sm:w-56 rounded-lg px-3 py-2.5 text-sm outline-none"
              style={{ background: 'rgba(255,255,255,.1)', color: '#fff', border: '1px solid rgba(255,255,255,.2)' }}
              placeholder="your@email.com"
            />
            <button type="submit" className="kb-btn kb-btn-accent text-sm" style={{ borderRadius: 8, padding: '0 16px' }}>
              Subscribe
            </button>
          </form>
        </div>
      </div>

      {/* Main footer grid */}
      <div className="max-w-7xl mx-auto px-4 lg:px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
        <div className="col-span-2">
          {site.logoUrl
            ? <img src={site.logoUrl} alt={site.name} className="h-8 w-auto mb-3 brightness-0 invert" />
            : <p className="text-xl font-extrabold text-white mb-3">{site.name}</p>
          }
          {site.tagline && <p className="max-w-sm text-slate-400 mb-3">{site.tagline}</p>}
          {(site.phone || site.email || site.address) && (
            <p className="text-slate-400 text-xs leading-relaxed">
              {site.phone && <>{`Tel: ${site.phone}`}<br /></>}
              {site.email && <>{site.email}<br /></>}
              {site.address && site.address}
            </p>
          )}
          <div className="mt-4 flex gap-2">
            {SOCIAL_LINKS.map((s) => (
              <a key={s.label} href={s.href} title={s.label}
                className="w-9 h-9 rounded-full flex items-center justify-center transition-colors"
                style={{ background: 'rgba(255,255,255,.1)' }}
                onMouseEnter={e => (e.currentTarget.style.background = 'rgba(255,255,255,.2)')}
                onMouseLeave={e => (e.currentTarget.style.background = 'rgba(255,255,255,.1)')}>
                <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={s.icon} />
                </svg>
              </a>
            ))}
          </div>
        </div>

        <div>
          <h4>Help</h4>
          <ul className="space-y-2">
            <li><Link href="/track">Track order</Link></li>
            <li><Link href="/page/returns-policy">Returns &amp; refunds</Link></li>
            <li><Link href="/page/shipping-policy">Shipping policy</Link></li>
            <li><Link href="/page/contact">Contact us</Link></li>
            <li><Link href="/shop">Shop</Link></li>
          </ul>
        </div>

        <div>
          <h4>Company</h4>
          <ul className="space-y-2">
            <li><Link href="/page/about-us">About us</Link></li>
            <li><Link href="/blog">Blog</Link></li>
            <li><Link href="/page/privacy-policy">Privacy</Link></li>
            <li><Link href="/page/terms">Terms</Link></li>
            <li><Link href="/account">My Account</Link></li>
          </ul>
        </div>

        <div className="col-span-2 md:col-span-4 mt-2">
          <div className="flex flex-wrap items-center gap-3 text-xs text-slate-400">
            <span>We accept:</span>
            {PAYMENT_METHODS.map((pm) => (
              <span key={pm} className="kb-chip" style={{ background: '#fff', color: '#0F172A' }}>{pm}</span>
            ))}
          </div>
        </div>
      </div>

      <div className="border-t py-4 text-center text-xs text-slate-500" style={{ borderColor: 'rgba(255,255,255,.08)' }}>
        {site.copyright
          ? site.copyright.replace('{year}', String(new Date().getFullYear()))
          : `© ${new Date().getFullYear()} ${site.name} · All rights reserved.`
        }
      </div>
    </footer>
  );
}
