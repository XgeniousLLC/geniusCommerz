import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Layout from '../../layouts/Layout';
import type { SharedProps } from '../../types';

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

function getStrength(v: string): { level: 0 | 1 | 2 | 3; text: string; color: string } {
  if (!v) return { level: 0, text: 'Use 8+ characters with a mix of letters, numbers & symbols.', color: 'var(--av-muted)' };
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  if (score <= 1) return { level: 1, text: 'Weak — add length & variety', color: '#9c2b2b' };
  if (score === 2) return { level: 2, text: 'Medium — getting there', color: '#B25E09' };
  return { level: 3, text: 'Strong password ✓', color: '#0F8A5F' };
}

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

interface DialCode { code: string; name: string; dial: string }

export default function Register({ dialCodes = [], storeCountry = 'BD' }: { dialCodes?: DialCode[]; storeCountry?: string }) {
  const { site } = usePage<SharedProps>().props;
  const { data, setData, post, processing, errors } = useForm({
    name: '', email: '', phone: '', password: '', password_confirmation: '',
  });
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName]   = useState('');
  const [showPwd, setShowPwd]     = useState(false);
  const [dial, setDial]           = useState(
    dialCodes.find(c => c.code === storeCountry)?.dial ?? dialCodes[0]?.dial ?? '',
  );
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
    // Combine the dial code with the local number so the stored phone is unambiguous.
    if (data.phone) {
      setData('phone', `+${dial}${data.phone.replace(/\D/g, '').replace(/^0+/, '')}`);
    }
    post('/register');
  }

  const fieldWrap: React.CSSProperties = { marginBottom: 16 };

  const tabIdle: React.CSSProperties = {
    textAlign: 'center', padding: '11px 0', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.16em',
    textTransform: 'uppercase', borderRadius: 2, color: 'var(--av-muted)', textDecoration: 'none',
    fontFamily: 'var(--av-sans)', display: 'block',
  };
  const tabActive: React.CSSProperties = {
    textAlign: 'center', padding: '11px 0', fontSize: 10.5, fontWeight: 500, letterSpacing: '0.16em',
    textTransform: 'uppercase', borderRadius: 2, background: 'var(--av-ink)', color: 'var(--av-paper)',
    fontFamily: 'var(--av-sans)', display: 'block',
  };

  return (
    <Layout>
      <Head title={`Create account · ${site.name}`} />

      {/* Editorial shell — blobs at opposite corners vs login */}
      <section style={{ position: 'relative', overflow: 'hidden', display: 'grid', placeItems: 'center', padding: 'clamp(48px,8vw,96px) 16px', background: 'radial-gradient(ellipse 620px 420px at 18% 14%, var(--av-paper) 0%, transparent 62%), radial-gradient(ellipse 520px 380px at 88% 88%, var(--av-paper-2) 0%, transparent 66%), var(--av-ivory)' }}>
        <div style={{ position: 'absolute', inset: 0, zIndex: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', pointerEvents: 'none', overflow: 'hidden' }}>
          <span style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(180px,32vw,420px)', fontWeight: 600, color: 'rgba(31,26,21,.025)', letterSpacing: '-0.04em', userSelect: 'none', whiteSpace: 'nowrap', fontStyle: 'italic' }}>
            {site.name}
          </span>
        </div>

        <div style={{ width: '100%', maxWidth: 440, position: 'relative', zIndex: 1 }}>

          {/* Card */}
          <div style={{ background: 'var(--av-paper)', border: '1px solid var(--av-line)', borderRadius: 4, padding: '44px 40px', boxShadow: '0 1px 2px rgba(31,26,21,.04), 0 24px 48px -24px rgba(31,26,21,.18)' }}>

            <div style={{ textAlign: 'center', marginBottom: 26 }}>
              <div style={{ fontSize: 10.5, fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.34em', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginBottom: 12 }}>Join Us</div>
              <h1 style={{ fontSize: 'clamp(28px,4vw,38px)', fontWeight: 400, letterSpacing: '-0.012em', margin: '0 0 6px', color: 'var(--av-ink)', fontFamily: 'var(--av-display)', lineHeight: 1.04 }}>Create your account</h1>
              <p style={{ color: 'var(--av-muted)', fontSize: 13.5, margin: 0, fontFamily: 'var(--av-sans)' }}>Join 10,000+ shoppers — it takes a minute</p>
            </div>

            {/* Auth tabs */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', border: '1px solid var(--av-line)', borderRadius: 2, padding: 3, marginBottom: 22, gap: 3 }}>
              <Link href="/login" style={tabIdle} className="hover:text-[#1f1a15]">Sign in</Link>
              <span style={tabActive}>Create account</span>
            </div>

            {/* Errors */}
            {Object.keys(errors).length > 0 && (
              <div style={{ marginBottom: 16, padding: '11px 14px', borderRadius: 2, background: 'rgba(196,48,48,.07)', color: '#9c2b2b', border: '1px solid rgba(196,48,48,.22)', fontSize: 13, fontFamily: 'var(--av-sans)' }}>
                <ul style={{ margin: 0, padding: '0 0 0 16px' }}>
                  {Object.values(errors).map((e, i) => <li key={i}>{e}</li>)}
                </ul>
              </div>
            )}

            <form onSubmit={submit}>
              {/* First + Last name row */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12, marginBottom: 16 }}>
                <div>
                  <label style={labelStyle}>First name</label>
                  <input type="text" value={firstName} onChange={e => handleFirst(e.target.value)}
                    placeholder="Tahmid" required autoFocus style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                </div>
                <div>
                  <label style={labelStyle}>Last name</label>
                  <input type="text" value={lastName} onChange={e => handleLast(e.target.value)}
                    placeholder="Rahman" style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                </div>
              </div>

              {/* Email */}
              <div style={fieldWrap}>
                <label style={labelStyle}>Email</label>
                <input type="email" value={data.email} onChange={e => setData('email', e.target.value)}
                  placeholder="you@example.com" required style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                {errors.email && <div style={{ fontSize: 11, color: '#9c2b2b', marginTop: 5, fontFamily: 'var(--av-sans)' }}>{errors.email}</div>}
              </div>

              {/* Phone */}
              <div style={fieldWrap}>
                <label style={labelStyle}>Phone number</label>
                <div style={{ display: 'flex', gap: 8 }}>
                  <select value={dial} onChange={e => setDial(e.target.value)}
                    style={{ ...inputStyle, width: 130, flexShrink: 0 }} onFocus={focusOn} onBlur={focusOff}>
                    {dialCodes.map(c => <option key={c.code} value={c.dial}>{c.code} +{c.dial}</option>)}
                  </select>
                  <input type="tel" value={data.phone} onChange={e => setData('phone', e.target.value)}
                    placeholder="1712 345 678" style={{ ...inputStyle, flex: 1 }} onFocus={focusOn} onBlur={focusOff}/>
                </div>
                <div style={{ fontSize: 11.5, color: 'var(--av-muted)', marginTop: 6, fontFamily: 'var(--av-sans)' }}>We'll send order updates here.</div>
              </div>

              {/* Password */}
              <div style={fieldWrap}>
                <label style={labelStyle}>Password</label>
                <div style={{ position: 'relative' }}>
                  <input type={showPwd ? 'text' : 'password'} value={data.password}
                    onChange={e => setData('password', e.target.value)}
                    placeholder="At least 8 characters" required
                    style={{ ...inputStyle, paddingRight: 42 }} onFocus={focusOn} onBlur={focusOff}/>
                  <button type="button" onClick={() => setShowPwd(v => !v)}
                    style={{ position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', color: 'var(--av-muted)', padding: 4, cursor: 'pointer', display: 'grid', placeItems: 'center' }}>
                    <EyeIcon open={showPwd}/>
                  </button>
                </div>
                {/* Strength bar */}
                {data.password.length > 0 && (
                  <div style={{ display: 'flex', gap: 4, marginTop: 7 }}>
                    {[1, 2, 3].map(i => (
                      <div key={i} style={{ flex: 1, height: 3, borderRadius: 2, background: i <= strength.level ? strength.color : 'var(--av-line)', transition: 'background .2s' }}/>
                    ))}
                  </div>
                )}
                <div style={{ fontSize: 11.5, color: data.password.length > 0 ? strength.color : 'var(--av-muted)', marginTop: 5, fontFamily: 'var(--av-sans)' }}>{strength.text}</div>
                {errors.password && <div style={{ fontSize: 11, color: '#9c2b2b', marginTop: 2, fontFamily: 'var(--av-sans)' }}>{errors.password}</div>}
              </div>

              {/* Confirm password */}
              <div style={fieldWrap}>
                <label style={labelStyle}>Confirm password</label>
                <input type="password" value={data.password_confirmation}
                  onChange={e => setData('password_confirmation', e.target.value)}
                  placeholder="Repeat password" required style={inputStyle} onFocus={focusOn} onBlur={focusOff}/>
                {errors.password_confirmation && <div style={{ fontSize: 11, color: '#9c2b2b', marginTop: 5, fontFamily: 'var(--av-sans)' }}>{errors.password_confirmation}</div>}
              </div>

              {/* Terms */}
              <label style={{ display: 'inline-flex', alignItems: 'flex-start', gap: 9, fontSize: 13, color: 'var(--av-muted)', cursor: 'pointer', marginBottom: 22, fontFamily: 'var(--av-sans)' }}>
                <input type="checkbox" required checked={agreed} onChange={e => setAgreed(e.target.checked)}
                  style={{ width: 16, height: 16, accentColor: 'var(--av-cognac)', marginTop: 2, flexShrink: 0 }}/>
                <span>
                  I agree to the{' '}
                  <Link href="/terms" style={{ color: 'var(--av-cognac)', fontWeight: 500, textDecoration: 'none' }} className="hover:underline">Terms of Service</Link>
                  {' '}and{' '}
                  <Link href="/privacy" style={{ color: 'var(--av-cognac)', fontWeight: 500, textDecoration: 'none' }} className="hover:underline">Privacy Policy</Link>
                </span>
              </label>

              <button type="submit" disabled={processing || !agreed}
                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: '100%', height: 50, borderRadius: 2, background: (processing || !agreed) ? 'var(--av-muted)' : 'var(--av-ink)', color: 'var(--av-paper)', fontWeight: 500, fontSize: 11.5, letterSpacing: '0.2em', textTransform: 'uppercase', border: 'none', cursor: (processing || !agreed) ? 'not-allowed' : 'pointer', fontFamily: 'var(--av-sans)', transition: 'background .2s' }}>
                {processing ? 'Creating account…' : 'Create account'}
              </button>
            </form>

            <p style={{ textAlign: 'center', fontSize: 13, color: 'var(--av-muted)', marginTop: 24, fontFamily: 'var(--av-sans)' }}>
              Already have an account?{' '}
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
