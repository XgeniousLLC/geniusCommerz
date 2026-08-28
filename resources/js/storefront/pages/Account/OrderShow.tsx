import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { usePrice } from '../../usePrice';

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
  pending: '#95613a', processing: '#6f4527', shipped: '#b2904f',
  delivered: '#16A34A', cancelled: '#b94040', refunded: '#756a59',
};

export default function OrderShow({ order, reviewedProductIds }: Props) {
  const [reviewForm, setReviewForm] = useState<{ product_id: number; rating: number; title: string; body: string } | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const fmt = usePrice();

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

      <Link href="/account/orders" style={{ fontSize: 11.5, display: 'inline-flex', alignItems: 'center', gap: 4, color: 'var(--av-muted)', textDecoration: 'none', fontFamily: 'var(--av-sans)', marginBottom: 16 }}>
        ← Back to orders
      </Link>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
        <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18, display: 'flex', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between', gap: 12 }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
              <span style={{ fontFamily: 'var(--av-sans)', fontWeight: 500, fontSize: 16, color: 'var(--av-ink)' }}>#{order.order_number}</span>
              <span style={{ fontSize: 10, letterSpacing: '0.12em', textTransform: 'uppercase', padding: '4px 8px', background: STATUS_COLOR[order.status] + '14', color: STATUS_COLOR[order.status], fontFamily: 'var(--av-sans)', fontWeight: 500 }}>
                {order.status}
              </span>
            </div>
            <p style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 4, fontFamily: 'var(--av-sans)' }}>Placed {order.created_at}</p>
          </div>
          {order.status === 'delivered' && !hasRefund && (
            <Link href="/account/refunds" className="av-btn av-btn-secondary av-btn-sm" style={{ textDecoration: 'none' }}>
              Request refund
            </Link>
          )}
        </div>

        <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)' }}>
          <h3 style={{ padding: '14px 18px', fontSize: 11, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: 0, borderBottom: '1px solid var(--av-line-soft)' }}>Items</h3>
          <div>
            {order.items.map(item => (
              <div key={item.id} style={{ padding: '16px 18px', borderTop: '1px solid var(--av-line-soft)' }}>
                <div style={{ display: 'flex', gap: 14 }}>
                  <div style={{ width: 56, height: 70, overflow: 'hidden', flexShrink: 0, background: 'var(--av-paper-2)' }}>
                    {item.thumb ? <img src={item.thumb} alt={item.product_name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} /> : null}
                  </div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <p style={{ fontFamily: 'var(--av-display)', fontSize: 14.5, fontWeight: 400, color: 'var(--av-ink)', margin: 0 }}>{item.product_name}</p>
                    {item.variant_label && <p style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 3, fontFamily: 'var(--av-sans)' }}>{item.variant_label}</p>}
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 8 }}>
                      <span style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{fmt(item.unit_price)} × {item.quantity}</span>
                      <span style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{fmt(item.total)}</span>
                    </div>
                  </div>
                </div>

                {order.status === 'delivered' && item.product_id && (
                  reviewedProductIds.includes(item.product_id) ? (
                    <p style={{ fontSize: 11.5, color: 'var(--av-cognac)', marginTop: 10, fontFamily: 'var(--av-sans)' }}>✓ You reviewed this</p>
                  ) : reviewForm?.product_id === item.product_id ? (
                    <form onSubmit={submitReview} style={{ marginTop: 12, display: 'flex', flexDirection: 'column', gap: 10, padding: 14, border: '1px solid var(--av-line-soft)', background: 'var(--av-paper-2)' }}>
                      <div style={{ display: 'flex', gap: 4 }}>
                        {[1,2,3,4,5].map(s => (
                          <button key={s} type="button" onClick={() => setReviewForm(f => f ? { ...f, rating: s } : f)}
                            style={{ fontSize: 18, color: s <= (reviewForm?.rating ?? 0) ? 'var(--av-gold)' : 'var(--av-line)', background: 'transparent', border: 'none', cursor: 'pointer' }}>★</button>
                        ))}
                      </div>
                      <input className="av-input" style={{ height: 38, fontSize: 13 }} placeholder="Title (optional)" value={reviewForm.title}
                        onChange={e => setReviewForm(f => f ? { ...f, title: e.target.value } : f)} />
                      <textarea className="av-input av-textarea" style={{ fontSize: 13 }} rows={3} placeholder="Your review…" value={reviewForm.body}
                        onChange={e => setReviewForm(f => f ? { ...f, body: e.target.value } : f)} />
                      <div style={{ display: 'flex', gap: 8 }}>
                        <button type="submit" disabled={submitting} className="av-btn av-btn-primary av-btn-sm">
                          {submitting ? 'Submitting…' : 'Submit'}
                        </button>
                        <button type="button" onClick={() => setReviewForm(null)} className="av-btn av-btn-secondary av-btn-sm">Cancel</button>
                      </div>
                    </form>
                  ) : (
                    <button onClick={() => setReviewForm({ product_id: item.product_id!, rating: 5, title: '', body: '' })}
                      style={{ fontSize: 11.5, marginTop: 10, color: 'var(--av-cognac)', background: 'transparent', border: 'none', cursor: 'pointer', textDecoration: 'underline', textDecorationColor: 'var(--av-line)', fontFamily: 'var(--av-sans)' }}>
                      Write a review
                    </button>
                  )
                )}
              </div>
            ))}
          </div>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }} className="av-order-bottom">
          <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
            <h3 style={{ fontSize: 11, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: '0 0 14px' }}>Payment</h3>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, fontSize: 13, fontFamily: 'var(--av-sans)' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', color: 'var(--av-muted)' }}><span>Subtotal</span><span style={{ color: 'var(--av-ink)', fontWeight: 500 }}>{fmt(order.subtotal)}</span></div>
              {Number(order.discount_amount) > 0 && <div style={{ display: 'flex', justifyContent: 'space-between', color: 'var(--av-cognac)', fontWeight: 500 }}><span>Discount {order.coupon_code && `(${order.coupon_code})`}</span><span>−{fmt(order.discount_amount)}</span></div>}
              <div style={{ display: 'flex', justifyContent: 'space-between', color: 'var(--av-muted)' }}><span>Shipping</span><span style={{ color: Number(order.shipping_cost) === 0 ? 'var(--av-cognac)' : 'var(--av-ink)', fontWeight: 500 }}>{Number(order.shipping_cost) === 0 ? 'Complimentary' : fmt(order.shipping_cost)}</span></div>
              <div style={{ display: 'flex', justifyContent: 'space-between', fontWeight: 500, fontSize: 14, paddingTop: 10, borderTop: '1px solid var(--av-line)', color: 'var(--av-ink)' }}><span>Total</span><span>{fmt(order.total)}</span></div>
            </div>
            <p style={{ fontSize: 10, letterSpacing: '0.08em', textTransform: 'uppercase', marginTop: 12, color: order.payment_status === 'paid' ? '#16A34A' : 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
              Payment: {order.payment_status.replace('_', ' ')} · {order.payment_method?.replace('_', ' ') || '—'}
            </p>
          </div>
          {order.shipping_address && (
            <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
              <h3 style={{ fontSize: 11, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: '0 0 14px' }}>Delivery address</h3>
              <p style={{ fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', margin: 0 }}>{order.shipping_address.address}</p>
              <p style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: '4px 0 0' }}>{order.shipping_address.city}</p>
              {order.tracking_number && <p style={{ fontSize: 11, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginTop: 10 }}>Tracking: {order.tracking_number}</p>}
            </div>
          )}
        </div>

        {order.activities.length > 0 && (
          <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
            <h3 style={{ fontSize: 11, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: '0 0 16px' }}>Timeline</h3>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
              {order.activities.map((a, i) => (
                <div key={a.id} style={{ display: 'flex', gap: 12 }}>
                  <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                    <div style={{ width: 8, height: 8, borderRadius: '50%', marginTop: 4, flexShrink: 0, background: i === 0 ? 'var(--av-ink)' : 'var(--av-line)' }}/>
                    {i < order.activities.length - 1 && <div style={{ width: 1, flex: 1, marginTop: 4, background: 'var(--av-line-soft)' }}/>}
                  </div>
                  <div style={{ paddingBottom: 16 }}>
                    <p style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', margin: 0 }}>{a.title}</p>
                    {a.description && <p style={{ fontSize: 12.5, color: 'var(--av-muted)', marginTop: 2, fontFamily: 'var(--av-sans)' }}>{a.description}</p>}
                    <p style={{ fontSize: 11, color: 'var(--av-muted)', marginTop: 4, fontFamily: 'var(--av-sans)' }}>{a.created_at}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      <style>{`@media(max-width:640px){ .av-order-bottom{ grid-template-columns: 1fr !important; } }`}</style>
    </AccountLayout>
  );
}
