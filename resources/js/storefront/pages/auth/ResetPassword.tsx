import { Head, useForm, usePage } from '@inertiajs/react';
import Layout from '../../layouts/Layout';
import type { SharedProps } from '../../types';

export default function ResetPassword({ token, email }: { token: string; email: string }) {
  const { site } = usePage<SharedProps>().props;
  const { data, setData, post, processing, errors } = useForm({
    token,
    email,
    password: '',
    password_confirmation: '',
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/reset-password');
  }

  const inputStyle: React.CSSProperties = {
    width: '100%', height: 46, padding: '0 14px', border: '1px solid var(--av-line)',
    background: 'var(--av-paper)', borderRadius: 2, outline: 'none', fontSize: 14,
    color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', boxSizing: 'border-box',
    transition: 'border-color .2s, box-shadow .2s',
  };
  const labelStyle: React.CSSProperties = {
    fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase',
    color: 'var(--av-muted)', display: 'block', marginBottom: 8, fontFamily: 'var(--av-sans)',
  };

  return (
    <Layout>
      <Head title={`Set new password · ${site.name}`} />

      <section style={{ position: 'relative', overflow: 'hidden', display: 'grid', placeItems: 'center', padding: 'clamp(48px,8vw,96px) 16px', background: 'radial-gradient(ellipse 620px 420px at 82% 14%, var(--av-paper) 0%, transparent 62%), radial-gradient(ellipse 520px 380px at 12% 88%, var(--av-paper-2) 0%, transparent 66%), var(--av-ivory)' }}>
        <div style={{ position: 'absolute', inset: 0, zIndex: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', pointerEvents: 'none', overflow: 'hidden' }}>
          <span style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(160px,30vw,380px)', fontWeight: 600, color: 'rgba(31,26,21,.025)', letterSpacing: '-0.04em', userSelect: 'none', whiteSpace: 'nowrap', fontStyle: 'italic' }}>{site.name}</span>
        </div>

        <div style={{ width: '100%', maxWidth: 440, position: 'relative', zIndex: 1 }}>
          <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 4, padding: '40px 36px', boxShadow: '0 1px 2px rgba(31,26,21,.04), 0 24px 48px -24px rgba(31,26,21,.18)' }}>

            <div style={{ textAlign: 'center', marginBottom: 24 }}>
              <div style={{ fontSize: 10.5, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.34em', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 12 }}>Account</div>
              <h1 style={{ fontSize: 'clamp(24px,3.5vw,30px)', fontWeight: 400, letterSpacing: '-0.012em', margin: '0 0 6px', color: 'var(--av-ink)', fontFamily: 'var(--av-display)', lineHeight: 1.06 }}>Set new password</h1>
              <p style={{ color: 'var(--av-muted)', fontSize: 13.5, margin: 0, fontFamily: 'var(--av-sans)' }}>Choose a strong password for your account.</p>
            </div>

            {errors.email && (
              <div style={{ marginBottom: 16, padding: '11px 14px', borderRadius: 2, background: 'rgba(196,48,48,.07)', color: '#9c2b2b', border: '1px solid rgba(196,48,48,.22)', fontSize: 13, fontFamily: 'var(--av-sans)' }}>
                {errors.email}
              </div>
            )}

            <form onSubmit={submit} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
              <div>
                <label style={labelStyle}>Email</label>
                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                  placeholder="you@example.com" required
                  style={inputStyle}
                  onFocus={e => { e.currentTarget.style.borderColor = 'var(--av-ink)'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(149,97,58,.10)'; }}
                  onBlur={e => { e.currentTarget.style.borderColor = 'var(--av-line)'; e.currentTarget.style.boxShadow = 'none'; }} />
              </div>
              <div>
                <label style={labelStyle}>New password</label>
                <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)}
                  placeholder="Min. 8 characters" required minLength={8}
                  style={inputStyle}
                  onFocus={e => { e.currentTarget.style.borderColor = 'var(--av-ink)'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(149,97,58,.10)'; }}
                  onBlur={e => { e.currentTarget.style.borderColor = 'var(--av-line)'; e.currentTarget.style.boxShadow = 'none'; }} />
                {errors.password && <p style={{ fontSize: 11, color: '#9c2b2b', marginTop: 5, fontFamily: 'var(--av-sans)' }}>{errors.password}</p>}
              </div>
              <div>
                <label style={labelStyle}>Confirm password</label>
                <input type="password" value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  placeholder="••••••••" required
                  style={inputStyle}
                  onFocus={e => { e.currentTarget.style.borderColor = 'var(--av-ink)'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(149,97,58,.10)'; }}
                  onBlur={e => { e.currentTarget.style.borderColor = 'var(--av-line)'; e.currentTarget.style.boxShadow = 'none'; }} />
              </div>
              <button type="submit" disabled={processing}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 50, borderRadius: 2, background: processing ? 'var(--av-muted)' : 'var(--av-ink)', color: 'var(--av-paper)', fontWeight: 500, fontSize: 11.5, letterSpacing: '0.2em', textTransform: 'uppercase', border: 'none', cursor: processing ? 'not-allowed' : 'pointer', fontFamily: 'var(--av-sans)', marginTop: 4 }}>
                {processing ? 'Saving…' : 'Reset password'}
              </button>
            </form>
          </div>
        </div>
      </section>
    </Layout>
  );
}
