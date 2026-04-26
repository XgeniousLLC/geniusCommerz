import { Head, Link } from '@inertiajs/react';
import Layout from '../layouts/Layout';

interface Props {
  order: {
    order_number: string;
    customer_name: string;
    total: number;
    payment_method: string;
    status: string;
  };
}

export default function OrderConfirmed({ order }: Props) {
  return (
    <Layout>
      <Head title="Order Confirmed" />
      <div className="max-w-lg mx-auto px-4 py-12 text-center">

        <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5"
          style={{ background: 'var(--kb-success-50)' }}>
          <svg className="w-8 h-8" style={{ color: 'var(--kb-success)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <h1 className="text-2xl font-bold mb-2" style={{ color: 'var(--kb-ink)' }}>Order Placed!</h1>
        <p className="text-sm mb-6" style={{ color: 'var(--kb-ink-soft)' }}>
          Thank you, {order.customer_name}. We've received your order and will process it shortly.
        </p>

        <div className="kb-card p-5 text-left mb-6 space-y-3">
          <div className="flex justify-between text-sm">
            <span style={{ color: 'var(--kb-ink-soft)' }}>Order Number</span>
            <span className="font-mono font-bold" style={{ color: 'var(--kb-primary)' }}>#{order.order_number}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span style={{ color: 'var(--kb-ink-soft)' }}>Total</span>
            <span className="font-bold" style={{ color: 'var(--kb-ink)' }}>৳{Number(order.total).toLocaleString()}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span style={{ color: 'var(--kb-ink-soft)' }}>Payment</span>
            <span className="font-medium capitalize" style={{ color: 'var(--kb-ink)' }}>{order.payment_method.replace('_', ' ')}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span style={{ color: 'var(--kb-ink-soft)' }}>Status</span>
            <span className="font-medium capitalize" style={{ color: 'var(--kb-ink)' }}>{order.status}</span>
          </div>
        </div>

        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <a href={`/track?order_number=${order.order_number}`}
            className="kb-btn kb-btn-primary">
            Track Order
          </a>
          <Link href="/shop" className="kb-btn" style={{ border: '1px solid var(--kb-border)' }}>
            Continue Shopping
          </Link>
        </div>

      </div>
    </Layout>
  );
}
