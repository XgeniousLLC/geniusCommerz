import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import type { SharedProps } from '../../types';

interface Props {
  loginMethod?: 'email_password' | 'phone_otp' | 'both';
}

function EyeIcon({ open }: { open: boolean }) {
  return open ? (
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
  ) : (
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
    </svg>
  );
}

function EmailPasswordForm() {
  const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: false });
  const [showPwd, setShowPwd] = useState(false);

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/login');
  }

  return (
    <form onSubmit={submit}>
      {errors.email && (
        <div style={{ marginBottom: 14, padding: '10px 14px', borderRadius: 8, background: '#FCEDED', color: '#C43030', border: '1px solid #f5c6c6', fontSize: 13 }}>
          {errors.email}
        </div>
      )}

      {/* Email */}
      <div style={{ marginBottom: 14 }}>
        <label style={{ fontSize: 12, fontWeight: 600, color: '#2A3142', display: 'block', marginBottom: 6 }}>Email</label>
        <input
          type="email"
          value={data.email}
          onChange={e => setData('email', e.target.value)}
          placeholder="you@example.com"
          required
          autoFocus
          style={{ width: '100%', height: 38, padding: '0 12px', border: '1px solid #E6E8EE', background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14, color: '#0E1320', transition: 'border-color .15s, box-shadow .15s', boxSizing: 'border-box' }}
          onFocus={e => { e.currentTarget.style.borderColor = '#0B1F4F'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(11,31,79,.12)'; }}
          onBlur={e => { e.currentTarget.style.borderColor = '#E6E8EE'; e.currentTarget.style.boxShadow = 'none'; }}
        />
      </div>

      {/* Password */}
      <div style={{ marginBottom: 14 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 }}>
          <label style={{ fontSize: 12, fontWeight: 600, color: '#2A3142' }}>Password</label>
          <Link href="/forgot-password" style={{ fontSize: 12, color: '#0B1F4F', fontWeight: 600, textDecoration: 'none' }}
            className="hover:underline">
            Forgot?
          </Link>
        </div>
        <div style={{ position: 'relative' }}>
          <input
            type={showPwd ? 'text' : 'password'}
            value={data.password}
            onChange={e => setData('password', e.target.value)}
            placeholder="••••••••"
            required
            style={{ width: '100%', height: 38, padding: '0 38px 0 12px', border: '1px solid #E6E8EE', background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14, color: '#0E1320', transition: 'border-color .15s, box-shadow .15s', boxSizing: 'border-box' }}
            onFocus={e => { e.currentTarget.style.borderColor = '#0B1F4F'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(11,31,79,.12)'; }}
            onBlur={e => { e.currentTarget.style.borderColor = '#E6E8EE'; e.currentTarget.style.boxShadow = 'none'; }}
          />
          <button type="button" onClick={() => setShowPwd(v => !v)}
            style={{ position: 'absolute', right: 10, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', color: '#8A93A6', padding: 4, cursor: 'pointer', display: 'grid', placeItems: 'center' }}>
            <EyeIcon open={showPwd} />
          </button>
        </div>
        {errors.password && <div style={{ fontSize: 11, color: '#C43030', marginTop: 4 }}>{errors.password}</div>}
      </div>

      {/* Remember */}
      <label style={{ display: 'inline-flex', alignItems: 'center', gap: 8, fontSize: 13, color: '#2A3142', cursor: 'pointer', marginBottom: 18 }}>
        <input type="checkbox" checked={data.remember} onChange={e => setData('remember', e.target.checked)}
          style={{ width: 16, height: 16, accentColor: '#0B1F4F' }}/>
        Keep me signed in for 30 days
      </label>

      <button type="submit" disabled={processing}
        style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 48, borderRadius: 10, background: processing ? '#5A6478' : '#0B1F4F', color: '#fff', fontWeight: 600, fontSize: 15, border: 'none', cursor: processing ? 'not-allowed' : 'pointer', transition: 'background .15s' }}
        className="hover:bg-[#102B6B]">
        {processing ? 'Signing in…' : 'Sign in'}
      </button>
    </form>
  );
}

function PhoneOtpForm() {
  const [phone, setPhone]   = useState('');
  const [step, setStep]     = useState<'phone' | 'otp'>('phone');
  const [otp, setOtp]       = useState(['', '', '', '', '', '']);
  const [loading, setLoading] = useState(false);
  const [error, setError]   = useState('');
  const [info, setInfo]     = useState('');
  const otpRefs = useRef<(HTMLInputElement | null)[]>([]);

  const csrfToken = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

  function handleOtpChange(i: number, val: string) {
    if (!/^\d?$/.test(val)) return;
    const next = [...otp];
    next[i] = val;
    setOtp(next);
    if (val && i < 5) otpRefs.current[i + 1]?.focus();
  }

  function handleOtpKeyDown(i: number, e: React.KeyboardEvent) {
    if (e.key === 'Backspace' && !otp[i] && i > 0) otpRefs.current[i - 1]?.focus();
  }

  async function handleSend(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true); setError('');
    try {
      const res = await fetch('/login/otp/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ phone }),
      });
      const json = await res.json();
      if (!res.ok) setError(json.message ?? 'Failed to send OTP.');
      else { setInfo(json.message); setStep('otp'); }
    } catch { setError('Network error. Please try again.'); }
    finally { setLoading(false); }
  }

  async function handleVerify(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true); setError('');
    try {
      const res = await fetch('/login/otp/verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ phone, otp: otp.join('') }),
      });
      const json = await res.json();
      if (!res.ok) setError(json.message ?? 'Invalid OTP.');
      else window.location.href = json.redirect ?? '/';
    } catch { setError('Network error. Please try again.'); }
    finally { setLoading(false); }
  }

  const inputStyle: React.CSSProperties = {
    width: '100%', height: 38, padding: '0 12px', border: '1px solid #E6E8EE',
    background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14,
    color: '#0E1320', transition: 'border-color .15s, box-shadow .15s', boxSizing: 'border-box',
  };

  return (
    <>
      {error && (
        <div style={{ marginBottom: 14, padding: '10px 14px', borderRadius: 8, background: '#FCEDED', color: '#C43030', border: '1px solid #f5c6c6', fontSize: 13 }}>
          {error}
        </div>
      )}
      {info && step === 'otp' && (
        <div style={{ marginBottom: 14, padding: '10px 14px', borderRadius: 8, background: '#E6F5EE', color: '#0F8A5F', border: '1px solid #A3D9C0', fontSize: 13 }}>
          {info}
        </div>
      )}

      {step === 'phone' ? (
        <form onSubmit={handleSend}>
          <div style={{ marginBottom: 14 }}>
            <label style={{ fontSize: 12, fontWeight: 600, color: '#2A3142', display: 'block', marginBottom: 6 }}>Phone number</label>
            <div style={{ display: 'flex', gap: 8 }}>
              <select style={{ width: 110, height: 38, padding: '0 8px', border: '1px solid #E6E8EE', background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14, color: '#0E1320' }}>
                <option>🇧🇩 +880</option>
              </select>
              <input type="tel" value={phone} onChange={e => setPhone(e.target.value)}
                placeholder="1712 345 678" required autoFocus style={{ ...inputStyle, flex: 1 }}
                onFocus={e => { e.currentTarget.style.borderColor = '#0B1F4F'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(11,31,79,.12)'; }}
                onBlur={e => { e.currentTarget.style.borderColor = '#E6E8EE'; e.currentTarget.style.boxShadow = 'none'; }}
              />
            </div>
            <div style={{ fontSize: 11, color: '#8A93A6', marginTop: 4 }}>We'll send a 6-digit code to verify.</div>
          </div>
          <button type="submit" disabled={loading}
            style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 48, borderRadius: 10, background: loading ? '#5A6478' : '#0B1F4F', color: '#fff', fontWeight: 600, fontSize: 15, border: 'none', cursor: loading ? 'not-allowed' : 'pointer' }}>
            {loading ? 'Sending…' : 'Send OTP'}
          </button>
        </form>
      ) : (
        <form onSubmit={handleVerify}>
          <div style={{ marginBottom: 14 }}>
            <label style={{ fontSize: 12, fontWeight: 600, color: '#2A3142', display: 'block', marginBottom: 6 }}>Enter the 6-digit code</label>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(6, 1fr)', gap: 8 }}>
              {otp.map((v, i) => (
                <input key={i}
                  ref={el => { otpRefs.current[i] = el; }}
                  type="text" inputMode="numeric" maxLength={1} value={v}
                  onChange={e => handleOtpChange(i, e.target.value)}
                  onKeyDown={e => handleOtpKeyDown(i, e)}
                  style={{ height: 52, textAlign: 'center', fontSize: 20, fontWeight: 700, border: '1px solid #E6E8EE', borderRadius: 10, background: '#fff', outline: 'none', transition: 'border-color .15s, box-shadow .15s' }}
                  onFocus={e => { e.currentTarget.style.borderColor = '#0B1F4F'; e.currentTarget.style.boxShadow = '0 0 0 3px rgba(11,31,79,.12)'; }}
                  onBlur={e => { e.currentTarget.style.borderColor = '#E6E8EE'; e.currentTarget.style.boxShadow = 'none'; }}
                />
              ))}
            </div>
            <div style={{ fontSize: 11, color: '#8A93A6', marginTop: 6 }}>
              Sent to +880 {phone} ·{' '}
              <button type="button" style={{ color: '#0B1F4F', fontWeight: 600, background: 'none', border: 'none', cursor: 'pointer', fontSize: 11 }}>Resend</button>
            </div>
          </div>
          <button type="submit" disabled={loading || otp.join('').length < 6}
            style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 48, borderRadius: 10, background: (loading || otp.join('').length < 6) ? '#5A6478' : '#0B1F4F', color: '#fff', fontWeight: 600, fontSize: 15, border: 'none', cursor: (loading || otp.join('').length < 6) ? 'not-allowed' : 'pointer' }}>
            {loading ? 'Verifying…' : 'Verify & Continue'}
          </button>
          <button type="button" onClick={() => { setStep('phone'); setOtp(['', '', '', '', '', '']); setError(''); }}
            style={{ display: 'block', width: '100%', textAlign: 'center', marginTop: 10, fontSize: 13, color: '#8A93A6', background: 'none', border: 'none', cursor: 'pointer' }}
            className="hover:text-[#0E1320]">
            Change phone number
          </button>
        </form>
      )}
    </>
  );
}

export default function Login({ loginMethod = 'email_password' }: Props) {
  const { site } = usePage<SharedProps>().props;
  const [method, setMethod] = useState<'email' | 'phone'>(
    loginMethod === 'phone_otp' ? 'phone' : 'email'
  );

  const showToggle = loginMethod === 'both';

  return (
    <>
      <Head title={`Sign in · ${site.name}`} />

      {/* Gradient background */}
      <div style={{
        position: 'fixed', inset: 0, zIndex: 0,
        background: 'radial-gradient(ellipse 600px 400px at 80% 20%, #EEF2FB 0%, transparent 60%), radial-gradient(ellipse 500px 360px at 15% 85%, #DDE4F4 0%, transparent 65%), #F2F4F9',
      }}/>

      {/* Shell */}
      <div style={{ position: 'relative', zIndex: 1, minHeight: '100vh', display: 'grid', placeItems: 'center', padding: '48px 16px' }}>
        <div style={{ width: '100%', maxWidth: 440 }}>

          {/* Back link */}
          <Link href="/"
            style={{ display: 'inline-flex', alignItems: 'center', gap: 6, fontSize: 13, color: '#8A93A6', marginBottom: 24, textDecoration: 'none' }}
            className="hover:text-[#0E1320]">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
            </svg>
            Back to {site.name}
          </Link>

          {/* Card */}
          <div style={{ background: '#fff', border: '1px solid #E6E8EE', borderRadius: 16, padding: '36px 32px', boxShadow: '0 1px 2px rgba(14,19,32,.06), 0 8px 24px rgba(14,19,32,.06)' }}>

            {/* Logo */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, marginBottom: 8 }}>
              <div style={{ width: 36, height: 36, borderRadius: 10, background: '#0B1F4F', display: 'grid', placeItems: 'center', color: '#fff', fontWeight: 800, fontSize: 18, letterSpacing: '-0.02em', flexShrink: 0 }}>
                {site.name.slice(0, 1).toUpperCase()}
              </div>
              <span style={{ fontWeight: 700, fontSize: 20, letterSpacing: '-0.01em', color: '#0E1320' }}>{site.name}</span>
            </div>

            <h1 style={{ fontSize: 22, fontWeight: 700, letterSpacing: '-0.01em', margin: '8px 0 4px', textAlign: 'center', color: '#0E1320' }}>Welcome back</h1>
            <p style={{ color: '#8A93A6', fontSize: 13, textAlign: 'center', margin: '0 0 24px' }}>Sign in to your account to continue</p>

            {/* Auth tabs */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', background: '#F2F4F9', borderRadius: 10, padding: 4, marginBottom: 20 }}>
              <Link href="/login"
                style={{ textAlign: 'center', padding: '8px 0', fontSize: 13, fontWeight: 600, borderRadius: 8, background: '#fff', color: '#0E1320', boxShadow: '0 1px 0 rgba(14,19,32,.04), 0 1px 2px rgba(14,19,32,.04)', textDecoration: 'none' }}>
                Sign in
              </Link>
              <Link href="/register"
                style={{ textAlign: 'center', padding: '8px 0', fontSize: 13, fontWeight: 600, borderRadius: 8, color: '#8A93A6', textDecoration: 'none' }}
                className="hover:text-[#0E1320]">
                Create account
              </Link>
            </div>

            {/* Method toggle */}
            {showToggle && (
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 4, background: '#F2F4F9', borderRadius: 10, padding: 4, marginBottom: 16 }}>
                {(['email', 'phone'] as const).map(m => (
                  <button key={m} type="button" onClick={() => setMethod(m)}
                    style={{ border: 'none', padding: '8px 0', fontSize: 12, fontWeight: 600, borderRadius: 8, cursor: 'pointer', transition: 'background .15s, color .15s', background: method === m ? '#fff' : 'transparent', color: method === m ? '#0E1320' : '#8A93A6', boxShadow: method === m ? '0 1px 0 rgba(14,19,32,.04), 0 1px 2px rgba(14,19,32,.04)' : 'none' }}>
                    {m === 'email' ? 'Email & Password' : 'Phone OTP'}
                  </button>
                ))}
              </div>
            )}

            {/* Form */}
            {(loginMethod === 'email_password' || (showToggle && method === 'email'))
              ? <EmailPasswordForm />
              : <PhoneOtpForm />
            }

            {/* Divider */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, color: '#8A93A6', fontSize: 11, fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', margin: '20px 0' }}>
              <div style={{ flex: 1, height: 1, background: '#E6E8EE' }}/>
              Or continue with
              <div style={{ flex: 1, height: 1, background: '#E6E8EE' }}/>
            </div>

            {/* Social buttons */}
            <div style={{ display: 'grid', gap: 8 }}>
              <a href="/auth/google"
                style={{ height: 42, border: '1px solid #E6E8EE', background: '#fff', color: '#2A3142', fontWeight: 600, fontSize: 13, borderRadius: 10, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, textDecoration: 'none', transition: 'border-color .15s' }}
                className="hover:border-[#2A3142]">
                <svg width="18" height="18" viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
                  <path fill="#FBBC05" d="M5.84 14.09a6.6 6.6 0 0 1 0-4.18V7.07H2.18a11 11 0 0 0 0 9.86l3.66-2.84z"/>
                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                </svg>
                Continue with Google
              </a>
              <a href="/auth/facebook"
                style={{ height: 42, border: '1px solid #E6E8EE', background: '#fff', color: '#2A3142', fontWeight: 600, fontSize: 13, borderRadius: 10, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, textDecoration: 'none', transition: 'border-color .15s' }}
                className="hover:border-[#2A3142]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
                  <path d="M22 12a10 10 0 1 0-11.6 9.9V14.9H8V12h2.4V9.8c0-2.4 1.4-3.8 3.6-3.8 1 0 2.1.2 2.1.2v2.4h-1.2c-1.2 0-1.5.7-1.5 1.5V12h2.7l-.4 2.9h-2.2v6.9A10 10 0 0 0 22 12Z"/>
                </svg>
                Continue with Facebook
              </a>
            </div>

            <p style={{ textAlign: 'center', fontSize: 13, color: '#8A93A6', marginTop: 18 }}>
              Don't have an account?{' '}
              <Link href="/register" style={{ color: '#0B1F4F', fontWeight: 600, textDecoration: 'none' }} className="hover:underline">
                Create one
              </Link>
            </p>
          </div>
        </div>
      </div>
    </>
  );
}
