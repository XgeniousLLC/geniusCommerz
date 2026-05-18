import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';
import type { SharedProps } from '../types';

export default function Cart() {
  const { site } = usePage<SharedProps>().props;
  const items        = useCartStore(s => s.items);
  const coupon       = useCartStore(s => s.coupon);
  const removeItem   = useCartStore(s => s.removeItem);
  const updateQty    = useCartStore(s => s.updateQty);
  const clearCart    = useCartStore(s => s.clearCart);
  const applyCoupon  = useCartStore(s => s.applyCoupon);
  const removeCoupon = useCartStore(s => s.removeCoupon);
  const { subtotal, discount, total, count } = useCartDerived();
  const fmt = usePrice();

  const [couponInput,   setCouponInput]   = useState('');
  const [couponError,   setCouponError]   = useState('');
  const [couponLoading, setCouponLoading] = useState(false);

  const freeShippingAbove = site.freeShippingAbove ?? 0;
  const shippingCost      = site.shippingCost ?? 0;
  const shippingFree      = freeShippingAbove > 0 && subtotal >= freeShippingAbove;
  const shipping          = shippingFree ? 0 : shippingCost;
  const grandTotal        = total + shipping;

  // Free shipping progress
  const needed    = Math.max(0, freeShippingAbove - subtotal);
  const shipPct   = freeShippingAbove > 0 ? Math.min(100, Math.round((subtotal / freeShippingAbove) * 100)) : 0;

  // Cart goals (from CartDrawer logic)
  const goals = site.cartGoals ?? [];
  const allGoals = freeShippingAbove > 0
    ? [...goals, { amount: freeShippingAbove, reward: 'free_shipping' }].sort((a, b) => a.amount - b.amount)
    : goals.sort((a, b) => a.amount - b.amount);
  const nextGoal = allGoals.find(g => subtotal < g.amount);

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
    <Layout>
      <Head title="Your Cart" />

      <div style={{ maxWidth: 1280, margin: '0 auto', padding: '0 24px 64px' }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--kb-ink-soft)', padding: '16px 0 0' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-700">Home</Link>
          <span>/</span>
          <span style={{ color: 'var(--kb-ink)', fontWeight: 500 }}>Shopping cart</span>
        </nav>

        {/* Page head */}
        <div style={{ padding: '16px 0 20px' }}>
          <h1 style={{ fontSize: 28, fontWeight: 800, letterSpacing: '-0.02em', margin: 0, color: 'var(--kb-ink)' }}>Your cart</h1>
          <div style={{ fontSize: 14, color: 'var(--kb-ink-soft)', marginTop: 4 }}>
            {items.length === 0
              ? 'Your cart is empty.'
              : `${count} item${count !== 1 ? 's' : ''} · update quantities or move to wishlist`
            }
          </div>
        </div>

        {items.length === 0 ? (
          /* Empty state */
          <div style={{ textAlign: 'center', padding: '80px 24px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 0 }}>
            <div style={{ width: 72, height: 72, borderRadius: '50%', background: 'var(--kb-surface-2)', color: 'var(--kb-border)', display: 'grid', placeItems: 'center', marginBottom: 20 }}>
              <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <h2 style={{ fontSize: 20, fontWeight: 700, margin: '0 0 8px', color: 'var(--kb-ink)' }}>Your cart is empty</h2>
            <p style={{ fontSize: 14, color: 'var(--kb-ink-soft)', margin: '0 0 28px' }}>Looks like you haven't added anything yet.</p>
            <Link href="/shop"
              style={{ display: 'inline-flex', alignItems: 'center', height: 48, padding: '0 28px', borderRadius: 10, background: 'var(--kb-primary)', color: '#fff', fontWeight: 700, fontSize: 15, textDecoration: 'none' }}>
              Shop now
            </Link>
          </div>
        ) : (
          /* Cart grid */
          <div className="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-8 items-start">

            {/* ── Left: Items ── */}
            <div>
              {/* Cart goal banner */}
              {nextGoal ? (
                <div style={{ padding: '10px 16px', background: '#F0F9FF', border: '1px solid #BAE6FD', borderRadius: 10, marginBottom: 16 }}>
                  <p style={{ fontSize: 13, fontWeight: 500, color: '#0369A1', margin: '0 0 6px' }}>
                    Add <strong>{fmt(nextGoal.amount - subtotal)}</strong> more to get{' '}
                    <strong>
                      {nextGoal.reward === 'free_shipping'
                        ? 'free shipping'
                        : (nextGoal as any).label || ((nextGoal as any).reward === 'discount_pct' ? `${(nextGoal as any).value}% off` : `${fmt((nextGoal as any).value)} off`)}
                    </strong>
                  </p>
                  <div style={{ width: '100%', height: 6, background: '#BAE6FD', borderRadius: 999, overflow: 'hidden' }}>
                    <div style={{ height: '100%', background: '#0EA5E9', borderRadius: 999, width: `${Math.min(100, Math.round((subtotal / nextGoal.amount) * 100))}%`, transition: 'width .4s' }} />
                  </div>
                </div>
              ) : allGoals.length > 0 ? (
                <div style={{ padding: '10px 16px', background: '#F0FDF4', border: '1px solid #BBF7D0', borderRadius: 10, marginBottom: 16 }}>
                  <p style={{ fontSize: 13, fontWeight: 600, color: '#15803D', margin: 0 }}>You've unlocked all rewards!</p>
                </div>
              ) : null}

              {/* Table header (desktop) */}
              <div className="hidden md:grid" style={{ gridTemplateColumns: '64px 1fr 140px 100px 100px 36px', gap: 12, padding: '10px 16px', background: 'var(--kb-surface-2)', borderRadius: '10px 10px 0 0', border: '1px solid var(--kb-border)', borderBottom: 'none', fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.06em', color: 'var(--kb-ink-soft)' }}>
                <div />
                <div>Product</div>
                <div style={{ textAlign: 'center' }}>Quantity</div>
                <div style={{ textAlign: 'right' }}>Price</div>
                <div style={{ textAlign: 'right' }}>Total</div>
                <div />
              </div>

              {/* Rows */}
              <div style={{ border: '1px solid var(--kb-border)', borderRadius: '0 0 10px 10px', overflow: 'hidden' }} className="rounded-t-none md:rounded-t-none">
                {items.map((item, idx) => (
                  <div key={item.key}
                    style={{ borderTop: idx === 0 ? 'none' : '1px solid var(--kb-border)', background: '#fff' }}>

                    {/* Desktop row */}
                    <div className="hidden md:grid items-center" style={{ gridTemplateColumns: '64px 1fr 140px 100px 100px 36px', gap: 12, padding: '16px' }}>
                      {/* Image */}
                      <Link href={`/shop/${item.slug}`}>
                        <div style={{ width: 64, height: 64, borderRadius: 8, overflow: 'hidden', background: 'var(--kb-surface-2)', flexShrink: 0 }}>
                          {item.image_url
                            ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                            : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', color: 'var(--kb-border)' }}>
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/>
                                  <circle cx="8.5" cy="8.5" r="1.5" strokeWidth={1.5}/>
                                  <path strokeWidth={1.5} d="M21 15l-5-5L5 21"/>
                                </svg>
                              </div>
                          }
                        </div>
                      </Link>

                      {/* Name + variant */}
                      <div>
                        <Link href={`/shop/${item.slug}`} style={{ fontSize: 14, fontWeight: 600, color: 'var(--kb-ink)', textDecoration: 'none', lineHeight: 1.4, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}
                          className="hover:text-[var(--kb-primary)]">
                          {item.name}
                        </Link>
                        {item.variant_label && <div style={{ fontSize: 12, color: 'var(--kb-ink-soft)', marginTop: 3 }}>{item.variant_label}</div>}
                      </div>

                      {/* Qty stepper */}
                      <div style={{ display: 'flex', justifyContent: 'center' }}>
                        <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--kb-border)', borderRadius: 8, overflow: 'hidden', height: 38, background: '#fff' }}>
                          <button onClick={() => updateQty(item.key, item.quantity - 1)}
                            style={{ width: 34, height: '100%', background: '#fff', border: 'none', cursor: 'pointer', color: 'var(--kb-ink)', display: 'grid', placeItems: 'center', fontSize: 16 }}
                            className="hover:bg-gray-50">−</button>
                          <span style={{ width: 40, textAlign: 'center', fontSize: 14, fontWeight: 600, color: 'var(--kb-ink)' }}>{item.quantity}</span>
                          <button onClick={() => updateQty(item.key, item.quantity + 1)}
                            style={{ width: 34, height: '100%', background: '#fff', border: 'none', cursor: 'pointer', color: 'var(--kb-ink)', display: 'grid', placeItems: 'center', fontSize: 16 }}
                            className="hover:bg-gray-50">+</button>
                        </div>
                      </div>

                      {/* Unit price */}
                      <div style={{ textAlign: 'right', fontSize: 14, fontWeight: 600, color: 'var(--kb-ink-soft)' }}>{fmt(item.price)}</div>

                      {/* Line total */}
                      <div style={{ textAlign: 'right', fontSize: 14, fontWeight: 700, color: 'var(--kb-ink)' }}>{fmt(item.price * item.quantity)}</div>

                      {/* Remove */}
                      <button onClick={() => removeItem(item.key)}
                        style={{ width: 30, height: 30, borderRadius: 6, border: 'none', background: 'none', cursor: 'pointer', color: 'var(--kb-ink-soft)', display: 'grid', placeItems: 'center' }}
                        className="hover:bg-red-50 hover:text-red-500">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                    </div>

                    {/* Mobile row */}
                    <div className="md:hidden" style={{ padding: 16, display: 'flex', gap: 12 }}>
                      <Link href={`/shop/${item.slug}`} style={{ flexShrink: 0 }}>
                        <div style={{ width: 72, height: 72, borderRadius: 8, overflow: 'hidden', background: 'var(--kb-surface-2)' }}>
                          {item.image_url
                            ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                            : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', color: 'var(--kb-border)' }}>
                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/>
                                </svg>
                              </div>
                          }
                        </div>
                      </Link>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <Link href={`/shop/${item.slug}`} style={{ fontSize: 14, fontWeight: 600, color: 'var(--kb-ink)', textDecoration: 'none', lineHeight: 1.35, display: 'block' }}
                          className="line-clamp-2">
                          {item.name}
                        </Link>
                        {item.variant_label && <div style={{ fontSize: 12, color: 'var(--kb-ink-soft)', marginTop: 2 }}>{item.variant_label}</div>}
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 10 }}>
                          <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--kb-border)', borderRadius: 8, overflow: 'hidden', height: 34 }}>
                            <button onClick={() => updateQty(item.key, item.quantity - 1)}
                              style={{ width: 30, height: '100%', background: '#fff', border: 'none', cursor: 'pointer', color: 'var(--kb-ink)', fontSize: 16 }}>−</button>
                            <span style={{ width: 32, textAlign: 'center', fontSize: 13, fontWeight: 600 }}>{item.quantity}</span>
                            <button onClick={() => updateQty(item.key, item.quantity + 1)}
                              style={{ width: 30, height: '100%', background: '#fff', border: 'none', cursor: 'pointer', color: 'var(--kb-ink)', fontSize: 16 }}>+</button>
                          </div>
                          <span style={{ fontSize: 15, fontWeight: 700, color: 'var(--kb-ink)' }}>{fmt(item.price * item.quantity)}</span>
                        </div>
                      </div>
                      <button onClick={() => removeItem(item.key)}
                        style={{ alignSelf: 'flex-start', width: 28, height: 28, border: 'none', background: 'none', cursor: 'pointer', color: 'var(--kb-ink-soft)', display: 'grid', placeItems: 'center', flexShrink: 0, marginTop: 2 }}>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                ))}
              </div>

              {/* Actions */}
              <div style={{ display: 'flex', gap: 8, marginTop: 16, flexWrap: 'wrap' }}>
                <Link href="/shop"
                  style={{ display: 'inline-flex', alignItems: 'center', gap: 6, height: 40, padding: '0 16px', border: '1px solid var(--kb-border)', borderRadius: 8, background: '#fff', color: 'var(--kb-ink)', fontWeight: 600, fontSize: 13, textDecoration: 'none' }}>
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                  </svg>
                  Continue shopping
                </Link>
                <button onClick={() => { if (confirm('Clear your cart?')) clearCart(); }}
                  style={{ display: 'inline-flex', alignItems: 'center', gap: 6, height: 40, padding: '0 16px', border: '1px solid var(--kb-border)', borderRadius: 8, background: '#fff', color: 'var(--kb-ink-soft)', fontWeight: 600, fontSize: 13, cursor: 'pointer' }}
                  className="hover:text-red-500 hover:border-red-200">
                  Clear cart
                </button>
              </div>
            </div>

            {/* ── Right: Summary ── */}
            <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: 24, position: 'sticky', top: 100 }} className="lg:sticky">
              <h3 style={{ fontSize: 17, fontWeight: 700, color: 'var(--kb-ink)', margin: '0 0 16px' }}>Order summary</h3>

              {/* Summary rows */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 16 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, color: 'var(--kb-ink-soft)' }}>
                  <span>Subtotal ({count} item{count !== 1 ? 's' : ''})</span>
                  <span style={{ color: 'var(--kb-ink)', fontWeight: 600 }}>{fmt(subtotal)}</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, color: 'var(--kb-ink-soft)' }}>
                  <span>Shipping</span>
                  <span style={{ color: shippingFree ? '#16a34a' : 'var(--kb-ink)', fontWeight: 600 }}>
                    {shippingFree ? 'Free' : fmt(shipping)}
                  </span>
                </div>
                {discount > 0 && (
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, color: '#16a34a', fontWeight: 600 }}>
                    <span>Discount ({coupon?.code})</span>
                    <span>−{fmt(discount)}</span>
                  </div>
                )}
              </div>

              {/* Coupon */}
              {!coupon ? (
                <form onSubmit={handleCoupon} style={{ display: 'flex', gap: 8, marginBottom: 12 }}>
                  <input
                    type="text"
                    placeholder="Coupon code"
                    value={couponInput}
                    onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(''); }}
                    style={{ flex: 1, height: 42, padding: '0 12px', border: '1.5px solid var(--kb-border)', borderRadius: 8, fontSize: 13, outline: 'none', color: 'var(--kb-ink)', textTransform: 'uppercase' }}
                    onFocus={e => e.currentTarget.style.borderColor = 'var(--kb-primary)'}
                    onBlur={e => e.currentTarget.style.borderColor = 'var(--kb-border)'}
                  />
                  <button type="submit" disabled={couponLoading}
                    style={{ height: 42, padding: '0 16px', borderRadius: 8, border: 'none', background: 'var(--kb-primary)', color: '#fff', fontWeight: 700, fontSize: 13, cursor: 'pointer', flexShrink: 0 }}>
                    {couponLoading ? '…' : 'Apply'}
                  </button>
                </form>
              ) : (
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: '#F0FDF4', border: '1px solid #BBF7D0', borderRadius: 8, padding: '10px 12px', marginBottom: 12 }}>
                  <div>
                    <span style={{ fontSize: 12, fontWeight: 700, color: '#15803D' }}>{coupon.code}</span>
                    <span style={{ fontSize: 12, color: '#16A34A', marginLeft: 8 }}>{coupon.message}</span>
                  </div>
                  <button onClick={removeCoupon} style={{ background: 'none', border: 'none', cursor: 'pointer', color: '#86EFAC', display: 'grid', placeItems: 'center' }}
                    className="hover:text-green-600">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              )}
              {couponError && <p style={{ fontSize: 12, color: '#EF4444', marginBottom: 8, marginTop: -6 }}>{couponError}</p>}

              {/* Free shipping progress */}
              {!shippingFree && freeShippingAbove > 0 && (
                <div style={{ marginBottom: 12 }}>
                  <p style={{ fontSize: 12, color: 'var(--kb-ink-soft)', marginBottom: 6 }}>
                    Add <strong style={{ color: 'var(--kb-ink)' }}>{fmt(needed)}</strong> more for free shipping
                  </p>
                  <div style={{ width: '100%', height: 5, background: 'var(--kb-border)', borderRadius: 999, overflow: 'hidden' }}>
                    <div style={{ height: '100%', background: 'var(--kb-primary)', width: `${shipPct}%`, borderRadius: 999, transition: 'width .4s' }} />
                  </div>
                </div>
              )}
              {shippingFree && (
                <p style={{ fontSize: 12, color: '#16a34a', fontWeight: 600, marginBottom: 12 }}>✓ You qualify for free shipping</p>
              )}

              {/* Total */}
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 17, fontWeight: 800, color: 'var(--kb-ink)', borderTop: '1px solid var(--kb-border)', paddingTop: 14, marginBottom: 14 }}>
                <span>Total</span>
                <span>{fmt(grandTotal)}</span>
              </div>

              <Link href="/checkout"
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 50, borderRadius: 10, background: 'var(--kb-primary)', color: '#fff', fontWeight: 700, fontSize: 15, textDecoration: 'none', outline: 'none' }}>
                Proceed to checkout →
              </Link>

              {/* Payment methods */}
              <div style={{ display: 'flex', gap: 6, justifyContent: 'center', marginTop: 14, flexWrap: 'wrap' }}>
                {['bKash', 'Nagad', 'Card', 'COD'].map(m => (
                  <span key={m} style={{ background: 'var(--kb-surface-2)', color: 'var(--kb-ink)', border: '1px solid var(--kb-border)', fontSize: 11, fontWeight: 600, padding: '3px 10px', borderRadius: 999 }}>
                    {m}
                  </span>
                ))}
              </div>
            </div>

          </div>
        )}
      </div>
    </Layout>
  );
}
