import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect, useCallback } from 'react';
import Layout from '../layouts/Layout';
import { useCartStore, useCartDerived } from '../store/cartStore';
import { usePrice } from '../usePrice';
import type { SharedProps } from '../types';

interface LocationOption { id: number; name: string; }

interface Prefill {
  name: string;
  email: string;
  phone: string;
  address: string;
  city: string;
}

interface Props {
  shippingCost: number;
  freeAbove: number;
  paymentMethods: Record<string, string>;
  loyaltyEnabled?: boolean;
  loyaltyBalance?: number;
  loyaltyTaka?: number;
  courierLocationEnabled?: boolean;
  prefill?: Prefill | null;
}

const PAYMENT_META: Record<string, string> = {
  bkash:  'Instant mobile payment',
  nagad:  'Instant mobile payment',
  rocket: 'Mobile banking payment',
  card:   'Visa, Mastercard, Amex',
  cod:    'Pay in cash on arrival',
};

function SectionCard({ title, sub, children }: { title: string; sub?: string; children: React.ReactNode }) {
  return (
    <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '20px 24px' }}>
      <h3 style={{ fontSize: 15, fontWeight: 700, color: 'var(--kb-ink)', margin: '0 0 2px' }}>{title}</h3>
      {sub && <p style={{ fontSize: 13, color: 'var(--kb-ink-soft)', margin: '0 0 16px' }}>{sub}</p>}
      {!sub && <div style={{ marginBottom: 16 }} />}
      {children}
    </div>
  );
}

function FieldLabel({ children, required }: { children: React.ReactNode; required?: boolean }) {
  return (
    <label style={{ display: 'block', fontSize: 13, fontWeight: 600, color: 'var(--kb-ink)', marginBottom: 6 }}>
      {children}{required && <span style={{ color: '#EF4444', marginLeft: 2 }}>*</span>}
    </label>
  );
}

function FieldError({ msg }: { msg?: string }) {
  if (!msg) return null;
  return <p style={{ fontSize: 12, color: '#EF4444', marginTop: 4 }}>{msg}</p>;
}

const inputStyle: React.CSSProperties = {
  width: '100%', height: 44, padding: '0 12px',
  border: '1.5px solid var(--kb-border)', borderRadius: 8,
  fontSize: 14, outline: 'none', color: 'var(--kb-ink)',
  background: '#fff', boxSizing: 'border-box',
  transition: 'border-color .15s',
};

function Inp({ value, onChange, placeholder, type = 'text', required, onFocus, onBlur }: {
  value: string; onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  placeholder?: string; type?: string; required?: boolean;
  onFocus?: () => void; onBlur?: () => void;
}) {
  const [focused, setFocused] = useState(false);
  return (
    <input
      type={type} value={value} onChange={onChange} placeholder={placeholder} required={required}
      style={{ ...inputStyle, borderColor: focused ? 'var(--kb-primary)' : 'var(--kb-border)' }}
      onFocus={() => setFocused(true)}
      onBlur={() => setFocused(false)}
    />
  );
}

function Sel({ value, onChange, children, required }: {
  value: string | number; onChange: (e: React.ChangeEvent<HTMLSelectElement>) => void;
  children: React.ReactNode; required?: boolean;
}) {
  const [focused, setFocused] = useState(false);
  return (
    <select value={value} onChange={onChange} required={required}
      style={{ ...inputStyle, appearance: 'none', cursor: 'pointer', borderColor: focused ? 'var(--kb-primary)' : 'var(--kb-border)' }}
      onFocus={() => setFocused(true)}
      onBlur={() => setFocused(false)}>
      {children}
    </select>
  );
}

export default function Checkout({
  shippingCost,
  freeAbove,
  paymentMethods,
  loyaltyEnabled = false,
  loyaltyBalance = 0,
  loyaltyTaka    = 0,
  courierLocationEnabled = false,
  prefill = null,
}: Props) {
  const { auth } = usePage<SharedProps>().props;
  const items        = useCartStore(s => s.items);
  const coupon       = useCartStore(s => s.coupon);
  const applyCoupon  = useCartStore(s => s.applyCoupon);
  const removeCoupon = useCartStore(s => s.removeCoupon);
  const clearCart    = useCartStore(s => s.clearCart);
  const { subtotal, discount } = useCartDerived();
  const fmt = usePrice();

  const allShippingIncluded = items.length > 0 && items.every(i => i.shipping_included);

  const [cities, setCities]         = useState<LocationOption[]>([]);
  const [zones, setZones]           = useState<LocationOption[]>([]);
  const [areas, setAreas]           = useState<LocationOption[]>([]);
  const [cityId, setCityId]         = useState<number | null>(null);
  const [zoneId, setZoneId]         = useState<number | null>(null);
  const [areaId, setAreaId]         = useState<number | null>(null);
  const [courierCharge, setCourierCharge] = useState<number | null>(null);
  const [loadingCities, setLoadingCities] = useState(false);
  const [loadingZones, setLoadingZones]   = useState(false);
  const [loadingAreas, setLoadingAreas]   = useState(false);
  const [loadingCharge, setLoadingCharge] = useState(false);

  useEffect(() => {
    if (!courierLocationEnabled) return;
    setLoadingCities(true);
    fetch('/api/courier/cities')
      .then(r => r.json())
      .then(d => setCities(d.cities ?? []))
      .catch(() => setCities([]))
      .finally(() => setLoadingCities(false));
  }, [courierLocationEnabled]);

  const handleCityChange = useCallback((id: number) => {
    setCityId(id); setZoneId(null); setAreaId(null);
    setZones([]); setAreas([]); setCourierCharge(null);
    if (!id) return;
    setLoadingZones(true);
    fetch(`/api/courier/zones/${id}`)
      .then(r => r.json())
      .then(d => setZones(d.zones ?? []))
      .catch(() => setZones([]))
      .finally(() => setLoadingZones(false));
  }, []);

  const handleZoneChange = useCallback((id: number) => {
    setZoneId(id); setAreaId(null); setAreas([]); setCourierCharge(null);
    if (!id) return;
    setLoadingAreas(true);
    fetch(`/api/courier/areas/${id}`)
      .then(r => r.json())
      .then(d => setAreas(d.areas ?? []))
      .catch(() => setAreas([]))
      .finally(() => setLoadingAreas(false));
    fetchCharge(cityId!, id, null);
  }, [cityId]);

  const handleAreaChange = useCallback((id: number) => {
    setAreaId(id);
    fetchCharge(cityId!, zoneId!, id);
  }, [cityId, zoneId]);

  const fetchCharge = (c: number, z: number, a: number | null) => {
    setLoadingCharge(true);
    fetch('/api/courier/charge', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '' },
      body: JSON.stringify({ city_id: c, zone_id: z, area_id: a, item_weight: 0.5 }),
    })
      .then(r => r.json())
      .then(d => setCourierCharge(d.charge !== null && d.charge !== undefined ? Number(d.charge) : null))
      .catch(() => setCourierCharge(null))
      .finally(() => setLoadingCharge(false));
  };

  const effectiveShipping = (() => {
    if (allShippingIncluded) return 0;
    if (courierLocationEnabled && courierCharge !== null) return courierCharge;
    if (freeAbove > 0 && subtotal >= freeAbove) return 0;
    return shippingCost;
  })();

  const [redeemPoints, setRedeemPoints] = useState(false);
  const loyaltyDiscount = redeemPoints ? loyaltyTaka : 0;
  const total = Math.max(0, subtotal - discount - loyaltyDiscount + effectiveShipping);

  const [form, setForm] = useState({
    customer_name:  prefill?.name  ?? '',
    customer_phone: prefill?.phone ?? '',
    customer_email: prefill?.email ?? '',
    address:        prefill?.address ?? '',
    city:           prefill?.city    ?? '',
    notes:          '',
    payment_method: Object.keys(paymentMethods)[0] ?? 'cod',
  });
  const [errors, setErrors]             = useState<Record<string, string>>({});
  const [couponInput, setCouponInput]   = useState('');
  const [couponError, setCouponError]   = useState('');
  const [couponLoading, setCouponLoading] = useState(false);
  const [submitting, setSubmitting]     = useState(false);
  const [agreeTerms, setAgreeTerms]     = useState(true);

  const [hydrated, setHydrated] = useState(false);
  useEffect(() => { setHydrated(true); }, []);
  useEffect(() => {
    if (hydrated && items.length === 0) router.visit('/shop');
  }, [hydrated, items.length]);

  const set = (k: string) => (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) =>
    setForm(f => ({ ...f, [k]: e.target.value }));

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

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (items.length === 0) return;
    setSubmitting(true);
    setErrors({});
    router.post('/checkout', {
      ...form,
      coupon_code:           coupon?.code ?? '',
      loyalty_points_redeem: redeemPoints ? loyaltyBalance : 0,
      courier_city_id:       cityId,
      courier_zone_id:       zoneId,
      courier_area_id:       areaId,
      items: items.map(i => ({
        product_id: i.product_id, variant_id: i.variant_id,
        name: i.name, sku: null, variant_label: i.variant_label,
        price: i.price, quantity: i.quantity,
      })),
    }, {
      onSuccess: () => clearCart(),
      onError:   (errs) => { setErrors(errs); setSubmitting(false); },
      onFinish:  () => setSubmitting(false),
    });
  };

  if (!hydrated || items.length === 0) return null;

  return (
    <Layout>
      <Head title="Checkout" />

      <div style={{ maxWidth: 1100, margin: '0 auto', padding: '0 24px 64px' }}>

        {/* Breadcrumb */}
        <nav style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--kb-ink-soft)', padding: '16px 0 0' }}>
          <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-700">Home</Link>
          <span>/</span>
          <Link href="/cart" style={{ color: 'inherit', textDecoration: 'none' }} className="hover:text-slate-700">Cart</Link>
          <span>/</span>
          <span style={{ color: 'var(--kb-ink)', fontWeight: 500 }}>Checkout</span>
        </nav>

        {/* Page head */}
        <div style={{ padding: '16px 0 20px', display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
          <div>
            <h1 style={{ fontSize: 28, fontWeight: 800, letterSpacing: '-0.02em', margin: 0, color: 'var(--kb-ink)' }}>Checkout</h1>
            <p style={{ fontSize: 14, color: 'var(--kb-ink-soft)', margin: '4px 0 0' }}>Complete your order securely.</p>
          </div>
        </div>

        {/* Auth bar (guests only) */}
        {!auth.user && (
          <div style={{ background: 'var(--kb-primary-50)', border: '1px solid var(--kb-primary-100)', borderRadius: 12, padding: '14px 18px', marginBottom: 20, display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12, flexWrap: 'wrap' }}>
            <span style={{ fontSize: 14, color: 'var(--kb-ink)' }}>
              Already have an account?{' '}
              <Link href="/login" style={{ color: 'var(--kb-primary)', fontWeight: 700, textDecoration: 'none' }} className="hover:underline">Sign in</Link>
              {' '}to use saved addresses, or <strong>checkout as guest</strong> below.
            </span>
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6 items-start">

            {/* ── Left ── */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>

              {/* Contact */}
              <SectionCard title="Contact Details" sub="How can we reach you about this order?">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-[14px]">
                  <div>
                    <FieldLabel required>Full name</FieldLabel>
                    <Inp value={form.customer_name} onChange={set('customer_name')} placeholder="Tahmid Rahman" required />
                    <FieldError msg={errors.customer_name} />
                  </div>
                  <div>
                    <FieldLabel required>Phone</FieldLabel>
                    <Inp value={form.customer_phone} onChange={set('customer_phone')} placeholder="01XXXXXXXXX" required />
                    <FieldError msg={errors.customer_phone} />
                  </div>
                </div>
                <div style={{ marginTop: 14 }}>
                  <FieldLabel>Email <span style={{ fontSize: 12, fontWeight: 400, color: 'var(--kb-ink-soft)' }}>(optional)</span></FieldLabel>
                  <Inp value={form.customer_email} onChange={set('customer_email')} placeholder="you@email.com" type="email" />
                  <FieldError msg={errors.customer_email} />
                </div>
              </SectionCard>

              {/* Delivery */}
              <SectionCard title="Delivery Address" sub="Where should we send your order?">
                <div>
                  <FieldLabel required>Street address</FieldLabel>
                  <textarea
                    value={form.address} onChange={set('address')} placeholder="House, road, block, area" required rows={2}
                    style={{ ...inputStyle, height: 'auto', padding: '10px 12px', resize: 'none' }}
                    onFocus={e => e.currentTarget.style.borderColor = 'var(--kb-primary)'}
                    onBlur={e => e.currentTarget.style.borderColor = 'var(--kb-border)'}
                  />
                  <FieldError msg={errors.address} />
                </div>

                <div style={{ marginTop: 14 }}>
                  {courierLocationEnabled ? (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                      <div>
                        <FieldLabel required>City / District</FieldLabel>
                        <Sel value={cityId ?? ''} onChange={e => handleCityChange(Number(e.target.value))} required>
                          <option value="">{loadingCities ? 'Loading…' : 'Select city'}</option>
                          {cities.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </Sel>
                        <FieldError msg={errors.city} />
                      </div>
                      {zones.length > 0 && (
                        <div>
                          <FieldLabel required>Zone / Thana</FieldLabel>
                          <Sel value={zoneId ?? ''} onChange={e => handleZoneChange(Number(e.target.value))} required>
                            <option value="">{loadingZones ? 'Loading…' : 'Select zone'}</option>
                            {zones.map(z => <option key={z.id} value={z.id}>{z.name}</option>)}
                          </Sel>
                        </div>
                      )}
                      {areas.length > 0 && (
                        <div>
                          <FieldLabel>Area <span style={{ fontSize: 12, fontWeight: 400, color: 'var(--kb-ink-soft)' }}>(optional)</span></FieldLabel>
                          <Sel value={areaId ?? ''} onChange={e => handleAreaChange(Number(e.target.value))}>
                            <option value="">{loadingAreas ? 'Loading…' : 'Select area'}</option>
                            {areas.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                          </Sel>
                        </div>
                      )}
                      {cityId && (
                        <div style={{ background: 'var(--kb-surface-2)', borderRadius: 8, padding: '10px 14px', fontSize: 13 }}>
                          <span style={{ color: 'var(--kb-ink-soft)' }}>Delivery charge: </span>
                          {loadingCharge
                            ? <span style={{ color: 'var(--kb-ink-soft)' }}>calculating…</span>
                            : courierCharge !== null
                              ? <strong style={{ color: 'var(--kb-ink)' }}>{fmt(courierCharge)}</strong>
                              : <span style={{ color: 'var(--kb-ink-soft)' }}>Select zone to see charge</span>
                          }
                        </div>
                      )}
                      <input type="hidden" name="city" value={cities.find(c => c.id === cityId)?.name ?? ''} />
                    </div>
                  ) : (
                    <div>
                      <FieldLabel required>City / District</FieldLabel>
                      <Inp value={form.city} onChange={set('city')} placeholder="e.g. Dhaka" required />
                      <FieldError msg={errors.city} />
                    </div>
                  )}
                </div>

                <div style={{ marginTop: 14 }}>
                  <FieldLabel>Order note <span style={{ fontSize: 12, fontWeight: 400, color: 'var(--kb-ink-soft)' }}>(optional)</span></FieldLabel>
                  <textarea
                    value={form.notes} onChange={set('notes')} placeholder="Any special instructions…" rows={2}
                    style={{ ...inputStyle, height: 'auto', padding: '10px 12px', resize: 'none' }}
                    onFocus={e => e.currentTarget.style.borderColor = 'var(--kb-primary)'}
                    onBlur={e => e.currentTarget.style.borderColor = 'var(--kb-border)'}
                  />
                </div>
              </SectionCard>

              {/* Loyalty */}
              {loyaltyEnabled && loyaltyTaka > 0 && (
                <SectionCard title="Loyalty Points">
                  <label style={{ display: 'flex', alignItems: 'flex-start', gap: 12, cursor: 'pointer', padding: '12px 14px', background: 'var(--kb-surface-2)', borderRadius: 8 }}>
                    <input type="checkbox" checked={redeemPoints} onChange={e => setRedeemPoints(e.target.checked)}
                      style={{ marginTop: 2, accentColor: 'var(--kb-primary)', width: 16, height: 16, flexShrink: 0 }} />
                    <span style={{ fontSize: 14, color: 'var(--kb-ink)' }}>
                      Use <strong>{loyaltyBalance.toLocaleString()}</strong> points → save <strong style={{ color: 'var(--kb-success)' }}>{fmt(loyaltyTaka)}</strong>
                    </span>
                  </label>
                </SectionCard>
              )}

              {/* Payment */}
              <SectionCard title="Payment Method" sub="All payments are encrypted and secure.">
                <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {Object.entries(paymentMethods).map(([value, label]) => {
                    const active = form.payment_method === value;
                    return (
                      <label key={value} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 14px', borderRadius: 10, border: `${active ? 2 : 1}px solid ${active ? 'var(--kb-primary)' : 'var(--kb-border)'}`, background: active ? 'var(--kb-primary-50)' : '#fff', cursor: 'pointer', transition: 'all .15s' }}>
                        <input type="radio" name="payment_method" value={value}
                          checked={active} onChange={set('payment_method')}
                          style={{ accentColor: 'var(--kb-primary)', width: 16, height: 16, flexShrink: 0 }} />
                        <div>
                          <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--kb-ink)' }}>{label}</div>
                          {PAYMENT_META[value] && <div style={{ fontSize: 12, color: 'var(--kb-ink-soft)', marginTop: 1 }}>{PAYMENT_META[value]}</div>}
                        </div>
                      </label>
                    );
                  })}
                  <FieldError msg={errors.payment_method} />
                </div>

                {/* COD notice */}
                {form.payment_method === 'cod' && (
                  <div style={{ marginTop: 12, padding: '12px 14px', background: 'var(--kb-success-50)', border: '1px solid var(--kb-success-200)', borderRadius: 8, fontSize: 13, color: 'var(--kb-success)', fontWeight: 600 }}>
                    ✓ Pay when you receive your order
                  </div>
                )}
              </SectionCard>

              {/* Terms + place order (desktop only shows here) */}
              <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '20px 24px' }}>
                <label style={{ display: 'flex', alignItems: 'flex-start', gap: 10, cursor: 'pointer', marginBottom: 16 }}>
                  <input type="checkbox" checked={agreeTerms} onChange={e => setAgreeTerms(e.target.checked)}
                    style={{ marginTop: 2, accentColor: 'var(--kb-primary)', width: 16, height: 16, flexShrink: 0 }} />
                  <span style={{ fontSize: 13, color: 'var(--kb-ink-soft)' }}>
                    I agree to the{' '}
                    <Link href="/page/terms" style={{ color: 'var(--kb-primary)', fontWeight: 600, textDecoration: 'none' }} className="hover:underline">Terms</Link>
                    {' & '}
                    <Link href="/page/refund-policy" style={{ color: 'var(--kb-primary)', fontWeight: 600, textDecoration: 'none' }} className="hover:underline">Refund policy</Link>
                  </span>
                </label>

                <button type="submit" disabled={submitting || !agreeTerms || items.length === 0}
                  style={{ width: '100%', height: 50, borderRadius: 10, border: 'none', background: submitting ? '#6B7280' : 'var(--kb-primary)', color: '#fff', fontWeight: 700, fontSize: 16, cursor: submitting || !agreeTerms ? 'not-allowed' : 'pointer', opacity: !agreeTerms ? 0.6 : 1, transition: 'background .15s' }}>
                  {submitting ? 'Placing order…' : `Place order — ${fmt(total)}`}
                </button>

                <p style={{ fontSize: 12, color: 'var(--kb-ink-soft)', textAlign: 'center', marginTop: 10, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 4 }}>
                  <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  256-bit SSL encrypted · Your details are never shared
                </p>
              </div>
            </div>

            {/* ── Right: Summary ── */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 16, position: 'sticky', top: 100, alignSelf: 'start' }} className="lg:sticky">

              {/* Items */}
              <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '20px 20px 16px' }}>
                <h3 style={{ fontSize: 15, fontWeight: 700, color: 'var(--kb-ink)', margin: '0 0 14px' }}>
                  Order summary <span style={{ fontWeight: 400, color: 'var(--kb-ink-soft)', fontSize: 13 }}>({items.length} item{items.length !== 1 ? 's' : ''})</span>
                </h3>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                  {items.map(item => (
                    <div key={item.key} style={{ display: 'flex', gap: 10, alignItems: 'flex-start' }}>
                      {/* Image with qty badge */}
                      <div style={{ position: 'relative', flexShrink: 0 }}>
                        <div style={{ width: 48, height: 48, borderRadius: 8, overflow: 'hidden', background: 'var(--kb-surface-2)' }}>
                          {item.image_url
                            ? <img src={item.image_url} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                            : <div style={{ width: '100%', height: '100%', display: 'grid', placeItems: 'center', color: 'var(--kb-border)' }}>
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <rect x="3" y="3" width="18" height="18" rx="2" strokeWidth={1.5}/>
                                </svg>
                              </div>
                          }
                        </div>
                        <span style={{ position: 'absolute', top: -6, right: -6, width: 18, height: 18, borderRadius: '50%', background: 'var(--kb-primary)', color: '#fff', fontSize: 10, fontWeight: 700, display: 'grid', placeItems: 'center', border: '2px solid #fff' }}>
                          {item.quantity}
                        </span>
                      </div>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--kb-ink)', lineHeight: 1.35, overflow: 'hidden', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' }}>
                          {item.name}
                        </div>
                        {item.variant_label && <div style={{ fontSize: 11, color: 'var(--kb-ink-soft)', marginTop: 2 }}>{item.variant_label}</div>}
                      </div>
                      <span style={{ fontSize: 13, fontWeight: 700, color: 'var(--kb-ink)', flexShrink: 0 }}>{fmt(item.price * item.quantity)}</span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Coupon */}
              <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '16px 20px' }}>
                <h4 style={{ fontSize: 13, fontWeight: 700, color: 'var(--kb-ink)', margin: '0 0 10px' }}>Coupon code</h4>
                {!coupon ? (
                  <form onSubmit={handleCoupon} style={{ display: 'flex', gap: 8 }}>
                    <input type="text" placeholder="Enter code" value={couponInput}
                      onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(''); }}
                      style={{ ...inputStyle, flex: 1, height: 40, textTransform: 'uppercase', fontSize: 13 }}
                      onFocus={e => e.currentTarget.style.borderColor = 'var(--kb-primary)'}
                      onBlur={e => e.currentTarget.style.borderColor = 'var(--kb-border)'}
                    />
                    <button type="submit" disabled={couponLoading}
                      style={{ height: 40, padding: '0 14px', border: 'none', borderRadius: 8, background: 'var(--kb-primary)', color: '#fff', fontWeight: 700, fontSize: 13, cursor: 'pointer', flexShrink: 0 }}>
                      {couponLoading ? '…' : 'Apply'}
                    </button>
                  </form>
                ) : (
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', background: 'var(--kb-success-50)', border: '1px solid var(--kb-success-200)', borderRadius: 8, padding: '8px 12px' }}>
                    <div>
                      <span style={{ fontSize: 12, fontWeight: 700, color: 'var(--kb-success)' }}>{coupon.code}</span>
                      <span style={{ fontSize: 12, color: 'var(--kb-success)', marginLeft: 8 }}>{coupon.message}</span>
                    </div>
                    <button type="button" onClick={removeCoupon} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--kb-success-200)', display: 'grid', placeItems: 'center' }}>
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </button>
                  </div>
                )}
                {couponError && <p style={{ fontSize: 12, color: '#EF4444', marginTop: 6 }}>{couponError}</p>}
              </div>

              {/* Totals */}
              <div style={{ background: '#fff', border: '1px solid var(--kb-border)', borderRadius: 12, padding: '16px 20px' }}>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 14 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, color: 'var(--kb-ink-soft)' }}>
                    <span>Subtotal</span>
                    <span style={{ color: 'var(--kb-ink)', fontWeight: 600 }}>{fmt(subtotal)}</span>
                  </div>
                  {discount > 0 && (
                    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, color: 'var(--kb-success)', fontWeight: 600 }}>
                      <span>Discount ({coupon?.code})</span>
                      <span>−{fmt(discount)}</span>
                    </div>
                  )}
                  {loyaltyDiscount > 0 && (
                    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, color: 'var(--kb-success)', fontWeight: 600 }}>
                      <span>Loyalty</span>
                      <span>−{fmt(loyaltyDiscount)}</span>
                    </div>
                  )}
                  <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 14, color: 'var(--kb-ink-soft)' }}>
                    <span>Shipping</span>
                    <span style={{ fontWeight: 600, color: effectiveShipping === 0 ? 'var(--kb-success)' : 'var(--kb-ink)' }}>
                      {effectiveShipping === 0
                        ? 'Free'
                        : loadingCharge ? '…' : fmt(effectiveShipping)
                      }
                    </span>
                  </div>
                  {!allShippingIncluded && !courierLocationEnabled && freeAbove > 0 && effectiveShipping > 0 && (
                    <p style={{ fontSize: 12, color: 'var(--kb-ink-soft)', margin: 0 }}>
                      Add {fmt(freeAbove - subtotal)} more for free shipping
                    </p>
                  )}
                </div>

                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 17, fontWeight: 800, color: 'var(--kb-ink)', borderTop: '1px solid var(--kb-border)', paddingTop: 14 }}>
                  <span>Total</span>
                  <span>{fmt(total)}</span>
                </div>
              </div>

            </div>
          </div>
        </form>
      </div>
    </Layout>
  );
}
