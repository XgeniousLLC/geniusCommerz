import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';

interface Props {
  order: {
    order_number: string;
    customer_name: string;
    total: number;
    payment_method: string;
    status: string;
  };
}

const CONFETTI_COLORS = ['#0B1F4F', '#E2136E', '#0F8A5F', '#E8A317', '#1A4F8C', '#C43030'];

export default function OrderConfirmed({ order }: Props) {
  const fmt = usePrice();

  const confetti = useMemo(() =>
    Array.from({ length: 60 }, (_, i) => ({
      left: `${Math.random() * 100}%`,
      background: CONFETTI_COLORS[i % CONFETTI_COLORS.length],
      animationDelay: `${(Math.random() * 0.6).toFixed(2)}s`,
      animationDuration: `${(2.5 + Math.random() * 2).toFixed(2)}s`,
    })), []);

  const paymentLabel = order.payment_method
    .replace(/_/g, ' ')
    .replace(/\b\w/g, c => c.toUpperCase());

  return (
    <Layout>
      <Head title="Order Confirmed" />

      <style>{`
        @keyframes confetti-fall {
          0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
          100% { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }
      `}</style>

      {/* Confetti layer */}
      <div style={{ position: 'fixed', inset: 0, pointerEvents: 'none', overflow: 'hidden', zIndex: 1 }}>
        {confetti.map((c, i) => (
          <span key={i} style={{
            position: 'absolute',
            width: 8, height: 14,
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

      <div className="max-w-lg mx-auto px-4 py-14" style={{ position: 'relative', zIndex: 2 }}>

        {/* Checkmark + heading */}
        <div className="text-center mb-8">
          <div className="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5"
            style={{ background: 'var(--kb-success-50)', border: '3px solid var(--kb-success)' }}>
            <svg className="w-10 h-10" style={{ color: 'var(--kb-success)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 className="text-2xl font-bold mb-2" style={{ color: 'var(--kb-ink)' }}>
            ধন্যবাদ — Thank you for your order!
          </h1>
          <p className="text-sm leading-relaxed" style={{ color: 'var(--kb-ink-soft)' }}>
            Order{' '}
            <span className="font-mono font-bold" style={{ color: 'var(--kb-primary)' }}>
              #{order.order_number}
            </span>
            {' '}· A confirmation has been sent to{' '}
            <span className="font-semibold" style={{ color: 'var(--kb-ink)' }}>{order.customer_name}</span>.
          </p>
        </div>

        {/* Meta strip */}
        <div className="kb-card mb-5" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr' }}>
          <div className="p-4 text-center border-r" style={{ borderColor: 'var(--kb-border)' }}>
            <div className="text-xs mb-1.5" style={{ color: 'var(--kb-ink-soft)' }}>Order total</div>
            <div className="font-bold text-sm" style={{ color: 'var(--kb-ink)' }}>{fmt(order.total)}</div>
          </div>
          <div className="p-4 text-center border-r" style={{ borderColor: 'var(--kb-border)' }}>
            <div className="text-xs mb-1.5" style={{ color: 'var(--kb-ink-soft)' }}>Payment</div>
            <div className="font-bold text-sm" style={{ color: 'var(--kb-ink)' }}>{paymentLabel}</div>
          </div>
          <div className="p-4 text-center">
            <div className="text-xs mb-1.5" style={{ color: 'var(--kb-ink-soft)' }}>Estimated delivery</div>
            <div className="font-bold text-sm" style={{ color: 'var(--kb-ink)' }}>2–4 business days</div>
          </div>
        </div>

        {/* Actions */}
        <div className="flex flex-col sm:flex-row gap-3 mb-6">
          <Link href="/account/orders"
            className="kb-btn kb-btn-primary flex-1 justify-center py-3 text-sm font-semibold">
            View my orders
          </Link>
          <Link href="/shop"
            className="kb-btn flex-1 justify-center py-3 text-sm font-medium"
            style={{ border: '1px solid var(--kb-border)', color: 'var(--kb-ink)' }}>
            Continue shopping
          </Link>
        </div>

        {/* Help */}
        <p className="text-center text-xs" style={{ color: 'var(--kb-ink-soft)' }}>
          Track at any time from your{' '}
          <Link href="/account" style={{ color: 'var(--kb-primary)', fontWeight: 600 }}>
            dashboard
          </Link>.
        </p>
      </div>
    </Layout>
  );
}
