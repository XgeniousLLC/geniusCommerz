import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import Layout from '../layouts/Layout';
import type { Order } from '../types';

interface Props {
  order: Order | null;
  searched: boolean;
  orderNumber: string;
  phone: string;
}

const STATUS_STYLES: Record<string, { bg: string; color: string }> = {
  pending:    { bg: '#FFF7ED', color: '#C2410C' },
  processing: { bg: '#EFF6FF', color: '#1D4ED8' },
  shipped:    { bg: '#F0FDF4', color: '#15803D' },
  delivered:  { bg: '#F0FDF4', color: '#15803D' },
  cancelled:  { bg: '#FEF2F2', color: '#DC2626' },
};

export default function Track({ order, searched, orderNumber, phone }: Props) {
  const [form, setForm] = useState({ order_number: orderNumber ?? '', phone: phone ?? '' });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    router.get('/track', form);
  }

  const st = order ? (STATUS_STYLES[order.status] ?? { bg: '#f1f5f9', color: '#475569' }) : null;

  return (
    <Layout>
      <Head title="Track Your Order" />
      <div className="max-w-2xl mx-auto px-4 py-10 lg:py-14">

        <nav className="text-xs mb-6 flex items-center gap-1.5" style={{ color: 'var(--kb-ink-soft)' }}>
          <a href="/" className="hover:text-slate-900">Home</a>
          <span>›</span>
          <span style={{ color: 'var(--kb-ink)' }}>Track Order</span>
        </nav>

        {/* Search card */}
        <div className="kb-card p-6 md:p-8 mb-6">
          <div className="flex items-center gap-3 mb-5">
            <div className="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style={{ background: 'var(--kb-primary-50)' }}>
              <svg className="w-5 h-5" style={{ color: 'var(--kb-primary)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <h1 className="text-xl font-bold" style={{ color: 'var(--kb-ink)' }}>Track Your Order</h1>
              <p className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>Enter your order number for live status updates.</p>
            </div>
          </div>

          <form onSubmit={submit} className="space-y-4">
            <div>
              <label className="block text-sm font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Order Number</label>
              <input type="text" value={form.order_number} onChange={(e) => setForm({ ...form, order_number: e.target.value })}
                placeholder="e.g. ORD-A1B2C3D4" required autoComplete="off" className="kb-input" />
            </div>
            <div>
              <label className="block text-sm font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>
                Phone Number <span className="font-normal" style={{ color: 'var(--kb-ink-soft)' }}>(optional)</span>
              </label>
              <input type="text" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })}
                placeholder="+880 1xxx-xxxxxx" autoComplete="off" className="kb-input" />
            </div>
            <button type="submit" className="kb-btn kb-btn-primary kb-btn-lg w-full" style={{ borderRadius: 12 }}>
              Track Order
            </button>
          </form>
        </div>

        {/* Not found */}
        {searched && !order && (
          <div className="kb-card p-6 text-center">
            <svg className="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p className="font-semibold" style={{ color: 'var(--kb-ink)' }}>Order not found</p>
            <p className="text-sm mt-1" style={{ color: 'var(--kb-ink-soft)' }}>Check your order number and try again.</p>
          </div>
        )}

        {/* Order details */}
        {order && st && (
          <div className="space-y-4">
            <div className="kb-card p-6">
              <div className="flex items-start justify-between gap-4 mb-4">
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: 'var(--kb-ink-soft)' }}>Order</p>
                  <p className="text-2xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>{order.order_number}</p>
                </div>
                <span className="kb-chip text-xs px-3 py-1" style={{ background: st.bg, color: st.color }}>
                  {order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                </span>
              </div>
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>Customer</p>
                  <p className="font-semibold" style={{ color: 'var(--kb-ink)' }}>{order.customer_name}</p>
                  <p style={{ color: 'var(--kb-ink-muted)' }}>{order.customer_phone}</p>
                </div>
                <div>
                  <p className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>Placed on</p>
                  <p className="font-semibold" style={{ color: 'var(--kb-ink)' }}>{order.created_at}</p>
                </div>
                {order.tracking_number && (
                  <div className="col-span-2">
                    <p className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>Tracking #</p>
                    <p className="font-mono font-semibold" style={{ color: 'var(--kb-primary)' }}>{order.tracking_number}</p>
                  </div>
                )}
              </div>
            </div>

            {order.items.length > 0 && (
              <div className="kb-card p-6">
                <h3 className="font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Items</h3>
                <div className="space-y-3">
                  {order.items.map((item) => (
                    <div key={item.id} className="flex items-center justify-between text-sm">
                      <span style={{ color: 'var(--kb-ink)' }}>{item.product_name} × {item.quantity}</span>
                      <span className="font-semibold" style={{ color: 'var(--kb-ink)' }}>৳{item.total_price}</span>
                    </div>
                  ))}
                </div>
                <div className="border-t mt-4 pt-4 space-y-1 text-sm" style={{ borderColor: 'var(--kb-border)' }}>
                  <div className="flex justify-between" style={{ color: 'var(--kb-ink-muted)' }}>
                    <span>Subtotal</span><span>৳{order.subtotal}</span>
                  </div>
                  <div className="flex justify-between" style={{ color: 'var(--kb-ink-muted)' }}>
                    <span>Shipping</span><span>৳{order.shipping_charge}</span>
                  </div>
                  {order.discount > 0 && (
                    <div className="flex justify-between" style={{ color: 'var(--kb-success)' }}>
                      <span>Discount</span><span>-৳{order.discount}</span>
                    </div>
                  )}
                  <div className="flex justify-between font-bold text-base pt-1" style={{ color: 'var(--kb-ink)' }}>
                    <span>Total</span><span>৳{order.total}</span>
                  </div>
                </div>
              </div>
            )}

            {order.activities.length > 0 && (
              <div className="kb-card p-6">
                <h3 className="font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Activity</h3>
                <div className="space-y-4">
                  {order.activities.map((act, i) => (
                    <div key={act.id} className="flex gap-3">
                      <div className="flex flex-col items-center">
                        <div className="w-2.5 h-2.5 rounded-full mt-1" style={{ background: i === 0 ? 'var(--kb-primary)' : 'var(--kb-border)' }} />
                        {i < order.activities.length - 1 && <div className="w-px flex-1 mt-1" style={{ background: 'var(--kb-border)' }} />}
                      </div>
                      <div className="pb-4">
                        <p className="text-sm font-medium" style={{ color: 'var(--kb-ink)' }}>{act.description}</p>
                        <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{act.created_at}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    </Layout>
  );
}
