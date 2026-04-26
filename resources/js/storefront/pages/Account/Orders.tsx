import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AccountLayout from '../../components/AccountLayout';

interface Order { id: number; order_number: string; status: string; payment_status: string; total: number; items_count: number; created_at: string }
interface Props {
  orders: { data: Order[]; links: any[]; meta: any };
  filters: { status?: string };
  statuses: string[];
}

const STATUS_COLOR: Record<string, string> = {
  pending: '#D97706', processing: '#2563EB', shipped: '#7C3AED',
  delivered: '#16A34A', cancelled: '#DC2626', refunded: '#6B7280',
};

export default function Orders({ orders, filters, statuses }: Props) {
  const [status, setStatus] = useState(filters.status ?? '');

  const filter = (s: string) => {
    setStatus(s);
    router.get('/account/orders', s ? { status: s } : {}, { preserveState: true });
  };

  return (
    <AccountLayout title="My Orders" active="/account/orders">
      <Head title="My Orders" />

      {/* Filter tabs */}
      <div className="flex gap-2 flex-wrap mb-4">
        {['', ...statuses].map(s => (
          <button key={s} onClick={() => filter(s)}
            className="text-xs px-3 py-1.5 rounded-full font-medium transition-colors capitalize"
            style={status === s
              ? { background: 'var(--kb-primary)', color: '#fff' }
              : { background: 'var(--kb-surface-2)', color: 'var(--kb-ink-soft)' }}>
            {s || 'All'}
          </button>
        ))}
      </div>

      <div className="kb-card">
        {orders.data.length === 0 ? (
          <p className="text-sm text-center py-10" style={{ color: 'var(--kb-ink-soft)' }}>No orders found.</p>
        ) : (
          <div className="divide-y" style={{ borderColor: 'var(--kb-border)' }}>
            {orders.data.map(o => (
              <Link key={o.id} href={`/account/orders/${o.id}`}
                className="flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors gap-4">
                <div className="min-w-0">
                  <div className="flex items-center gap-2 flex-wrap">
                    <span className="font-mono text-sm font-bold" style={{ color: 'var(--kb-ink)' }}>#{o.order_number}</span>
                    <span className="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                      style={{ background: STATUS_COLOR[o.status] + '20', color: STATUS_COLOR[o.status] }}>
                      {o.status}
                    </span>
                  </div>
                  <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{o.items_count} item{o.items_count !== 1 ? 's' : ''} · {o.created_at}</p>
                </div>
                <div className="text-right shrink-0">
                  <p className="text-sm font-bold" style={{ color: 'var(--kb-ink)' }}>৳{Number(o.total).toLocaleString()}</p>
                  <p className="text-xs capitalize mt-0.5" style={{ color: o.payment_status === 'paid' ? 'var(--kb-success)' : 'var(--kb-warn)' }}>{o.payment_status.replace('_', ' ')}</p>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* Pagination */}
      {orders.meta?.last_page > 1 && (
        <div className="flex justify-center gap-1 mt-4">
          {orders.links.map((link: any, i: number) => (
            <button key={i} disabled={!link.url}
              onClick={() => link.url && router.visit(link.url)}
              className="text-xs px-3 py-1.5 rounded font-medium"
              style={link.active
                ? { background: 'var(--kb-primary)', color: '#fff' }
                : { background: 'var(--kb-surface-2)', color: 'var(--kb-ink-soft)' }}
              dangerouslySetInnerHTML={{ __html: link.label }} />
          ))}
        </div>
      )}
    </AccountLayout>
  );
}
