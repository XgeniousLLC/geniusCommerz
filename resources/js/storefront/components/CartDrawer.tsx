import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import type { SharedProps } from '../types';

function CartGoalBanner({ subtotal }: { subtotal: number }) {
  const { site } = usePage<SharedProps>().props;
  const goals = site.cartGoals ?? [];
  const freeShip = site.freeShippingAbove > 0
    ? { amount: site.freeShippingAbove, reward: 'free_shipping' as const }
    : null;

  // Build full list: configured goals + free shipping threshold
  const allGoals = [
    ...goals,
    ...(freeShip ? [freeShip] : []),
  ].sort((a, b) => a.amount - b.amount);

  if (allGoals.length === 0) return null;

  // Find the next unachieved goal
  const next = allGoals.find(g => subtotal < g.amount);
  if (!next) {
    // All goals unlocked
    return (
      <div className="px-5 py-2.5 bg-green-50 border-b border-green-100">
        <div className="flex items-center gap-2 text-green-700 text-xs font-semibold">
          <span>🎉</span>
          <span>You've unlocked all rewards!</span>
        </div>
        <div className="w-full h-1.5 rounded-full bg-green-200 mt-1.5">
          <div className="h-1.5 rounded-full bg-green-500 w-full" />
        </div>
      </div>
    );
  }

  const needed = next.amount - subtotal;
  const prev   = allGoals.filter(g => g.amount <= subtotal).at(-1);
  const from   = prev?.amount ?? 0;
  const pct    = Math.min(100, Math.round(((subtotal - from) / (next.amount - from)) * 100));

  const label = next.reward === 'free_shipping'
    ? `free shipping`
    : next.label || (next.reward === 'discount_pct' ? `${next.value}% off` : `৳${next.value} off`);

  return (
    <div className="px-5 py-2.5 border-b border-gray-100" style={{ background: '#F0F9FF' }}>
      <p className="text-xs font-medium" style={{ color: '#0369A1' }}>
        Add <span className="font-bold">৳{needed.toLocaleString()}</span> more to get{' '}
        <span className="font-bold">{label}</span>
      </p>
      <div className="w-full h-1.5 rounded-full bg-sky-200 mt-1.5 overflow-hidden">
        <div className="h-1.5 rounded-full bg-sky-500 transition-all duration-500" style={{ width: `${pct}%` }} />
      </div>
    </div>
  );
}

export default function CartDrawer() {
  const items        = useCartStore(s => s.items);
  const coupon       = useCartStore(s => s.coupon);
  const isOpen       = useCartStore(s => s.isOpen);
  const closeCart    = useCartStore(s => s.closeCart);
  const removeItem   = useCartStore(s => s.removeItem);
  const updateQty    = useCartStore(s => s.updateQty);
  const applyCoupon  = useCartStore(s => s.applyCoupon);
  const removeCoupon = useCartStore(s => s.removeCoupon);
  const { subtotal, discount, total } = useCartDerived();

  const [couponInput,   setCouponInput]   = useState('');
  const [couponError,   setCouponError]   = useState('');
  const [couponLoading, setCouponLoading] = useState(false);

  const handleCoupon = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!couponInput.trim()) return;
    setCouponLoading(true);
    setCouponError('');
    const err = await applyCoupon(couponInput.trim().toUpperCase());
    if (err) setCouponError(err);
    else setCouponInput('');
    setCouponLoading(false);
  };

  return (
    <>
      {isOpen && <div className="fixed inset-0 bg-black/40 z-40" onClick={closeCart} />}

      <div className={`fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-2xl z-50 flex flex-col transition-transform duration-300 ${isOpen ? 'translate-x-0' : 'translate-x-full'}`}>

        {/* Header */}
        <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <h2 className="text-base font-semibold text-gray-900">
            Cart {items.length > 0 && <span className="text-gray-400 font-normal text-sm">({items.length} item{items.length !== 1 ? 's' : ''})</span>}
          </h2>
          <button onClick={closeCart} className="text-gray-400 hover:text-gray-600 transition-colors">
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        {/* Cart goal banner */}
        {items.length > 0 && <CartGoalBanner subtotal={subtotal} />}

        {/* Items */}
        <div className="flex-1 overflow-y-auto px-5 py-4 space-y-4">
          {items.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-full text-center text-gray-400 py-16">
              <svg className="w-14 h-14 mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              <p className="font-medium text-gray-500">Your cart is empty</p>
              <button onClick={closeCart} className="mt-4 kb-btn kb-btn-primary text-sm">Continue Shopping</button>
            </div>
          ) : (
            items.map(item => (
              <div key={item.key} className="flex gap-3">
                <Link href={`/shop/${item.slug}`} onClick={closeCart} className="shrink-0">
                  <div className="w-16 h-16 rounded-lg overflow-hidden bg-gray-100">
                    {item.image_url
                      ? <img src={item.image_url} alt={item.name} className="w-full h-full object-cover" />
                      : <div className="w-full h-full flex items-center justify-center">
                          <svg className="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                        </div>
                    }
                  </div>
                </Link>

                <div className="flex-1 min-w-0">
                  <Link href={`/shop/${item.slug}`} onClick={closeCart} className="text-sm font-medium text-gray-800 hover:text-blue-600 line-clamp-2 leading-snug">
                    {item.name}
                  </Link>
                  {item.variant_label && <p className="text-xs text-gray-400 mt-0.5">{item.variant_label}</p>}
                  <div className="flex items-center justify-between mt-2">
                    <div className="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                      <button onClick={() => updateQty(item.key, item.quantity - 1)} className="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-base">−</button>
                      <span className="w-8 text-center text-xs font-semibold">{item.quantity}</span>
                      <button onClick={() => updateQty(item.key, item.quantity + 1)} className="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-50 text-base">+</button>
                    </div>
                    <span className="text-sm font-bold text-gray-900">৳{(item.price * item.quantity).toLocaleString()}</span>
                  </div>
                </div>

                <button onClick={() => removeItem(item.key)} className="shrink-0 text-gray-300 hover:text-red-400 transition-colors self-start mt-1">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            ))
          )}
        </div>

        {/* Footer */}
        {items.length > 0 && (
          <div className="border-t border-gray-100 px-5 pt-4 pb-[calc(1rem+56px)] md:pb-4 space-y-4">
            {/* Coupon */}
            {!coupon ? (
              <form onSubmit={handleCoupon} className="flex gap-2">
                <input
                  type="text"
                  placeholder="Coupon code"
                  value={couponInput}
                  onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(''); }}
                  className="kb-input flex-1 text-sm uppercase placeholder:normal-case"
                />
                <button type="submit" disabled={couponLoading} className="kb-btn kb-btn-primary text-sm shrink-0 px-4">
                  {couponLoading ? '…' : 'Apply'}
                </button>
              </form>
            ) : (
              <div className="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                <div>
                  <span className="text-xs font-bold text-green-700">{coupon.code}</span>
                  <span className="text-xs text-green-600 ml-2">{coupon.message}</span>
                </div>
                <button onClick={removeCoupon} className="text-green-400 hover:text-green-600 ml-2">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
            )}
            {couponError && <p className="text-xs text-red-500 -mt-2">{couponError}</p>}

            {/* Totals */}
            <div className="space-y-1.5 text-sm">
              <div className="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>৳{subtotal.toLocaleString()}</span>
              </div>
              {discount > 0 && (
                <div className="flex justify-between text-green-600">
                  <span>Discount</span>
                  <span>−৳{discount.toLocaleString()}</span>
                </div>
              )}
              <div className="flex justify-between font-bold text-gray-900 text-base pt-1 border-t border-gray-100">
                <span>Total</span>
                <span>৳{total.toLocaleString()}</span>
              </div>
            </div>

            <Link href="/checkout" onClick={closeCart} className="kb-btn kb-btn-primary w-full justify-center text-sm font-semibold py-3 outline-none">
              Proceed to Checkout →
            </Link>
          </div>
        )}
      </div>
    </>
  );
}
