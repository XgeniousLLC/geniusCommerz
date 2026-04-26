import { Link, usePage } from '@inertiajs/react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import { useWishlistStore } from '../store/wishlistStore';
import type { SharedProps } from '../types';

export default function BottomNav() {
  const { auth } = usePage<SharedProps>().props;
  const openCart      = useCartStore(s => s.openCart);
  const { count }     = useCartDerived();
  const wishlistCount = useWishlistStore(s => s.items.length);
  const url           = usePage().url;

  const isActive = (path: string) => url === path || url.startsWith(path + '/') || url.startsWith(path + '?');

  return (
    <nav className="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200 safe-bottom">
      <div className="grid grid-cols-5 h-14">

        {/* Home */}
        <Link href="/" className={`flex flex-col items-center justify-center gap-0.5 text-[10px] font-medium transition-colors ${isActive('/') && url === '/' ? 'text-blue-600' : 'text-slate-500'}`}>
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Home
        </Link>

        {/* Shop */}
        <Link href="/shop" className={`flex flex-col items-center justify-center gap-0.5 text-[10px] font-medium transition-colors ${isActive('/shop') ? 'text-blue-600' : 'text-slate-500'}`}>
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
          Shop
        </Link>

        {/* Cart — center pill */}
        <button
          onClick={openCart}
          className="flex flex-col items-center justify-center gap-0.5 text-[10px] font-medium text-slate-500 relative"
          style={{ background: 'none', border: 'none', cursor: 'pointer' }}
        >
          <div className="relative">
            <div className="w-10 h-10 rounded-full flex items-center justify-center -mt-4 shadow-md" style={{ background: 'var(--kb-primary)' }}>
              <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            {count > 0 && (
              <span className="absolute -top-1 -right-1 w-4 h-4 rounded-full text-white text-[9px] font-bold flex items-center justify-center" style={{ background: 'var(--kb-danger)' }}>
                {count > 9 ? '9+' : count}
              </span>
            )}
          </div>
          <span className="mt-1">Cart</span>
        </button>

        {/* Wishlist */}
        <Link href="/wishlist" className={`flex flex-col items-center justify-center gap-0.5 text-[10px] font-medium transition-colors relative ${isActive('/wishlist') ? 'text-blue-600' : 'text-slate-500'}`}>
          <div className="relative">
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            {wishlistCount > 0 && (
              <span className="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full text-white text-[9px] font-bold flex items-center justify-center" style={{ background: 'var(--kb-danger)' }}>
                {wishlistCount > 9 ? '9+' : wishlistCount}
              </span>
            )}
          </div>
          Wishlist
        </Link>

        {/* Account */}
        <Link
          href={auth.user ? '/account' : '/login'}
          className={`flex flex-col items-center justify-center gap-0.5 text-[10px] font-medium transition-colors ${isActive('/account') || isActive('/login') ? 'text-blue-600' : 'text-slate-500'}`}
        >
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          {auth.user ? 'Account' : 'Login'}
        </Link>

      </div>
    </nav>
  );
}
