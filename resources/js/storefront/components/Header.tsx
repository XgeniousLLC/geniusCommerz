import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import { useWishlistStore } from '../store/wishlistStore';
import type { SharedProps } from '../types';
import CurrencySwitcher from './CurrencySwitcher';
import LocaleSwitcher from './LocaleSwitcher';
import SearchBox from './SearchBox';

/* ── Thin SVG icons matching Aveline's stroke style ── */
const stroke = { fill: 'none', stroke: 'currentColor', strokeWidth: 1.4, strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const };
const IcoSearch = () => <svg viewBox="0 0 24 24" width="20" height="20" {...stroke}><circle cx="11" cy="11" r="6"/><path {...stroke} d="m20 20-3.6-3.6"/></svg>;
const IcoUser   = () => <svg viewBox="0 0 24 24" width="20" height="20" {...stroke}><circle {...stroke} cx="12" cy="8.5" r="3.5"/><path {...stroke} d="M5 20c1.2-3.6 4-5 7-5s5.8 1.4 7 5"/></svg>;
const IcoBag    = () => <svg viewBox="0 0 24 24" width="20" height="20" {...stroke}><path {...stroke} d="M5 9h14l-1 11H6L5 9Z"/><path {...stroke} d="M8.5 9V7.5a3.5 3.5 0 0 1 7 0V9"/></svg>;
const IcoHeart  = () => <svg viewBox="0 0 24 24" width="20" height="20" {...stroke}><path {...stroke} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>;
const IcoMenu   = () => <svg viewBox="0 0 24 24" width="22" height="22" {...stroke}><path {...stroke} d="M4 7h16M4 12h16M4 17h16"/></svg>;
const IcoClose  = () => <svg viewBox="0 0 24 24" width="20" height="20" {...stroke}><path {...stroke} d="m6 6 12 12M18 6 6 18"/></svg>;
const IcoTruck  = () => <svg viewBox="0 0 24 24" width="19" height="19" {...stroke}><path {...stroke} d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle {...stroke} cx="7" cy="18" r="1.6"/><circle {...stroke} cx="17.5" cy="18" r="1.6"/></svg>;

export default function Header() {
  const { site, auth } = usePage<SharedProps>().props;
  const [scrolled,    setScrolled]    = useState(false);
  const [mobileOpen,  setMobileOpen]  = useState(false);
  const [userOpen,    setUserOpen]    = useState(false);
  const [searchOpen,  setSearchOpen]  = useState(false);
  const url         = usePage().url;
  const openCart    = useCartStore(s => s.openCart);
  const { count }   = useCartDerived();
  const wishlistCount = useWishlistStore(s => s.items.length);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    window.addEventListener('scroll', onScroll);
    onScroll();
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? 'hidden' : '';
    return () => { document.body.style.overflow = ''; };
  }, [mobileOpen]);

  useEffect(() => { setMobileOpen(false); setUserOpen(false); }, [url]);

  const navItems = (site.mainNav && site.mainNav.length > 0)
    ? site.mainNav
    : [
        { label: 'Shop',  url: '/shop',  target: '_self', children: [] },
        { label: 'Blog',  url: '/blog',  target: '_self', children: [] },
        { label: 'Track', url: '/track', target: '_self', children: [] },
      ];

  const badgeStyle: React.CSSProperties = {
    position: 'absolute', top: -7, right: -9,
    background: 'var(--av-cognac)', color: '#fff',
    fontSize: 9.5, fontWeight: 600,
    width: 16, height: 16, borderRadius: '50%',
    display: 'flex', alignItems: 'center', justifyContent: 'center',
  };

  const iconBtn: React.CSSProperties = {
    display: 'flex', alignItems: 'center', position: 'relative',
    color: 'var(--av-ink)', background: 'none', border: 'none', cursor: 'pointer', padding: 2,
  };

  return (
    <header style={{ position: 'relative', zIndex: 60 }}>

      {/* ── Announce bar ── */}
      {site.announceBar && (
        <div style={{ background: 'var(--av-ink)', color: 'var(--av-paper)', height: 38, display: 'flex', alignItems: 'center', justifyContent: 'center', overflow: 'hidden' }}>
          <span style={{ fontSize: 10.5, letterSpacing: '0.26em', textTransform: 'uppercase', opacity: 0.86 }}>
            {site.announceBar}
          </span>
        </div>
      )}

      {/* ── Sticky bar ── */}
      <div style={{
        position: 'sticky', top: 0, zIndex: 60,
        background: scrolled ? 'rgba(251,248,241,.94)' : 'var(--av-paper)',
        backdropFilter: scrolled ? 'saturate(140%) blur(8px)' : 'none',
        borderBottom: '1px solid var(--av-line-soft)',
        transition: 'background .4s, height .35s',
      }}>
        <div style={{
          maxWidth: 'var(--av-maxw)', margin: '0 auto',
          padding: '0 var(--av-gutter)',
          height: scrolled ? 64 : 74,
          display: 'grid', gridTemplateColumns: '1fr auto 1fr',
          alignItems: 'center', gap: 16,
          transition: 'height .35s',
        }}>

          {/* Left: desktop nav + mobile burger */}
          <div style={{ display: 'flex', alignItems: 'center', gap: 28 }}>
            <button onClick={() => setMobileOpen(true)} className="av-burger"
              style={{ display: 'none', color: 'var(--av-ink)', background: 'none', border: 'none', cursor: 'pointer', padding: '8px 8px 8px 0' }}
              aria-label="Menu">
              <IcoMenu />
            </button>
            <nav className="av-hdr-nav" style={{ display: 'flex', gap: 28 }}>
              {navItems.map(item => (
                <NavLink key={item.label} href={item.url || '#'} target={item.target} active={url.startsWith(item.url || '__')}>
                  {item.label}
                </NavLink>
              ))}
            </nav>
          </div>

          {/* Center: brand */}
          <Link href="/" style={{ display: 'inline-flex', alignItems: 'center', gap: 12, textDecoration: 'none' }}>
            {site.logoUrl ? (
              <img
                src={site.logoUrl}
                alt={site.name}
                style={{ height: scrolled ? 36 : 40, width: 'auto', transition: 'height .35s', objectFit: 'contain' }}
              />
            ) : (
              <span style={{
                fontFamily: 'var(--av-display)', fontWeight: 500,
                fontSize: scrolled ? 19 : 22, transition: 'font-size .35s',
                letterSpacing: '0.26em', color: 'var(--av-ink)',
                paddingLeft: '0.26em',
              }}>
                {site.name.toUpperCase()}
              </span>
            )}
          </Link>

          {/* Right: utilities */}
          <div style={{ display: 'flex', gap: 16, justifyContent: 'flex-end', alignItems: 'center' }}>

            {/* Search */}
            <button onClick={() => setSearchOpen(o => !o)} style={iconBtn} title="Search" className="av-search-icon">
              <IcoSearch />
            </button>

            {/* Track order */}
            <Link href="/track" style={{ ...iconBtn, textDecoration: 'none' }} title="Track order" className="av-track-icon">
              <IcoTruck />
            </Link>

            {/* Locale + Currency */}
            <div className="av-locale-wrap" style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
              <LocaleSwitcher />
              <CurrencySwitcher />
            </div>

            {/* Wishlist */}
            <Link href="/wishlist" style={{ ...iconBtn, textDecoration: 'none' }} title="Wishlist">
              <IcoHeart />
              {wishlistCount > 0 && (
                <span style={badgeStyle}>{wishlistCount > 9 ? '9+' : wishlistCount}</span>
              )}
            </Link>

            {/* Cart */}
            <button onClick={openCart} style={iconBtn} title="Cart">
              <IcoBag />
              {count > 0 && (
                <span style={badgeStyle}>{count > 9 ? '9+' : count}</span>
              )}
            </button>

            {/* User */}
            {auth.user ? (
              <div style={{ position: 'relative' }}>
                <button onClick={() => setUserOpen(o => !o)} style={iconBtn}>
                  <IcoUser />
                </button>
                {userOpen && (
                  <>
                    <div style={{ position: 'fixed', inset: 0, zIndex: 40 }} onClick={() => setUserOpen(false)} />
                    <div style={{
                      position: 'absolute', right: 0, top: '100%', marginTop: 10,
                      width: 220, background: 'var(--av-paper)',
                      border: '1px solid var(--av-line)', borderRadius: 3,
                      boxShadow: '0 12px 40px rgba(31,26,21,.12)', zIndex: 50,
                    }}>
                      <div style={{ padding: '14px 16px', borderBottom: '1px solid var(--av-line-soft)' }}>
                        <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: 'var(--av-ink)' }}>{auth.user.name}</p>
                        <p style={{ margin: '3px 0 0', fontSize: 11.5, color: 'var(--av-muted)' }}>{auth.user.email}</p>
                      </div>
                      {[
                        { href: '/account',         label: 'Dashboard'    },
                        { href: '/account/orders',  label: 'My Orders'    },
                        { href: '/account/reviews', label: 'My Reviews'   },
                        { href: '/account/address', label: 'Address Book' },
                        { href: '/account/refunds', label: 'Refunds'      },
                      ].map(item => (
                        <Link key={item.href} href={item.href} onClick={() => setUserOpen(false)}
                          style={{ display: 'block', padding: '11px 16px', fontSize: 13, color: 'var(--av-ink)', textDecoration: 'none', transition: 'background .15s' }}
                          onMouseEnter={e => { (e.currentTarget as HTMLElement).style.background = 'var(--av-ivory)'; }}
                          onMouseLeave={e => { (e.currentTarget as HTMLElement).style.background = 'transparent'; }}>
                          {item.label}
                        </Link>
                      ))}
                      <div style={{ borderTop: '1px solid var(--av-line-soft)', marginTop: 4, paddingTop: 4 }}>
                        <button
                          onClick={() => { setUserOpen(false); router.post('/logout'); }}
                          style={{ width: '100%', textAlign: 'left', padding: '11px 16px', fontSize: 13, background: 'none', border: 'none', cursor: 'pointer', color: '#c04', transition: 'background .15s' }}
                          onMouseEnter={e => { (e.currentTarget as HTMLElement).style.background = 'rgba(204,0,68,.06)'; }}
                          onMouseLeave={e => { (e.currentTarget as HTMLElement).style.background = 'transparent'; }}>
                          Sign out
                        </button>
                      </div>
                    </div>
                  </>
                )}
              </div>
            ) : (
              <Link href="/login" style={{ ...iconBtn, textDecoration: 'none' }} title="Sign in">
                <IcoUser />
              </Link>
            )}
          </div>
        </div>

        {/* Search expand row */}
        {searchOpen && (
          <div style={{ borderTop: '1px solid var(--av-line-soft)', padding: '12px var(--av-gutter)' }}>
            <SearchBox />
          </div>
        )}
      </div>

      {/* ── Mobile menu overlay ── */}
      <div style={{ position: 'fixed', inset: 0, zIndex: 120, pointerEvents: mobileOpen ? 'auto' : 'none' }} aria-hidden={!mobileOpen}>
        <div
          onClick={() => setMobileOpen(false)}
          style={{
            position: 'absolute', inset: 0,
            background: 'rgba(20,16,12,.42)',
            opacity: mobileOpen ? 1 : 0,
            transition: mobileOpen ? 'none' : 'opacity .3s ease',
          }}
        />
        <aside style={{
          position: 'absolute', top: 0, left: 0, height: '100%',
          width: 'min(330px,86vw)',
          background: 'var(--av-paper)',
          display: 'flex', flexDirection: 'column',
          transform: mobileOpen ? 'translateX(0)' : 'translateX(-100%)',
          transition: mobileOpen ? 'none' : 'transform .4s cubic-bezier(.2,.7,.3,1)',
          boxShadow: '30px 0 60px -30px rgba(20,16,12,.5)',
        }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '20px 22px', borderBottom: '1px solid var(--av-line-soft)' }}>
            <Link href="/" onClick={() => setMobileOpen(false)} style={{ textDecoration: 'none' }}>
              {site.logoUrl
                ? <img src={site.logoUrl} alt={site.name} style={{ height: 30 }} />
                : <span style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 500, letterSpacing: '0.22em', color: 'var(--av-ink)' }}>{site.name.toUpperCase()}</span>
              }
            </Link>
            <button onClick={() => setMobileOpen(false)} style={{ display: 'flex', color: 'var(--av-ink)', background: 'none', border: 'none', cursor: 'pointer' }} aria-label="Close">
              <IcoClose />
            </button>
          </div>

          <nav style={{ display: 'flex', flexDirection: 'column', padding: '10px 0', flex: 1 }}>
            {navItems.map(item => (
              <Link key={item.label} href={item.url || '#'} onClick={() => setMobileOpen(false)}
                style={{ textAlign: 'left', padding: '16px 22px', fontFamily: 'var(--av-display)', fontSize: 24, color: 'var(--av-ink)', borderBottom: '1px solid var(--av-line-soft)', textDecoration: 'none' }}>
                {item.label}
              </Link>
            ))}
          </nav>

          <div style={{ display: 'flex', flexDirection: 'column', gap: 4, padding: '16px 22px', borderTop: '1px solid var(--av-line-soft)' }}>
            <div style={{ marginBottom: 12 }}>
              <SearchBox />
            </div>
            <div style={{ display: 'flex', gap: 12, marginBottom: 10 }}>
              <LocaleSwitcher />
              <CurrencySwitcher />
            </div>
            {([['Track my order', '/track'], ['Wishlist', '/wishlist']] as [string, string][]).map(([l, href]) => (
              <Link key={href} href={href} onClick={() => setMobileOpen(false)}
                style={{ padding: '8px 0', fontSize: 12, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-muted)', textDecoration: 'none' }}>
                {l}
              </Link>
            ))}
          </div>
        </aside>
      </div>

      <style>{`
        @media (max-width: 860px) {
          .av-hdr-nav  { display: none !important; }
          .av-burger   { display: flex !important; }
        }
        @media (max-width: 560px) {
          .av-track-icon, .av-search-icon, .av-locale-wrap { display: none !important; }
        }
      `}</style>
    </header>
  );
}

/* ── Underline-hover nav link ── */
function NavLink({ href, target, active, children }: { href: string; target?: string; active?: boolean; children: React.ReactNode }) {
  const [hov, setHov] = useState(false);
  return (
    <Link
      href={href}
      target={target}
      style={{
        fontSize: 11.5, letterSpacing: '0.16em', textTransform: 'uppercase', fontWeight: 500,
        color: 'var(--av-ink)', paddingBottom: 2, textDecoration: 'none',
        borderBottom: (hov || active) ? '1px solid var(--av-ink)' : '1px solid transparent',
        transition: 'border-color .25s',
      }}
      onMouseEnter={() => setHov(true)}
      onMouseLeave={() => setHov(false)}>
      {children}
    </Link>
  );
}
