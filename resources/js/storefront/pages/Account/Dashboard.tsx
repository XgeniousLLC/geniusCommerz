import { Head, Link } from '@inertiajs/react';
import AccountLayout from '../../components/AccountLayout';

interface Props {
  stats: { total_orders: number; pending_orders: number; delivered: number; total_reviews: number; open_refunds: number };
  recentOrders: Array<{ id: number; order_number: string; status: string; payment_status: string; total: number; items_count: number; created_at: string }>;
}

const STATUS_COLOR: Record<string, string> = {
  pending: '#D97706', processing: '#2563EB', shipped: '#7C3AED',
  delivered: '#16A34A', cancelled: '#DC2626', refunded: '#6B7280',
};

export default function Dashboard({ stats, recentOrders }: Props) {
  return (
    <AccountLayout title="Dashboard" active="/account">
      <Head title="My Account" />

      {/* Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        {[
          { label: 'Total Orders',    value: stats.total_orders,   color: 'var(--kb-primary)' },
          { label: 'Active Orders',   value: stats.pending_orders, color: '#D97706' },
          { label: 'Delivered',       value: stats.delivered,      color: 'var(--kb-success)' },
          { label: 'My Reviews',      value: stats.total_reviews,  color: '#7C3AED' },
          { label: 'Open Refunds',    value: stats.open_refunds,   color: '#DC2626' },
        ].map(s => (
          <div key={s.label} className="kb-card p-4 text-center">
            <div className="text-2xl font-bold" style={{ color: s.color }}>{s.value}</div>
            <div className="text-xs mt-1" style={{ color: 'var(--kb-ink-soft)' }}>{s.label}</div>
          </div>
        ))}
      </div>

      {/* Recent orders */}
      <div className="kb-card">
        <div className="flex items-center justify-between px-5 py-4 border-b" style={{ borderColor: 'var(--kb-border)' }}>
          <h2 className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>Recent Orders</h2>
          <Link href="/account/orders" className="text-xs" style={{ color: 'var(--kb-primary)' }}>View all</Link>
        </div>
        {recentOrders.length === 0 ? (
          <p className="text-sm text-center py-8" style={{ color: 'var(--kb-ink-soft)' }}>No orders yet. <Link href="/shop" style={{ color: 'var(--kb-primary)' }}>Start shopping →</Link></p>
        ) : (
          <div className="divide-y" style={{ borderColor: 'var(--kb-border)' }}>
            {recentOrders.map(o => (
              <Link key={o.id} href={`/account/orders/${o.id}`}
                className="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 transition-colors">
                <div>
                  <span className="text-sm font-mono font-semibold" style={{ color: 'var(--kb-ink)' }}>#{o.order_number}</span>
                  <span className="text-xs ml-3" style={{ color: 'var(--kb-ink-soft)' }}>{o.items_count} item{o.items_count !== 1 ? 's' : ''} · {o.created_at}</span>
                </div>
                <div className="flex items-center gap-3">
                  <span className="text-sm font-bold" style={{ color: 'var(--kb-ink)' }}>৳{Number(o.total).toLocaleString()}</span>
                  <span className="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                    style={{ background: STATUS_COLOR[o.status] + '20', color: STATUS_COLOR[o.status] }}>
                    {o.status}
                  </span>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* Quick links */}
      <div className="grid grid-cols-2 gap-3 mt-4">
        <Link href="/account/address" className="kb-card p-4 flex items-center gap-3 hover:border-blue-200 transition-colors text-sm font-medium" style={{ color: 'var(--kb-ink)' }}>
          <svg className="w-5 h-5 shrink-0" style={{ color: 'var(--kb-primary)' }} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Manage Address
        </Link>
        <Link href="/account/reviews" className="kb-card p-4 flex items-center gap-3 hover:border-purple-200 transition-colors text-sm font-medium" style={{ color: 'var(--kb-ink)' }}>
          <svg className="w-5 h-5 shrink-0" style={{ color: '#7C3AED' }} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
          My Reviews
        </Link>
      </div>
    </AccountLayout>
  );
}
