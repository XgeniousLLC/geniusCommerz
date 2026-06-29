import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import type { SharedProps } from '../../types';

interface Props {
  loginMethod?: 'email_password' | 'phone_otp' | 'both';
}

/* ── Shared editorial (av-*) styles ─────────────────────────────────────── */
const labelStyle: React.CSSProperties = {
  fontSize: 10.5, fontWeight: 500, letterSpacing: '0.18em', textTransform: 'uppercase',
  color: 'var(--av-muted)', display: 'block', marginBottom: 8, fontFamily: 'var(--av-sans)',
};
const inputStyle: React.CSSProperties = {
  width: '100%', height: 46, padding: '0 14px', border: '1px solid var(--av-line)',
  background: 'var(--av-paper)', borderRadius: 2, outline: 'none', fontSize: 14,
  color: 'var(--av-ink)', fontFamily: 'var(--av-sans)',
  transition: 'border-color .2s, box-shadow .2s', boxSizing: 'border-box',
};
function focusOn(e: React.FocusEvent<HTMLInputElement | HTMLSelectElement>) {
  e.currentTarget.style.borderColor = 'var(--av-ink)';
  e.currentTarget.style.boxShadow = '0 0 0 3px rgba(149,97,58,.10)';
}
function focusOff(e: React.FocusEvent<HTMLInputElement | HTMLSelectElement>) {
  e.currentTarget.style.borderColor = 'var(--av-line)';
  e.currentTarget.style.boxShadow = 'none';
}
const btnPrimary: React.CSSProperties = {
  display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 50,
  borderRadius: 2, background: 'var(--av-ink)', color: 'var(--av-paper)', fontWeight: 500,
  fontSize: 11.5, letterSpacing: '0.2em', textTransform: 'uppercase', border: 'none',
  cursor: 'pointer', fontFamily: 'var(--av-sans)', transition: 'background .2s',
};
const errorBox: React.CSSProperties = {
  marginBottom: 16, padding: '11px 14px', borderRadius: 2, background: 'rgba(196,48,48,.07)',
  color: '#9c2b2b', border: '1px solid rgba(196,48,48,.22)', fontSize: 13, fontFamily: 'var(--av-sans)',
};

function EyeIcon({ open }: { open: boolean }) {
  return open ? (
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
  ) : (
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
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
      {errors.email && <div style={errorBox}>{errors.email}</div>}

      {/* Email */}
      <div style={{ marginBottom: 16 }}>
        <label style={labelStyle}>Email</label>
        <input
          type="email"
          value={data.email}
          onChange={e => setData('email', e.target.value)}
          placeholder="you@example.com"
          required
          autoFocus
          style={inputStyle}
          onFocus={focusOn}
          onBlur={focusOff}
        />
      </div>

      {/* Password */}
      <div style={{ marginBottom: 16 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8 }}>
          <label style={{ ...labelStyle, marginBottom: 0 }}>Password</label>
          <Link href="/forgot-password" style={{ fontSize: 11, color: 'var(--av-cognac)', fontWeight: 500, textDecoration: 'none', letterSpacing: '0.04em', fontFamily: 'var(--av-sans)' }}
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
            style={{ ...inputStyle, paddingRight: 42 }}
            onFocus={focusOn}
            onBlur={focusOff}
          />
          <button type="button" onClick={() => setShowPwd(v => !v)}
            style={{ position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', color: 'var(--av-muted)', padding: 4, cursor: 'pointer', display: 'grid', placeItems: 'center' }}>
            <EyeIcon open={showPwd} />
          </button>
        </div>
        {errors.password && <div style={{ fontSize: 11, color: '#9c2b2b', marginTop: 5, fontFamily: 'var(--av-sans)' }}>{errors.password}</div>}
      </div>

      {/* Remember */}
      <label style={{ display: 'inline-flex', alignItems: 'center', gap: 9, fontSize: 13, color: 'var(--av-muted)', cursor: 'pointer', marginBottom: 22, fontFamily: 'var(--av-sans)' }}>
        <input type="checkbox" checked={data.remember} onChange={e => setData('remember', e.target.checked)}
          style={{ width: 16, height: 16, accentColor: 'var(--av-cognac)' }}/>
        Keep me signed in for 30 days
      </label>

      <button type="submit" disabled={processing}
        style={{ ...btnPrimary, background: processing ? 'var(--av-muted)' : 'var(--av-ink)', cursor: processing ? 'not-allowed' : 'pointer' }}>
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

  return (
    <>
      {error && <div style={errorBox}>{error}</div>}
      {info && step === 'otp' && (
        <div style={{ marginBottom: 16, padding: '11px 14px', borderRadius: 2, background: 'rgba(15,138,95,.08)', color: '#0F8A5F', border: '1px solid rgba(15,138,95,.25)', fontSize: 13, fontFamily: 'var(--av-sans)' }}>
          {info}
        </div>
      )}

      {step === 'phone' ? (
        <form onSubmit={handleSend}>
          <div style={{ marginBottom: 16 }}>
            <label style={labelStyle}>Phone number</label>
            <div style={{ display: 'flex', gap: 8 }}>
              <select style={{ ...inputStyle, width: 110, flexShrink: 0 }} onFocus={focusOn} onBlur={focusOff}>
                <option>🇧🇩 +880</option>
              </select>
              <input type="tel" value={phone} onChange={e => setPhone(e.target.value)}
                placeholder="1712 345 678" required autoFocus style={{ ...inputStyle, flex: 1 }}
                onFocus={focusOn} onBlur={focusOff}
              />
            </div>
            <div style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 6, fontFamily: 'var(--av-sans)' }}>We'll send a 6-digit code to verify.</div>
          </div>
          <button type="submit" disabled={loading}
            style={{ ...btnPrimary, background: loading ? 'var(--av-muted)' : 'var(--av-ink)', cursor: loading ? 'not-allowed' : 'pointer' }}>
            {loading ? 'Sending…' : 'Send OTP'}
          </button>
        </form>
      ) : (
        <form onSubmit={handleVerify}>
          <div style={{ marginBottom: 16 }}>
            <label style={labelStyle}>Enter the 6-digit code</label>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(6, 1fr)', gap: 8 }}>
              {otp.map((v, i) => (
                <input key={i}
                  ref={el => { otpRefs.current[i] = el; }}
                  type="text" inputMode="numeric" maxLength={1} value={v}
                  onChange={e => handleOtpChange(i, e.target.value)}
                  onKeyDown={e => handleOtpKeyDown(i, e)}
                  style={{ height: 56, textAlign: 'center', fontSize: 21, fontWeight: 500, border: '1px solid var(--av-line)', borderRadius: 2, background: 'var(--av-paper)', outline: 'none', color: 'var(--av-ink)', fontFamily: 'var(--av-display)', transition: 'border-color .2s, box-shadow .2s' }}
                  onFocus={focusOn} onBlur={focusOff}
                />
              ))}
            </div>
            <div style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 8, fontFamily: 'var(--av-sans)' }}>
              Sent to +880 {phone} ·{' '}
              <button type="button" style={{ color: 'var(--av-cognac)', fontWeight: 500, background: 'none', border: 'none', cursor: 'pointer', fontSize: 11.5, fontFamily: 'var(--av-sans)' }}>Resend</button>
            </div>
          </div>
          <button type="submit" disabled={loading || otp.join('').length < 6}
            style={{ ...btnPrimary, background: (loading || otp.join('').length < 6) ? 'var(--av-muted)' : 'var(--av-ink)', cursor: (loading || otp.join('').length < 6) ? 'not-allowed' : 'pointer' }}>
            {loading ? 'Verifying…' : 'Verify & Continue'}
          </button>
          <button type="button" onClick={() => { setStep('phone'); setOtp(['', '', '', '', '', '']); setError(''); }}
            style={{ display: 'block', width: '100%', textAlign: 'center', marginTop: 12, fontSize: 12, letterSpacing: '0.08em', color: 'var(--av-muted)', background: 'none', border: 'none', cursor: 'pointer', fontFamily: 'var(--av-sans)' }}>
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

  const tabActive: React.CSSProperties = {
    textAlign: 'center', padding: '11px 0', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.16em',
    textTransform: 'uppercase', borderRadius: 2, background: 'var(--av-ink)', color: 'var(--av-paper)',
    textDecoration: 'none', fontFamily: 'var(--av-sans)', display: 'block',
  };
  const tabIdle: React.CSSProperties = {
    textAlign: 'center', padding: '11px 0', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.16em',
    textTransform: 'uppercase', borderRadius: 2, color: 'var(--av-muted)', textDecoration: 'none',
    fontFamily: 'var(--av-sans)', display: 'block',
  };

  return (
    <>
      <Head title={`Sign in · ${site.name}`} />

      {/* Editorial background */}
      <div style={{
        position: 'fixed', inset: 0, zIndex: 0,
        background: 'radial-gradient(ellipse 620px 420px at 82% 14%, var(--av-paper) 0%, transparent 62%), radial-gradient(ellipse 520px 380px at 12% 88%, var(--av-paper-2) 0%, transparent 66%), var(--av-ivory)',
      }}/>
      {/* Faint oversized watermark */}
      <div style={{ position: 'fixed', inset: 0, zIndex: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', pointerEvents: 'none', overflow: 'hidden' }}>
        <span style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(180px,32vw,420px)', fontWeight: 600, color: 'rgba(31,26,21,.025)', letterSpacing: '-0.04em', userSelect: 'none', whiteSpace: 'nowrap', fontStyle: 'italic' }}>
          {site.name}
        </span>
      </div>

      {/* Shell */}
      <div style={{ position: 'relative', zIndex: 1, minHeight: '100vh', display: 'grid', placeItems: 'center', padding: '48px 16px' }}>
        <div style={{ width: '100%', maxWidth: 440 }}>

          {/* Back link */}
          <Link href="/"
            style={{ display: 'inline-flex', alignItems: 'center', gap: 7, fontSize: 11, letterSpacing: '0.16em', textTransform: 'uppercase', color: 'var(--av-muted)', marginBottom: 26, textDecoration: 'none', fontFamily: 'var(--av-sans)' }}
            className="hover:text-[#1f1a15]">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.6} d="M15 19l-7-7 7-7"/>
            </svg>
            Back to {site.name}
          </Link>

          {/* Card */}
          <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 4, padding: '44px 40px', boxShadow: '0 1px 2px rgba(31,26,21,.04), 0 24px 48px -24px rgba(31,26,21,.18)' }}>

            {/* Logo */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 11, marginBottom: 18 }}>
              <div style={{ width: 40, height: 40, borderRadius: 2, background: 'var(--av-ink)', display: 'grid', placeItems: 'center', color: 'var(--av-paper)', fontWeight: 600, fontSize: 22, fontFamily: 'var(--av-display)', flexShrink: 0 }}>
                {site.name.slice(0, 1).toUpperCase()}
              </div>
              <span style={{ fontWeight: 500, fontSize: 22, letterSpacing: '0.04em', color: 'var(--av-ink)', fontFamily: 'var(--av-display)' }}>{site.name}</span>
            </div>

            <div style={{ textAlign: 'center', marginBottom: 26 }}>
              <div style={{ fontSize: 10.5, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.34em', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 12 }}>Members</div>
              <h1 style={{ fontSize: 'clamp(28px,4vw,38px)', fontWeight: 400, letterSpacing: '-0.012em', margin: '0 0 6px', color: 'var(--av-ink)', fontFamily: 'var(--av-display)', lineHeight: 1.04 }}>Welcome back</h1>
              <p style={{ color: 'var(--av-muted)', fontSize: 13.5, margin: 0, fontFamily: 'var(--av-sans)' }}>Sign in to your account to continue</p>
            </div>

            {/* Auth tabs */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', border: '1px solid var(--av-line)', borderRadius: 2, padding: 3, marginBottom: 22, gap: 3 }}>
              <Link href="/login" style={tabActive}>Sign in</Link>
              <Link href="/register" style={tabIdle} className="hover:text-[#1f1a15]">Create account</Link>
            </div>

            {/* Method toggle */}
            {showToggle && (
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 3, border: '1px solid var(--av-line)', borderRadius: 2, padding: 3, marginBottom: 18 }}>
                {(['email', 'phone'] as const).map(m => (
                  <button key={m} type="button" onClick={() => setMethod(m)}
                    style={{ border: 'none', padding: '10px 0', fontSize: 10, fontWeight: 500, letterSpacing: '0.14em', textTransform: 'uppercase', borderRadius: 2, cursor: 'pointer', transition: 'background .2s, color .2s', fontFamily: 'var(--av-sans)', background: method === m ? 'var(--av-ink)' : 'transparent', color: method === m ? 'var(--av-paper)' : 'var(--av-muted)' }}>
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
            <div style={{ display: 'flex', alignItems: 'center', gap: 14, color: 'var(--av-muted)', fontSize: 10, fontWeight: 500, letterSpacing: '0.2em', textTransform: 'uppercase', margin: '24px 0', fontFamily: 'var(--av-sans)' }}>
              <div style={{ flex: 1, height: 1, background: 'var(--av-line)' }}/>
              Or continue with
              <div style={{ flex: 1, height: 1, background: 'var(--av-line)' }}/>
            </div>

            {/* Social buttons */}
            <div style={{ display: 'grid', gap: 9 }}>
              <a href="/auth/google"
                style={{ height: 46, border: '1px solid var(--av-line)', background: 'var(--av-paper)', color: 'var(--av-ink)', fontWeight: 500, fontSize: 11, letterSpacing: '0.1em', textTransform: 'uppercase', borderRadius: 2, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, textDecoration: 'none', fontFamily: 'var(--av-sans)', transition: 'border-color .2s' }}
                className="hover:border-[#1f1a15]">
                <svg width="18" height="18" viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
                  <path fill="#FBBC05" d="M5.84 14.09a6.6 6.6 0 0 1 0-4.18V7.07H2.18a11 11 0 0 0 0 9.86l3.66-2.84z"/>
                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                </svg>
                Continue with Google
              </a>
              <a href="/auth/facebook"
                style={{ height: 46, border: '1px solid var(--av-line)', background: 'var(--av-paper)', color: 'var(--av-ink)', fontWeight: 500, fontSize: 11, letterSpacing: '0.1em', textTransform: 'uppercase', borderRadius: 2, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, textDecoration: 'none', fontFamily: 'var(--av-sans)', transition: 'border-color .2s' }}
                className="hover:border-[#1f1a15]">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
                  <path d="M22 12a10 10 0 1 0-11.6 9.9V14.9H8V12h2.4V9.8c0-2.4 1.4-3.8 3.6-3.8 1 0 2.1.2 2.1.2v2.4h-1.2c-1.2 0-1.5.7-1.5 1.5V12h2.7l-.4 2.9h-2.2v6.9A10 10 0 0 0 22 12Z"/>
                </svg>
                Continue with Facebook
              </a>
            </div>

            <p style={{ textAlign: 'center', fontSize: 13, color: 'var(--av-muted)', marginTop: 22, fontFamily: 'var(--av-sans)' }}>
              Don't have an account?{' '}
              <Link href="/register" style={{ color: 'var(--av-cognac)', fontWeight: 500, textDecoration: 'none' }} className="hover:underline">
                Create one
              </Link>
            </p>
          </div>
        </div>
      </div>
    </>
  );
}
