import { Link, router, usePage } from '@inertiajs/react';
import Layout from '../layouts/Layout';

const nav = [
  { href: '/account',         label: 'Dashboard',  icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { href: '/account/orders',  label: 'Orders',     icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  { href: '/account/reviews', label: 'Reviews',    icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
  { href: '/account/address', label: 'Addresses',  icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z' },
  { href: '/account/refunds', label: 'Refunds',    icon: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6' },
];

interface Props {
  children: React.ReactNode;
  title: string;
  active: string;
}

export default function AccountLayout({ children, title, active }: Props) {
  const logout = () => router.post('/logout');
  const url = usePage().url;

  return (
    <Layout>
      <div style={{ maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '32px var(--av-gutter) 64px' }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', gap: 8, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 16 }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link><span>/</span>
          <span style={{ color: 'var(--av-ink)' }}>Account</span>
        </nav>

        {/* Title */}
        <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(22px,3vw,34px)', fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 28px', letterSpacing: '-0.012em' }}>{title}</h1>

        <div style={{ display: 'grid', gridTemplateColumns: '220px 1fr', gap: 32, alignItems: 'start' }} className="av-account-layout">

          {/* Desktop sidebar */}
          <aside className="av-account-sidebar" style={{ position: 'sticky', top: 88 }}>
            <div style={{ border: '1px solid var(--av-line)', background: 'var(--av-paper)', overflow: 'hidden' }}>
              <div style={{ padding: '14px 16px', borderBottom: '1px solid var(--av-line-soft)', background: 'var(--av-paper-2)' }}>
                <p style={{ fontSize: 10, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: 0 }}>My Account</p>
              </div>
              <nav style={{ padding: '8px 0' }}>
                {nav.map(item => {
                  const isActive = active === item.href || url.startsWith(item.href + '/') && item.href !== '/account' || active === item.href && url === item.href;
                  const activeExact = active === item.href;
                  return (
                    <Link key={item.href} href={item.href}
                      style={{
                        display: 'flex', alignItems: 'center', gap: 10, padding: '11px 16px', fontSize: 13, textDecoration: 'none',
                        color: activeExact ? 'var(--av-ink)' : 'var(--av-muted)', fontFamily: 'var(--av-sans)',
                        fontWeight: activeExact ? 500 : 400, background: activeExact ? 'var(--av-paper-2)' : 'transparent',
                        borderLeft: activeExact ? '2px solid var(--av-ink)' : '2px solid transparent',
                      }}>
                      <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d={item.icon}/>
                      </svg>
                      {item.label}
                    </Link>
                  );
                })}
                <button onClick={logout}
                  style={{ display: 'flex', width: '100%', alignItems: 'center', gap: 10, padding: '11px 16px', fontSize: 13, background: 'transparent', border: 'none', borderTop: '1px solid var(--av-line-soft)', marginTop: 8, cursor: 'pointer', color: '#b94040', fontFamily: 'var(--av-sans)' }}>
                  <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                  Sign Out
                </button>
              </nav>
            </div>
          </aside>

          {/* Mobile horizontal nav */}
          <div className="av-account-mobile-nav" style={{ display: 'none', overflowX: 'auto', scrollbarWidth: 'none', borderBottom: '1px solid var(--av-line)', marginBottom: 20, gap: 0 }}>
            {nav.map(item => {
              const activeExact = active === item.href;
              return (
                <Link key={item.href} href={item.href}
                  style={{
                    display: 'inline-flex', alignItems: 'center', gap: 6, padding: '12px 14px', fontSize: 12.5, whiteSpace: 'nowrap', textDecoration: 'none',
                    color: activeExact ? 'var(--av-ink)' : 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontWeight: activeExact ? 600 : 400,
                    borderBottom: activeExact ? '2px solid var(--av-ink)' : '2px solid transparent', flexShrink: 0
                  }}>
                  {item.label}
                </Link>
              );
            })}
            <button onClick={logout} style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '12px 14px', fontSize: 12.5, whiteSpace: 'nowrap', background: 'transparent', border: 'none', borderBottom: '2px solid transparent', cursor: 'pointer', color: '#b94040', fontFamily: 'var(--av-sans)', flexShrink: 0 }}>
              Sign Out
            </button>
          </div>

          {/* Content */}
          <main style={{ minWidth: 0 }}>
            {children}
          </main>

        </div>
      </div>

      <style>{`
        @media(max-width: 860px){
          .av-account-layout{ grid-template-columns: 1fr !important; }
          .av-account-sidebar{ display: none !important; }
          .av-account-mobile-nav{ display: flex !important; }
        }
      `}</style>
    </Layout>
  );
}
