import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import type { SharedProps } from '../types';

export default function Header() {
  const { site, auth } = usePage<SharedProps>().props;
  const [userOpen, setUserOpen] = useState(false);
  const url = usePage().url;
  const openCart = useCartStore(s => s.openCart);
  const { count } = useCartDerived();

  function logout() {
    router.post('/logout');
  }

  return (
    <header className="bg-white border-b border-slate-200 sticky top-0 z-40">
      <div className="max-w-7xl mx-auto px-4 lg:px-6">
        <div className="flex items-center justify-between h-16">

          {/* Logo */}
          <Link href="/" className="flex items-center gap-2">
            {site.logoUrl
              ? <img src={site.logoUrl} alt={site.name} className="h-9 w-auto" />
              : <span className="text-xl font-extrabold tracking-tight" style={{ color: 'var(--kb-primary)' }}>{site.name}</span>
            }
          </Link>

          {/* Desktop nav */}
          <nav className="hidden lg:flex items-center gap-7 text-sm">
            <Link href="/shop" className="kb-nav-link">Shop</Link>
            <Link href="/blog" className={`kb-nav-link ${url.startsWith('/blog') ? 'font-semibold !text-slate-900' : ''}`}>Blog</Link>
            <Link href="/track" className="kb-nav-link">Track Order</Link>
          </nav>

          {/* Right icons */}
          <div className="flex items-center gap-3">
            {auth.user ? (
              <div className="relative">
                <button
                  onClick={() => setUserOpen((o) => !o)}
                  className="kb-nav-link flex items-center gap-1.5 text-sm"
                  style={{ background: 'none', border: 'none', cursor: 'pointer' }}
                >
                  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span className="hidden sm:inline text-xs font-medium">{auth.user.name.split(' ')[0]}</span>
                </button>
                {userOpen && (
                  <>
                    <div className="fixed inset-0 z-40" onClick={() => setUserOpen(false)} />
                    <div className="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl py-1 z-50 shadow-lg">
                      <div className="px-4 py-2 text-xs border-b border-slate-100 text-slate-400">{auth.user.email}</div>
                      <button
                        onClick={logout}
                        className="w-full text-left px-4 py-2 text-sm hover:bg-slate-50"
                        style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--kb-ink)' }}
                      >
                        Sign out
                      </button>
                    </div>
                  </>
                )}
              </div>
            ) : (
              <Link href="/login" className="kb-nav-link" title="Sign in">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </Link>
            )}
            <button onClick={openCart} className="kb-nav-link relative" title="Cart" style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {count > 0 && (
                <span className="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full text-white text-[10px] font-bold flex items-center justify-center" style={{ background: 'var(--kb-primary)' }}>
                  {count > 9 ? '9+' : count}
                </span>
              )}
            </button>
          </div>

        </div>

        {/* Mobile search */}
        <div className="md:hidden pb-3">
          <div className="relative">
            <svg className="w-4 h-4 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input className="kb-input pl-9 text-sm" placeholder="Search products…" />
          </div>
        </div>
      </div>
    </header>
  );
}
