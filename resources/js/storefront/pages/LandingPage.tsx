import { Head, router } from '@inertiajs/react';
import { useState, useMemo, useRef } from 'react';

interface Variant {
  id: number;
  price: number;
  compare_at_price: number | null;
  sku: string | null;
  stock_qty: number | null;
  in_stock: boolean;
  label: string;
  image: string | null;
}

interface Product {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  price: number;
  compare_at_price: number | null;
  has_variants: boolean;
  stock_qty: number | null;
  in_stock: boolean;
  shipping_included: boolean;
  images: Array<{ url: string; thumb: string }>;
  variants: Variant[];
}

interface Prefill { name: string; email: string; phone: string; address: string; city: string }
interface Props { product: Product; paymentMethods: Record<string, string>; prefill?: Prefill | null }

function fmt(n: number) { return '৳' + n.toLocaleString(); }

function QtyControl({ value, onChange, max }: { value: number; onChange: (n: number) => void; max?: number | null }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', border: '1.5px solid #CBD5E1', borderRadius: 10, overflow: 'hidden' }}>
      <button type="button" onClick={() => onChange(Math.max(0, value - 1))}
        style={{ width: 36, height: 36, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18, color: '#64748B', background: 'none', border: 'none', cursor: 'pointer' }}>
        −
      </button>
      <span style={{ width: 32, textAlign: 'center', fontSize: 14, fontWeight: 700, color: '#0B1F4F' }}>{value}</span>
      <button type="button" onClick={() => onChange(max !== null && max !== undefined ? Math.min(max, value + 1) : value + 1)}
        style={{ width: 36, height: 36, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 18, color: '#64748B', background: 'none', border: 'none', cursor: 'pointer' }}>
        +
      </button>
    </div>
  );
}

function Inp({ value, onChange, placeholder, type = 'text', required }: {
  value: string; onChange: (v: string) => void; placeholder?: string; type?: string; required?: boolean;
}) {
  const [focused, setFocused] = useState(false);
  return (
    <input
      type={type} value={value} required={required} placeholder={placeholder}
      onChange={e => onChange(e.target.value)}
      onFocus={() => setFocused(true)} onBlur={() => setFocused(false)}
      style={{
        width: '100%', height: 44, padding: '0 12px', boxSizing: 'border-box',
        border: `1.5px solid ${focused ? '#0B1F4F' : '#CBD5E1'}`, borderRadius: 8,
        fontSize: 14, outline: 'none', color: '#0F172A', background: '#fff', transition: 'border-color .15s',
      }}
    />
  );
}

const TRUST = [
  { label: 'Fast Delivery', icon: (
    <svg width={14} height={14} fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
  )},
  { label: '100% Original', icon: (
    <svg width={14} height={14} fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
  )},
  { label: 'Easy Returns', icon: (
    <svg width={14} height={14} fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
  )},
  { label: 'Secure Payment', icon: (
    <svg width={14} height={14} fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
  )},
];

export default function LandingPage({ product, paymentMethods, prefill }: Props) {
  const [imgIdx, setImgIdx] = useState(0);
  const formRef = useRef<HTMLDivElement>(null);

  // Default first in-stock variant to qty 1
  const initQtys = useMemo(() => {
    if (!product.has_variants) return { 0: 1 };
    let first = true;
    return Object.fromEntries(
      product.variants.map(v => {
        const qty = (first && v.in_stock) ? 1 : 0;
        if (first && v.in_stock) first = false;
        return [v.id, qty];
      })
    );
  }, [product]);

  const [qtys, setQtys] = useState<Record<number, number>>(initQtys);
  const setQty = (key: number, val: number) => setQtys(q => ({ ...q, [key]: val }));

  const totalItems = Object.values(qtys).reduce((a, b) => a + b, 0);
  const thumbUrl   = product.images[0]?.url ?? null;
  const currentImg = product.images[imgIdx]?.url ?? thumbUrl;

  // Form state
  const firstMethod = Object.keys(paymentMethods)[0] ?? 'cod';
  const [form, setForm] = useState({
    customer_name:  prefill?.name    ?? '',
    customer_phone: prefill?.phone   ?? '',
    customer_email: prefill?.email   ?? '',
    address:        prefill?.address ?? '',
    city:           prefill?.city    ?? '',
    payment_method: firstMethod,
  });
  const [errors, setErrors]       = useState<Record<string, string>>({});
  const [submitting, setSubmitting] = useState(false);

  const set = (k: string) => (v: string) => setForm(f => ({ ...f, [k]: v }));

  // Build items array for submission
  const buildItems = () => {
    if (!product.has_variants) {
      const qty = qtys[0] ?? 0;
      if (qty < 1) return [];
      return [{
        product_id:    product.id,
        variant_id:    null,
        name:          product.name,
        sku:           null,
        variant_label: null,
        price:         product.price,
        quantity:      qty,
      }];
    }
    return product.variants
      .filter(v => (qtys[v.id] ?? 0) > 0)
      .map(v => ({
        product_id:    product.id,
        variant_id:    v.id,
        name:          product.name,
        sku:           v.sku ?? null,
        variant_label: v.label,
        price:         v.price,
        quantity:      qtys[v.id],
      }));
  };

  const totalPrice = (() => {
    if (!product.has_variants) return (qtys[0] ?? 0) * product.price;
    return product.variants.reduce((sum, v) => sum + (qtys[v.id] ?? 0) * v.price, 0);
  })();

  const scrollToForm = () => {
    formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const items = buildItems();
    if (items.length === 0) return;
    setSubmitting(true);
    setErrors({});
    router.post('/checkout', { ...form, items }, {
      onError: (errs) => { setErrors(errs); setSubmitting(false); },
      onFinish: () => setSubmitting(false),
    });
  };

  return (
    <>
      <Head title={product.name} />
      <div style={{ minHeight: '100dvh', background: '#F8FAFC', fontFamily: 'Inter, system-ui, sans-serif' }}>
        <div style={{ maxWidth: 480, margin: '0 auto', background: '#fff', minHeight: '100dvh', paddingBottom: 32 }}>

          {/* Image gallery */}
          <div style={{ position: 'relative', background: '#F1F5F9', aspectRatio: '4/3', overflow: 'hidden' }}>
            {currentImg
              ? <img src={currentImg} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
              : <div style={{ width: '100%', height: '100%' }} />
            }
            {product.images.length > 1 && (
              <div style={{ position: 'absolute', bottom: 10, left: 0, right: 0, display: 'flex', justifyContent: 'center', gap: 6 }}>
                {product.images.map((img, i) => (
                  <button key={i} type="button" onClick={() => setImgIdx(i)}
                    style={{
                      width: 38, height: 38, borderRadius: 8, overflow: 'hidden', border: 'none', padding: 0, cursor: 'pointer',
                      outline: i === imgIdx ? '2.5px solid #0B1F4F' : '2.5px solid rgba(255,255,255,.7)', outlineOffset: 1,
                    }}>
                    <img src={img.thumb} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div style={{ padding: '18px 16px 0' }}>

            {/* Name + price */}
            <h1 style={{ fontSize: 20, fontWeight: 800, color: '#0B1F4F', lineHeight: 1.25, margin: '0 0 10px' }}>
              {product.name}
            </h1>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 14 }}>
              <span style={{ fontSize: 24, fontWeight: 800, color: '#0B1F4F' }}>{fmt(product.price)}</span>
              {product.compare_at_price && product.compare_at_price > product.price && (
                <>
                  <span style={{ fontSize: 14, color: '#94A3B8', textDecoration: 'line-through' }}>{fmt(product.compare_at_price)}</span>
                  <span style={{ background: '#E2136E', color: '#fff', fontSize: 11, fontWeight: 700, borderRadius: 6, padding: '2px 8px' }}>
                    -{Math.round((1 - product.price / product.compare_at_price) * 100)}%
                  </span>
                </>
              )}
            </div>

            {product.short_description && (
              <p style={{ fontSize: 13, color: '#64748B', lineHeight: 1.65, margin: '0 0 16px' }}>
                {product.short_description}
              </p>
            )}

            {/* Trust badges — SVG only, no emoji */}
            <div style={{ display: 'flex', gap: 7, flexWrap: 'wrap', marginBottom: 22 }}>
              {TRUST.map(b => (
                <div key={b.label} style={{
                  display: 'inline-flex', alignItems: 'center', gap: 5,
                  background: '#F0F9FF', borderRadius: 8, padding: '5px 10px',
                  fontSize: 11, fontWeight: 600, color: '#0369A1',
                }}>
                  {b.icon} {b.label}
                </div>
              ))}
            </div>

            {/* ── Select & Order ── */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 14 }}>
              <div style={{ flex: 1, height: 1, background: '#E2E8F0' }} />
              <span style={{ fontSize: 11, fontWeight: 700, color: '#94A3B8', letterSpacing: '0.07em', textTransform: 'uppercase', whiteSpace: 'nowrap' }}>
                Select &amp; Order
              </span>
              <div style={{ flex: 1, height: 1, background: '#E2E8F0' }} />
            </div>

            {/* Variant cards */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 24 }}>
              {!product.has_variants ? (
                <div style={{
                  border: `${(qtys[0] ?? 0) > 0 ? '2px solid #0B1F4F' : '1.5px solid #E2E8F0'}`,
                  borderRadius: 12, padding: '14px', background: (qtys[0] ?? 0) > 0 ? '#F0F4FF' : '#fff',
                  display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10,
                }}>
                  <div>
                    <div style={{ fontSize: 14, fontWeight: 700, color: '#0B1F4F' }}>{product.name}</div>
                    <div style={{ fontSize: 14, fontWeight: 800, color: '#0B1F4F', marginTop: 2 }}>{fmt(product.price)}</div>
                    <div style={{ fontSize: 11, marginTop: 3, color: product.in_stock ? '#16A34A' : '#DC2626', fontWeight: 600 }}>
                      {product.in_stock ? 'In stock' : 'Out of stock'}
                    </div>
                  </div>
                  <QtyControl value={qtys[0] ?? 0} onChange={v => setQty(0, v)} max={product.stock_qty ?? null} />
                </div>
              ) : (
                product.variants.map(v => {
                  const qty = qtys[v.id] ?? 0;
                  const selected = qty > 0;
                  return (
                    <div key={v.id} style={{
                      border: selected ? '2px solid #0B1F4F' : '1.5px solid #E2E8F0',
                      borderRadius: 12, padding: '12px 14px', background: selected ? '#F0F4FF' : '#fff',
                      display: 'flex', alignItems: 'center', gap: 12,
                      opacity: v.in_stock ? 1 : 0.5, transition: 'all .15s',
                    }}>
                      {(v.image ?? thumbUrl) && (
                        <div style={{ width: 52, height: 52, borderRadius: 8, overflow: 'hidden', flexShrink: 0, background: '#F1F5F9' }}>
                          <img src={v.image ?? thumbUrl!} alt={v.label} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                        </div>
                      )}
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ fontSize: 13, fontWeight: 700, color: '#0B1F4F' }}>{v.label}</div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginTop: 2 }}>
                          <span style={{ fontSize: 14, fontWeight: 800, color: '#0B1F4F' }}>{fmt(v.price)}</span>
                          {v.compare_at_price && v.compare_at_price > v.price && (
                            <span style={{ fontSize: 11, color: '#94A3B8', textDecoration: 'line-through' }}>{fmt(v.compare_at_price)}</span>
                          )}
                        </div>
                        <div style={{ fontSize: 11, marginTop: 2, color: v.in_stock ? '#16A34A' : '#DC2626', fontWeight: 600 }}>
                          {v.in_stock ? 'In stock' : 'Out of stock'}
                        </div>
                      </div>
                      {v.in_stock
                        ? <QtyControl value={qty} onChange={val => setQty(v.id, val)} max={v.stock_qty ?? null} />
                        : <span style={{ fontSize: 11, color: '#DC2626', fontWeight: 600 }}>Unavailable</span>
                      }
                    </div>
                  );
                })
              )}
            </div>

            {/* Inline CTA to scroll to checkout form */}
            {totalItems > 0 && (
              <button type="button" onClick={scrollToForm}
                style={{
                  width: '100%', height: 52, borderRadius: 14, border: 'none', cursor: 'pointer',
                  background: 'linear-gradient(135deg, #0B1F4F, #1A3FA8)',
                  color: '#fff', fontSize: 16, fontWeight: 800,
                  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
                  boxShadow: '0 4px 16px rgba(11,31,79,.35)', marginBottom: 28,
                }}>
                <span>Continue to Order</span>
                <span style={{ background: 'rgba(255,255,255,.22)', borderRadius: 8, padding: '2px 10px', fontSize: 13 }}>
                  {totalItems} item{totalItems !== 1 ? 's' : ''}
                </span>
                <span>↓</span>
              </button>
            )}

            {/* ── Checkout form ── */}
            <div ref={formRef}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 18 }}>
                <div style={{ flex: 1, height: 1, background: '#E2E8F0' }} />
                <span style={{ fontSize: 11, fontWeight: 700, color: '#94A3B8', letterSpacing: '0.07em', textTransform: 'uppercase', whiteSpace: 'nowrap' }}>
                  Your Details
                </span>
                <div style={{ flex: 1, height: 1, background: '#E2E8F0' }} />
              </div>

              <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>

                {/* Name + Phone */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
                  <div>
                    <label style={{ fontSize: 12, fontWeight: 600, color: '#475569', display: 'block', marginBottom: 5 }}>
                      Full name <span style={{ color: '#EF4444' }}>*</span>
                    </label>
                    <Inp value={form.customer_name} onChange={set('customer_name')} placeholder="Your name" required />
                    {errors.customer_name && <p style={{ fontSize: 11, color: '#EF4444', marginTop: 3 }}>{errors.customer_name}</p>}
                  </div>
                  <div>
                    <label style={{ fontSize: 12, fontWeight: 600, color: '#475569', display: 'block', marginBottom: 5 }}>
                      Phone <span style={{ color: '#EF4444' }}>*</span>
                    </label>
                    <Inp value={form.customer_phone} onChange={set('customer_phone')} placeholder="01XXXXXXXXX" type="tel" required />
                    {errors.customer_phone && <p style={{ fontSize: 11, color: '#EF4444', marginTop: 3 }}>{errors.customer_phone}</p>}
                  </div>
                </div>

                {/* Address + City */}
                <div>
                  <label style={{ fontSize: 12, fontWeight: 600, color: '#475569', display: 'block', marginBottom: 5 }}>
                    Street address <span style={{ color: '#EF4444' }}>*</span>
                  </label>
                  <Inp value={form.address} onChange={set('address')} placeholder="House, road, block, area" required />
                  {errors.address && <p style={{ fontSize: 11, color: '#EF4444', marginTop: 3 }}>{errors.address}</p>}
                </div>
                <div>
                  <label style={{ fontSize: 12, fontWeight: 600, color: '#475569', display: 'block', marginBottom: 5 }}>
                    City / District <span style={{ color: '#EF4444' }}>*</span>
                  </label>
                  <Inp value={form.city} onChange={set('city')} placeholder="e.g. Dhaka" required />
                  {errors.city && <p style={{ fontSize: 11, color: '#EF4444', marginTop: 3 }}>{errors.city}</p>}
                </div>

                {/* Payment method */}
                <div>
                  <label style={{ fontSize: 12, fontWeight: 600, color: '#475569', display: 'block', marginBottom: 8 }}>
                    Payment method
                  </label>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {Object.entries(paymentMethods).map(([key, label]) => {
                      const active = form.payment_method === key;
                      return (
                        <label key={key} style={{
                          display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer',
                          border: active ? '2px solid #0B1F4F' : '1.5px solid #E2E8F0',
                          borderRadius: 10, padding: '10px 14px',
                          background: active ? '#F0F4FF' : '#fff', transition: 'all .12s',
                        }}>
                          <input type="radio" name="payment_method" value={key} checked={active}
                            onChange={() => setForm(f => ({ ...f, payment_method: key }))}
                            style={{ accentColor: '#0B1F4F', width: 16, height: 16 }} />
                          <span style={{ fontSize: 13, fontWeight: 600, color: '#0B1F4F' }}>{label}</span>
                          {key === 'cod' && (
                            <span style={{ fontSize: 11, color: '#16A34A', fontWeight: 600, marginLeft: 'auto' }}>Pay on delivery</span>
                          )}
                        </label>
                      );
                    })}
                  </div>
                </div>

                {/* Order summary mini */}
                {totalItems > 0 && (
                  <div style={{ background: '#F8FAFC', borderRadius: 10, padding: '12px 14px', fontSize: 13, color: '#475569' }}>
                    <div style={{ fontWeight: 700, color: '#0B1F4F', marginBottom: 8 }}>Order summary</div>
                    {!product.has_variants ? (
                      <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                        <span>{product.name} × {qtys[0]}</span>
                        <span style={{ fontWeight: 700 }}>{fmt(product.price * (qtys[0] ?? 0))}</span>
                      </div>
                    ) : (
                      product.variants.filter(v => (qtys[v.id] ?? 0) > 0).map(v => (
                        <div key={v.id} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                          <span>{v.label} × {qtys[v.id]}</span>
                          <span style={{ fontWeight: 700 }}>{fmt(v.price * qtys[v.id])}</span>
                        </div>
                      ))
                    )}
                    <div style={{ borderTop: '1px solid #E2E8F0', marginTop: 8, paddingTop: 8, display: 'flex', justifyContent: 'space-between', fontWeight: 800, color: '#0B1F4F', fontSize: 15 }}>
                      <span>Total</span>
                      <span>{fmt(totalPrice)}</span>
                    </div>
                  </div>
                )}

                {/* Submit */}
                <button type="submit" disabled={submitting || totalItems === 0}
                  style={{
                    width: '100%', height: 54, borderRadius: 14, border: 'none',
                    cursor: submitting || totalItems === 0 ? 'default' : 'pointer',
                    background: totalItems > 0 ? 'linear-gradient(135deg, #0B1F4F, #1A3FA8)' : '#CBD5E1',
                    color: '#fff', fontSize: 16, fontWeight: 800, letterSpacing: '0.01em',
                    boxShadow: totalItems > 0 ? '0 4px 16px rgba(11,31,79,.35)' : 'none',
                    transition: 'all .15s', marginTop: 4,
                  }}>
                  {submitting ? 'Placing order...' : totalItems === 0 ? 'Select items above' : `Place Order — ${fmt(totalPrice)}`}
                </button>

                {errors.items && <p style={{ fontSize: 12, color: '#EF4444', textAlign: 'center' }}>{errors.items}</p>}

                <p style={{ fontSize: 11, color: '#94A3B8', textAlign: 'center', margin: '4px 0 0' }}>
                  By placing an order you agree to our terms and conditions.
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
