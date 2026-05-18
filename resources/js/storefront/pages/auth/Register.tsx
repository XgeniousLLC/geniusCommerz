import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { SharedProps } from '../../types';

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

function getStrength(v: string): { level: 0 | 1 | 2 | 3; text: string; color: string } {
  if (!v) return { level: 0, text: 'Use 8+ characters with a mix of letters, numbers & symbols.', color: '#8A93A6' };
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  if (score <= 1) return { level: 1, text: 'Weak — add length & variety', color: '#C43030' };
  if (score === 2) return { level: 2, text: 'Medium — getting there', color: '#B25E09' };
  return { level: 3, text: 'Strong password ✓', color: '#0F8A5F' };
}

const inputStyle: React.CSSProperties = {
  width: '100%', height: 38, padding: '0 12px', border: '1px solid #E6E8EE',
  background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14,
  color: '#0E1320', transition: 'border-color .15s, box-shadow .15s', boxSizing: 'border-box',
};
function focusOn(e: React.FocusEvent<HTMLInputElement | HTMLSelectElement>) {
  e.currentTarget.style.borderColor = '#0B1F4F';
  e.currentTarget.style.boxShadow = '0 0 0 3px rgba(11,31,79,.12)';
}
function focusOff(e: React.FocusEvent<HTMLInputElement | HTMLSelectElement>) {
  e.currentTarget.style.borderColor = '#E6E8EE';
  e.currentTarget.style.boxShadow = 'none';
}

export default function Register() {
  const { site } = usePage<SharedProps>().props;
  const { data, setData, post, processing, errors } = useForm({
    name: '', email: '', phone: '', password: '', password_confirmation: '',
  });
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName]   = useState('');
  const [showPwd, setShowPwd]     = useState(false);
  const [agreed, setAgreed]       = useState(false);

  const strength = getStrength(data.password);

  function handleFirst(v: string) {
    setFirstName(v);
    setData('name', [v, lastName].filter(Boolean).join(' '));
  }
  function handleLast(v: string) {
    setLastName(v);
    setData('name', [firstName, v].filter(Boolean).join(' '));
  }

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/register');
  }

  const fieldLabel: React.CSSProperties = { fontSize: 12, fontWeight: 600, color: '#2A3142', display: 'block', marginBottom: 6 };
  const fieldWrap: React.CSSProperties = { marginBottom: 14 };

  return (
    <>
      <Head title={`Create account · ${site.name}`} />

      {/* Gradient background — blobs at opposite corners vs login */}
      <div style={{
        position: 'fixed', inset: 0, zIndex: 0,
        background: 'radial-gradient(ellipse 600px 400px at 20% 20%, #EEF2FB 0%, transparent 60%), radial-gradient(ellipse 500px 360px at 85% 85%, #DDE4F4 0%, transparent 65%), #F2F4F9',
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

            <h1 style={{ fontSize: 22, fontWeight: 700, letterSpacing: '-0.01em', margin: '8px 0 4px', textAlign: 'center', color: '#0E1320' }}>Create your account</h1>
            <p style={{ color: '#8A93A6', fontSize: 13, textAlign: 'center', margin: '0 0 24px' }}>Join 10,000+ shoppers — it takes a minute</p>

            {/* Auth tabs */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', background: '#F2F4F9', borderRadius: 10, padding: 4, marginBottom: 20 }}>
              <Link href="/login"
                style={{ textAlign: 'center', padding: '8px 0', fontSize: 13, fontWeight: 600, borderRadius: 8, color: '#8A93A6', textDecoration: 'none' }}
                className="hover:text-[#0E1320]">
                Sign in
              </Link>
              <span style={{ textAlign: 'center', padding: '8px 0', fontSize: 13, fontWeight: 600, borderRadius: 8, background: '#fff', color: '#0E1320', boxShadow: '0 1px 0 rgba(14,19,32,.04), 0 1px 2px rgba(14,19,32,.04)', display: 'block' }}>
                Create account
              </span>
            </div>

            {/* Errors */}
            {Object.keys(errors).length > 0 && (
              <div style={{ marginBottom: 14, padding: '10px 14px', borderRadius: 8, background: '#FCEDED', color: '#C43030', border: '1px solid #f5c6c6', fontSize: 13 }}>
                <ul style={{ margin: 0, padding: '0 0 0 16px' }}>
                  {Object.values(errors).map((e, i) => <li key={i}>{e}</li>)}
                </ul>
              </div>
            )}

            <form onSubmit={submit}>
              {/* First + Last name row */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, marginBottom: 14 }}>
                <div>
                  <label style={fieldLabel}>First name</label>
                  <input type="text" value={firstName} onChange={e => handleFirst(e.target.value)}
                    placeholder="Tahmid" required autoFocus style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                </div>
                <div>
                  <label style={fieldLabel}>Last name</label>
                  <input type="text" value={lastName} onChange={e => handleLast(e.target.value)}
                    placeholder="Rahman" style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                </div>
              </div>

              {/* Email */}
              <div style={fieldWrap}>
                <label style={fieldLabel}>Email</label>
                <input type="email" value={data.email} onChange={e => setData('email', e.target.value)}
                  placeholder="you@example.com" required style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                {errors.email && <div style={{ fontSize: 11, color: '#C43030', marginTop: 4 }}>{errors.email}</div>}
              </div>

              {/* Phone */}
              <div style={fieldWrap}>
                <label style={fieldLabel}>Phone number</label>
                <div style={{ display: 'flex', gap: 8 }}>
                  <select style={{ width: 110, height: 38, padding: '0 8px', border: '1px solid #E6E8EE', background: '#fff', borderRadius: 8, outline: 'none', fontSize: 14, color: '#0E1320', flexShrink: 0 }}
                    onFocus={focusOn} onBlur={focusOff}>
                    <option>🇧🇩 +880</option>
                  </select>
                  <input type="tel" value={data.phone} onChange={e => setData('phone', e.target.value)}
                    placeholder="1712 345 678" style={{ ...inputStyle, flex: 1 }} onFocus={focusOn} onBlur={focusOff}/>
                </div>
                <div style={{ fontSize: 11, color: '#8A93A6', marginTop: 4 }}>We'll send order updates here. Bangla SMS supported.</div>
              </div>

              {/* Password */}
              <div style={fieldWrap}>
                <label style={fieldLabel}>Password</label>
                <div style={{ position: 'relative' }}>
                  <input type={showPwd ? 'text' : 'password'} value={data.password}
                    onChange={e => setData('password', e.target.value)}
                    placeholder="At least 8 characters" required
                    style={{ ...inputStyle, paddingRight: 38 }} onFocus={focusOn} onBlur={focusOff}/>
                  <button type="button" onClick={() => setShowPwd(v => !v)}
                    style={{ position: 'absolute', right: 10, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', color: '#8A93A6', padding: 4, cursor: 'pointer', display: 'grid', placeItems: 'center' }}>
                    <EyeIcon open={showPwd}/>
                  </button>
                </div>
                {/* Strength bar */}
                {data.password.length > 0 && (
                  <div style={{ display: 'flex', gap: 4, marginTop: 6 }}>
                    {[1, 2, 3].map(i => (
                      <div key={i} style={{ flex: 1, height: 4, borderRadius: 2, background: i <= strength.level ? strength.color : '#E6E8EE', transition: 'background .2s' }}/>
                    ))}
                  </div>
                )}
                <div style={{ fontSize: 11, color: data.password.length > 0 ? strength.color : '#8A93A6', marginTop: 4 }}>{strength.text}</div>
                {errors.password && <div style={{ fontSize: 11, color: '#C43030', marginTop: 2 }}>{errors.password}</div>}
              </div>

              {/* Confirm password */}
              <div style={fieldWrap}>
                <label style={fieldLabel}>Confirm password</label>
                <input type="password" value={data.password_confirmation}
                  onChange={e => setData('password_confirmation', e.target.value)}
                  placeholder="Repeat password" required style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                {errors.password_confirmation && <div style={{ fontSize: 11, color: '#C43030', marginTop: 4 }}>{errors.password_confirmation}</div>}
              </div>

              {/* Terms */}
              <label style={{ display: 'inline-flex', alignItems: 'flex-start', gap: 8, fontSize: 13, color: '#2A3142', cursor: 'pointer', marginBottom: 18 }}>
                <input type="checkbox" required checked={agreed} onChange={e => setAgreed(e.target.checked)}
                  style={{ width: 16, height: 16, accentColor: '#0B1F4F', marginTop: 2, flexShrink: 0 }}/>
                <span>
                  I agree to the{' '}
                  <Link href="/terms" style={{ color: '#0B1F4F', fontWeight: 600, textDecoration: 'none' }} className="hover:underline">Terms of Service</Link>
                  {' '}and{' '}
                  <Link href="/privacy" style={{ color: '#0B1F4F', fontWeight: 600, textDecoration: 'none' }} className="hover:underline">Privacy Policy</Link>
                </span>
              </label>

              <button type="submit" disabled={processing || !agreed}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 48, borderRadius: 10, background: (processing || !agreed) ? '#8A93A6' : '#0B1F4F', color: '#fff', fontWeight: 600, fontSize: 15, border: 'none', cursor: (processing || !agreed) ? 'not-allowed' : 'pointer', transition: 'background .15s' }}
                className="hover:enabled:bg-[#102B6B]">
                {processing ? 'Creating account…' : 'Create account'}
              </button>
            </form>

            {/* Divider */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, color: '#8A93A6', fontSize: 11, fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', margin: '20px 0' }}>
              <div style={{ flex: 1, height: 1, background: '#E6E8EE' }}/>
              Or sign up with
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
              Already have an account?{' '}
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
