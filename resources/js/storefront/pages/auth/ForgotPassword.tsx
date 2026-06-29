import { Head, Link, useForm, usePage } from '@inertiajs/react';
import Layout from '../../layouts/Layout';
import type { SharedProps } from '../../types';

export default function ForgotPassword({ status }: { status?: string }) {
  const { site } = usePage<SharedProps>().props;
  const { data, setData, post, processing, errors } = useForm({ email: '' });

  const sent = !!status;

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/forgot-password');
  }

  const inputStyle: React.CSSProperties = {
    width: '100%', height: 46, padding: '0 14px', border: '1px solid var(--av-line)',
    background: 'var(--av-paper)', borderRadius: 2, outline: 'none', fontSize: 14,
    color: 'var(--av-ink)', fontFamily: 'var(--av-sans)',
    transition: 'border-color .2s, box-shadow .2s', boxSizing: 'border-box',
  };

  return (
    <Layout>
      <Head title={`Reset password · ${site.name}`} />

      {/* Editorial shell */}
      <section style={{ position: 'relative', overflow: 'hidden', display: 'grid', placeItems: 'center', padding: 'clamp(48px,8vw,96px) 16px', background: 'radial-gradient(ellipse 620px 420px at 82% 14%, var(--av-paper) 0%, transparent 62%), radial-gradient(ellipse 520px 380px at 12% 88%, var(--av-paper-2) 0%, transparent 66%), var(--av-ivory)' }}>
        <div style={{ position: 'absolute', inset: 0, zIndex: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', pointerEvents: 'none', overflow: 'hidden' }}>
          <span style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(180px,32vw,420px)', fontWeight: 600, color: 'rgba(31,26,21,.025)', letterSpacing: '-0.04em', userSelect: 'none', whiteSpace: 'nowrap', fontStyle: 'italic' }}>
          {site.name}
        </span>
        </div>

        <div style={{ width: '100%', maxWidth: 440, position: 'relative', zIndex: 1 }}>

          {/* Card */}
          <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 4, padding: '44px 40px', boxShadow: '0 1px 2px rgba(31,26,21,.04), 0 24px 48px -24px rgba(31,26,21,.18)' }}>

            {sent ? (
              /* ── Step 2: sent confirmation ── */
              <>
                <div style={{ textAlign: 'center', padding: '12px 0 22px' }}>
                  <div style={{ width: 64, height: 64, borderRadius: '50%', background: 'rgba(15,138,95,.10)', color: '#0F8A5F', margin: '0 auto 16px', display: 'grid', placeItems: 'center' }}>
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                  <h2 style={{ fontSize: 'clamp(24px,3.5vw,32px)', fontWeight: 400, letterSpacing: '-0.012em', margin: '0 0 10px', color: 'var(--av-ink)', fontFamily: 'var(--av-display)', lineHeight: 1.06 }}>Check your email</h2>
                  <p style={{ color: 'var(--av-muted)', fontSize: 13.5, margin: 0, lineHeight: 1.7, fontFamily: 'var(--av-sans)' }}>
                    We've sent a password reset link to<br/>
                    <strong style={{ color: 'var(--av-ink)', fontWeight: 600 }}>{data.email}</strong>
                  </p>
                  {status && (
                    <p style={{ marginTop: 10, fontSize: 12.5, color: '#0F8A5F', fontFamily: 'var(--av-sans)' }}>{status}</p>
                  )}
                </div>

                <Link href="/login"
                  style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 50, borderRadius: 2, background: 'var(--av-ink)', color: 'var(--av-paper)', fontWeight: 500, fontSize: 11.5, letterSpacing: '0.2em', textTransform: 'uppercase', textDecoration: 'none', fontFamily: 'var(--av-sans)', transition: 'background .2s' }}>
                  Back to sign in
                </Link>

                <p style={{ textAlign: 'center', fontSize: 12.5, color: 'var(--av-muted)', marginTop: 18, fontFamily: 'var(--av-sans)' }}>
                  Didn't receive it?{' '}
                  <button type="button" onClick={() => post('/forgot-password')}
                    style={{ color: 'var(--av-cognac)', fontWeight: 500, background: 'none', border: 'none', cursor: 'pointer', fontSize: 12.5, fontFamily: 'var(--av-sans)' }}
                    className="hover:underline">
                    Resend
                  </button>
                </p>
              </>
            ) : (
              /* ── Step 1: email form ── */
              <>
                {/* Lock icon */}
                <div style={{ width: 58, height: 58, borderRadius: 2, background: 'var(--av-paper-2)', color: 'var(--av-cognac)', display: 'grid', placeItems: 'center', margin: '0 auto 18px' }}>
                  <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" strokeWidth={1.6} strokeLinecap="round" strokeLinejoin="round"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" strokeWidth={1.6} strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </div>

                <div style={{ textAlign: 'center', marginBottom: 26 }}>
                  <div style={{ fontSize: 10.5, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.34em', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 12 }}>Account Recovery</div>
                  <h1 style={{ fontSize: 'clamp(26px,3.8vw,34px)', fontWeight: 400, letterSpacing: '-0.012em', margin: '0 0 8px', color: 'var(--av-ink)', fontFamily: 'var(--av-display)', lineHeight: 1.06 }}>Forgot your password?</h1>
                  <p style={{ color: 'var(--av-muted)', fontSize: 13.5, margin: 0, lineHeight: 1.6, fontFamily: 'var(--av-sans)' }}>
                    No worries — enter your email and we'll send you a reset link.
                  </p>
                </div>

                {errors.email && (
                  <div style={{ marginBottom: 16, padding: '11px 14px', borderRadius: 2, background: 'rgba(196,48,48,.07)', color: '#9c2b2b', border: '1px solid rgba(196,48,48,.22)', fontSize: 13, fontFamily: 'var(--av-sans)' }}>
                    {errors.email}
                  </div>
                )}

                <form onSubmit={submit}>
                  <div style={{ marginBottom: 20 }}>
                    <label style={{ fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase', color: 'var(--av-muted)', display: 'block', marginBottom: 8, fontFamily: 'var(--av-sans)' }}>Email address</label>
                    <input
                      type="email"
                      value={data.email}
                      onChange={e => setData('email', e.target.value)}
                      placeholder="you@example.com"
                      required
                      autoFocus
                      style={inputStyle}
                      onFocus={e => { e.currentTarget.style.borderColor = 'var(--av-ink)'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(149,97,58,.10)'; }}
                      onBlur={e => { e.currentTarget.style.borderColor = 'var(--av-line)'; e.currentTarget.style.boxShadow = 'none'; }}
                    />
                  </div>
                  <button type="submit" disabled={processing}
                    style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 50, borderRadius: 2, background: processing ? 'var(--av-muted)' : 'var(--av-ink)', color: 'var(--av-paper)', fontWeight: 500, fontSize: 11.5, letterSpacing: '0.2em', textTransform: 'uppercase', border: 'none', cursor: processing ? 'not-allowed' : 'pointer', fontFamily: 'var(--av-sans)', transition: 'background .2s' }}>
                    {processing ? 'Sending…' : 'Send reset link'}
                  </button>
                </form>
              </>
            )}

            <p style={{ textAlign: 'center', fontSize: 13, color: 'var(--av-muted)', marginTop: 22, fontFamily: 'var(--av-sans)' }}>
              {sent ? 'Back to ' : 'Remember your password? '}
              <Link href="/login" style={{ color: 'var(--av-cognac)', fontWeight: 500, textDecoration: 'none' }} className="hover:underline">
                Sign in
              </Link>
            </p>
          </div>
        </div>
      </section>
    </Layout>
  );
}
