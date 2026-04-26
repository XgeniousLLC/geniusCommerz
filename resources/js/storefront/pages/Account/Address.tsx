import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AccountLayout from '../../components/AccountLayout';

interface Address { id: number; label: string; name: string; phone: string; address: string; city: string; is_default: boolean }
interface Profile { name: string; email: string; phone: string | null }
interface Props { addresses: Address[]; profile: Profile }

const emptyForm = { label: 'Home', name: '', phone: '', address: '', city: '', is_default: false };

export default function AddressPage({ addresses, profile }: Props) {
  const [addrForm, setAddrForm] = useState<typeof emptyForm | null>(null);
  const [editId, setEditId]   = useState<number | null>(null);
  const [profForm, setProfForm] = useState({ name: profile.name, phone: profile.phone ?? '', password: '', password_confirmation: '' });
  const [saving, setSaving]   = useState(false);

  const openNew  = () => { setEditId(null); setAddrForm({ ...emptyForm }); };
  const openEdit = (a: Address) => {
    setEditId(a.id);
    setAddrForm({ label: a.label, name: a.name, phone: a.phone, address: a.address, city: a.city, is_default: a.is_default });
  };

  const saveAddress = (e: React.FormEvent) => {
    e.preventDefault();
    if (!addrForm) return;
    setSaving(true);
    if (editId) {
      router.put(`/account/address/${editId}`, addrForm, { onFinish: () => { setSaving(false); setAddrForm(null); } });
    } else {
      router.post('/account/address', addrForm, { onFinish: () => { setSaving(false); setAddrForm(null); } });
    }
  };

  const del = (id: number) => {
    if (!confirm('Remove this address?')) return;
    router.delete(`/account/address/${id}`);
  };

  const setDefault = (id: number) => {
    router.put(`/account/address/${id}`, { is_default: true, label: '', name: '', phone: '', address: '', city: '' });
  };

  const saveProfile = (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    router.post('/account/profile', profForm, { onFinish: () => setSaving(false) });
  };

  return (
    <AccountLayout title="Address & Profile" active="/account/address">
      <Head title="Address & Profile" />

      {/* Address Book */}
      <section className="mb-8">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-base font-semibold" style={{ color: 'var(--kb-ink)' }}>Saved Addresses</h2>
          <button onClick={openNew} className="kb-btn kb-btn-primary text-xs px-3 py-1.5">+ Add Address</button>
        </div>

        {addresses.length === 0 && !addrForm && (
          <div className="kb-card p-6 text-center">
            <p className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>No addresses saved yet.</p>
          </div>
        )}

        <div className="space-y-3">
          {addresses.map(a => (
            <div key={a.id} className="kb-card p-4 flex items-start justify-between gap-4">
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 flex-wrap">
                  <span className="text-xs font-semibold px-2 py-0.5 rounded" style={{ background: 'var(--kb-surface-2)', color: 'var(--kb-ink)' }}>{a.label}</span>
                  {a.is_default && <span className="text-xs px-2 py-0.5 rounded-full font-semibold" style={{ background: 'var(--kb-primary)', color: '#fff' }}>Default</span>}
                </div>
                <p className="text-sm font-medium mt-1.5" style={{ color: 'var(--kb-ink)' }}>{a.name}</p>
                <p className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>{a.phone}</p>
                <p className="text-sm mt-0.5" style={{ color: 'var(--kb-ink-soft)' }}>{a.address}, {a.city}</p>
              </div>
              <div className="flex flex-col gap-1 shrink-0">
                <button onClick={() => openEdit(a)} className="text-xs underline" style={{ color: 'var(--kb-primary)' }}>Edit</button>
                {!a.is_default && (
                  <button onClick={() => setDefault(a.id)} className="text-xs underline" style={{ color: 'var(--kb-ink-soft)' }}>Set default</button>
                )}
                <button onClick={() => del(a.id)} className="text-xs underline" style={{ color: 'var(--kb-danger)' }}>Remove</button>
              </div>
            </div>
          ))}
        </div>

        {/* Add / Edit form */}
        {addrForm && (
          <form onSubmit={saveAddress} className="kb-card p-5 mt-4 space-y-3">
            <h3 className="text-sm font-semibold" style={{ color: 'var(--kb-ink)' }}>{editId ? 'Edit Address' : 'New Address'}</h3>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Label</label>
                <input className="kb-input text-sm" value={addrForm.label}
                  onChange={e => setAddrForm(f => f ? { ...f, label: e.target.value } : f)} placeholder="e.g. Home, Work" />
              </div>
              <div>
                <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Full Name</label>
                <input className="kb-input text-sm" value={addrForm.name}
                  onChange={e => setAddrForm(f => f ? { ...f, name: e.target.value } : f)} placeholder="Recipient name" />
              </div>
              <div>
                <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Phone</label>
                <input className="kb-input text-sm" value={addrForm.phone}
                  onChange={e => setAddrForm(f => f ? { ...f, phone: e.target.value } : f)} placeholder="01XXXXXXXXX" />
              </div>
              <div>
                <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>City</label>
                <input className="kb-input text-sm" value={addrForm.city}
                  onChange={e => setAddrForm(f => f ? { ...f, city: e.target.value } : f)} placeholder="Dhaka" />
              </div>
            </div>
            <div>
              <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Address</label>
              <textarea className="kb-input text-sm resize-none" rows={2} value={addrForm.address}
                onChange={e => setAddrForm(f => f ? { ...f, address: e.target.value } : f)} placeholder="Street, area, house/flat number" />
            </div>
            <label className="flex items-center gap-2 text-sm cursor-pointer" style={{ color: 'var(--kb-ink)' }}>
              <input type="checkbox" checked={addrForm.is_default}
                onChange={e => setAddrForm(f => f ? { ...f, is_default: e.target.checked } : f)} />
              Set as default address
            </label>
            <div className="flex gap-2 pt-1">
              <button type="submit" disabled={saving} className="kb-btn kb-btn-primary text-sm px-4 py-2">
                {saving ? 'Saving…' : 'Save Address'}
              </button>
              <button type="button" onClick={() => setAddrForm(null)} className="text-sm" style={{ color: 'var(--kb-ink-soft)' }}>Cancel</button>
            </div>
          </form>
        )}
      </section>

      {/* Profile */}
      <section>
        <h2 className="text-base font-semibold mb-4" style={{ color: 'var(--kb-ink)' }}>Profile & Password</h2>
        <form onSubmit={saveProfile} className="kb-card p-5 space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Full Name</label>
              <input className="kb-input" value={profForm.name}
                onChange={e => setProfForm(f => ({ ...f, name: e.target.value }))} />
            </div>
            <div>
              <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Phone</label>
              <input className="kb-input" value={profForm.phone}
                onChange={e => setProfForm(f => ({ ...f, phone: e.target.value }))} placeholder="01XXXXXXXXX" />
            </div>
            <div className="sm:col-span-2">
              <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Email (read-only)</label>
              <input className="kb-input opacity-60" value={profile.email} readOnly />
            </div>
          </div>

          <div className="border-t pt-4" style={{ borderColor: 'var(--kb-border)' }}>
            <p className="text-xs font-semibold mb-3" style={{ color: 'var(--kb-ink-soft)' }}>Change Password (leave blank to keep current)</p>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>New Password</label>
                <input type="password" className="kb-input" value={profForm.password}
                  onChange={e => setProfForm(f => ({ ...f, password: e.target.value }))} placeholder="Min 8 characters" />
              </div>
              <div>
                <label className="text-xs font-medium block mb-1" style={{ color: 'var(--kb-ink-soft)' }}>Confirm Password</label>
                <input type="password" className="kb-input" value={profForm.password_confirmation}
                  onChange={e => setProfForm(f => ({ ...f, password_confirmation: e.target.value }))} />
              </div>
            </div>
          </div>

          <button type="submit" disabled={saving} className="kb-btn kb-btn-primary text-sm px-4 py-2">
            {saving ? 'Saving…' : 'Save Changes'}
          </button>
        </form>
      </section>
    </AccountLayout>
  );
}
