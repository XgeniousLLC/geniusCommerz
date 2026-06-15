import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';
import type { Order } from '../types';

interface Props {
  order: Order | null;
  searched: boolean;
  orderNumber: string;
  phone: string;
}

const STATUS_LABEL: Record<string, string> = {
  pending: 'Pending', confirmed: 'Confirmed', processing: 'Processing',
  shipped: 'Shipped', delivered: 'Delivered', cancelled: 'Cancelled',
};

const STATUS_COLOR: Record<string, string> = {
  pending: '#b07020', confirmed: 'var(--av-cognac)', processing: 'var(--av-cognac)',
  shipped: '#2a6b3c', delivered: '#2a6b3c', cancelled: '#a33030',
};

const S = { fill: 'none', stroke: 'currentColor', strokeWidth: 1.4, strokeLinecap: 'round', strokeLinejoin: 'round' } as React.SVGProps<SVGSVGElement>;

function InpFlat({ value, onChange, placeholder, type = 'text', required }: {
  value: string; onChange: (v: string) => void;
  placeholder?: string; type?: string; required?: boolean;
}) {
  const [f, setF] = useState(false);
  return (
    <input type={type} value={value} onChange={e => onChange(e.target.value)} placeholder={placeholder} required={required}
      style={{ display: 'block', width: '100%', height: 46, padding: 0, border: 'none', borderBottom: `1px solid ${f ? 'var(--av-ink)' : 'var(--av-line)'}`, fontSize: 14, outline: 'none', color: 'var(--av-ink)', background: 'transparent', boxSizing: 'border-box', fontFamily: 'var(--av-sans)', transition: 'border-color .15s' }}
      onFocus={() => setF(true)} onBlur={() => setF(false)} />
  );
}

export default function Track({ order, searched, orderNumber, phone }: Props) {
  const fmt = usePrice();
  const [form, setForm] = useState({ order_number: orderNumber ?? '', phone: phone ?? '' });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    router.get('/track', form);
  }

  const statusLabel = order ? (STATUS_LABEL[order.status] ?? order.status) : '';
  const statusColor = order ? (STATUS_COLOR[order.status] ?? 'var(--av-muted)') : '';

  return (
    <Layout>
      <Head title="Track Order" />

      {/* Hero band */}
      <div style={{ background: 'var(--av-paper)', borderBottom: '1px solid var(--av-line)', padding: '20px var(--av-gutter)' }}>
        <div style={{ maxWidth: 680, margin: '0 auto' }}>
          <nav style={{ display: 'flex', gap: 8, fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 8 }}>
            <Link href="/" style={{ color: 'inherit', textDecoration: 'none' }}>Home</Link>
            <span>/</span>
            <span style={{ color: 'var(--av-ink)' }}>Track order</span>
          </nav>
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(22px,3vw,36px)', fontWeight: 400, color: 'var(--av-ink)', margin: 0, letterSpacing: '-0.012em' }}>Track your order</h1>
        </div>
      </div>

      <div style={{ maxWidth: 680, margin: '0 auto', padding: '48px var(--av-gutter) 80px' }}>

        {/* Search form */}
        <form onSubmit={submit} style={{ marginBottom: 40 }}>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
            <div>
              <label style={{ display: 'block', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 8, fontFamily: 'var(--av-sans)' }}>
                Order number <span style={{ color: 'var(--av-cognac)' }}>*</span>
              </label>
              <InpFlat value={form.order_number} onChange={v => setForm(f => ({ ...f, order_number: v }))} placeholder="ORD-XXXXXXXX" required />
            </div>
            <div>
              <label style={{ display: 'block', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 8, fontFamily: 'var(--av-sans)' }}>
                Phone <span style={{ color: 'var(--av-muted)', fontWeight: 400, textTransform: 'none', letterSpacing: 0, fontSize: 10 }}>optional</span>
              </label>
              <InpFlat value={form.phone} onChange={v => setForm(f => ({ ...f, phone: v }))} placeholder="01XXXXXXXXX" />
            </div>
            <button type="submit"
              style={{ width: '100%', height: 52, background: 'var(--av-ink)', color: 'var(--av-paper)', border: 'none', cursor: 'pointer', fontFamily: 'var(--av-sans)', fontSize: 12, fontWeight: 500, letterSpacing: '0.24em', textTransform: 'uppercase' }}>
              Track order
            </button>
          </div>
        </form>

        {/* Not found */}
        {searched && !order && (
          <div style={{ padding: '32px 24px', border: '1px solid var(--av-line)', textAlign: 'center', background: 'var(--av-paper)' }}>
            <svg viewBox="0 0 24 24" width="32" height="32" {...S} style={{ color: 'var(--av-line)', marginBottom: 12, display: 'block', margin: '0 auto 12px' }}>
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35M11 8v6M11 16h.01"/>
            </svg>
            <p style={{ fontSize: 16, fontFamily: 'var(--av-display)', fontWeight: 400, color: 'var(--av-ink)', marginBottom: 6 }}>Order not found</p>
            <p style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: 0 }}>Check the order number and try again.</p>
          </div>
        )}

        {/* Results */}
        {order && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>

            {/* Status header */}
            <div style={{ border: '1px solid var(--av-line)', background: 'var(--av-paper)', padding: '24px 28px' }}>
              <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12, marginBottom: 20 }}>
                <div>
                  <div style={{ fontSize: 10.5, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 6 }}>Order</div>
                  <div style={{ fontFamily: 'var(--av-display)', fontSize: 26, fontWeight: 400, color: 'var(--av-ink)', letterSpacing: '-0.01em', lineHeight: 1 }}>{order.order_number}</div>
                </div>
                <span style={{ fontSize: 11, fontWeight: 500, letterSpacing: '0.16em', textTransform: 'uppercase', fontFamily: 'var(--av-sans)', color: statusColor, border: `1px solid ${statusColor}`, padding: '5px 10px', flexShrink: 0 }}>
                  {statusLabel}
                </span>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px 24px' }}>
                <div>
                  <div style={{ fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 4 }}>Customer</div>
                  <div style={{ fontSize: 14, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>{order.customer_name}</div>
                  <div style={{ fontSize: 12.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{order.customer_phone}</div>
                </div>
                <div>
                  <div style={{ fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 4 }}>Placed</div>
                  <div style={{ fontSize: 14, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>{order.created_at}</div>
                </div>
                {order.tracking_number && (
                  <div style={{ gridColumn: '1/-1' }}>
                    <div style={{ fontSize: 10, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 4 }}>Tracking #</div>
                    <div style={{ fontSize: 13, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, letterSpacing: '0.06em' }}>{order.tracking_number}</div>
                  </div>
                )}
              </div>
            </div>

            {/* Items */}
            {order.items.length > 0 && (
              <div style={{ border: '1px solid var(--av-line)', background: 'var(--av-paper)' }}>
                <div style={{ padding: '16px 28px', borderBottom: '1px solid var(--av-line-soft)', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)' }}>
                  Items
                </div>
                <div style={{ padding: '0 28px' }}>
                  {order.items.map((item, i) => (
                    <div key={item.id} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '14px 0', borderBottom: i < order.items.length - 1 ? '1px solid var(--av-line-soft)' : 'none' }}>
                      <div>
                        <div style={{ fontSize: 14, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>
                          {item.product_name}
                        </div>
                        <div style={{ fontSize: 12, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 2 }}>
                          Qty: {item.quantity}
                        </div>
                      </div>
                      <span style={{ fontSize: 14, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{fmt(item.total_price)}</span>
                    </div>
                  ))}
                </div>
                <div style={{ padding: '16px 28px', borderTop: '1px solid var(--av-line-soft)', display: 'flex', flexDirection: 'column', gap: 8 }}>
                  {[
                    ['Subtotal', fmt(order.subtotal), 'var(--av-muted)'],
                    ['Shipping', fmt(order.shipping_charge), 'var(--av-muted)'],
                    ...(order.discount > 0 ? [['Discount', `−${fmt(order.discount)}`, 'var(--av-cognac)']] : []),
                  ].map(([label, value, color], i) => (
                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13, fontFamily: 'var(--av-sans)', color: 'var(--av-muted)' }}>
                      <span>{label}</span><span style={{ color }}>{value}</span>
                    </div>
                  ))}
                  <div style={{ display: 'flex', justifyContent: 'space-between', paddingTop: 10, marginTop: 2, borderTop: '1px solid var(--av-line-soft)' }}>
                    <span style={{ fontSize: 15, fontFamily: 'var(--av-sans)', fontWeight: 500, color: 'var(--av-ink)' }}>Total</span>
                    <span style={{ fontSize: 16, fontFamily: 'var(--av-sans)', fontWeight: 600, color: 'var(--av-ink)' }}>{fmt(order.total)}</span>
                  </div>
                </div>
              </div>
            )}

            {/* Activity timeline */}
            {order.activities.length > 0 && (
              <div style={{ border: '1px solid var(--av-line)', background: 'var(--av-paper)' }}>
                <div style={{ padding: '16px 28px', borderBottom: '1px solid var(--av-line-soft)', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)' }}>
                  Timeline
                </div>
                <div style={{ padding: '20px 28px', display: 'flex', flexDirection: 'column', gap: 0 }}>
                  {order.activities.map((act, i) => {
                    const isFirst = i === 0;
                    const isLast  = i === order.activities.length - 1;
                    return (
                      <div key={act.id} style={{ display: 'flex', gap: 16, position: 'relative' }}>
                        {/* Spine */}
                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', flexShrink: 0, width: 16 }}>
                          <div style={{ width: 10, height: 10, borderRadius: '50%', background: isFirst ? 'var(--av-ink)' : 'var(--av-line)', flexShrink: 0, marginTop: 3, border: isFirst ? 'none' : '1px solid var(--av-line)' }} />
                          {!isLast && <div style={{ width: 1, flex: 1, background: 'var(--av-line-soft)', marginTop: 4 }} />}
                        </div>
                        {/* Content */}
                        <div style={{ paddingBottom: isLast ? 0 : 20, flex: 1 }}>
                          <p style={{ margin: 0, fontSize: 14, fontFamily: 'var(--av-sans)', color: isFirst ? 'var(--av-ink)' : 'var(--av-muted)', fontWeight: isFirst ? 500 : 400 }}>{act.description}</p>
                          <p style={{ margin: '3px 0 0', fontSize: 11.5, fontFamily: 'var(--av-sans)', color: 'var(--av-muted)' }}>{act.created_at}</p>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            )}

            {/* Support link */}
            <p style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', textAlign: 'center' }}>
              Need help?{' '}
              <Link href="/page/contact" style={{ color: 'var(--av-ink)', textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>Contact us</Link>
            </p>
          </div>
        )}
      </div>
    </Layout>
  );
}
