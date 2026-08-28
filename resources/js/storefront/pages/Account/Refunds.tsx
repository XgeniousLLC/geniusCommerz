import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AccountLayout from '../../components/AccountLayout';
import { usePrice } from '../../usePrice';

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
  pending:   { bg: 'var(--av-paper-2)', color: 'var(--av-cognac)' },
  approved:  { bg: '#dcfce7', color: '#166534' },
  rejected:  { bg: '#fee2e2', color: '#991b1b' },
  processed: { bg: '#dbeafe', color: '#1e40af' },
};

export default function Refunds({ refunds, eligibleOrders, reasons }: Props) {
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ order_id: '', reason: '', details: '' });
  const [submitting, setSubmitting] = useState(false);
  const fmt = usePrice();

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

      {eligibleOrders.length > 0 && !showForm && (
        <div style={{ marginBottom: 16 }}>
          <button onClick={() => setShowForm(true)} className="av-btn av-btn-primary av-btn-sm">
            Request a refund
          </button>
        </div>
      )}

      {showForm && (
        <form onSubmit={submit} style={{ border: '1px solid var(--av-line)', background: 'var(--av-paper)', padding: 20, marginBottom: 20, display: 'flex', flexDirection: 'column', gap: 14 }}>
          <h2 style={{ fontSize: 12, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: 0 }}>New refund request</h2>

          <div>
            <label style={{ display: 'block', fontSize: 10.5, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 6 }}>Order</label>
            <select className="av-select" value={form.order_id}
              onChange={e => setForm(f => ({ ...f, order_id: e.target.value }))} required>
              <option value="">Select order…</option>
              {eligibleOrders.map(o => (
                <option key={o.id} value={o.id}>#{o.order_number} — {fmt(o.total)}</option>
              ))}
            </select>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: 10.5, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 6 }}>Reason</label>
            <select className="av-select" value={form.reason}
              onChange={e => setForm(f => ({ ...f, reason: e.target.value }))} required>
              <option value="">Select reason…</option>
              {Object.entries(reasons).map(([k, v]) => (
                <option key={k} value={k}>{v}</option>
              ))}
            </select>
          </div>

          <div>
            <label style={{ display: 'block', fontSize: 10.5, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 6 }}>Details (optional)</label>
            <textarea className="av-input av-textarea" rows={3} value={form.details}
              onChange={e => setForm(f => ({ ...f, details: e.target.value }))}
              placeholder="Describe the issue…" />
          </div>

          <div style={{ display: 'flex', gap: 8 }}>
            <button type="submit" disabled={submitting} className="av-btn av-btn-primary av-btn-sm">
              {submitting ? 'Submitting…' : 'Submit'}
            </button>
            <button type="button" onClick={() => setShowForm(false)} className="av-btn av-btn-secondary av-btn-sm">Cancel</button>
          </div>
        </form>
      )}

      {refunds.length === 0 ? (
        <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: '28px 18px', textAlign: 'center' }}>
          <p style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: 0 }}>No refund requests yet.</p>
          {eligibleOrders.length === 0 && (
            <p style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 6 }}>Refunds are available for delivered orders.</p>
          )}
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {refunds.map(r => {
            const style = STATUS_STYLE[r.status] ?? STATUS_STYLE.pending;
            return (
              <div key={r.id} style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
                <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap' }}>
                  <div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                      <span style={{ fontFamily: 'var(--av-sans)', fontWeight: 500, fontSize: 13, color: 'var(--av-ink)' }}>#{r.order_number}</span>
                      <span style={{ fontSize: 10, letterSpacing: '0.08em', textTransform: 'uppercase', padding: '3px 7px', background: style.bg, color: style.color, border: r.status === 'pending' ? '1px solid var(--av-line)' : 'none', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>
                        {r.status}
                      </span>
                    </div>
                    <p style={{ fontSize: 13, marginTop: 8, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', textTransform: 'capitalize' }}>
                      {reasons[r.reason] ?? r.reason}
                    </p>
                    {r.details && <p style={{ fontSize: 12.5, marginTop: 4, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>{r.details}</p>}
                  </div>
                  <div style={{ textAlign: 'right', flexShrink: 0 }}>
                    {r.amount !== null && (
                      <p style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', margin: 0 }}>{fmt(r.amount)}</p>
                    )}
                    <p style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 4 }}>Requested {r.created_at}</p>
                    {r.resolved_at && <p style={{ fontSize: 11, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>Resolved {r.resolved_at}</p>}
                  </div>
                </div>

                {r.admin_note && (
                  <div style={{ marginTop: 12, padding: '10px 12px', background: 'var(--av-paper-2)', border: '1px solid var(--av-line-soft)', fontSize: 12.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', lineHeight: 1.5 }}>
                    <span style={{ fontWeight: 500, color: 'var(--av-ink)' }}>Note: </span>
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
