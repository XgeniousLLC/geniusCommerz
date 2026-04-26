import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import { useWishlistStore } from '../store/wishlistStore';
import type { NavItem, SharedProps } from '../types';
import CurrencySwitcher from './CurrencySwitcher';
import LocaleSwitcher from './LocaleSwitcher';
import SearchBox from './SearchBox';

export default function Header() {
  const { site, auth } = usePage<SharedProps>().props;
  const [userOpen,   setUserOpen]   = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const url = usePage().url;
  const openCart     = useCartStore(s => s.openCart);
  const { count }    = useCartDerived();
  const wishlistCount = useWishlistStore(s => s.items.length);

  useEffect(() => { setMobileOpen(false); }, [url]);

  function logout() { router.post('/logout'); }

  const navItems: NavItem[] = (site.mainNav && site.mainNav.length > 0)
    ? site.mainNav
    : [
        { label: 'Shop',        url: '/shop',  target: '_self', children: [] },
        { label: 'Blog',        url: '/blog',  target: '_self', children: [] },
        { label: 'Track Order', url: '/track', target: '_self', children: [] },
      ];

  return (
    <header className="bg-white border-b border-slate-200 sticky top-0 z-40">
      <div className="max-w-7xl mx-auto px-4 lg:px-6">
        <div className="flex items-center justify-between h-16">

          {/* Logo */}
          <Link href="/" className="flex items-center gap-2 shrink-0">
            {site.logoUrl
              ? <img src={site.logoUrl} alt={site.name} className="h-9 w-auto" />
              : <span className="text-xl font-extrabold tracking-tight" style={{ color: 'var(--kb-primary)' }}>{site.name}</span>
            }
          </Link>

          {/* Desktop search */}
          <div className="hidden lg:flex items-center flex-1 max-w-md mx-6">
            <SearchBox className="w-full" />
          </div>

          {/* Desktop nav */}
          <nav className="hidden lg:flex items-center gap-6 text-sm">
            {navItems.map((item) => (
              <DesktopNavItem key={item.label} item={item} url={url} />
            ))}
          </nav>

          {/* Right icons */}
          <div className="flex items-center gap-2 sm:gap-3">

            {/* User */}
            {auth.user ? (
              <div className="relative">
                <button onClick={() => setUserOpen(o => !o)}
                  className="kb-nav-link flex items-center gap-1.5 text-sm"
                  style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <span className="hidden sm:inline text-xs font-medium">{auth.user.name.split(' ')[0]}</span>
                </button>
                {userOpen && (
                  <>
                    <div className="fixed inset-0 z-40" onClick={() => setUserOpen(false)} />
                    <div className="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl py-1 z-50 shadow-lg">
                      <div className="px-4 py-2.5 border-b border-slate-100">
                        <p className="text-sm font-semibold text-slate-800 truncate">{auth.user.name}</p>
                        <p className="text-xs text-slate-400 truncate">{auth.user.email}</p>
                      </div>
                      {[
                        { href: '/account',         label: 'Dashboard'    },
                        { href: '/account/orders',  label: 'My Orders'    },
                        { href: '/account/reviews', label: 'My Reviews'   },
                        { href: '/account/address', label: 'Address Book' },
                        { href: '/account/refunds', label: 'Refunds'      },
                      ].map(item => (
                        <Link key={item.href} href={item.href} onClick={() => setUserOpen(false)}
                          className="block px-4 py-2 text-sm hover:bg-slate-50 transition-colors"
                          style={{ color: 'var(--kb-ink)' }}>
                          {item.label}
                        </Link>
                      ))}
                      <div className="border-t border-slate-100 mt-1 pt-1">
                        <button onClick={() => { setUserOpen(false); logout(); }}
                          className="w-full text-left px-4 py-2 text-sm hover:bg-red-50 transition-colors"
                          style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--kb-danger)' }}>
                          Sign out
                        </button>
                      </div>
                    </div>
                  </>
                )}
              </div>
            ) : (
              <Link href="/login" className="kb-nav-link" title="Sign in">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </Link>
            )}

            {/* Locale + Currency switchers */}
            <div className="hidden lg:flex items-center gap-1">
              <LocaleSwitcher />
              <CurrencySwitcher />
            </div>

            {/* Wishlist */}
            <Link href="/wishlist" className="kb-nav-link relative" title="Wishlist">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              {wishlistCount > 0 && (
                <span className="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full text-white text-[10px] font-bold flex items-center justify-center" style={{ background: 'var(--kb-danger)' }}>
                  {wishlistCount > 9 ? '9+' : wishlistCount}
                </span>
              )}
            </Link>

            {/* Cart */}
            <button onClick={openCart} className="kb-nav-link relative" title="Cart"
                    style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              {count > 0 && (
                <span className="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full text-white text-[10px] font-bold flex items-center justify-center" style={{ background: 'var(--kb-primary)' }}>
                  {count > 9 ? '9+' : count}
                </span>
              )}
            </button>

            {/* Mobile hamburger */}
            <button onClick={() => setMobileOpen(o => !o)}
                    className="lg:hidden kb-nav-link" title="Menu"
                    style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
              {mobileOpen
                ? <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>
                : <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16"/></svg>
              }
            </button>

          </div>
        </div>

        {/* Mobile search */}
        <div className="md:hidden pb-3">
          <SearchBox />
        </div>
      </div>

      {/* Mobile nav drawer */}
      {mobileOpen && (
        <>
          <div className="fixed inset-0 z-30 bg-black/20" onClick={() => setMobileOpen(false)} />
          <div className="lg:hidden absolute left-0 right-0 top-full bg-white border-b border-slate-200 z-40 shadow-lg max-h-[70vh] overflow-y-auto">
            <nav className="px-4 py-3 space-y-0.5">
              {navItems.map((item) => (
                <MobileNavItem key={item.label} item={item} url={url} onClose={() => setMobileOpen(false)} />
              ))}
            </nav>
            {/* Language + Currency switchers in mobile menu */}
            <div className="px-4 py-3 border-t border-slate-100 flex items-center gap-3">
              <LocaleSwitcher />
              <CurrencySwitcher />
            </div>
          </div>
        </>
      )}
    </header>
  );
}

/* ─── Desktop nav item with hover dropdown ─── */
function DesktopNavItem({ item, url }: { item: NavItem; url: string }) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  const hasChildren = item.children && item.children.length > 0;
  const isActive = item.url ? (url === item.url || url.startsWith(item.url + '/')) : false;

  // Close on outside click (for non-hover use)
  useEffect(() => {
    if (!open) return;
    const handler = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [open]);

  if (!hasChildren) {
    return (
      <Link href={item.url || '#'} target={item.target}
            className={`kb-nav-link text-sm ${isActive ? 'font-semibold !text-slate-900' : ''}`}>
        {item.label}
      </Link>
    );
  }

  return (
    <div ref={ref} className="relative"
         onMouseEnter={() => setOpen(true)}
         onMouseLeave={() => setOpen(false)}>
      <button onClick={() => setOpen(o => !o)}
              className={`kb-nav-link text-sm flex items-center gap-1 ${isActive ? 'font-semibold !text-slate-900' : ''}`}
              style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
        {item.label}
        <svg className={`w-3.5 h-3.5 transition-transform duration-150 ${open ? 'rotate-180' : ''}`}
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      {/* Dropdown panel */}
      <div className={`absolute top-full left-1/2 -translate-x-1/2 mt-2 min-w-[200px] bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 transition-all duration-150 origin-top
                       ${open ? 'opacity-100 scale-100 pointer-events-auto' : 'opacity-0 scale-95 pointer-events-none'}`}>
        {/* Arrow */}
        <div className="absolute -top-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-white border-l border-t border-slate-200 rotate-45 rounded-sm" />
        {item.children.map((child) => (
          <Link key={child.label} href={child.url || '#'} target={child.target}
                className="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
            <svg className="w-1 h-1 rounded-full bg-slate-300 shrink-0" viewBox="0 0 4 4" fill="currentColor"><circle cx="2" cy="2" r="2"/></svg>
            {child.label}
          </Link>
        ))}
      </div>
    </div>
  );
}

/* ─── Mobile nav item with accordion dropdown ─── */
function MobileNavItem({ item, url, onClose }: { item: NavItem; url: string; onClose: () => void }) {
  const [open, setOpen] = useState(false);
  const hasChildren = item.children && item.children.length > 0;
  const isActive = item.url ? (url === item.url || url.startsWith(item.url + '/')) : false;

  if (!hasChildren) {
    return (
      <Link href={item.url || '#'} target={item.target} onClick={onClose}
            className={`block px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${isActive ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50'}`}>
        {item.label}
      </Link>
    );
  }

  return (
    <div>
      <button onClick={() => setOpen(o => !o)}
              className={`w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${isActive ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50'}`}
              style={{ background: open ? 'var(--kb-primary-50, #EFF6FF)' : '', border: 'none', cursor: 'pointer' }}>
        <span style={{ color: open ? 'var(--kb-primary)' : undefined }}>{item.label}</span>
        <svg className={`w-4 h-4 transition-transform duration-200 ${open ? 'rotate-180' : ''}`}
             fill="none" stroke="currentColor" viewBox="0 0 24 24"
             style={{ color: open ? 'var(--kb-primary)' : undefined }}>
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      {open && (
        <div className="mt-0.5 ml-3 pl-3 border-l-2 space-y-0.5" style={{ borderColor: 'var(--kb-primary-200, #BFDBFE)' }}>
          {item.children.map((child) => (
            <Link key={child.label} href={child.url || '#'} target={child.target} onClick={onClose}
                  className="block px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
              {child.label}
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
