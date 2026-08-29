import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useCartDerived, useCartStore } from '../store/cartStore';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';
import type { SharedProps } from '../types';

const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };

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

  const needed    = Math.max(0, freeShippingAbove - subtotal);
  const shipPct   = freeShippingAbove > 0 ? Math.min(100, Math.round((subtotal / freeShippingAbove) * 100)) : 0;

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

      <div style={{ ...W, paddingBottom: 64 }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 11.5, color: 'var(--av-muted)', padding: '18px 0 0', fontFamily: 'var(--av-sans)' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link>
          <span>/</span>
          <span style={{ color: 'var(--av-ink)', fontWeight: 500 }}>Cart</span>
        </nav>

        {/* Page head */}
        <div style={{ padding: '20px 0 28px', borderBottom: items.length ? '1px solid var(--av-line)' : 'none', marginBottom: items.length ? 28 : 0 }}>
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(26px,3vw,38px)', fontWeight: 400, letterSpacing: '-0.012em', margin: 0, color: 'var(--av-ink)', lineHeight: 1.04 }}>Your cart</h1>
          <div style={{ fontSize: 13.5, color: 'var(--av-muted)', marginTop: 8, fontFamily: 'var(--av-sans)' }}>
            {items.length === 0
              ? 'Your bag is empty.'
              : `${count} piece${count !== 1 ? 's' : ''} · quantities update instantly`
            }
          </div>
        </div>

        {items.length === 0 ? (
          <div className="av-empty" style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)' }}>
            <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'var(--av-paper-2)', color: 'var(--av-muted)', display: 'grid', placeItems: 'center', margin: '0 auto 18px' }}>
              <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <h2>Your bag is empty</h2>
            <p>Looks like you haven't added anything yet.</p>
            <Link href="/shop" className="av-btn av-btn-primary av-btn-md" style={{ textDecoration: 'none' }}>
              Browse collection
            </Link>
          </div>
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 380px', gap: 32, alignItems: 'start' }} className="av-cart-layout">

            {/* ── Left: Items ── */}
            <div>
              {/* Cart goal banner */}
              {nextGoal ? (
                <div style={{ padding: '12px 16px', background: 'var(--av-paper)', border: '1px solid var(--av-line)', marginBottom: 16 }}>
                  <p style={{ fontSize: 13, color: 'var(--av-ink)', margin: '0 0 8px', fontFamily: 'var(--av-sans)', lineHeight: 1.5 }}>
                    Add <strong style={{ color: 'var(--av-cognac)' }}>{fmt(nextGoal.amount - subtotal)}</strong> more to get{' '}
                    <strong style={{ color: 'var(--av-ink)' }}>
                      {nextGoal.reward === 'free_shipping'
                        ? 'complimentary shipping'
                        : (nextGoal as any).label || ((nextGoal as any).reward === 'discount_pct' ? `${(nextGoal as any).value}% off` : `${fmt((nextGoal as any).value)} off`)}
                    </strong>
                  </p>
                  <div style={{ width: '100%', height: 3, background: 'var(--av-paper-2)', overflow: 'hidden' }}>
                    <div style={{ height: '100%', background: 'var(--av-cognac)', width: `${Math.min(100, Math.round((subtotal / nextGoal.amount) * 100))}%`, transition: 'width .4s' }} />
                  </div>
                </div>
              ) : allGoals.length > 0 ? (
                <div style={{ padding: '12px 16px', background: 'var(--av-paper)', border: '1px solid var(--av-line)', marginBottom: 16 }}>
                  <p style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-cognac)', margin: 0, fontFamily: 'var(--av-sans)' }}>You've unlocked all rewards.</p>
                </div>
              ) : null}

              {/* Table header (desktop) */}
              <div className="av-cart-head" style={{ display: 'grid', gridTemplateColumns: '64px 1fr 120px 100px 100px 32px', gap: 12, padding: '12px 16px', background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderBottom: 'none', fontSize: 10, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.16em', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                <div />
                <div>Product</div>
                <div style={{ textAlign: 'center' }}>Qty</div>
                <div style={{ textAlign: 'right' }}>Price</div>
                <div style={{ textAlign: 'right' }}>Total</div>
                <div />
              </div>

              {/* Rows */}
              <div style={{ border: '1px solid var(--av-line)', overflow: 'hidden' }}>
                {items.map((item, idx) => (
                  <div key={item.key}
                    style={{ borderTop: idx === 0 ? 'none' : '1px solid var(--av-line-soft)', background: 'var(--av-paper)' }}>

                    {/* Desktop row */}
                    <div className="av-cart-row-desktop" style={{ display: 'grid', gridTemplateColumns: '64px 1fr 120px 100px 100px 32px', gap: 12, padding: 16, alignItems: 'center' }}>
                      <Link href={`/shop/${item.slug}`}>
                        <div style={{ width: 64, height: 80, overflow: 'hidden', background: 'var(--av-paper-2)', flexShrink: 0 }}>
                          {item.image_url
                            ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
                            : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', color: 'var(--av-muted)' }}>
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.2}/>
                                  <circle cx="8.5" cy="8.5" r="1.5" strokeWidth={1.2}/>
                                  <path strokeWidth={1.2} d="M21 15l-5-5L5 21"/>
                                </svg>
                              </div>
                          }
                        </div>
                      </Link>

                      <div>
                        <Link href={`/shop/${item.slug}`} style={{ fontFamily: 'var(--av-display)', fontSize: 15, fontWeight: 400, color: 'var(--av-ink)', textDecoration: 'none', lineHeight: 1.3, display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                          {item.name}
                        </Link>
                        {item.variant_label && <div style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 4, fontFamily: 'var(--av-sans)', letterSpacing: '0.04em' }}>{item.variant_label}</div>}
                      </div>

                      <div style={{ display: 'flex', justifyContent: 'center' }}>
                        <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--av-line)', height: 36, background: 'var(--av-paper)' }}>
                          <button onClick={() => updateQty(item.key, item.quantity - 1)}
                            style={{ width: 32, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', display: 'grid', placeItems: 'center', fontSize: 16 }}
                            aria-label="Decrease">−</button>
                          <span style={{ width: 32, textAlign: 'center', fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{item.quantity}</span>
                          <button onClick={() => updateQty(item.key, item.quantity + 1)}
                            style={{ width: 32, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', display: 'grid', placeItems: 'center', fontSize: 16 }}
                            aria-label="Increase">+</button>
                        </div>
                      </div>

                      <div style={{ textAlign: 'right', fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{fmt(item.price)}</div>
                      <div style={{ textAlign: 'right', fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{fmt(item.price * item.quantity)}</div>

                      <button onClick={() => removeItem(item.key)}
                        style={{ width: 28, height: 28, border: 'none', background: 'transparent', cursor: 'pointer', color: 'var(--av-muted)', display: 'grid', placeItems: 'center' }}
                        aria-label="Remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                    </div>

                    {/* Mobile row */}
                    <div className="av-cart-row-mobile" style={{ display: 'none', padding: 16, gap: 12 }}>
                      <Link href={`/shop/${item.slug}`} style={{ flexShrink: 0 }}>
                        <div style={{ width: 72, height: 88, overflow: 'hidden', background: 'var(--av-paper-2)' }}>
                          {item.image_url
                            ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                            : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', color: 'var(--av-muted)' }}>
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.2}/>
                                </svg>
                              </div>
                          }
                        </div>
                      </Link>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <Link href={`/shop/${item.slug}`} style={{ fontFamily: 'var(--av-display)', fontSize: 15, fontWeight: 400, color: 'var(--av-ink)', textDecoration: 'none', lineHeight: 1.3, display: 'block' }}>
                          {item.name}
                        </Link>
                        {item.variant_label && <div style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 2, fontFamily: 'var(--av-sans)' }}>{item.variant_label}</div>}
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 12 }}>
                          <div style={{ display: 'flex', alignItems: 'center', border: '1px solid var(--av-line)', height: 34 }}>
                            <button onClick={() => updateQty(item.key, item.quantity - 1)}
                              style={{ width: 30, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 16 }}>−</button>
                            <span style={{ width: 30, textAlign: 'center', fontSize: 13, fontWeight: 500, fontFamily: 'var(--av-sans)' }}>{item.quantity}</span>
                            <button onClick={() => updateQty(item.key, item.quantity + 1)}
                              style={{ width: 30, height: '100%', background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-ink)', fontSize: 16 }}>+</button>
                          </div>
                          <span style={{ fontSize: 14, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{fmt(item.price * item.quantity)}</span>
                        </div>
                      </div>
                      <button onClick={() => removeItem(item.key)}
                        style={{ alignSelf: 'flex-start', width: 26, height: 26, border: 'none', background: 'transparent', cursor: 'pointer', color: 'var(--av-muted)', display: 'grid', placeItems: 'center', flexShrink: 0, marginTop: 2 }}
                        aria-label="Remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                ))}
              </div>

              <div style={{ display: 'flex', gap: 10, marginTop: 16, flexWrap: 'wrap' }}>
                <Link href="/shop" className="av-btn av-btn-secondary av-btn-sm" style={{ textDecoration: 'none' }}>
                  ← Continue shopping
                </Link>
                <button onClick={() => { if (confirm('Clear your cart?')) clearCart(); }}
                  className="av-btn av-btn-secondary av-btn-sm">
                  Clear cart
                </button>
              </div>
            </div>

            {/* ── Right: Summary ── */}
            <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', padding: 24, position: 'sticky', top: 88 }}>
              <h3 style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 18px', letterSpacing: '-0.01em' }}>Order summary</h3>

              <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 16 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                  <span>Subtotal ({count} item{count !== 1 ? 's' : ''})</span>
                  <span style={{ color: 'var(--av-ink)', fontWeight: 500 }}>{fmt(subtotal)}</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                  <span>Shipping</span>
                  <span style={{ color: shippingFree ? 'var(--av-cognac)' : 'var(--av-ink)', fontWeight: 500 }}>
                    {shippingFree ? 'Complimentary' : fmt(shipping)}
                  </span>
                </div>
                {discount > 0 && (
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13.5, color: 'var(--av-cognac)', fontWeight: 500, fontFamily: 'var(--av-sans)' }}>
                    <span>Discount ({coupon?.code})</span>
                    <span>−{fmt(discount)}</span>
                  </div>
                )}
              </div>

              {!coupon ? (
                <form onSubmit={handleCoupon} style={{ display: 'flex', gap: 8, marginBottom: 12 }}>
                  <input
                    type="text"
                    placeholder="Promo code"
                    value={couponInput}
                    onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(''); }}
                    className="av-input"
                    style={{ flex: 1, textTransform: 'uppercase', letterSpacing: '0.06em', fontSize: 12 }}
                  />
                  <button type="submit" disabled={couponLoading} className="av-btn av-btn-primary av-btn-sm" style={{ flexShrink: 0, height: 42 }}>
                    {couponLoading ? '…' : 'Apply'}
                  </button>
                </form>
              ) : (
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: 'var(--av-paper-2)', border: '1px solid var(--av-line)', padding: '10px 12px', marginBottom: 12 }}>
                  <div>
                    <span style={{ fontSize: 11.5, fontWeight: 600, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', letterSpacing: '0.08em' }}>{coupon.code}</span>
                    <span style={{ fontSize: 11.5, color: 'var(--av-cognac)', marginLeft: 8, fontFamily: 'var(--av-sans)' }}>{coupon.message}</span>
                  </div>
                  <button onClick={removeCoupon} style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-muted)', display: 'grid', placeItems: 'center', padding: 4 }} aria-label="Remove coupon">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              )}
              {couponError && <p style={{ fontSize: 11.5, color: '#c04', marginBottom: 10, marginTop: -4, fontFamily: 'var(--av-sans)' }}>{couponError}</p>}

              {!shippingFree && freeShippingAbove > 0 && (
                <div style={{ marginBottom: 14 }}>
                  <p style={{ fontSize: 11.5, color: 'var(--av-muted)', marginBottom: 6, fontFamily: 'var(--av-sans)', lineHeight: 1.5 }}>
                    Add <strong style={{ color: 'var(--av-ink)' }}>{fmt(needed)}</strong> more for complimentary shipping
                  </p>
                  <div style={{ width: '100%', height: 2, background: 'var(--av-paper-2)', overflow: 'hidden' }}>
                    <div style={{ height: '100%', background: 'var(--av-ink)', width: `${shipPct}%`, transition: 'width .4s' }} />
                  </div>
                </div>
              )}
              {shippingFree && (
                <p style={{ fontSize: 11.5, color: 'var(--av-cognac)', fontWeight: 500, marginBottom: 14, fontFamily: 'var(--av-sans)' }}>✓ Complimentary shipping unlocked</p>
              )}

              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 15, fontWeight: 500, color: 'var(--av-ink)', borderTop: '1px solid var(--av-line)', paddingTop: 14, marginBottom: 16, fontFamily: 'var(--av-sans)' }}>
                <span style={{ letterSpacing: '0.04em' }}>Total</span>
                <span style={{ fontSize: 16 }}>{fmt(grandTotal)}</span>
              </div>

              <Link href="/checkout" className="av-btn av-btn-primary av-btn-lg av-btn-block" style={{ textDecoration: 'none' }}>
                Proceed to checkout
              </Link>

              <div style={{ display: 'flex', gap: 6, justifyContent: 'center', marginTop: 14, flexWrap: 'wrap' }}>
                {['bKash', 'Nagad', 'Card', 'COD'].map(m => (
                  <span key={m} style={{ color: 'var(--av-muted)', border: '1px solid var(--av-line-soft)', fontSize: 10, fontWeight: 500, padding: '3px 8px', letterSpacing: '0.08em', textTransform: 'uppercase', fontFamily: 'var(--av-sans)' }}>
                    {m}
                  </span>
                ))}
              </div>
            </div>

          </div>
        )}
      </div>

      <style>{`
        @media(max-width: 960px){
          .av-cart-layout{ grid-template-columns: 1fr !important; }
          .av-cart-head{ display:none !important; }
          .av-cart-row-desktop{ display:none !important; }
          .av-cart-row-mobile{ display:flex !important; }
        }
      `}</style>
    </Layout>
  );
}
