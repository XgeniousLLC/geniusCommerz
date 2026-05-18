import { Head, Link } from '@inertiajs/react';
import AccountLayout from '../../components/AccountLayout';
import { usePrice } from '../../usePrice';

interface Address {
  id: number;
  label: string;
  name: string;
  phone: string;
  address: string;
  city: string;
  is_default: boolean;
}

interface Props {
  stats: {
    total_orders: number;
    pending_orders: number;
    delivered: number;
    total_reviews: number;
    open_refunds: number;
  };
  recentOrders: Array<{
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    total: number;
    items_count: number;
    created_at: string;
  }>;
  loyaltyPoints: number;
  defaultAddress: Address | null;
}

const STATUS_COLOR: Record<string, string> = {
  pending:    '#D97706',
  processing: '#2563EB',
  shipped:    '#7C3AED',
  delivered:  '#16A34A',
  cancelled:  '#DC2626',
  refunded:   '#6B7280',
};

export default function Dashboard({ stats, recentOrders, loyaltyPoints, defaultAddress }: Props) {
  const fmt = usePrice();

  const statCards = [
    { label: 'Total orders', value: stats.total_orders,   color: 'var(--kb-primary)' },
    { label: 'In transit',   value: stats.pending_orders, color: '#D97706' },
    { label: 'My reviews',   value: stats.total_reviews,  color: '#7C3AED' },
    { label: 'Open refunds', value: stats.open_refunds,   color: '#DC2626' },
  ];

  return (
    <AccountLayout title="Dashboard" active="/account">
      <Head title="My Account" />

      {/* Loyalty / Gold card */}
      <div className="relative overflow-hidden rounded-2xl p-5 mb-5 text-white"
        style={{ background: 'linear-gradient(135deg, #1A1F33 0%, #0B1F4F 60%, #2A3FA8 100%)' }}>
        {/* Decorative circle */}
        <div style={{
          position: 'absolute', right: -40, top: -40,
          width: 180, height: 180, borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(232,163,23,.4), transparent 70%)',
          pointerEvents: 'none',
        }} />
        <div className="text-xs font-bold tracking-widest opacity-70 mb-1.5" style={{ letterSpacing: '0.16em' }}>
          ★ KLIXBD MEMBER
        </div>
        <div className="text-4xl font-extrabold tracking-tight mb-1.5" style={{ letterSpacing: '-0.02em' }}>
          {loyaltyPoints.toLocaleString()} pts
        </div>
        <div className="text-sm opacity-85">
          {loyaltyPoints > 0
            ? 'Earn more points with every purchase'
            : 'Place your first order to start earning points'}
        </div>
        <Link href="/loyalty" className="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold"
          style={{ color: '#E8A317' }}>
          View loyalty program →
        </Link>
      </div>

      {/* Stats grid */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        {statCards.map(s => (
          <div key={s.label} className="kb-card p-4 text-center">
            <div className="text-2xl font-extrabold mb-0.5" style={{ color: s.color }}>{s.value}</div>
            <div className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>{s.label}</div>
          </div>
        ))}
      </div>

      {/* Recent orders */}
      <div className="kb-card mb-4">
        <div className="flex items-center justify-between px-5 py-4 border-b" style={{ borderColor: 'var(--kb-border)' }}>
          <h2 className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>Recent orders</h2>
          <Link href="/account/orders" className="text-xs font-semibold" style={{ color: 'var(--kb-primary)' }}>
            View all →
          </Link>
        </div>
        {recentOrders.length === 0 ? (
          <p className="text-sm text-center py-8" style={{ color: 'var(--kb-ink-soft)' }}>
            No orders yet.{' '}
            <Link href="/shop" style={{ color: 'var(--kb-primary)', fontWeight: 600 }}>Start shopping →</Link>
          </p>
        ) : (
          <div className="divide-y" style={{ borderColor: 'var(--kb-border)' }}>
            {recentOrders.map(o => (
              <Link key={o.id} href={`/account/orders/${o.id}`}
                className="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 transition-colors">
                <div>
                  <span className="text-sm font-mono font-semibold" style={{ color: 'var(--kb-ink)' }}>
                    #{o.order_number}
                  </span>
                  <span className="text-xs ml-2.5" style={{ color: 'var(--kb-ink-soft)' }}>
                    {o.items_count} item{o.items_count !== 1 ? 's' : ''} · {o.created_at}
                  </span>
                </div>
                <div className="flex items-center gap-3">
                  <span className="text-sm font-bold" style={{ color: 'var(--kb-ink)' }}>
                    {fmt(o.total)}
                  </span>
                  <span className="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                    style={{
                      background: (STATUS_COLOR[o.status] ?? '#6B7280') + '20',
                      color: STATUS_COLOR[o.status] ?? '#6B7280',
                    }}>
                    {o.status}
                  </span>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* Default address + quick links */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {/* Default address */}
        <div className="kb-card p-5">
          <div className="flex items-center justify-between mb-3">
            <h3 className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>Default address</h3>
            <Link href="/account/address" className="text-xs font-semibold" style={{ color: 'var(--kb-primary)' }}>
              Manage
            </Link>
          </div>
          {defaultAddress ? (
            <>
              <div className="flex items-center gap-2 mb-1.5">
                <span className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>
                  {defaultAddress.name}
                </span>
                {defaultAddress.label && (
                  <span className="text-xs px-2 py-0.5 rounded-full font-bold uppercase tracking-wide text-white"
                    style={{ background: 'var(--kb-primary)', fontSize: 10 }}>
                    {defaultAddress.label}
                  </span>
                )}
              </div>
              <div className="text-xs leading-relaxed" style={{ color: 'var(--kb-ink-soft)' }}>
                {defaultAddress.address}<br />
                {defaultAddress.city}<br />
                {defaultAddress.phone}
              </div>
            </>
          ) : (
            <div className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>
              No address saved.{' '}
              <Link href="/account/address" style={{ color: 'var(--kb-primary)', fontWeight: 600 }}>Add one →</Link>
            </div>
          )}
        </div>

        {/* Quick links */}
        <div className="grid grid-cols-1 gap-3">
          <Link href="/account/reviews"
            className="kb-card p-4 flex items-center gap-3 hover:border-purple-200 transition-colors">
            <div className="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
              style={{ background: '#F5F3FF' }}>
              <svg className="w-5 h-5" style={{ color: '#7C3AED' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <div>
              <div className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>My reviews</div>
              <div className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>
                {stats.total_reviews} review{stats.total_reviews !== 1 ? 's' : ''} written
              </div>
            </div>
          </Link>

          <Link href="/account/refunds"
            className="kb-card p-4 flex items-center gap-3 hover:border-red-100 transition-colors">
            <div className="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
              style={{ background: '#FEF2F2' }}>
              <svg className="w-5 h-5" style={{ color: '#DC2626' }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
              </svg>
            </div>
            <div>
              <div className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>Refunds</div>
              <div className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>
                {stats.open_refunds} open request{stats.open_refunds !== 1 ? 's' : ''}
              </div>
            </div>
          </Link>
        </div>
      </div>
    </AccountLayout>
  );
}
