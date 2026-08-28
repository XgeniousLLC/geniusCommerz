import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { usePrice } from '../../usePrice';

interface Order { id: number; order_number: string; status: string; payment_status: string; total: number; items_count: number; created_at: string }
interface Props {
  orders: { data: Order[]; links: any[]; meta: any };
  filters: { status?: string };
  statuses: string[];
}

const STATUS_COLOR: Record<string, string> = {
  pending: '#95613a', processing: '#6f4527', shipped: '#b2904f',
  delivered: '#16A34A', cancelled: '#b94040', refunded: '#756a59',
};

export default function Orders({ orders, filters, statuses }: Props) {
  const [status, setStatus] = useState(filters.status ?? '');
  const fmt = usePrice();

  const filter = (s: string) => {
    setStatus(s);
    router.get('/account/orders', s ? { status: s } : {}, { preserveState: true });
  };

  return (
    <AccountLayout title="My Orders" active="/account/orders">
      <Head title="My Orders" />

      <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', marginBottom: 16 }}>
        {['', ...statuses].map(s => (
          <button key={s} onClick={() => filter(s)}
            style={{
              fontSize: 11, letterSpacing: '0.1em', textTransform: 'uppercase', padding: '7px 14px', fontFamily: 'var(--av-sans)', fontWeight: 500, cursor: 'pointer', border: `1px solid ${status === s ? 'var(--av-ink)' : 'var(--av-line)'}`,
              background: status === s ? 'var(--av-ink)' : 'transparent', color: status === s ? 'var(--av-paper)' : 'var(--av-muted)'
            }}>
            {s || 'All'}
          </button>
        ))}
      </div>

      <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)' }}>
        {orders.data.length === 0 ? (
          <p style={{ fontSize: 13.5, textAlign: 'center', padding: '36px 18px', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>No orders found.</p>
        ) : (
          <div>
            {orders.data.map(o => (
              <Link key={o.id} href={`/account/orders/${o.id}`}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 18px', textDecoration: 'none', borderTop: '1px solid var(--av-line-soft)', gap: 12 }}>
                <div style={{ minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                    <span style={{ fontFamily: 'var(--av-sans)', fontSize: 13, fontWeight: 500, color: 'var(--av-ink)' }}>#{o.order_number}</span>
                    <span style={{ fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase', padding: '3px 7px', background: STATUS_COLOR[o.status] + '14', color: STATUS_COLOR[o.status], fontFamily: 'var(--av-sans)', fontWeight: 500 }}>
                      {o.status}
                    </span>
                  </div>
                  <p style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 4, fontFamily: 'var(--av-sans)' }}>{o.items_count} · {o.created_at}</p>
                </div>
                <div style={{ textAlign: 'right', flexShrink: 0 }}>
                  <p style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>{fmt(o.total)}</p>
                  <p style={{ fontSize: 10, letterSpacing: '0.08em', textTransform: 'uppercase', marginTop: 2, color: o.payment_status === 'paid' ? '#16A34A' : 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{o.payment_status.replace('_', ' ')}</p>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      {orders.meta?.last_page > 1 && (
        <div style={{ display: 'flex', justifyContent: 'center', gap: 6, marginTop: 20 }}>
          {orders.links.map((link: any, i: number) => (
            <button key={i} disabled={!link.url}
              onClick={() => link.url && router.visit(link.url)}
              style={{
                fontSize: 12, padding: '7px 12px', fontFamily: 'var(--av-sans)', cursor: link.url ? 'pointer' : 'default',
                background: link.active ? 'var(--av-ink)' : 'transparent', color: link.active ? 'var(--av-paper)' : 'var(--av-muted)',
                border: `1px solid ${link.active ? 'var(--av-ink)' : 'var(--av-line)'}`, opacity: !link.url ? 0.4 : 1
              }}
              dangerouslySetInnerHTML={{ __html: link.label }} />
          ))}
        </div>
      )}
    </AccountLayout>
  );
}
