import { Head, Link, router } from '@inertiajs/react';
import AccountLayout from '../../components/AccountLayout';

interface Review { id: number; rating: number; title: string | null; body: string | null; is_approved: boolean; created_at: string; product: { id: number; name: string; slug: string; thumb: string | null } }
interface Props { reviews: Review[] }

function Stars({ n }: { n: number }) {
  return <span style={{ color: '#F59E0B' }}>{Array.from({ length: 5 }, (_, i) => i < n ? '★' : '☆').join('')}</span>;
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
        <div className="kb-card p-8 text-center">
          <p className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>You haven't written any reviews yet.</p>
          <p className="text-xs mt-1" style={{ color: 'var(--kb-ink-soft)' }}>Reviews can be left from your delivered orders.</p>
          <Link href="/account/orders" className="kb-btn kb-btn-primary kb-btn-md text-sm mt-4 inline-flex outline-none">View Orders</Link>
        </div>
      ) : (
        <div className="space-y-3">
          {reviews.map(r => (
            <div key={r.id} className="kb-card p-5">
              <div className="flex gap-4">
                <div className="w-12 h-12 rounded-lg overflow-hidden shrink-0" style={{ background: 'var(--kb-surface-2)' }}>
                  {r.product.thumb ? <img src={r.product.thumb} alt={r.product.name} className="w-full h-full object-cover"/> : <div className="w-full h-full"/>}
                </div>
                <div className="flex-1 min-w-0">
                  <Link href={`/shop/${r.product.slug}`} className="text-sm font-semibold hover:underline" style={{ color: 'var(--kb-ink)' }}>
                    {r.product.name}
                  </Link>
                  <div className="flex items-center gap-2 mt-1">
                    <Stars n={r.rating} />
                    {!r.is_approved && <span className="text-xs px-2 py-0.5 rounded-full" style={{ background: '#FEF3C7', color: '#92400E' }}>Pending approval</span>}
                  </div>
                  {r.title && <p className="text-sm font-medium mt-1.5" style={{ color: 'var(--kb-ink)' }}>{r.title}</p>}
                  {r.body && <p className="text-sm mt-1" style={{ color: 'var(--kb-ink-soft)' }}>{r.body}</p>}
                  <div className="flex items-center justify-between mt-2">
                    <span className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>{r.created_at}</span>
                    <button onClick={() => del(r.id)} className="text-xs" style={{ color: 'var(--kb-danger)' }}>Delete</button>
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
