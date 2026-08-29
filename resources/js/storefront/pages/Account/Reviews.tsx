import { Head, Link, router } from '@inertiajs/react';
import AccountLayout from '../../components/AccountLayout';

interface Review { id: number; rating: number; title: string | null; body: string | null; is_approved: boolean; created_at: string; product: { id: number; name: string; slug: string; thumb: string | null } }
interface Props { reviews: Review[] }

function Stars({ n }: { n: number }) {
  return <span style={{ color: 'var(--av-gold)', fontSize: 13, letterSpacing: 1 }}>{Array.from({ length: 5 }, (_, i) => i < n ? '★' : '☆').join('')}</span>;
}

export default function Reviews({ reviews }: Props) {
  const del = (id: number) => {
    if (!confirm('Delete this review?')) return;
    router.delete(`/reviews/${id}`);
  };

  return (
    <AccountLayout title="My Reviews" active="/account/reviews">
      <Head title="My Reviews" />

      {reviews.length === 0 ? (
        <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: '32px 18px', textAlign: 'center' }}>
          <p style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: 0 }}>You haven't written any reviews yet.</p>
          <p style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 6 }}>Reviews can be left from delivered orders.</p>
          <Link href="/account/orders" className="av-btn av-btn-primary av-btn-sm" style={{ marginTop: 16, textDecoration: 'none' }}>View orders</Link>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {reviews.map(r => (
            <div key={r.id} style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
              <div style={{ display: 'flex', gap: 14 }}>
                <div style={{ width: 48, height: 60, overflow: 'hidden', flexShrink: 0, background: 'var(--av-paper-2)' }}>
                  {r.product.thumb ? <img src={r.product.thumb} alt={r.product.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}/> : null}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <Link href={`/shop/${r.product.slug}`} style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', textDecoration: 'none', fontFamily: 'var(--av-sans)' }}>
                    {r.product.name}
                  </Link>
                  <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 6 }}>
                    <Stars n={r.rating} />
                    {!r.is_approved && <span style={{ fontSize: 10, letterSpacing: '0.08em', textTransform: 'uppercase', padding: '3px 7px', background: 'var(--av-paper-2)', color: 'var(--av-muted)', border: '1px solid var(--av-line-soft)', fontFamily: 'var(--av-sans)' }}>Pending approval</span>}
                  </div>
                  {r.title && <p style={{ fontSize: 13, fontWeight: 500, marginTop: 10, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', marginBottom: 0 }}>{r.title}</p>}
                  {r.body && <p style={{ fontSize: 13, marginTop: 6, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', lineHeight: 1.6 }}>{r.body}</p>}
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 12 }}>
                    <span style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{r.created_at}</span>
                    <button onClick={() => del(r.id)} style={{ fontSize: 11.5, color: '#b94040', background: 'transparent', border: 'none', cursor: 'pointer', textDecoration: 'underline', fontFamily: 'var(--av-sans)' }}>Delete</button>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </AccountLayout>
  );
}
