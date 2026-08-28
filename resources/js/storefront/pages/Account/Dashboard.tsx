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
  pending:    '#95613a',
  processing: '#6f4527',
  shipped:    '#b2904f',
  delivered:  '#16A34A',
  cancelled:  '#b94040',
  refunded:   '#756a59',
};

export default function Dashboard({ stats, recentOrders, loyaltyPoints, defaultAddress }: Props) {
  const fmt = usePrice();

  const statCards = [
    { label: 'Total orders', value: stats.total_orders },
    { label: 'In transit',   value: stats.pending_orders },
    { label: 'My reviews',   value: stats.total_reviews },
    { label: 'Open refunds', value: stats.open_refunds },
  ];

  return (
    <AccountLayout title="Dashboard" active="/account">
      <Head title="My Account" />

      {/* Loyalty card */}
      <div style={{ border: '1px solid var(--av-line)', background: 'var(--av-ink)', color: 'var(--av-paper)', padding: 24, marginBottom: 20, position: 'relative', overflow: 'hidden' }}>
        <div style={{ position: 'absolute', right: -30, top: -30, width: 160, height: 160, borderRadius: '50%', background: 'radial-gradient(circle, rgba(149,97,58,.35), transparent 70%)', pointerEvents: 'none' }} />
        <div style={{ fontSize: 10, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>
          Member
        </div>
        <div style={{ fontFamily: 'var(--av-display)', fontSize: 36, fontWeight: 400, letterSpacing: '-0.02em', lineHeight: 1, marginBottom: 8 }}>
          {loyaltyPoints.toLocaleString()} <span style={{ fontSize: 16, letterSpacing: '0.12em', textTransform: 'uppercase', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>pts</span>
        </div>
        <div style={{ fontSize: 13, color: 'rgba(251,248,241,.72)', fontFamily: 'var(--av-sans)', lineHeight: 1.5 }}>
          {loyaltyPoints > 0 ? 'Earn more with every purchase.' : 'Place your first order to start earning.'}
        </div>
        <Link href="/loyalty" style={{ display: 'inline-flex', marginTop: 14, fontSize: 11.5, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-paper)', textDecoration: 'none', borderBottom: '1px solid rgba(251,248,241,.3)', paddingBottom: 2, fontFamily: 'var(--av-sans)', fontWeight: 500 }}>
          Loyalty program →
        </Link>
      </div>

      {/* Stats */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 12, marginBottom: 20 }} className="av-stats-grid">
        {statCards.map(s => (
          <div key={s.label} style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: '18px 14px', textAlign: 'center' }}>
            <div style={{ fontFamily: 'var(--av-display)', fontSize: 28, fontWeight: 400, color: 'var(--av-ink)', lineHeight: 1 }}>{s.value}</div>
            <div style={{ fontSize: 10, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 6 }}>{s.label}</div>
          </div>
        ))}
      </div>

      {/* Recent orders */}
      <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', marginBottom: 16 }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 18px', borderBottom: '1px solid var(--av-line-soft)' }}>
          <h2 style={{ fontSize: 12, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: 0 }}>Recent orders</h2>
          <Link href="/account/orders" style={{ fontSize: 11.5, color: 'var(--av-cognac)', textDecoration: 'none', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>
            View all →
          </Link>
        </div>
        {recentOrders.length === 0 ? (
          <p style={{ fontSize: 13.5, textAlign: 'center', padding: '28px 18px', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
            No orders yet.{' '}
            <Link href="/shop" style={{ color: 'var(--av-ink)', fontWeight: 500, textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>Start shopping →</Link>
          </p>
        ) : (
          <div>
            {recentOrders.map(o => (
              <Link key={o.id} href={`/account/orders/${o.id}`}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '14px 18px', textDecoration: 'none', borderTop: '1px solid var(--av-line-soft)', gap: 12 }}>
                <div>
                  <span style={{ fontFamily: 'var(--av-sans)', fontSize: 13, fontWeight: 500, color: 'var(--av-ink)' }}>
                    #{o.order_number}
                  </span>
                  <span style={{ fontSize: 11.5, color: 'var(--av-muted)', marginLeft: 10, fontFamily: 'var(--av-sans)' }}>
                    {o.items_count} · {o.created_at}
                  </span>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexShrink: 0 }}>
                  <span style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>
                    {fmt(o.total)}
                  </span>
                  <span style={{
                    fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase', padding: '3px 7px',
                    background: (STATUS_COLOR[o.status] ?? '#756a59') + '14', color: STATUS_COLOR[o.status] ?? '#756a59', fontFamily: 'var(--av-sans)', fontWeight: 500
                  }}>
                    {o.status}
                  </span>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      {/* Address + quick links */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }} className="av-dash-bottom">
        <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 14 }}>
            <h3 style={{ fontSize: 11.5, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: 0 }}>Default address</h3>
            <Link href="/account/address" style={{ fontSize: 11.5, color: 'var(--av-cognac)', textDecoration: 'none', fontFamily: 'var(--av-sans)' }}>
              Manage
            </Link>
          </div>
          {defaultAddress ? (
            <>
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
                <span style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>
                  {defaultAddress.name}
                </span>
                {defaultAddress.label && (
                  <span style={{ fontSize: 10, letterSpacing: '0.12em', textTransform: 'uppercase', padding: '2px 7px', background: 'var(--av-ink)', color: 'var(--av-paper)', fontFamily: 'var(--av-sans)' }}>
                    {defaultAddress.label}
                  </span>
                )}
              </div>
              <div style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', lineHeight: 1.6 }}>
                {defaultAddress.address}<br />
                {defaultAddress.city}<br />
                {defaultAddress.phone}
              </div>
            </>
          ) : (
            <div style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
              No address saved.{' '}
              <Link href="/account/address" style={{ color: 'var(--av-ink)', fontWeight: 500, textDecoration: 'underline', textDecorationColor: 'var(--av-line)' }}>Add one →</Link>
            </div>
          )}
        </div>

        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          <Link href="/account/reviews"
            style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 16, display: 'flex', alignItems: 'center', gap: 14, textDecoration: 'none' }}>
            <div style={{ width: 36, height: 36, display: 'grid', placeItems: 'center', background: 'var(--av-paper-2)', color: 'var(--av-cognac)', flexShrink: 0 }}>
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <div>
              <div style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>My reviews</div>
              <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                {stats.total_reviews} review{stats.total_reviews !== 1 ? 's' : ''} written
              </div>
            </div>
          </Link>

          <Link href="/account/refunds"
            style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 16, display: 'flex', alignItems: 'center', gap: 14, textDecoration: 'none' }}>
            <div style={{ width: 36, height: 36, display: 'grid', placeItems: 'center', background: 'var(--av-paper-2)', color: 'var(--av-muted)', flexShrink: 0 }}>
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.4} d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
              </svg>
            </div>
            <div>
              <div style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)' }}>Refunds</div>
              <div style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>
                {stats.open_refunds} open
              </div>
            </div>
          </Link>
        </div>
      </div>

      <style>{`
        @media(max-width: 640px){
          .av-stats-grid{ grid-template-columns: 1fr 1fr !important; }
          .av-dash-bottom{ grid-template-columns: 1fr !important; }
        }
      `}</style>
    </AccountLayout>
  );
}
