import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AccountLayout from '../../components/AccountLayout';

interface Refund {
  id: number; order_number: string; reason: string; details: string | null;
  amount: number | null; status: string; admin_note: string | null;
  created_at: string; resolved_at: string | null;
}
interface EligibleOrder { id: number; order_number: string; total: number }
interface Props {
  refunds: Refund[];
  eligibleOrders: EligibleOrder[];
  reasons: Record<string, string>;
}

const STATUS_STYLE: Record<string, { bg: string; color: string }> = {
  pending:   { bg: '#FEF3C7', color: '#92400E' },
  approved:  { bg: '#D1FAE5', color: '#065F46' },
  rejected:  { bg: '#FEE2E2', color: '#991B1B' },
  processed: { bg: '#DBEAFE', color: '#1E40AF' },
};

export default function Refunds({ refunds, eligibleOrders, reasons }: Props) {
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ order_id: '', reason: '', details: '' });
  const [submitting, setSubmitting] = useState(false);

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    router.post('/account/refunds', form, {
      onSuccess: () => { setShowForm(false); setForm({ order_id: '', reason: '', details: '' }); },
      onFinish: () => setSubmitting(false),
    });
  };

  return (
    <AccountLayout title="Refunds" active="/account/refunds">
      <Head title="My Refunds" />

      {/* Request button */}
      {eligibleOrders.length > 0 && !showForm && (
        <div className="mb-4">
          <button onClick={() => setShowForm(true)} className="kb-btn kb-btn-primary text-sm px-4 py-2">
            Request a Refund
          </button>
        </div>
      )}

      {/* New refund form */}
      {showForm && (
        <form onSubmit={submit} className="kb-card p-5 mb-5 space-y-4">
          <h2 className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>New Refund Request</h2>

          <div>
            <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Order</label>
            <select className="kb-input text-sm" value={form.order_id}
              onChange={e => setForm(f => ({ ...f, order_id: e.target.value }))} required>
              <option value="">Select an order…</option>
              {eligibleOrders.map(o => (
                <option key={o.id} value={o.id}>#{o.order_number} — ৳{Number(o.total).toLocaleString()}</option>
              ))}
            </select>
          </div>

          <div>
            <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Reason</label>
            <select className="kb-input text-sm" value={form.reason}
              onChange={e => setForm(f => ({ ...f, reason: e.target.value }))} required>
              <option value="">Select a reason…</option>
              {Object.entries(reasons).map(([k, v]) => (
                <option key={k} value={k}>{v}</option>
              ))}
            </select>
          </div>

          <div>
            <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Additional Details (optional)</label>
            <textarea className="kb-input text-sm resize-none" rows={3} value={form.details}
              onChange={e => setForm(f => ({ ...f, details: e.target.value }))}
              placeholder="Describe the issue in detail…" />
          </div>

          <div className="flex gap-2">
            <button type="submit" disabled={submitting} className="kb-btn kb-btn-primary text-sm px-4 py-2">
              {submitting ? 'Submitting…' : 'Submit Request'}
            </button>
            <button type="button" onClick={() => setShowForm(false)} className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>Cancel</button>
          </div>
        </form>
      )}

      {/* Refund list */}
      {refunds.length === 0 ? (
        <div className="kb-card p-8 text-center">
          <p className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>No refund requests yet.</p>
          {eligibleOrders.length === 0 && (
            <p className="text-xs mt-1" style={{ color: 'var(--kb-ink-soft)' }}>Refunds can only be requested for delivered orders.</p>
          )}
        </div>
      ) : (
        <div className="space-y-3">
          {refunds.map(r => {
            const style = STATUS_STYLE[r.status] ?? STATUS_STYLE.pending;
            return (
              <div key={r.id} className="kb-card p-5">
                <div className="flex items-start justify-between gap-4 flex-wrap">
                  <div>
                    <div className="flex items-center gap-2 flex-wrap">
                      <span className="font-mono font-bold text-sm" style={{ color: 'var(--kb-ink)' }}>#{r.order_number}</span>
                      <span className="text-xs px-2 py-0.5 rounded-full font-semibold capitalize"
                        style={{ background: style.bg, color: style.color }}>
                        {r.status}
                      </span>
                    </div>
                    <p className="text-sm mt-1 capitalize" style={{ color: 'var(--kb-ink)' }}>
                      {reasons[r.reason] ?? r.reason}
                    </p>
                    {r.details && <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{r.details}</p>}
                  </div>
                  <div className="text-right shrink-0">
                    {r.amount && (
                      <p className="text-sm font-bold" style={{ color: 'var(--kb-ink)' }}>৳{Number(r.amount).toLocaleString()}</p>
                    )}
                    <p className="text-xs mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>Requested {r.created_at}</p>
                    {r.resolved_at && <p className="text-xs" style={{ color: 'var(--kb-ink-soft)' }}>Resolved {r.resolved_at}</p>}
                  </div>
                </div>

                {r.admin_note && (
                  <div className="mt-3 px-3 py-2 rounded-lg text-xs" style={{ background: 'var(--kb-surface-2)', color: 'var(--kb-ink-soft)' }}>
                    <span className="font-semibold" style={{ color: 'var(--kb-ink)' }}>Note from us: </span>
                    {r.admin_note}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}
    </AccountLayout>
  );
}
