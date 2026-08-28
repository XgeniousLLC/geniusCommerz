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

  const Lbl = ({ children }: { children: React.ReactNode }) => (
    <label style={{ display: 'block', fontSize: 10.5, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 6 }}>{children}</label>
  );

  return (
    <AccountLayout title="Addresses & Profile" active="/account/address">
      <Head title="Addresses & Profile" />

      <section style={{ marginBottom: 32 }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 }}>
          <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 18, fontWeight: 400, color: 'var(--av-ink)', margin: 0 }}>Saved addresses</h2>
          <button onClick={openNew} className="av-btn av-btn-primary av-btn-sm">+ Add</button>
        </div>

        {addresses.length === 0 && !addrForm && (
          <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 24, textAlign: 'center' }}>
            <p style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)' }}>No addresses saved yet.</p>
          </div>
        )}

        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
          {addresses.map(a => (
            <div key={a.id} style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 16, display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16 }}>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap', marginBottom: 8 }}>
                  <span style={{ fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase', padding: '3px 7px', background: 'var(--av-paper-2)', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', border: '1px solid var(--av-line-soft)' }}>{a.label}</span>
                  {a.is_default && <span style={{ fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase', padding: '3px 7px', background: 'var(--av-ink)', color: 'var(--av-paper)', fontFamily: 'var(--av-sans)' }}>Default</span>}
                </div>
                <p style={{ fontSize: 13.5, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', margin: 0 }}>{a.name}</p>
                <p style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: '2px 0 0' }}>{a.phone}</p>
                <p style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: '4px 0 0' }}>{a.address}, {a.city}</p>
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 6, flexShrink: 0 }}>
                <button onClick={() => openEdit(a)} style={{ fontSize: 11.5, color: 'var(--av-ink)', background: 'transparent', border: 'none', cursor: 'pointer', textDecoration: 'underline', textDecorationColor: 'var(--av-line)', fontFamily: 'var(--av-sans)' }}>Edit</button>
                {!a.is_default && (
                  <button onClick={() => setDefault(a.id)} style={{ fontSize: 11.5, color: 'var(--av-muted)', background: 'transparent', border: 'none', cursor: 'pointer', textDecoration: 'underline', textDecorationColor: 'var(--av-line)', fontFamily: 'var(--av-sans)' }}>Default</button>
                )}
                <button onClick={() => del(a.id)} style={{ fontSize: 11.5, color: '#b94040', background: 'transparent', border: 'none', cursor: 'pointer', textDecoration: 'underline', fontFamily: 'var(--av-sans)' }}>Remove</button>
              </div>
            </div>
          ))}
        </div>

        {addrForm && (
          <form onSubmit={saveAddress} style={{ border: '1px solid var(--av-line)', background: 'var(--av-paper)', padding: 20, marginTop: 16, display: 'flex', flexDirection: 'column', gap: 14 }}>
            <h3 style={{ fontSize: 12, letterSpacing: '0.14em', textTransform: 'uppercase', color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: 0 }}>{editId ? 'Edit address' : 'New address'}</h3>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }} className="av-addr-grid">
              <div>
                <Lbl>Label</Lbl>
                <input className="av-input" value={addrForm.label}
                  onChange={e => setAddrForm(f => f ? { ...f, label: e.target.value } : f)} placeholder="Home, Work" />
              </div>
              <div>
                <Lbl>Full Name</Lbl>
                <input className="av-input" value={addrForm.name}
                  onChange={e => setAddrForm(f => f ? { ...f, name: e.target.value } : f)} placeholder="Recipient" />
              </div>
              <div>
                <Lbl>Phone</Lbl>
                <input className="av-input" value={addrForm.phone}
                  onChange={e => setAddrForm(f => f ? { ...f, phone: e.target.value } : f)} placeholder="01XXXXXXXXX" />
              </div>
              <div>
                <Lbl>City</Lbl>
                <input className="av-input" value={addrForm.city}
                  onChange={e => setAddrForm(f => f ? { ...f, city: e.target.value } : f)} placeholder="Dhaka" />
              </div>
            </div>
            <div>
              <Lbl>Address</Lbl>
              <textarea className="av-input av-textarea" rows={2} value={addrForm.address}
                onChange={e => setAddrForm(f => f ? { ...f, address: e.target.value } : f)} placeholder="Street, area, house/flat" />
            </div>
            <label style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', cursor: 'pointer' }}>
              <input type="checkbox" checked={addrForm.is_default}
                onChange={e => setAddrForm(f => f ? { ...f, is_default: e.target.checked } : f)} style={{ accentColor: 'var(--av-ink)' }} />
              Set as default
            </label>
            <div style={{ display: 'flex', gap: 8, paddingTop: 4 }}>
              <button type="submit" disabled={saving} className="av-btn av-btn-primary av-btn-sm">
                {saving ? 'Saving…' : 'Save'}
              </button>
              <button type="button" onClick={() => setAddrForm(null)} className="av-btn av-btn-secondary av-btn-sm">Cancel</button>
            </div>
          </form>
        )}
      </section>

      <section>
        <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 18, fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 16px' }}>Profile & password</h2>
        <form onSubmit={saveProfile} style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 20, display: 'flex', flexDirection: 'column', gap: 16 }}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }} className="av-addr-grid">
            <div>
              <Lbl>Full Name</Lbl>
              <input className="av-input" value={profForm.name}
                onChange={e => setProfForm(f => ({ ...f, name: e.target.value }))} />
            </div>
            <div>
              <Lbl>Phone</Lbl>
              <input className="av-input" value={profForm.phone}
                onChange={e => setProfForm(f => ({ ...f, phone: e.target.value }))} placeholder="01XXXXXXXXX" />
            </div>
            <div style={{ gridColumn: '1/-1' }}>
              <Lbl>Email (read-only)</Lbl>
              <input className="av-input" value={profile.email} readOnly style={{ opacity: 0.6 }} />
            </div>
          </div>

          <div style={{ borderTop: '1px solid var(--av-line-soft)', paddingTop: 16 }}>
            <p style={{ fontSize: 10.5, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontWeight: 500, margin: '0 0 12px' }}>Change password (leave blank to keep)</p>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }} className="av-addr-grid">
              <div>
                <Lbl>New Password</Lbl>
                <input type="password" className="av-input" value={profForm.password}
                  onChange={e => setProfForm(f => ({ ...f, password: e.target.value }))} placeholder="Min 8 characters" />
              </div>
              <div>
                <Lbl>Confirm Password</Lbl>
                <input type="password" className="av-input" value={profForm.password_confirmation}
                  onChange={e => setProfForm(f => ({ ...f, password_confirmation: e.target.value }))} />
              </div>
            </div>
          </div>

          <button type="submit" disabled={saving} className="av-btn av-btn-primary av-btn-sm" style={{ alignSelf: 'flex-start' }}>
            {saving ? 'Saving…' : 'Save changes'}
          </button>
        </form>
      </section>

      <style>{`@media(max-width:640px){ .av-addr-grid{ grid-template-columns: 1fr !important; } }`}</style>
    </AccountLayout>
  );
}
