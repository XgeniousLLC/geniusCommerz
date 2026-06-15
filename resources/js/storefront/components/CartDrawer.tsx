import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import { usePrice } from '../usePrice';
import type { SharedProps } from '../types';

const S = { fill: 'none', stroke: 'currentColor', strokeWidth: 1.4, strokeLinecap: 'round', strokeLinejoin: 'round' } as React.SVGProps<SVGSVGElement>;

function GoalBar({ subtotal }: { subtotal: number }) {
  const { site } = usePage<SharedProps>().props;
  const goals = site.cartGoals ?? [];
  const freeShip = site.freeShippingAbove > 0
    ? { amount: site.freeShippingAbove, reward: 'free_shipping' as const }
    : null;
  const allGoals = [...goals, ...(freeShip ? [freeShip] : [])].sort((a, b) => a.amount - b.amount);
  if (!allGoals.length) return null;

  const next = allGoals.find(g => subtotal < g.amount);
  if (!next) {
    return (
      <div style={{ padding: '10px 24px', borderBottom: '1px solid var(--av-line-soft)', background: 'rgba(149,97,58,.07)' }}>
        <div style={{ fontSize: 11.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', letterSpacing: '0.04em' }}>All rewards unlocked.</div>
        <div style={{ height: 2, background: 'var(--av-line-soft)', marginTop: 6, borderRadius: 1 }}>
          <div style={{ height: 2, background: 'var(--av-cognac)', width: '100%', borderRadius: 1 }} />
        </div>
      </div>
    );
  }

  const needed   = next.amount - subtotal;
  const prev     = allGoals.filter(g => g.amount <= subtotal).at(-1);
  const from     = prev?.amount ?? 0;
  const pct      = Math.min(100, Math.round(((subtotal - from) / (next.amount - from)) * 100));
  const label    = next.reward === 'free_shipping'
    ? 'free shipping'
    : (next as any).label || ((next as any).reward === 'discount_pct' ? `${(next as any).value}% off` : `৳${(next as any).value} off`);

  return (
    <div style={{ padding: '10px 24px', borderBottom: '1px solid var(--av-line-soft)' }}>
      <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
        Add <strong style={{ color: 'var(--av-ink)', fontWeight: 600 }}>৳{needed.toLocaleString()}</strong> more for <strong style={{ color: 'var(--av-cognac)', fontWeight: 600 }}>{label}</strong>
      </div>
      <div style={{ height: 2, background: 'var(--av-line)', marginTop: 7, borderRadius: 1, overflow: 'hidden' }}>
        <div style={{ height: 2, background: 'var(--av-cognac)', width: `${pct}%`, borderRadius: 1, transition: 'width .5s ease' }} />
      </div>
    </div>
  );
}

export default function CartDrawer() {
  const fmt         = usePrice();
  const items       = useCartStore(s => s.items);
  const coupon      = useCartStore(s => s.coupon);
  const isOpen      = useCartStore(s => s.isOpen);
  const closeCart   = useCartStore(s => s.closeCart);
  const removeItem  = useCartStore(s => s.removeItem);
  const updateQty   = useCartStore(s => s.updateQty);
  const applyCoupon = useCartStore(s => s.applyCoupon);
  const removeCoupon= useCartStore(s => s.removeCoupon);
  const { subtotal, discount, total } = useCartDerived();

  const [couponInput,   setCouponInput]   = useState('');
  const [couponError,   setCouponError]   = useState('');
  const [couponLoading, setCouponLoading] = useState(false);

  async function handleCoupon(e: React.FormEvent) {
    e.preventDefault();
    if (!couponInput.trim()) return;
    setCouponLoading(true); setCouponError('');
    const err = await applyCoupon(couponInput.trim().toUpperCase());
    if (err) setCouponError(err); else setCouponInput('');
    setCouponLoading(false);
  }

  return (
    <>
      {/* Overlay */}
      <div onClick={closeCart}
        style={{ position: 'fixed', inset: 0, background: 'rgba(31,26,21,.42)', zIndex: 40, opacity: isOpen ? 1 : 0, pointerEvents: isOpen ? 'auto' : 'none', transition: 'opacity .3s' }} />

      {/* Drawer */}
      <div style={{
        position: 'fixed', top: 0, right: 0, height: '100%',
        width: 'min(440px, 92vw)',
        background: 'var(--av-paper)',
        zIndex: 50,
        display: 'flex', flexDirection: 'column',
        transform: isOpen ? 'translateX(0)' : 'translateX(100%)',
        transition: 'transform .35s cubic-bezier(.32,.72,0,1)',
        boxShadow: '-24px 0 64px rgba(31,26,21,.14)',
      }}>

        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '20px 24px', borderBottom: '1px solid var(--av-line)' }}>
          <div>
            <div style={{ fontFamily: 'var(--av-display)', fontSize: 21, fontWeight: 400, color: 'var(--av-ink)', letterSpacing: '-0.01em' }}>
              Your Bag
            </div>
            {items.length > 0 && (
              <div style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 2, letterSpacing: '0.04em' }}>
                {items.length} item{items.length !== 1 ? 's' : ''}
              </div>
            )}
          </div>
          <button onClick={closeCart}
            style={{ width: 36, height: 36, display: 'grid', placeItems: 'center', border: '1px solid var(--av-line)', background: 'transparent', borderRadius: 2, cursor: 'pointer', color: 'var(--av-ink)', flexShrink: 0 }}>
            <svg viewBox="0 0 24 24" width="16" height="16" {...S}><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>

        {/* Goal bar */}
        {items.length > 0 && <GoalBar subtotal={subtotal} />}

        {/* Items */}
        <div style={{ flex: 1, overflowY: 'auto', padding: '20px 24px' }} className="av-scroll-y">
          {items.length === 0 ? (
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', height: '100%', textAlign: 'center', paddingTop: 60 }}>
              <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="var(--av-line)" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round">
                <path d="M6 2 3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>
              </svg>
              <div style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, color: 'var(--av-ink)', marginTop: 18, marginBottom: 8 }}>Your bag is empty</div>
              <div style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 24, lineHeight: 1.6 }}>Discover pieces crafted to last.</div>
              <button onClick={closeCart}
                style={{ padding: '11px 28px', background: 'var(--av-ink)', color: 'var(--av-paper)', fontFamily: 'var(--av-sans)', fontSize: 11, letterSpacing: '0.2em', textTransform: 'uppercase', border: 'none', borderRadius: 2, cursor: 'pointer', fontWeight: 500 }}>
                Continue Shopping
              </button>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
              {items.map(item => (
                <div key={item.key} style={{ display: 'flex', gap: 14 }}>
                  {/* Thumb */}
                  <Link href={`/shop/${item.slug}`} onClick={closeCart} style={{ flexShrink: 0 }}>
                    <div style={{ width: 72, height: 90, background: 'var(--av-paper-2)', overflow: 'hidden' }}>
                      {item.image_url
                        ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
                        : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center' }}>
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--av-line)" strokeWidth="1" strokeLinecap="round" strokeLinejoin="round">
                              <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                          </div>
                      }
                    </div>
                  </Link>

                  {/* Info */}
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <Link href={`/shop/${item.slug}`} onClick={closeCart}
                      style={{ fontFamily: 'var(--av-display)', fontSize: 15, fontWeight: 400, color: 'var(--av-ink)', textDecoration: 'none', lineHeight: 1.25, display: 'block', marginBottom: 4 }}>
                      {item.name}
                    </Link>
                    {item.variant_label && (
                      <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 4, letterSpacing: '0.04em' }}>{item.variant_label}</div>
                    )}
                    <div style={{ fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>
                      {fmt(item.price * item.quantity)}
                    </div>
                    {/* Qty controls */}
                    <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--av-line)', borderRadius: 2, width: 'fit-content', overflow: 'hidden' }}>
                      <button onClick={() => updateQty(item.key, item.quantity - 1)}
                        style={{ width: 30, height: 30, display: 'grid', placeItems: 'center', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 16, lineHeight: 1 }}>
                        −
                      </button>
                      <span style={{ width: 30, textAlign: 'center', fontSize: 12.5, fontFamily: 'var(--av-sans)', color: 'var(--av-ink)', fontWeight: 500 }}>{item.quantity}</span>
                      <button onClick={() => updateQty(item.key, item.quantity + 1)}
                        style={{ width: 30, height: 30, display: 'grid', placeItems: 'center', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 16, lineHeight: 1 }}>
                        +
                      </button>
                    </div>
                  </div>

                  {/* Remove */}
                  <button onClick={() => removeItem(item.key)}
                    style={{ flexShrink: 0, alignSelf: 'flex-start', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-muted)', padding: 2, marginTop: 2, transition: 'color .15s' }}
                    onMouseEnter={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-ink)'; }}
                    onMouseLeave={e => { (e.currentTarget as HTMLElement).style.color = 'var(--av-muted)'; }}>
                    <svg viewBox="0 0 24 24" width="14" height="14" {...S}><path d="M18 6 6 18M6 6l12 12"/></svg>
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Footer */}
        {items.length > 0 && (
          <div style={{ borderTop: '1px solid var(--av-line)', padding: '20px 24px', paddingBottom: 'calc(20px + env(safe-area-inset-bottom, 0px))' }}>
            {/* Coupon */}
            {!coupon ? (
              <form onSubmit={handleCoupon} style={{ display: 'flex', gap: 8, marginBottom: 16 }}>
                <input type="text" placeholder="Coupon code" value={couponInput}
                  onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(''); }}
                  style={{ flex: 1, border: '1px solid var(--av-line)', background: 'transparent', padding: '10px 12px', fontSize: 12.5, color: 'var(--av-ink)', outline: 'none', borderRadius: 2, fontFamily: 'var(--av-sans)', letterSpacing: '0.08em', textTransform: 'uppercase' }} />
                <button type="submit" disabled={couponLoading}
                  style={{ padding: '10px 16px', background: 'transparent', border: '1px solid var(--av-line)', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontSize: 11, fontWeight: 500, letterSpacing: '0.14em', textTransform: 'uppercase', borderRadius: 2, cursor: 'pointer', flexShrink: 0 }}>
                  {couponLoading ? '…' : 'Apply'}
                </button>
              </form>
            ) : (
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 12px', border: '1px solid rgba(149,97,58,.3)', background: 'rgba(149,97,58,.06)', borderRadius: 2, marginBottom: 16 }}>
                <div style={{ fontSize: 12.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', letterSpacing: '0.04em' }}>
                  <strong>{coupon.code}</strong> — {coupon.message}
                </div>
                <button onClick={removeCoupon}
                  style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-cognac)', padding: 2 }}>
                  <svg viewBox="0 0 24 24" width="12" height="12" {...S}><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
              </div>
            )}
            {couponError && <p style={{ fontSize: 11.5, color: '#b94040', fontFamily: 'var(--av-sans)', marginBottom: 12 }}>{couponError}</p>}

            {/* Totals */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 18 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                <span>Subtotal</span><span>{fmt(subtotal)}</span>
              </div>
              {discount > 0 && (
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)' }}>
                  <span>Discount</span><span>−{fmt(discount)}</span>
                </div>
              )}
              <div style={{ display: 'flex', justifyContent: 'space-between', paddingTop: 10, borderTop: '1px solid var(--av-line)', fontFamily: 'var(--av-sans)' }}>
                <span style={{ fontSize: 13.5, color: 'var(--av-ink)', fontWeight: 500 }}>Total</span>
                <span style={{ fontSize: 15, color: 'var(--av-ink)', fontWeight: 600 }}>{fmt(total)}</span>
              </div>
            </div>

            <Link href="/checkout" onClick={closeCart}
              style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', padding: '15px 24px', background: 'var(--av-ink)', color: 'var(--av-paper)', fontFamily: 'var(--av-sans)', fontSize: 11.5, fontWeight: 500, letterSpacing: '0.22em', textTransform: 'uppercase', textDecoration: 'none', borderRadius: 2, border: 'none', cursor: 'pointer', boxSizing: 'border-box' }}>
              Proceed to Checkout
            </Link>

            <div style={{ textAlign: 'center', marginTop: 12 }}>
              <button onClick={closeCart}
                style={{ background: 'transparent', border: 'none', cursor: 'pointer', fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', letterSpacing: '0.1em', textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>
                Continue shopping
              </button>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
