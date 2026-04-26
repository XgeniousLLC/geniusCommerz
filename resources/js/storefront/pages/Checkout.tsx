import { Head, router } from '@inertiajs/react';
import { useState, useEffect, useCallback } from 'react';
import Layout from '../layouts/Layout';
import { useCartStore, useCartDerived } from '../store/cartStore';

interface LocationOption { id: number; name: string; }

interface Props {
  shippingCost: number;
  freeAbove: number;
  paymentMethods: Record<string, string>;
  loyaltyEnabled?: boolean;
  loyaltyBalance?: number;
  loyaltyTaka?: number;
  courierLocationEnabled?: boolean;
}

export default function Checkout({
  shippingCost,
  freeAbove,
  paymentMethods,
  loyaltyEnabled = false,
  loyaltyBalance = 0,
  loyaltyTaka    = 0,
  courierLocationEnabled = false,
}: Props) {
  const items        = useCartStore(s => s.items);
  const coupon       = useCartStore(s => s.coupon);
  const applyCoupon  = useCartStore(s => s.applyCoupon);
  const removeCoupon = useCartStore(s => s.removeCoupon);
  const clearCart    = useCartStore(s => s.clearCart);
  const { subtotal, discount } = useCartDerived();

  const allShippingIncluded = items.length > 0 && items.every(i => i.shipping_included);

  // Courier location state
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

  // Load cities on mount if location mode is enabled
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
    setCityId(id);
    setZoneId(null);
    setAreaId(null);
    setZones([]);
    setAreas([]);
    setCourierCharge(null);
    if (!id) return;
    setLoadingZones(true);
    fetch(`/api/courier/zones/${id}`)
      .then(r => r.json())
      .then(d => setZones(d.zones ?? []))
      .catch(() => setZones([]))
      .finally(() => setLoadingZones(false));
  }, []);

  const handleZoneChange = useCallback((id: number) => {
    setZoneId(id);
    setAreaId(null);
    setAreas([]);
    setCourierCharge(null);
    if (!id) return;

    // Load areas if available
    setLoadingAreas(true);
    fetch(`/api/courier/areas/${id}`)
      .then(r => r.json())
      .then(d => setAreas(d.areas ?? []))
      .catch(() => setAreas([]))
      .finally(() => setLoadingAreas(false));

    // Fetch charge as soon as zone is selected
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

  // Compute effective shipping
  const effectiveShipping = (() => {
    if (allShippingIncluded) return 0;
    if (courierLocationEnabled && courierCharge !== null) return courierCharge;
    if (freeAbove > 0 && subtotal >= freeAbove) return 0;
    return shippingCost;
  })();

  // Loyalty
  const [redeemPoints, setRedeemPoints] = useState(false);
  const loyaltyDiscount = redeemPoints ? loyaltyTaka : 0;

  const total = Math.max(0, subtotal - discount - loyaltyDiscount + effectiveShipping);

  const [form, setForm] = useState({
    customer_name:  '',
    customer_phone: '',
    customer_email: '',
    address:        '',
    city:           '',
    notes:          '',
    payment_method: Object.keys(paymentMethods)[0] ?? 'cod',
  });
  const [errors, setErrors]           = useState<Record<string, string>>({});
  const [couponInput, setCouponInput] = useState('');
  const [couponError, setCouponError] = useState('');
  const [couponLoading, setCouponLoading] = useState(false);
  const [submitting, setSubmitting]   = useState(false);

  const [hydrated, setHydrated] = useState(false);
  useEffect(() => { setHydrated(true); }, []);
  useEffect(() => {
    if (hydrated && items.length === 0) {
      router.visit('/shop');
    }
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
        product_id:    i.product_id,
        variant_id:    i.variant_id,
        name:          i.name,
        sku:           null,
        variant_label: i.variant_label,
        price:         i.price,
        quantity:      i.quantity,
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
      <div className="max-w-5xl mx-auto px-4 py-8 lg:py-12">

        <nav className="text-xs mb-6 flex items-center gap-1.5" style={{ color: 'var(--kb-ink-soft)' }}>
          <a href="/" className="hover:underline">Home</a>
          <span>›</span>
          <span style={{ color: 'var(--kb-ink)' }}>Checkout</span>
        </nav>

        <h1 className="text-2xl font-bold mb-8" style={{ color: 'var(--kb-ink)' }}>Checkout</h1>

        <form onSubmit={handleSubmit}>
          <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {/* Left — customer details */}
            <div className="lg:col-span-3 space-y-5">

              {/* Contact */}
              <div className="kb-card p-6">
                <h2 className="text-base font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Contact Details</h2>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>Full Name <span className="text-red-500">*</span></label>
                    <input className="kb-input" value={form.customer_name} onChange={set('customer_name')} placeholder="Your full name" required />
                    {errors.customer_name && <p className="text-red-500 text-xs mt-1">{errors.customer_name}</p>}
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>Phone <span className="text-red-500">*</span></label>
                    <input className="kb-input" value={form.customer_phone} onChange={set('customer_phone')} placeholder="01XXXXXXXXX" required />
                    {errors.customer_phone && <p className="text-red-500 text-xs mt-1">{errors.customer_phone}</p>}
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>
                      Email <span className="text-xs font-normal" style={{ color: 'var(--kb-ink-soft)' }}>(optional)</span>
                    </label>
                    <input className="kb-input" type="email" value={form.customer_email} onChange={set('customer_email')} placeholder="you@email.com" />
                    {errors.customer_email && <p className="text-red-500 text-xs mt-1">{errors.customer_email}</p>}
                  </div>
                </div>
              </div>

              {/* Delivery */}
              <div className="kb-card p-6">
                <h2 className="text-base font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Delivery Address</h2>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>Address <span className="text-red-500">*</span></label>
                    <textarea className="kb-input resize-none" rows={2} value={form.address} onChange={set('address')} placeholder="Street address, house no." required />
                    {errors.address && <p className="text-red-500 text-xs mt-1">{errors.address}</p>}
                  </div>

                  {courierLocationEnabled ? (
                    <>
                      <div>
                        <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>City / District <span className="text-red-500">*</span></label>
                        <select className="kb-input" required
                          value={cityId ?? ''}
                          onChange={e => handleCityChange(Number(e.target.value))}>
                          <option value="">{loadingCities ? 'Loading cities…' : 'Select city'}</option>
                          {cities.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                        {errors.city && <p className="text-red-500 text-xs mt-1">{errors.city}</p>}
                      </div>

                      {zones.length > 0 && (
                        <div>
                          <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>Zone / Thana <span className="text-red-500">*</span></label>
                          <select className="kb-input" required
                            value={zoneId ?? ''}
                            onChange={e => handleZoneChange(Number(e.target.value))}>
                            <option value="">{loadingZones ? 'Loading zones…' : 'Select zone'}</option>
                            {zones.map(z => <option key={z.id} value={z.id}>{z.name}</option>)}
                          </select>
                        </div>
                      )}

                      {areas.length > 0 && (
                        <div>
                          <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>Area</label>
                          <select className="kb-input"
                            value={areaId ?? ''}
                            onChange={e => handleAreaChange(Number(e.target.value))}>
                            <option value="">{loadingAreas ? 'Loading areas…' : 'Select area (optional)'}</option>
                            {areas.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                          </select>
                        </div>
                      )}

                      {courierLocationEnabled && cityId && (
                        <div className="text-sm rounded-lg px-3 py-2" style={{ background: 'var(--kb-surface-2)' }}>
                          <span style={{ color: 'var(--kb-ink-soft)' }}>Delivery charge: </span>
                          {loadingCharge
                            ? <span style={{ color: 'var(--kb-ink-soft)' }}>calculating…</span>
                            : courierCharge !== null
                              ? <span className="font-bold" style={{ color: 'var(--kb-ink)' }}>৳{courierCharge}</span>
                              : <span style={{ color: 'var(--kb-ink-soft)' }}>Select zone to see charge</span>
                          }
                        </div>
                      )}

                      {/* Hidden text city for server */}
                      <input type="hidden" name="city" value={cities.find(c => c.id === cityId)?.name ?? ''} />
                    </>
                  ) : (
                    <div>
                      <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>City / District <span className="text-red-500">*</span></label>
                      <input className="kb-input" value={form.city} onChange={set('city')} placeholder="e.g. Dhaka" required />
                      {errors.city && <p className="text-red-500 text-xs mt-1">{errors.city}</p>}
                    </div>
                  )}

                  <div>
                    <label className="block text-sm font-medium mb-1.5" style={{ color: 'var(--kb-ink)' }}>Order Note <span className="text-xs font-normal" style={{ color: 'var(--kb-ink-soft)' }}>(optional)</span></label>
                    <textarea className="kb-input resize-none" rows={2} value={form.notes} onChange={set('notes')} placeholder="Any special instructions…" />
                  </div>
                </div>
              </div>

              {/* Loyalty */}
              {loyaltyEnabled && loyaltyTaka > 0 && (
                <div className="kb-card p-6">
                  <h2 className="text-base font-semibold mb-3" style={{ color: 'var(--kb-ink)' }}>Loyalty Points</h2>
                  <label className="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" className="mt-0.5 rounded"
                      checked={redeemPoints}
                      onChange={e => setRedeemPoints(e.target.checked)} />
                    <span className="text-sm" style={{ color: 'var(--kb-ink)' }}>
                      Use {loyaltyBalance.toLocaleString()} points → save <strong>৳{loyaltyTaka.toLocaleString()}</strong>
                    </span>
                  </label>
                </div>
              )}

              {/* Payment */}
              <div className="kb-card p-6">
                <h2 className="text-base font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Payment Method</h2>
                <div className="space-y-2">
                  {Object.entries(paymentMethods).map(([value, label]) => (
                    <label key={value} className="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                      style={{
                        borderColor: form.payment_method === value ? 'var(--kb-primary)' : 'var(--kb-border)',
                        background:  form.payment_method === value ? 'var(--kb-primary-50)' : 'transparent',
                      }}>
                      <input type="radio" name="payment_method" value={value}
                        checked={form.payment_method === value}
                        onChange={set('payment_method')}
                        className="text-blue-600" />
                      <span className="text-sm font-medium" style={{ color: 'var(--kb-ink)' }}>{label}</span>
                    </label>
                  ))}
                  {errors.payment_method && <p className="text-red-500 text-xs mt-1">{errors.payment_method}</p>}
                </div>
              </div>

            </div>

            {/* Right — order summary */}
            <div className="lg:col-span-2 space-y-4">

              {/* Items */}
              <div className="kb-card p-5">
                <h2 className="text-base font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Your Order ({items.length} item{items.length !== 1 ? 's' : ''})</h2>
                <div className="space-y-3">
                  {items.map(item => (
                    <div key={item.key} className="flex gap-3">
                      <div className="w-12 h-12 rounded-lg overflow-hidden shrink-0" style={{ background: 'var(--kb-surface-2)' }}>
                        {item.image_url
                          ? <img src={item.image_url} alt={item.name} className="w-full h-full object-cover" />
                          : <div className="w-full h-full flex items-center justify-center">
                              <svg className="w-5 h-5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                            </div>
                        }
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium leading-snug" style={{ color: 'var(--kb-ink)' }}>{item.name}</p>
                        {item.variant_label && <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{item.variant_label}</p>}
                        <div className="flex items-center justify-between mt-1">
                          <span className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>× {item.quantity}</span>
                          <span className="text-sm font-bold" style={{ color: 'var(--kb-ink)' }}>৳{(item.price * item.quantity).toLocaleString()}</span>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Coupon */}
              <div className="kb-card p-5">
                <h3 className="text-sm font-semibold mb-3" style={{ color: 'var(--kb-ink)' }}>Coupon Code</h3>
                {!coupon ? (
                  <form onSubmit={handleCoupon} className="flex gap-2">
                    <input
                      className="kb-input flex-1 text-sm uppercase placeholder:normal-case"
                      placeholder="Enter code"
                      value={couponInput}
                      onChange={e => { setCouponInput(e.target.value.toUpperCase()); setCouponError(''); }}
                    />
                    <button type="submit" disabled={couponLoading}
                      className="kb-btn kb-btn-primary text-sm shrink-0 px-4">
                      {couponLoading ? '…' : 'Apply'}
                    </button>
                  </form>
                ) : (
                  <div className="flex items-center justify-between rounded-xl px-3 py-2.5"
                    style={{ background: 'var(--kb-success-50)', border: '1px solid var(--kb-success-200)' }}>
                    <div>
                      <span className="text-sm font-bold" style={{ color: 'var(--kb-success)' }}>{coupon.code}</span>
                      <span className="text-xs ml-2" style={{ color: 'var(--kb-success)' }}>{coupon.message}</span>
                    </div>
                    <button type="button" onClick={removeCoupon} className="ml-2 opacity-60 hover:opacity-100">
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                  </div>
                )}
                {couponError && <p className="text-red-500 text-xs mt-1.5">{couponError}</p>}
              </div>

              {/* Totals + place order */}
              <div className="kb-card p-5">
                <div className="space-y-2 text-sm mb-4">
                  <div className="flex justify-between" style={{ color: 'var(--kb-ink-soft)' }}>
                    <span>Subtotal</span>
                    <span>৳{subtotal.toLocaleString()}</span>
                  </div>
                  {discount > 0 && (
                    <div className="flex justify-between" style={{ color: 'var(--kb-success)' }}>
                      <span>Discount</span>
                      <span>−৳{discount.toLocaleString()}</span>
                    </div>
                  )}
                  {loyaltyDiscount > 0 && (
                    <div className="flex justify-between" style={{ color: 'var(--kb-success)' }}>
                      <span>Loyalty</span>
                      <span>−৳{loyaltyDiscount.toLocaleString()}</span>
                    </div>
                  )}
                  <div className="flex justify-between" style={{ color: 'var(--kb-ink-soft)' }}>
                    <span>Shipping</span>
                    <span>
                      {effectiveShipping === 0
                        ? <span style={{ color: 'var(--kb-success)' }}>Free</span>
                        : loadingCharge
                          ? <span className="opacity-50">…</span>
                          : `৳${effectiveShipping}`}
                    </span>
                  </div>
                  {allShippingIncluded && (
                    <p className="text-xs" style={{ color: 'var(--kb-success)' }}>
                      Shipping included in product price
                    </p>
                  )}
                  {!allShippingIncluded && !courierLocationEnabled && freeAbove > 0 && effectiveShipping > 0 && (
                    <p className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>
                      Add ৳{(freeAbove - subtotal).toLocaleString()} more for free shipping
                    </p>
                  )}
                  <div className="flex justify-between font-bold text-base pt-2 border-t" style={{ color: 'var(--kb-ink)', borderColor: 'var(--kb-border)' }}>
                    <span>Total</span>
                    <span>৳{total.toLocaleString()}</span>
                  </div>
                </div>

                <button type="submit" disabled={submitting || items.length === 0}
                  className="kb-btn kb-btn-primary kb-btn-lg w-full justify-center font-semibold"
                  style={{ borderRadius: 12 }}>
                  {submitting ? 'Placing Order…' : 'Place Order'}
                </button>
              </div>

            </div>
          </div>
        </form>
      </div>
    </Layout>
  );
}
