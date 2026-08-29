import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';

interface OrderItem {
  product_name: string;
  variant_label: string | null;
  quantity: number;
  unit_price: number;
  total: number;
}

interface Props {
  order: {
    order_number: string;
    customer_name: string;
    total: number;
    payment_method: string;
    status: string;
    items: OrderItem[];
  };
}

const CONFETTI_COLORS = ['#1f1a15', '#95613a', '#b2904f', '#756a59', '#efe9dc', '#c04e2a'];

export default function OrderConfirmed({ order }: Props) {
  const fmt = usePrice();

  const confetti = useMemo(() =>
    Array.from({ length: 48 }, (_, i) => ({
      left: `${Math.random() * 100}%`,
      background: CONFETTI_COLORS[i % CONFETTI_COLORS.length],
      animationDelay: `${(Math.random() * 0.6).toFixed(2)}s`,
      animationDuration: `${(2.5 + Math.random() * 2).toFixed(2)}s`,
    })), []);

  const paymentLabel = order.payment_method
    .replace(/_/g, ' ')
    .replace(/\b\w/g, c => c.toUpperCase());

  const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };

  return (
    <Layout>
      <Head title="Order Confirmed" />

      <style>{`
        @keyframes confetti-fall {
          0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
          100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }
      `}</style>

      <div style={{ position: 'fixed', inset: 0, pointerEvents: 'none', overflow: 'hidden', zIndex: 1 }}>
        {confetti.map((c, i) => (
          <span key={i} style={{
            position: 'absolute',
            width: 6, height: 10,
            top: -20,
            left: c.left,
            background: c.background,
            animationName: 'confetti-fall',
            animationTimingFunction: 'ease-in',
            animationFillMode: 'forwards',
            animationDelay: c.animationDelay,
            animationDuration: c.animationDuration,
          }} />
        ))}
      </div>

      <div style={{ ...W, maxWidth: 560, paddingTop: 48, paddingBottom: 64, position: 'relative', zIndex: 2 }}>

        <div style={{ textAlign: 'center', marginBottom: 28 }}>
          <div style={{ width: 56, height: 56, borderRadius: '50%', display: 'grid', placeItems: 'center', margin: '0 auto 18px', background: 'var(--av-paper)', border: '1px solid var(--av-line)', color: 'var(--av-cognac)' }}>
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div style={{ fontSize: 10.5, letterSpacing: '0.28em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>Order Confirmed</div>
          <h1 style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(26px,4vw,34px)', fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 10px', letterSpacing: '-0.012em', lineHeight: 1.1 }}>
            Thank you
          </h1>
          <p style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', lineHeight: 1.7, margin: 0 }}>
            Order{' '}
            <span style={{ fontWeight: 600, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>
              #{order.order_number}
            </span>
            {' '}· Confirmation sent to{' '}
            <span style={{ fontWeight: 500, color: 'var(--av-ink)' }}>{order.customer_name}</span>.
          </p>
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', marginBottom: 16 }}>
          <div style={{ padding: '16px 12px', textAlign: 'center', borderRight: '1px solid var(--av-line-soft)' }}>
            <div style={{ fontSize: 10, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 6 }}>Total</div>
            <div style={{ fontWeight: 500, fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{fmt(order.total)}</div>
          </div>
          <div style={{ padding: '16px 12px', textAlign: 'center', borderRight: '1px solid var(--av-line-soft)' }}>
            <div style={{ fontSize: 10, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 6 }}>Payment</div>
            <div style={{ fontWeight: 500, fontSize: 13.5, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{paymentLabel}</div>
          </div>
          <div style={{ padding: '16px 12px', textAlign: 'center' }}>
            <div style={{ fontSize: 10, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 6 }}>Status</div>
            <div style={{ fontWeight: 500, fontSize: 13.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', textTransform: 'capitalize' }}>{order.status}</div>
          </div>
        </div>

        {order.items && order.items.length > 0 && (
          <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', marginBottom: 16 }}>
            {order.items.map((item, i) => (
              <div key={i} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 16px', gap: 12, borderTop: i ? '1px solid var(--av-line-soft)' : 'none' }}>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 13.5, fontWeight: 400, color: 'var(--av-ink)', fontFamily: 'var(--av-display)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                    {item.product_name}
                  </div>
                  {item.variant_label && (
                    <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 2 }}>
                      {item.variant_label}
                    </div>
                  )}
                </div>
                <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', flexShrink: 0 }}>
                  ×{item.quantity}
                </div>
                <div style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', flexShrink: 0 }}>
                  {fmt(item.total)}
                </div>
              </div>
            ))}
          </div>
        )}

        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginBottom: 18 }}>
          <Link href="/account/orders" className="av-btn av-btn-primary av-btn-md av-btn-block" style={{ textDecoration: 'none' }}>
            View my orders
          </Link>
          <Link href="/shop" className="av-btn av-btn-secondary av-btn-md av-btn-block" style={{ textDecoration: 'none' }}>
            Continue shopping
          </Link>
        </div>

        <p style={{ textAlign: 'center', fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
          Track anytime from your{' '}
          <Link href="/account" style={{ color: 'var(--av-ink)', fontWeight: 500, textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>
            dashboard
          </Link>.
        </p>
      </div>
    </Layout>
  );
}
