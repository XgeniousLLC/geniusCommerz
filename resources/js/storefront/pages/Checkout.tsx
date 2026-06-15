import { Head, Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import Layout from '../layouts/Layout';
import { useCartDerived, useCartStore } from '../store/cartStore';
import { usePrice } from '../usePrice';
import type { SharedProps } from '../types';

interface LocationOption { id: number; name: string; }
interface Prefill { name: string; email: string; phone: string; address: string; city: string; }
interface Props {
  shippingCost: number; freeAbove: number; paymentMethods: Record<string, string>;
  loyaltyEnabled?: boolean; loyaltyBalance?: number; loyaltyTaka?: number;
  courierLocationEnabled?: boolean; prefill?: Prefill | null;
}

const PAYMENT_META: Record<string, string> = {
  bkash: 'Instant mobile payment', nagad: 'Instant mobile payment',
  rocket: 'Mobile banking payment', card: 'Visa, Mastercard, Amex', cod: 'Pay on delivery',
};

const S = { fill: 'none', stroke: 'currentColor', strokeWidth: 1.4, strokeLinecap: 'round', strokeLinejoin: 'round' } as React.SVGProps<SVGSVGElement>;

function Lbl({ children, required }: { children: React.ReactNode; required?: boolean }) {
  return (
    <label style={{ display: 'block', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 8, fontFamily: 'var(--av-sans)' }}>
      {children}{required && <span style={{ color: 'var(--av-cognac)', marginLeft: 2 }}>*</span>}
    </label>
  );
}

function Err({ msg }: { msg?: string }) {
  if (!msg) return null;
  return <p style={{ fontSize: 11.5, color: '#b94040', marginTop: 5, fontFamily: 'var(--av-sans)' }}>{msg}</p>;
}

const inp: React.CSSProperties = {
  width: '100%', height: 46, padding: '0 0 0 0',
  border: 'none', borderBottom: '1px solid var(--av-line)',
  fontSize: 14, outline: 'none', color: 'var(--av-ink)',
  background: 'transparent', boxSizing: 'border-box',
  fontFamily: 'var(--av-sans)', transition: 'border-color .18s',
};

function Inp({ value, onChange, placeholder, type = 'text', required }: {
  value: string; onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  placeholder?: string; type?: string; required?: boolean;
}) {
  const [f, setF] = useState(false);
  return (
    <input type={type} value={value} onChange={onChange} placeholder={placeholder} required={required}
      style={{ ...inp, borderBottomColor: f ? 'var(--av-ink)' : 'var(--av-line)' }}
      onFocus={() => setF(true)} onBlur={() => setF(false)} />
  );
}

function Sel({ value, onChange, children, required }: {
  value: string | number; onChange: (e: React.ChangeEvent<HTMLSelectElement>) => void;
  children: React.ReactNode; required?: boolean;
}) {
  const [f, setF] = useState(false);
  return (
    <select value={value} onChange={onChange} required={required}
      style={{ ...inp, appearance: 'none', cursor: 'pointer', borderBottomColor: f ? 'var(--av-ink)' : 'var(--av-line)', paddingRight: 24 }}
      onFocus={() => setF(true)} onBlur={() => setF(false)}>
      {children}
    </select>
  );
}

function SectionEyebrow({ children }: { children: React.ReactNode }) {
  return (
    <div style={{ fontSize: 10.5, fontWeight: 500, letterSpacing: '0.28em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 20, paddingBottom: 12, borderBottom: '1px solid var(--av-line-soft)' }}>
      {children}
    </div>
  );
}

export default function Checkout({ shippingCost, freeAbove, paymentMethods, loyaltyEnabled = false, loyaltyBalance = 0, loyaltyTaka = 0, courierLocationEnabled = false, prefill = null }: Props) {
  const { auth } = usePage<SharedProps>().props;
  const items        = useCartStore(s => s.items);
  const coupon       = useCartStore(s => s.coupon);
  const applyCoupon  = useCartStore(s => s.applyCoupon);
  const removeCoupon = useCartStore(s => s.removeCoupon);
  const clearCart    = useCartStore(s => s.clearCart);
  const { subtotal, discount } = useCartDerived();
  const fmt = usePrice();

  const allShippingIncluded = items.length > 0 && items.every(i => i.shipping_included);

  const [cities, setCities]   = useState<LocationOption[]>([]);
  const [zones, setZones]     = useState<LocationOption[]>([]);
  const [areas, setAreas]     = useState<LocationOption[]>([]);
  const [cityId, setCityId]   = useState<number | null>(null);
  const [zoneId, setZoneId]   = useState<number | null>(null);
  const [areaId, setAreaId]   = useState<number | null>(null);
  const [courierCharge, setCourierCharge] = useState<number | null>(null);
  const [loadingCities, setLoadingCities] = useState(false);
  const [loadingZones, setLoadingZones]   = useState(false);
  const [loadingAreas, setLoadingAreas]   = useState(false);
  const [loadingCharge, setLoadingCharge] = useState(false);

  useEffect(() => {
    if (!courierLocationEnabled) return;
    setLoadingCities(true);
    fetch('/api/courier/cities').then(r => r.json()).then(d => setCities(d.cities ?? [])).catch(() => setCities([])).finally(() => setLoadingCities(false));
  }, [courierLocationEnabled]);

  const handleCityChange = useCallback((id: number) => {
    setCityId(id); setZoneId(null); setAreaId(null); setZones([]); setAreas([]); setCourierCharge(null);
    if (!id) return;
    setLoadingZones(true);
    fetch(`/api/courier/zones/${id}`).then(r => r.json()).then(d => setZones(d.zones ?? [])).catch(() => setZones([])).finally(() => setLoadingZones(false));
  }, []);

  const fetchCharge = (c: number, z: number, a: number | null) => {
    setLoadingCharge(true);
    fetch('/api/courier/charge', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '' }, body: JSON.stringify({ city_id: c, zone_id: z, area_id: a, item_weight: 0.5 }) })
      .then(r => r.json()).then(d => setCourierCharge(d.charge !== null && d.charge !== undefined ? Number(d.charge) : null)).catch(() => setCourierCharge(null)).finally(() => setLoadingCharge(false));
  };

  const handleZoneChange = useCallback((id: number) => {
    setZoneId(id); setAreaId(null); setAreas([]); setCourierCharge(null);
    if (!id) return;
    setLoadingAreas(true);
    fetch(`/api/courier/areas/${id}`).then(r => r.json()).then(d => setAreas(d.areas ?? [])).catch(() => setAreas([])).finally(() => setLoadingAreas(false));
    fetchCharge(cityId!, id, null);
  }, [cityId]);

  const handleAreaChange = useCallback((id: number) => { setAreaId(id); fetchCharge(cityId!, zoneId!, id); }, [cityId, zoneId]);

  const effectiveShipping = (() => {
    if (allShippingIncluded) return 0;
    if (courierLocationEnabled && courierCharge !== null) return courierCharge;
    if (freeAbove > 0 && subtotal >= freeAbove) return 0;
    return shippingCost;
  })();

  const [redeemPoints, setRedeemPoints] = useState(false);
  const loyaltyDiscount = redeemPoints ? loyaltyTaka : 0;
  const total = Math.max(0, subtotal - discount - loyaltyDiscount + effectiveShipping);

  const [form, setForm] = useState({ customer_name: prefill?.name ?? '', customer_phone: prefill?.phone ?? '', customer_email: prefill?.email ?? '', address: prefill?.address ?? '', city: prefill?.city ?? '', notes: '', payment_method: Object.keys(paymentMethods)[0] ?? 'cod' });
  const [errors, setErrors]             = useState<Record<string, string>>({});
  const [couponInput, setCouponInput]   = useState('');
  const [couponError, setCouponError]   = useState('');
  const [couponLoading, setCouponLoading] = useState(false);
  const [submitting, setSubmitting]     = useState(false);
  const [agreeTerms, setAgreeTerms]     = useState(true);
  const [hydrated, setHydrated]         = useState(false);

  useEffect(() => { setHydrated(true); }, []);
  useEffect(() => { if (hydrated && items.length === 0) router.visit('/shop'); }, [hydrated, items.length]);

  const set = (k: string) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => setForm(f => ({ ...f, [k]: e.target.value }));

  async function handleCoupon(e: React.FormEvent) {
    e.preventDefault(); if (!couponInput.trim()) return;
    setCouponLoading(true); setCouponError('');
    const err = await applyCoupon(couponInput.trim().toUpperCase());
    if (err) setCouponError(err); else setCouponInput('');
    setCouponLoading(false);
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault(); if (items.length === 0) return;
    setSubmitting(true); setErrors({});
    router.post('/checkout', { ...form, coupon_code: coupon?.code ?? '', loyalty_points_redeem: redeemPoints ? loyaltyBalance : 0, courier_city_id: cityId, courier_zone_id: zoneId, courier_area_id: areaId, items: items.map(i => ({ product_id: i.product_id, variant_id: i.variant_id, name: i.name, sku: null, variant_label: i.variant_label, price: i.price, quantity: i.quantity })) },
      { onSuccess: () => clearCart(), onError: errs => { setErrors(errs); setSubmitting(false); }, onFinish: () => setSubmitting(false) });
  }

  if (!hydrated || items.length === 0) return null;

  const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };

  return (
    <Layout>
      <Head title="Checkout" />

      {/* Header bar */}
      <div style={{ background: 'var(--av-paper)', borderBottom: '1px solid var(--av-line)', padding: '20px var(--av-gutter)' }}>
        <div style={{ ...W }}>
          <nav style={{ display: 'flex', gap: 8, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 8 }}>
            <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link><span>/</span>
            <Link href="/cart" style={{ color: 'inherit', textDecoration: 'none' }}>Cart</Link><span>/</span>
            <span style={{ color: 'var(--av-ink)' }}>Checkout</span>
          </nav>
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(22px,3vw,34px)', fontWeight: 400, color: 'var(--av-ink)', margin: 0, letterSpacing: '-0.012em' }}>Complete your order</h1>
        </div>
      </div>

      <div style={{ ...W, paddingTop: 40, paddingBottom: 80 }}>
        {!auth.user && (
          <div style={{ padding: '12px 0', marginBottom: 24, fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', borderBottom: '1px solid var(--av-line-soft)' }}>
            Have an account?{' '}
            <Link href="/login" style={{ color: 'var(--av-ink)', fontWeight: 500, textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>Sign in</Link>
            {' '}for saved addresses, or checkout as guest below.
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div style={{ display: 'grid', gridTemplateColumns: '1.3fr 1fr', gap: 64, alignItems: 'start' }} className="av-co-grid">

            {/* ── Left: form ── */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 40 }}>

              {/* Contact */}
              <div>
                <SectionEyebrow>Contact</SectionEyebrow>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px 24px' }} className="av-co-fields">
                  <div>
                    <Lbl required>Full name</Lbl>
                    <Inp value={form.customer_name} onChange={set('customer_name')} placeholder="Tahmid Rahman" required />
                    <Err msg={errors.customer_name} />
                  </div>
                  <div>
                    <Lbl required>Phone</Lbl>
                    <Inp value={form.customer_phone} onChange={set('customer_phone')} placeholder="01XXXXXXXXX" required />
                    <Err msg={errors.customer_phone} />
                  </div>
                  <div style={{ gridColumn: '1/-1' }}>
                    <Lbl>Email</Lbl>
                    <Inp value={form.customer_email} onChange={set('customer_email')} placeholder="you@email.com" type="email" />
                    <Err msg={errors.customer_email} />
                  </div>
                </div>
              </div>

              {/* Delivery */}
              <div>
                <SectionEyebrow>Delivery address</SectionEyebrow>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
                  <div>
                    <Lbl required>Street address</Lbl>
                    <textarea value={form.address} onChange={set('address')} placeholder="House, road, block, area" required rows={2}
                      style={{ ...inp, height: 'auto', padding: '10px 0', resize: 'none', borderBottom: '1px solid var(--av-line)' }}
                      onFocus={e => e.currentTarget.style.borderBottomColor = 'var(--av-ink)'}
                      onBlur={e => e.currentTarget.style.borderBottomColor = 'var(--av-line)'} />
                    <Err msg={errors.address} />
                  </div>

                  {courierLocationEnabled ? (
                    <>
                      <div>
                        <Lbl required>City / District</Lbl>
                        <Sel value={cityId ?? ''} onChange={e => handleCityChange(Number(e.target.value))} required>
                          <option value="">{loadingCities ? 'Loading…' : 'Select city'}</option>
                          {cities.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </Sel>
                        <Err msg={errors.city} />
                      </div>
                      {zones.length > 0 && (
                        <div>
                          <Lbl required>Zone / Thana</Lbl>
                          <Sel value={zoneId ?? ''} onChange={e => handleZoneChange(Number(e.target.value))} required>
                            <option value="">{loadingZones ? 'Loading…' : 'Select zone'}</option>
                            {zones.map(z => <option key={z.id} value={z.id}>{z.name}</option>)}
                          </Sel>
                        </div>
                      )}
                      {areas.length > 0 && (
                        <div>
                          <Lbl>Area</Lbl>
                          <Sel value={areaId ?? ''} onChange={e => handleAreaChange(Number(e.target.value))}>
                            <option value="">{loadingAreas ? 'Loading…' : 'Select area'}</option>
                            {areas.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                          </Sel>
                        </div>
                      )}
                      {cityId && (
                        <div style={{ fontSize: 12.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                          Delivery charge: {loadingCharge ? 'calculating…' : courierCharge !== null ? <strong style={{ color: 'var(--av-ink)' }}>{fmt(courierCharge)}</strong> : 'Select zone'}
                        </div>
                      )}
                      <input type="hidden" name="city" value={cities.find(c => c.id === cityId)?.name ?? ''} />
                    </>
                  ) : (
                    <div>
                      <Lbl required>City / District</Lbl>
                      <Inp value={form.city} onChange={set('city')} placeholder="e.g. Dhaka" required />
                      <Err msg={errors.city} />
                    </div>
                  )}

                  <div>
                    <Lbl>Order note</Lbl>
                    <textarea value={form.notes} onChange={set('notes')} placeholder="Any special instructions…" rows={2}
                      style={{ ...inp, height: 'auto', padding: '10px 0', resize: 'none', borderBottom: '1px solid var(--av-line)' }}
                      onFocus={e => e.currentTarget.style.borderBottomColor = 'var(--av-ink)'}
                      onBlur={e => e.currentTarget.style.borderBottomColor = 'var(--av-line)'} />
                  </div>
                </div>
              </div>

              {/* Loyalty */}
              {loyaltyEnabled && loyaltyTaka > 0 && (
                <div>
                  <SectionEyebrow>Loyalty rewards</SectionEyebrow>
                  <label style={{ display: 'flex', alignItems: 'center', gap: 12, cursor: 'pointer', padding: '14px 16px', border: '1px solid var(--av-line)', background: redeemPoints ? 'rgba(149,97,58,.06)' : 'transparent' }}>
                    <input type="checkbox" checked={redeemPoints} onChange={e => setRedeemPoints(e.target.checked)}
                      style={{ width: 14, height: 14, accentColor: 'var(--av-ink)', cursor: 'pointer', flexShrink: 0 }} />
                    <span style={{ fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>
                      Use <strong>{loyaltyBalance.toLocaleString()}</strong> points → save <strong style={{ color: 'var(--av-cognac)' }}>{fmt(loyaltyTaka)}</strong>
                    </span>
                  </label>
                </div>
              )}

              {/* Payment */}
              <div>
                <SectionEyebrow>Payment method</SectionEyebrow>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 0, border: '1px solid var(--av-line)' }}>
                  {Object.entries(paymentMethods).map(([value, label], i) => {
                    const active = form.payment_method === value;
                    return (
                      <label key={value}
                        style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '14px 16px', cursor: 'pointer', background: active ? 'rgba(149,97,58,.06)' : 'transparent', borderTop: i > 0 ? '1px solid var(--av-line-soft)' : 'none', transition: 'background .15s' }}>
                        <input type="radio" name="payment_method" value={value} checked={active} onChange={set('payment_method')}
                          style={{ accentColor: 'var(--av-ink)', width: 14, height: 14, flexShrink: 0 }} />
                        <div>
                          <div style={{ fontSize: 14, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{label}</div>
                          {PAYMENT_META[value] && <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 2 }}>{PAYMENT_META[value]}</div>}
                        </div>
                        {active && (
                          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="var(--av-cognac)" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" style={{ marginLeft: 'auto' }}>
                            <path d="m5 12 4.5 4.5L19 7"/>
                          </svg>
                        )}
                      </label>
                    );
                  })}
                </div>
                <Err msg={errors.payment_method} />
                {form.payment_method === 'cod' && (
                  <div style={{ marginTop: 12, fontSize: 12.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', letterSpacing: '0.04em' }}>
                    Pay when your order arrives. No advance needed.
                  </div>
                )}
              </div>

              {/* Terms + CTA */}
              <div>
                <label style={{ display: 'flex', alignItems: 'flex-start', gap: 10, cursor: 'pointer', marginBottom: 20 }}>
                  <input type="checkbox" checked={agreeTerms} onChange={e => setAgreeTerms(e.target.checked)}
                    style={{ marginTop: 2, width: 14, height: 14, accentColor: 'var(--av-ink)', cursor: 'pointer', flexShrink: 0 }} />
                  <span style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', lineHeight: 1.6 }}>
                    I agree to the{' '}
                    <Link href="/page/terms" style={{ color: 'var(--av-ink)', textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>Terms</Link>
                    {' & '}
                    <Link href="/page/refund-policy" style={{ color: 'var(--av-ink)', textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>Refund policy</Link>
                  </span>
                </label>

                <button type="submit" disabled={submitting || !agreeTerms || items.length === 0}
                  style={{ width: '100%', height: 52, background: submitting ? 'var(--av-muted)' : 'var(--av-ink)', color: 'var(--av-paper)', border: 'none', cursor: submitting || !agreeTerms ? 'not-allowed' : 'pointer', fontFamily: 'var(--av-sans)', fontSize: 12, fontWeight: 500, letterSpacing: '0.24em', textTransform: 'uppercase', opacity: !agreeTerms ? 0.5 : 1, transition: 'background .2s' }}>
                  {submitting ? 'Placing order…' : `Place order — ${fmt(total)}`}
                </button>

                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, marginTop: 12, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                  <svg viewBox="0 0 24 24" width="12" height="12" {...S}><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  256-bit SSL encrypted · details never shared
                </div>
              </div>
            </div>

            {/* ── Right: summary ── */}
            <div style={{ position: 'sticky', top: 80, display: 'flex', flexDirection: 'column', gap: 0 }} className="av-co-aside">
              <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', padding: 24 }}>

                {/* Items */}
                <div style={{ fontSize: 10.5, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 16 }}>
                  Order summary · {items.length} item{items.length !== 1 ? 's' : ''}
                </div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14, paddingBottom: 16, marginBottom: 16, borderBottom: '1px solid var(--av-line-soft)' }}>
                  {items.map(item => (
                    <div key={item.key} style={{ display: 'flex', gap: 12, alignItems: 'flex-start' }}>
                      <div style={{ position: 'relative', flexShrink: 0 }}>
                        <div style={{ width: 52, height: 64, background: 'var(--av-paper-2)', overflow: 'hidden' }}>
                          {item.image_url && <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />}
                        </div>
                        <span style={{ position: 'absolute', top: -6, right: -6, width: 18, height: 18, borderRadius: '50%', background: 'var(--av-ink)', color: 'var(--av-paper)', fontSize: 10, fontWeight: 600, display: 'grid', placeItems: 'center', fontFamily: 'var(--av-sans)' }}>
                          {item.quantity}
                        </span>
                      </div>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-display)', fontWeight: 400, lineHeight: 1.2, overflow: 'hidden', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' }}>{item.name}</div>
                        {item.variant_label && <div style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 3 }}>{item.variant_label}</div>}
                      </div>
                      <span style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', flexShrink: 0 }}>{fmt(item.price * item.quantity)}</span>
                    </div>
                  ))}
                </div>

                {/* Coupon */}
                <div style={{ marginBottom: 16, paddingBottom: 16, borderBottom: '1px solid var(--av-line-soft)' }}>
                  {!coupon ? (
                    <form onSubmit={handleCoupon} style={{ display: 'flex', gap: 8 }}>
                      <input type="text" placeholder="Coupon code" value={couponInput}
                        onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(''); }}
                        style={{ flex: 1, border: 'none', borderBottom: '1px solid var(--av-line)', height: 36, fontSize: 12.5, color: 'var(--av-ink)', background: 'transparent', outline: 'none', fontFamily: 'var(--av-sans)', letterSpacing: '0.08em', textTransform: 'uppercase' }} />
                      <button type="submit" disabled={couponLoading}
                        style={{ padding: '0 14px', height: 36, background: 'transparent', border: '1px solid var(--av-line)', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.14em', textTransform: 'uppercase', cursor: 'pointer', flexShrink: 0 }}>
                        {couponLoading ? '…' : 'Apply'}
                      </button>
                    </form>
                  ) : (
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '8px 10px', border: '1px solid rgba(149,97,58,.3)', background: 'rgba(149,97,58,.06)' }}>
                      <span style={{ fontSize: 12.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)' }}><strong>{coupon.code}</strong> — {coupon.message}</span>
                      <button type="button" onClick={removeCoupon} style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: 'var(--av-cognac)', padding: 3 }}>
                        <svg viewBox="0 0 24 24" width="12" height="12" {...S}><path d="M18 6 6 18M6 6l12 12"/></svg>
                      </button>
                    </div>
                  )}
                  {couponError && <p style={{ fontSize: 11.5, color: '#b94040', fontFamily: 'var(--av-sans)', marginTop: 6 }}>{couponError}</p>}
                </div>

                {/* Totals */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                  {[
                    ['Subtotal', fmt(subtotal), 'var(--av-ink)'],
                    ...(discount > 0 ? [[`Discount (${coupon?.code ?? ''})`, `−${fmt(discount)}`, 'var(--av-cognac)']] : []),
                    ...(loyaltyDiscount > 0 ? [['Loyalty', `−${fmt(loyaltyDiscount)}`, 'var(--av-cognac)']] : []),
                    ['Shipping', effectiveShipping === 0 ? 'Free' : loadingCharge ? '…' : fmt(effectiveShipping), effectiveShipping === 0 ? 'var(--av-cognac)' : 'var(--av-ink)'],
                  ].map(([label, value, color], i) => (
                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13, fontFamily: 'var(--av-sans)' }}>
                      <span style={{ color: 'var(--av-muted)' }}>{label}</span>
                      <span style={{ color, fontWeight: 500 }}>{value}</span>
                    </div>
                  ))}
                  {!allShippingIncluded && !courierLocationEnabled && freeAbove > 0 && effectiveShipping > 0 && (
                    <p style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: 0 }}>
                      Add {fmt(freeAbove - subtotal)} more for free shipping
                    </p>
                  )}
                  <div style={{ display: 'flex', justifyContent: 'space-between', paddingTop: 12, borderTop: '1px solid var(--av-line)', fontFamily: 'var(--av-sans)' }}>
                    <span style={{ fontSize: 14, color: 'var(--av-ink)', fontWeight: 500 }}>Total</span>
                    <span style={{ fontSize: 17, color: 'var(--av-ink)', fontWeight: 600 }}>{fmt(total)}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>

      <style>{`
        @media(max-width:860px){
          .av-co-grid{grid-template-columns:1fr!important;gap:40px!important}
          .av-co-aside{position:static!important;order:-1}
          .av-co-fields{grid-template-columns:1fr!important}
        }
      `}</style>
    </Layout>
  );
}
