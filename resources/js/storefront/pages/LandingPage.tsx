import { Head, router } from '@inertiajs/react';
import { useState, useMemo, useRef } from 'react';
import { type Country, findCountry, postalLabel } from '../countries';

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
interface ConfirmedOrder {
  order_number: string;
  customer_name: string;
  total: number;
  payment_method: string;
  items: Array<{ product_name: string; variant_label: string | null; quantity: number; unit_price: number; total: number }>;
}
interface Props { product: Product; paymentMethods: Record<string, string>; prefill?: Prefill | null; confirmedOrder?: ConfirmedOrder | null; countries: Country[]; storeCountry: string }

/* ── Design tokens ── */
const T = {
  ink:       '#1f1a15',
  inkSoft:   '#3a322a',
  paper:     '#fbf8f1',
  paper2:    '#f0e9da',
  ivory:     '#f4efe5',
  cognac:    '#95613a',
  cognacDeep:'#6f4527',
  gold:      '#b2904f',
  muted:     '#756a59',
  line:      'rgba(31,26,21,0.12)',
  lineSoft:  'rgba(31,26,21,0.07)',
  danger:    '#b91c1c',
  success:   '#3d6b45',
  display:   '"Cormorant Garamond", Georgia, serif',
  sans:      '"Jost", system-ui, sans-serif',
};

function fmt(n: number) { return '৳' + n.toLocaleString(); }

/* ── Divider ── */
function Divider({ label }: { label: string }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 12, margin: '24px 0 16px' }}>
      <div style={{ flex: 1, height: 1, background: T.line }} />
      <span style={{ fontFamily: T.sans, fontSize: 10, fontWeight: 600, color: T.muted, letterSpacing: '0.2em', textTransform: 'uppercase', whiteSpace: 'nowrap' }}>
        {label}
      </span>
      <div style={{ flex: 1, height: 1, background: T.line }} />
    </div>
  );
}

/* ── Qty control ── */
function QtyControl({ value, onChange, max }: { value: number; onChange: (n: number) => void; max?: number | null }) {
  const btn: React.CSSProperties = {
    width: 34, height: 34, display: 'flex', alignItems: 'center', justifyContent: 'center',
    background: 'none', border: 'none', cursor: 'pointer', color: T.muted,
    fontSize: 18, fontWeight: 300, transition: 'color .15s',
  };
  return (
    <div style={{ display: 'flex', alignItems: 'center', border: `1px solid ${T.line}`, borderRadius: 2, overflow: 'hidden', background: T.paper }}>
      <button type="button" onClick={() => onChange(Math.max(0, value - 1))} style={btn}>−</button>
      <span style={{ width: 28, textAlign: 'center', fontSize: 14, fontWeight: 600, color: T.ink, fontFamily: T.sans }}>{value}</span>
      <button type="button" onClick={() => onChange(max !== null && max !== undefined ? Math.min(max, value + 1) : value + 1)} style={btn}>+</button>
    </div>
  );
}

/* ── Form input ── */
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
        width: '100%', height: 44, padding: '0 14px', boxSizing: 'border-box',
        border: `1px solid ${focused ? T.cognac : T.line}`,
        borderRadius: 2, fontSize: 14, fontFamily: T.sans, outline: 'none',
        color: T.ink, background: T.paper, transition: 'border-color .15s',
      }}
    />
  );
}

const TRUST = [
  { label: 'Free Delivery',   icon: <svg width={13} height={13} fill="none" stroke="currentColor" strokeWidth={1.6} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg> },
  { label: '100% Authentic',  icon: <svg width={13} height={13} fill="none" stroke="currentColor" strokeWidth={1.6} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg> },
  { label: 'Easy Returns',    icon: <svg width={13} height={13} fill="none" stroke="currentColor" strokeWidth={1.6} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg> },
  { label: 'Secure Payment',  icon: <svg width={13} height={13} fill="none" stroke="currentColor" strokeWidth={1.6} viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> },
];

export default function LandingPage({ product, paymentMethods, prefill, confirmedOrder, countries = [], storeCountry = 'BD' }: Props) {
  const [imgIdx, setImgIdx]   = useState(0);
  const formRef               = useRef<HTMLDivElement>(null);

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

  const [qtys, setQtys]         = useState<Record<number, number>>(initQtys);
  const setQty = (key: number, val: number) => setQtys(q => ({ ...q, [key]: val }));

  const totalItems  = Object.values(qtys).reduce((a, b) => a + b, 0);
  const thumbUrl    = product.images[0]?.url ?? null;
  const currentImg  = product.images[imgIdx]?.url ?? thumbUrl;

  const firstMethod = Object.keys(paymentMethods)[0] ?? 'cod';
  const [form, setForm] = useState({
    customer_name:  prefill?.name    ?? '',
    customer_phone: prefill?.phone   ?? '',
    customer_email: prefill?.email   ?? '',
    country:        storeCountry,
    address:        prefill?.address ?? '',
    city:           prefill?.city    ?? '',
    postal_code:    '',
    payment_method: firstMethod,
  });

  const lpCountry     = findCountry(countries, form.country);
  const lpShowPostal  = lpCountry ? lpCountry.postal !== 'none' : true;
  const lpPostalReq   = lpCountry?.postal === 'required';
  const [errors, setErrors]         = useState<Record<string, string>>({});
  const [submitting, setSubmitting] = useState(false);

  const set = (k: string) => (v: string) => setForm(f => ({ ...f, [k]: v }));

  const buildItems = () => {
    if (!product.has_variants) {
      const qty = qtys[0] ?? 0;
      if (qty < 1) return [];
      return [{ product_id: product.id, variant_id: null, name: product.name, sku: null, variant_label: null, price: product.price, quantity: qty }];
    }
    return product.variants.filter(v => (qtys[v.id] ?? 0) > 0).map(v => ({
      product_id: product.id, variant_id: v.id, name: product.name,
      sku: v.sku ?? null, variant_label: v.label, price: v.price, quantity: qtys[v.id],
    }));
  };

  const totalPrice = !product.has_variants
    ? (qtys[0] ?? 0) * product.price
    : product.variants.reduce((sum, v) => sum + (qtys[v.id] ?? 0) * v.price, 0);

  const scrollToForm = () => formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const items = buildItems();
    if (items.length === 0) return;
    setSubmitting(true);
    setErrors({});
    router.post('/checkout', { ...form, items, lp_slug: product.slug }, {
      onError: (errs) => { setErrors(errs); setSubmitting(false); },
      onFinish: () => setSubmitting(false),
    });
  };

  const discount = product.compare_at_price && product.compare_at_price > product.price
    ? Math.round((1 - product.price / product.compare_at_price) * 100)
    : null;

  if (confirmedOrder) {
    return (
      <>
        <Head title={`Order ${confirmedOrder.order_number} Confirmed`} />
        <div style={{ minHeight: '100dvh', background: T.paper2, fontFamily: T.sans }}>
          <div style={{ maxWidth: 500, margin: '0 auto', background: T.paper, minHeight: '100dvh', boxShadow: '0 0 40px rgba(31,26,21,0.08)', padding: '48px 24px 56px', display: 'flex', flexDirection: 'column', alignItems: 'center' }}>

            {/* Check icon */}
            <div style={{ width: 64, height: 64, borderRadius: '50%', border: `1.5px solid ${T.cognac}`, display: 'flex', alignItems: 'center', justifyContent: 'center', color: T.cognac, marginBottom: 28 }}>
              <svg width={28} height={28} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.5} strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 13l4 4L19 7"/>
              </svg>
            </div>

            <p style={{ fontFamily: T.sans, fontSize: 10, fontWeight: 600, color: T.cognac, letterSpacing: '0.22em', textTransform: 'uppercase', marginBottom: 10 }}>
              Order Confirmed
            </p>
            <h1 style={{ fontFamily: T.display, fontSize: 26, fontWeight: 400, color: T.ink, margin: '0 0 6px', textAlign: 'center' }}>
              Thank you, {confirmedOrder.customer_name.split(' ')[0]}
            </h1>
            <p style={{ fontFamily: T.sans, fontSize: 13, color: T.muted, textAlign: 'center', margin: '0 0 32px' }}>
              Your order has been placed and will be processed shortly.
            </p>

            {/* Order number */}
            <div style={{ width: '100%', background: T.ivory, border: `1px solid ${T.line}`, borderRadius: 2, padding: '16px 20px', marginBottom: 24 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 14 }}>
                <span style={{ fontFamily: T.sans, fontSize: 10, fontWeight: 600, color: T.muted, letterSpacing: '0.16em', textTransform: 'uppercase' }}>Order</span>
                <span style={{ fontFamily: T.sans, fontSize: 13, fontWeight: 600, color: T.ink }}>#{confirmedOrder.order_number}</span>
              </div>

              {/* Items */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: 8, paddingBottom: 14, borderBottom: `1px solid ${T.line}`, marginBottom: 14 }}>
                {confirmedOrder.items.map((item, i) => (
                  <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                    <span style={{ fontFamily: T.sans, fontSize: 13, color: T.inkSoft }}>
                      {item.product_name}{item.variant_label ? ` — ${item.variant_label}` : ''} × {item.quantity}
                    </span>
                    <span style={{ fontFamily: T.sans, fontSize: 13, fontWeight: 600, color: T.ink }}>{fmt(item.total)}</span>
                  </div>
                ))}
              </div>

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                <span style={{ fontFamily: T.sans, fontSize: 10, fontWeight: 600, color: T.muted, letterSpacing: '0.12em', textTransform: 'uppercase' }}>Total</span>
                <span style={{ fontFamily: T.display, fontSize: 22, fontWeight: 600, color: T.cognac }}>{fmt(confirmedOrder.total)}</span>
              </div>

              <div style={{ marginTop: 12, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontFamily: T.sans, fontSize: 10, fontWeight: 600, color: T.muted, letterSpacing: '0.12em', textTransform: 'uppercase' }}>Payment</span>
                <span style={{ fontFamily: T.sans, fontSize: 12, color: T.ink, fontWeight: 500 }}>{paymentMethods[confirmedOrder.payment_method] ?? confirmedOrder.payment_method}</span>
              </div>
            </div>

            <a href={`/lp/${product.slug}`}
              style={{ fontFamily: T.sans, fontSize: 11, color: T.muted, letterSpacing: '0.1em', textDecoration: 'underline', textUnderlineOffset: 3 }}>
              Order again
            </a>
          </div>
        </div>
      </>
    );
  }

  return (
    <>
      <Head title={product.name} />

      <div style={{ minHeight: '100dvh', background: T.paper2, fontFamily: T.sans }}>
        <div style={{ maxWidth: 500, margin: '0 auto', background: T.paper, minHeight: '100dvh', boxShadow: '0 0 40px rgba(31,26,21,0.08)' }}>

          {/* ── Image gallery ── */}
          <div style={{ position: 'relative', background: T.ivory, aspectRatio: '4/3', overflow: 'hidden' }}>
            {currentImg
              ? <img src={currentImg} alt={product.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
              : (
                <div style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', flexDirection: 'column', gap: 12, color: 'rgba(31,26,21,.25)' }}>
                  <svg width={40} height={40} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.2} strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                  <span style={{ fontSize: 11, letterSpacing: '0.15em', textTransform: 'uppercase' }}>No image</span>
                </div>
              )
            }
            {product.images.length > 1 && (
              <div style={{ position: 'absolute', bottom: 12, left: 0, right: 0, display: 'flex', justifyContent: 'center', gap: 6 }}>
                {product.images.map((img, i) => (
                  <button key={i} type="button" onClick={() => setImgIdx(i)}
                    style={{
                      width: 40, height: 40, borderRadius: 2, overflow: 'hidden', border: 'none', padding: 0, cursor: 'pointer',
                      outline: i === imgIdx ? `2px solid ${T.cognac}` : '2px solid rgba(251,248,241,.6)', outlineOffset: 2,
                    }}>
                    <img src={img.thumb} alt="" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div style={{ padding: '24px 20px 40px' }}>

            {/* ── Name + price ── */}
            <div style={{ marginBottom: 20 }}>
              <h1 style={{ fontFamily: T.display, fontSize: 28, fontWeight: 400, color: T.ink, lineHeight: 1.15, margin: '0 0 14px', letterSpacing: '-0.01em' }}>
                {product.name}
              </h1>
              <div style={{ display: 'flex', alignItems: 'baseline', gap: 10 }}>
                <span style={{ fontFamily: T.display, fontSize: 26, fontWeight: 600, color: T.cognac }}>{fmt(product.price)}</span>
                {discount && (
                  <>
                    <span style={{ fontFamily: T.sans, fontSize: 13, color: T.muted, textDecoration: 'line-through' }}>{fmt(product.compare_at_price!)}</span>
                    <span style={{ fontFamily: T.sans, background: T.cognac, color: T.paper, fontSize: 10, fontWeight: 600, borderRadius: 2, padding: '2px 7px', letterSpacing: '0.06em' }}>
                      -{discount}%
                    </span>
                  </>
                )}
              </div>
              {product.shipping_included && (
                <p style={{ fontFamily: T.sans, fontSize: 11, color: T.success, marginTop: 6, letterSpacing: '0.06em', fontWeight: 500 }}>
                  Free shipping included
                </p>
              )}
            </div>

            {product.short_description && (
              <p style={{ fontFamily: T.sans, fontSize: 13.5, color: T.inkSoft, lineHeight: 1.7, margin: '0 0 22px' }}>
                {product.short_description}
              </p>
            )}

            {/* ── Trust badges ── */}
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, marginBottom: 6 }}>
              {TRUST.map(b => (
                <div key={b.label} style={{
                  display: 'inline-flex', alignItems: 'center', gap: 6,
                  border: `1px solid ${T.line}`, borderRadius: 2, padding: '5px 10px',
                  fontSize: 11, fontWeight: 500, color: T.inkSoft, fontFamily: T.sans,
                  background: T.ivory,
                }}>
                  <span style={{ color: T.cognac }}>{b.icon}</span>{b.label}
                </div>
              ))}
            </div>

            {/* ── Variants / qty ── */}
            <Divider label="Select & Order" />

            <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 24 }}>
              {!product.has_variants ? (
                <div style={{
                  border: `1px solid ${(qtys[0] ?? 0) > 0 ? T.cognac : T.line}`,
                  borderRadius: 2, padding: '14px 16px',
                  background: (qtys[0] ?? 0) > 0 ? T.ivory : T.paper,
                  display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 10,
                  transition: 'all .15s',
                }}>
                  <div>
                    <div style={{ fontFamily: T.sans, fontSize: 14, fontWeight: 500, color: T.ink }}>{product.name}</div>
                    <div style={{ fontFamily: T.display, fontSize: 18, fontWeight: 600, color: T.cognac, marginTop: 2 }}>{fmt(product.price)}</div>
                    <div style={{ fontFamily: T.sans, fontSize: 11, marginTop: 3, color: product.in_stock ? T.success : T.danger, fontWeight: 500 }}>
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
                      border: `1px solid ${selected ? T.cognac : T.line}`,
                      borderRadius: 2, padding: '12px 14px',
                      background: selected ? T.ivory : T.paper,
                      display: 'flex', alignItems: 'center', gap: 12,
                      opacity: v.in_stock ? 1 : 0.45, transition: 'all .15s',
                    }}>
                      {(v.image ?? thumbUrl) && (
                        <div style={{ width: 48, height: 48, borderRadius: 2, overflow: 'hidden', flexShrink: 0, background: T.ivory }}>
                          <img src={v.image ?? thumbUrl!} alt={v.label} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                        </div>
                      )}
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ fontFamily: T.sans, fontSize: 13, fontWeight: 500, color: T.ink }}>{v.label}</div>
                        <div style={{ display: 'flex', alignItems: 'baseline', gap: 6, marginTop: 2 }}>
                          <span style={{ fontFamily: T.display, fontSize: 16, fontWeight: 600, color: T.cognac }}>{fmt(v.price)}</span>
                          {v.compare_at_price && v.compare_at_price > v.price && (
                            <span style={{ fontFamily: T.sans, fontSize: 11, color: T.muted, textDecoration: 'line-through' }}>{fmt(v.compare_at_price)}</span>
                          )}
                        </div>
                        <div style={{ fontFamily: T.sans, fontSize: 11, marginTop: 2, color: v.in_stock ? T.success : T.danger, fontWeight: 500 }}>
                          {v.in_stock ? 'In stock' : 'Out of stock'}
                        </div>
                      </div>
                      {v.in_stock
                        ? <QtyControl value={qty} onChange={val => setQty(v.id, val)} max={v.stock_qty ?? null} />
                        : <span style={{ fontFamily: T.sans, fontSize: 11, color: T.danger, fontWeight: 500 }}>Unavailable</span>
                      }
                    </div>
                  );
                })
              )}
            </div>

            {/* ── Scroll CTA ── */}
            {totalItems > 0 && (
              <button type="button" onClick={scrollToForm}
                style={{
                  width: '100%', height: 50, borderRadius: 2, border: 'none', cursor: 'pointer',
                  background: T.ink, color: T.paper,
                  fontFamily: T.sans, fontSize: 12, fontWeight: 600, letterSpacing: '0.18em', textTransform: 'uppercase',
                  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
                  marginBottom: 28, transition: 'background .2s',
                }}>
                <span>Continue to Order</span>
                <span style={{ background: 'rgba(251,248,241,.15)', borderRadius: 1, padding: '2px 10px', fontSize: 11 }}>
                  {totalItems} item{totalItems !== 1 ? 's' : ''}
                </span>
              </button>
            )}

            {/* ── Checkout form ── */}
            <div ref={formRef}>
              <Divider label="Your Details" />

              <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                  <div>
                    <label style={{ fontFamily: T.sans, fontSize: 11, fontWeight: 600, color: T.muted, display: 'block', marginBottom: 6, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
                      Full name <span style={{ color: T.cognac }}>*</span>
                    </label>
                    <Inp value={form.customer_name} onChange={set('customer_name')} placeholder="Your name" required />
                    {errors.customer_name && <p style={{ fontFamily: T.sans, fontSize: 11, color: T.danger, marginTop: 3 }}>{errors.customer_name}</p>}
                  </div>
                  <div>
                    <label style={{ fontFamily: T.sans, fontSize: 11, fontWeight: 600, color: T.muted, display: 'block', marginBottom: 6, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
                      Phone <span style={{ color: T.cognac }}>*</span>
                    </label>
                    <Inp value={form.customer_phone} onChange={set('customer_phone')} placeholder="01XXXXXXXXX" type="tel" required />
                    {errors.customer_phone && <p style={{ fontFamily: T.sans, fontSize: 11, color: T.danger, marginTop: 3 }}>{errors.customer_phone}</p>}
                  </div>
                </div>

                <div>
                  <label style={{ fontFamily: T.sans, fontSize: 11, fontWeight: 600, color: T.muted, display: 'block', marginBottom: 6, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
                    Street address <span style={{ color: T.cognac }}>*</span>
                  </label>
                  <Inp value={form.address} onChange={set('address')} placeholder="House, road, block, area" required />
                  {errors.address && <p style={{ fontFamily: T.sans, fontSize: 11, color: T.danger, marginTop: 3 }}>{errors.address}</p>}
                </div>

                <div>
                  <label style={{ fontFamily: T.sans, fontSize: 11, fontWeight: 600, color: T.muted, display: 'block', marginBottom: 6, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
                    City / District <span style={{ color: T.cognac }}>*</span>
                  </label>
                  <Inp value={form.city} onChange={set('city')} placeholder="City" required />
                  {errors.city && <p style={{ fontFamily: T.sans, fontSize: 11, color: T.danger, marginTop: 3 }}>{errors.city}</p>}
                </div>

                {lpShowPostal && (
                  <div>
                    <label style={{ fontFamily: T.sans, fontSize: 11, fontWeight: 600, color: T.muted, display: 'block', marginBottom: 6, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
                      {postalLabel(form.country)} {lpPostalReq && <span style={{ color: T.cognac }}>*</span>}
                    </label>
                    <Inp value={form.postal_code} onChange={set('postal_code')} placeholder={lpPostalReq ? '' : 'Optional'} required={lpPostalReq} />
                    {errors.postal_code && <p style={{ fontFamily: T.sans, fontSize: 11, color: T.danger, marginTop: 3 }}>{errors.postal_code}</p>}
                  </div>
                )}

                <div>
                  <label style={{ fontFamily: T.sans, fontSize: 11, fontWeight: 600, color: T.muted, display: 'block', marginBottom: 6, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
                    Country <span style={{ color: T.cognac }}>*</span>
                  </label>
                  <select value={form.country}
                    onChange={e => setForm(f => ({ ...f, country: e.target.value, postal_code: '' }))}
                    style={{ width: '100%', fontFamily: T.sans, fontSize: 14, padding: '10px 12px', border: `1px solid ${T.line}`, background: T.paper, color: T.ink, borderRadius: 0 }}>
                    {countries.map(c => <option key={c.code} value={c.code}>{c.name}</option>)}
                  </select>
                  {errors.country && <p style={{ fontFamily: T.sans, fontSize: 11, color: T.danger, marginTop: 3 }}>{errors.country}</p>}
                </div>

                {/* Payment method */}
                <div>
                  <label style={{ fontFamily: T.sans, fontSize: 11, fontWeight: 600, color: T.muted, display: 'block', marginBottom: 10, letterSpacing: '0.06em', textTransform: 'uppercase' }}>
                    Payment method
                  </label>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                    {Object.entries(paymentMethods).map(([key, label]) => {
                      const active = form.payment_method === key;
                      return (
                        <label key={key} style={{
                          display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer',
                          border: `1px solid ${active ? T.cognac : T.line}`,
                          borderRadius: 2, padding: '11px 14px',
                          background: active ? T.ivory : T.paper, transition: 'all .12s',
                        }}>
                          <input type="radio" name="payment_method" value={key} checked={active}
                            onChange={() => setForm(f => ({ ...f, payment_method: key }))}
                            style={{ accentColor: T.cognac, width: 15, height: 15, flexShrink: 0 }} />
                          <span style={{ fontFamily: T.sans, fontSize: 13, fontWeight: 500, color: T.ink }}>{label}</span>
                          {key === 'cod' && (
                            <span style={{ fontFamily: T.sans, fontSize: 11, color: T.success, fontWeight: 500, marginLeft: 'auto' }}>Pay on delivery</span>
                          )}
                        </label>
                      );
                    })}
                  </div>
                </div>

                {/* Order summary */}
                {totalItems > 0 && (
                  <div style={{ background: T.ivory, border: `1px solid ${T.line}`, borderRadius: 2, padding: '14px 16px' }}>
                    <div style={{ fontFamily: T.display, fontSize: 16, fontWeight: 600, color: T.ink, marginBottom: 10, letterSpacing: '0.01em' }}>Order summary</div>
                    {!product.has_variants ? (
                      <div style={{ display: 'flex', justifyContent: 'space-between', fontFamily: T.sans, fontSize: 13, color: T.inkSoft }}>
                        <span>{product.name} × {qtys[0]}</span>
                        <span style={{ fontWeight: 600, color: T.ink }}>{fmt(product.price * (qtys[0] ?? 0))}</span>
                      </div>
                    ) : (
                      product.variants.filter(v => (qtys[v.id] ?? 0) > 0).map(v => (
                        <div key={v.id} style={{ display: 'flex', justifyContent: 'space-between', fontFamily: T.sans, fontSize: 13, color: T.inkSoft, marginBottom: 6 }}>
                          <span>{v.label} × {qtys[v.id]}</span>
                          <span style={{ fontWeight: 600, color: T.ink }}>{fmt(v.price * qtys[v.id])}</span>
                        </div>
                      ))
                    )}
                    <div style={{ borderTop: `1px solid ${T.line}`, marginTop: 10, paddingTop: 10, display: 'flex', justifyContent: 'space-between' }}>
                      <span style={{ fontFamily: T.sans, fontSize: 12, fontWeight: 600, color: T.muted, letterSpacing: '0.08em', textTransform: 'uppercase' }}>Total</span>
                      <span style={{ fontFamily: T.display, fontSize: 20, fontWeight: 600, color: T.cognac }}>{fmt(totalPrice)}</span>
                    </div>
                  </div>
                )}

                {/* Submit */}
                <button type="submit" disabled={submitting || totalItems === 0}
                  style={{
                    width: '100%', height: 52, borderRadius: 2, border: 'none',
                    cursor: submitting || totalItems === 0 ? 'default' : 'pointer',
                    background: totalItems > 0 ? T.ink : T.line,
                    color: totalItems > 0 ? T.paper : T.muted,
                    fontFamily: T.sans, fontSize: 12, fontWeight: 600, letterSpacing: '0.18em', textTransform: 'uppercase',
                    transition: 'all .15s', marginTop: 4,
                  }}>
                  {submitting ? 'Placing order…' : totalItems === 0 ? 'Select items above' : `Place Order — ${fmt(totalPrice)}`}
                </button>

                {errors.items && <p style={{ fontFamily: T.sans, fontSize: 12, color: T.danger, textAlign: 'center' }}>{errors.items}</p>}

                <p style={{ fontFamily: T.sans, fontSize: 11, color: T.muted, textAlign: 'center', margin: '4px 0 0', lineHeight: 1.6 }}>
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
