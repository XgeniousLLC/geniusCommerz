import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AccountLayout from '../../components/AccountLayout';

interface OrderItem { id: number; product_id: number; product_name: string; variant_label: string | null; sku: string | null; unit_price: number; quantity: number; total: number; thumb: string | null; slug: string | null }
interface Activity { id: number; title: string; description: string | null; created_at: string }
interface Order {
  id: number; order_number: string; status: string; payment_status: string; payment_method: string | null; tracking_number: string | null;
  subtotal: number; shipping_cost: number; discount_amount: number; total: number; notes: string | null;
  created_at: string; shipping_address: { address: string; city: string } | null; coupon_code: string | null;
  items: OrderItem[]; activities: Activity[]; refunds: Array<{ id: number; status: string; amount: number }>;
}
interface Props { order: Order; reviewedProductIds: number[] }

const STATUS_COLOR: Record<string, string> = {
  pending: '#D97706', processing: '#2563EB', shipped: '#7C3AED',
  delivered: '#16A34A', cancelled: '#DC2626', refunded: '#6B7280',
};

export default function OrderShow({ order, reviewedProductIds }: Props) {
  const [reviewForm, setReviewForm] = useState<{ product_id: number; rating: number; title: string; body: string } | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const submitReview = (e: React.FormEvent) => {
    e.preventDefault();
    if (!reviewForm) return;
    setSubmitting(true);
    router.post('/reviews', reviewForm, {
      onFinish: () => { setSubmitting(false); setReviewForm(null); },
    });
  };

  const hasRefund = order.refunds.some(r => ['pending','approved'].includes(r.status));

  return (
    <AccountLayout title={`Order #${order.order_number}`} active="/account/orders">
      <Head title={`Order #${order.order_number}`} />

      <Link href="/account/orders" className="text-xs mb-4 inline-flex items-center gap-1" style={{ color: 'var(--kb-ink-soft)' }}>
        ← Back to Orders
      </Link>

      <div className="space-y-4">
        {/* Header */}
        <div className="kb-card p-5 flex flex-wrap items-center justify-between gap-4">
          <div>
            <div className="flex items-center gap-3 flex-wrap">
              <span className="font-mono font-bold text-lg" style={{ color: 'var(--kb-ink)' }}>#{order.order_number}</span>
              <span className="text-xs px-2.5 py-1 rounded-full font-semibold capitalize"
                style={{ background: STATUS_COLOR[order.status] + '20', color: STATUS_COLOR[order.status] }}>
                {order.status}
              </span>
            </div>
            <p className="text-xs mt-1" style={{ color: 'var(--kb-ink-soft)' }}>Placed on {order.created_at}</p>
          </div>
          {order.status === 'delivered' && !hasRefund && (
            <Link href="/account/refunds" className="kb-btn text-xs px-3 py-2" style={{ border: '1px solid var(--kb-border)', color: 'var(--kb-ink)' }}>
              Request Refund
            </Link>
          )}
        </div>

        {/* Items */}
        <div className="kb-card">
          <h3 className="px-5 py-3 text-sm font-semibold border-b" style={{ color: 'var(--kb-ink)', borderColor: 'var(--kb-border)' }}>Items</h3>
          <div className="divide-y" style={{ borderColor: 'var(--kb-border)' }}>
            {order.items.map(item => (
              <div key={item.id} className="px-5 py-4">
                <div className="flex gap-3">
                  <div className="w-14 h-14 rounded-lg overflow-hidden shrink-0" style={{ background: 'var(--kb-surface-2)' }}>
                    {item.thumb ? <img src={item.thumb} alt={item.product_name} className="w-full h-full object-cover" /> : <div className="w-full h-full" />}
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium" style={{ color: 'var(--kb-ink)' }}>{item.product_name}</p>
                    {item.variant_label && <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{item.variant_label}</p>}
                    <div className="flex items-center justify-between mt-1.5">
                      <span className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>৳{Number(item.unit_price).toLocaleString()} × {item.quantity}</span>
                      <span className="text-sm font-bold" style={{ color: 'var(--kb-ink)' }}>৳{Number(item.total).toLocaleString()}</span>
                    </div>
                  </div>
                </div>

                {/* Review prompt */}
                {order.status === 'delivered' && item.product_id && (
                  reviewedProductIds.includes(item.product_id) ? (
                    <p className="text-xs mt-2" style={{ color: 'var(--kb-success)' }}>✓ You reviewed this product</p>
                  ) : reviewForm?.product_id === item.product_id ? (
                    <form onSubmit={submitReview} className="mt-3 space-y-2 p-3 rounded-lg" style={{ background: 'var(--kb-surface-2)' }}>
                      <div className="flex gap-1">
                        {[1,2,3,4,5].map(s => (
                          <button key={s} type="button" onClick={() => setReviewForm(f => f ? { ...f, rating: s } : f)}
                            className="text-xl" style={{ color: s <= (reviewForm?.rating ?? 0) ? '#F59E0B' : 'var(--kb-border)' }}>★</button>
                        ))}
                      </div>
                      <input className="kb-input text-sm" placeholder="Review title (optional)" value={reviewForm.title}
                        onChange={e => setReviewForm(f => f ? { ...f, title: e.target.value } : f)} />
                      <textarea className="kb-input text-sm resize-none" rows={3} placeholder="Your review…" value={reviewForm.body}
                        onChange={e => setReviewForm(f => f ? { ...f, body: e.target.value } : f)} />
                      <div className="flex gap-2">
                        <button type="submit" disabled={submitting} className="kb-btn kb-btn-primary text-xs px-3 py-1.5">
                          {submitting ? 'Submitting…' : 'Submit Review'}
                        </button>
                        <button type="button" onClick={() => setReviewForm(null)} className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>Cancel</button>
                      </div>
                    </form>
                  ) : (
                    <button onClick={() => setReviewForm({ product_id: item.product_id!, rating: 5, title: '', body: '' })}
                      className="text-xs mt-2 underline" style={{ color: 'var(--kb-primary)' }}>
                      Write a review
                    </button>
                  )
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Summary + address */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div className="kb-card p-5">
            <h3 className="text-sm font-semibold mb-3" style={{ color: 'var(--kb-ink)' }}>Payment Summary</h3>
            <div className="space-y-1.5 text-sm">
              <div className="flex justify-between" style={{ color: 'var(--kb-ink-soft)' }}><span>Subtotal</span><span>৳{Number(order.subtotal).toLocaleString()}</span></div>
              {Number(order.discount_amount) > 0 && <div className="flex justify-between" style={{ color: 'var(--kb-success)' }}><span>Discount {order.coupon_code && `(${order.coupon_code})`}</span><span>−৳{Number(order.discount_amount).toLocaleString()}</span></div>}
              <div className="flex justify-between" style={{ color: 'var(--kb-ink-soft)' }}><span>Shipping</span><span>{Number(order.shipping_cost) === 0 ? 'Free' : `৳${Number(order.shipping_cost).toLocaleString()}`}</span></div>
              <div className="flex justify-between font-bold text-base pt-1.5 border-t" style={{ color: 'var(--kb-ink)', borderColor: 'var(--kb-border)' }}><span>Total</span><span>৳{Number(order.total).toLocaleString()}</span></div>
            </div>
            <p className="text-xs mt-3 capitalize" style={{ color: order.payment_status === 'paid' ? 'var(--kb-success)' : 'var(--kb-warn)' }}>
              Payment: {order.payment_status.replace('_', ' ')} · {order.payment_method?.replace('_', ' ') || 'N/A'}
            </p>
          </div>
          {order.shipping_address && (
            <div className="kb-card p-5">
              <h3 className="text-sm font-semibold mb-3" style={{ color: 'var(--kb-ink)' }}>Delivery Address</h3>
              <p className="text-sm" style={{ color: 'var(--kb-ink)' }}>{order.shipping_address.address}</p>
              <p className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>{order.shipping_address.city}</p>
              {order.tracking_number && <p className="text-xs mt-2 font-mono" style={{ color: 'var(--kb-primary)' }}>Tracking: {order.tracking_number}</p>}
            </div>
          )}
        </div>

        {/* Activity timeline */}
        {order.activities.length > 0 && (
          <div className="kb-card p-5">
            <h3 className="text-sm font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Order Timeline</h3>
            <div className="space-y-4">
              {order.activities.map((a, i) => (
                <div key={a.id} className="flex gap-3">
                  <div className="flex flex-col items-center">
                    <div className="w-2.5 h-2.5 rounded-full mt-0.5 shrink-0" style={{ background: i === 0 ? 'var(--kb-primary)' : 'var(--kb-border)' }}/>
                    {i < order.activities.length - 1 && <div className="w-px flex-1 mt-1" style={{ background: 'var(--kb-border)' }}/>}
                  </div>
                  <div className="pb-3">
                    <p className="text-sm font-medium" style={{ color: 'var(--kb-ink)' }}>{a.title}</p>
                    {a.description && <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{a.description}</p>}
                    <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{a.created_at}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </AccountLayout>
  );
}
