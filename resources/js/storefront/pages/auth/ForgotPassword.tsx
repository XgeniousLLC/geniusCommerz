import { Head, Link, useForm, usePage } from '@inertiajs/react';
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
    width: '100%', height: 38, padding: '0 12px', border: '1px solid #E6E8EE',
    background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14,
    color: '#0E1320', transition: 'border-color .15s, box-shadow .15s', boxSizing: 'border-box',
  };

  return (
    <>
      <Head title={`Reset password · ${site.name}`} />

      {/* Gradient background */}
      <div style={{
        position: 'fixed', inset: 0, zIndex: 0,
        background: 'radial-gradient(ellipse 600px 400px at 80% 20%, #EEF2FB 0%, transparent 60%), radial-gradient(ellipse 500px 360px at 15% 85%, #DDE4F4 0%, transparent 65%), #F2F4F9',
      }}/>

      {/* Shell */}
      <div style={{ position: 'relative', zIndex: 1, minHeight: '100vh', display: 'grid', placeItems: 'center', padding: '48px 16px' }}>
        <div style={{ width: '100%', maxWidth: 440 }}>

          {/* Back link */}
          <Link href="/login"
            style={{ display: 'inline-flex', alignItems: 'center', gap: 6, fontSize: 13, color: '#8A93A6', marginBottom: 24, textDecoration: 'none' }}
            className="hover:text-[#0E1320]">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
            </svg>
            Back to sign in
          </Link>

          {/* Card */}
          <div style={{ background: '#fff', border: '1px solid #E6E8EE', borderRadius: 16, padding: '36px 32px', boxShadow: '0 1px 2px rgba(14,19,32,.06), 0 8px 24px rgba(14,19,32,.06)' }}>

            {sent ? (
              /* ── Step 2: sent confirmation ── */
              <>
                <div style={{ textAlign: 'center', padding: '12px 0 20px' }}>
                  <div style={{ width: 64, height: 64, borderRadius: '50%', background: '#E6F5EE', color: '#0F8A5F', margin: '0 auto 12px', display: 'grid', placeItems: 'center' }}>
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                  <h2 style={{ fontSize: 20, fontWeight: 700, letterSpacing: '-0.01em', margin: '0 0 8px', color: '#0E1320' }}>Check your email</h2>
                  <p style={{ color: '#5A6478', fontSize: 13, margin: 0, lineHeight: 1.6 }}>
                    We've sent a password reset link to<br/>
                    <strong style={{ color: '#0E1320', fontWeight: 700 }}>{data.email}</strong>
                  </p>
                  {status && (
                    <p style={{ marginTop: 8, fontSize: 12, color: '#0F8A5F' }}>{status}</p>
                  )}
                </div>

                <Link href="/login"
                  style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 48, borderRadius: 10, background: '#0B1F4F', color: '#fff', fontWeight: 600, fontSize: 15, textDecoration: 'none', transition: 'background .15s' }}
                  className="hover:bg-[#102B6B]">
                  Back to sign in
                </Link>

                <p style={{ textAlign: 'center', fontSize: 12, color: '#8A93A6', marginTop: 16 }}>
                  Didn't receive it?{' '}
                  <button type="button" onClick={() => post('/forgot-password')}
                    style={{ color: '#0B1F4F', fontWeight: 600, background: 'none', border: 'none', cursor: 'pointer', fontSize: 12 }}
                    className="hover:underline">
                    Resend
                  </button>
                </p>
              </>
            ) : (
              /* ── Step 1: email form ── */
              <>
                {/* Lock icon */}
                <div style={{ width: 56, height: 56, borderRadius: 14, background: '#EEF2FB', color: '#0B1F4F', display: 'grid', placeItems: 'center', margin: '0 auto 16px' }}>
                  <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </div>

                <h1 style={{ fontSize: 22, fontWeight: 700, letterSpacing: '-0.01em', margin: '0 0 4px', textAlign: 'center', color: '#0E1320' }}>Forgot your password?</h1>
                <p style={{ color: '#8A93A6', fontSize: 13, textAlign: 'center', margin: '0 0 24px', lineHeight: 1.5 }}>
                  No worries — enter your email and we'll send you a reset link.
                </p>

                {errors.email && (
                  <div style={{ marginBottom: 14, padding: '10px 14px', borderRadius: 8, background: '#FCEDED', color: '#C43030', border: '1px solid #f5c6c6', fontSize: 13 }}>
                    {errors.email}
                  </div>
                )}

                <form onSubmit={submit}>
                  <div style={{ marginBottom: 20 }}>
                    <label style={{ fontSize: 12, fontWeight: 600, color: '#2A3142', display: 'block', marginBottom: 6 }}>Email address</label>
                    <input
                      type="email"
                      value={data.email}
                      onChange={e => setData('email', e.target.value)}
                      placeholder="you@example.com"
                      required
                      autoFocus
                      style={inputStyle}
                      onFocus={e => { e.currentTarget.style.borderColor = '#0B1F4F'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(11,31,79,.12)'; }}
                      onBlur={e => { e.currentTarget.style.borderColor = '#E6E8EE'; e.currentTarget.style.boxShadow = 'none'; }}
                    />
                  </div>
                  <button type="submit" disabled={processing}
                    style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 48, borderRadius: 10, background: processing ? '#8A93A6' : '#0B1F4F', color: '#fff', fontWeight: 600, fontSize: 15, border: 'none', cursor: processing ? 'not-allowed' : 'pointer', transition: 'background .15s' }}
                    className="hover:bg-[#102B6B]">
                    {processing ? 'Sending…' : 'Send reset link'}
                  </button>
                </form>
              </>
            )}

            <p style={{ textAlign: 'center', fontSize: 13, color: '#8A93A6', marginTop: 18 }}>
              {sent ? 'Back to ' : 'Remember your password? '}
              <Link href="/login" style={{ color: '#0B1F4F', fontWeight: 600, textDecoration: 'none' }} className="hover:underline">
                Sign in
              </Link>
            </p>
          </div>
        </div>
      </div>
    </>
  );
}
