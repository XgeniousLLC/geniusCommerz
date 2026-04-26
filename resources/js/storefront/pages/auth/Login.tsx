import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Layout from '../../layouts/Layout';

interface Props {
  loginMethod?: 'email_password' | 'phone_otp' | 'both';
}

function EmailPasswordForm() {
  const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: false });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/login');
  }

  return (
    <>
      {errors.email && (
        <div className="mb-5 px-4 py-3 rounded-xl text-sm" style={{ background: '#fef2f2', color: '#dc2626', border: '1px solid #fecaca' }}>
          {errors.email}
        </div>
      )}
      <form onSubmit={submit} className="space-y-4">
        <div>
          <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Email</label>
          <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
            placeholder="you@example.com" required autoFocus className="kb-input text-sm" />
        </div>
        <div>
          <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Password</label>
          <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)}
            placeholder="••••••••" required className="kb-input text-sm" />
        </div>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <input type="checkbox" id="remember" checked={data.remember}
              onChange={(e) => setData('remember', e.target.checked)} className="rounded" />
            <label htmlFor="remember" className="text-sm" style={{ color: 'var(--kb-ink-muted)' }}>Remember me</label>
          </div>
          <Link href="/forgot-password" className="text-sm hover:underline" style={{ color: 'var(--kb-primary)' }}>
            Forgot password?
          </Link>
        </div>
        <button type="submit" disabled={processing}
          className="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
          Sign in
        </button>
      </form>
    </>
  );
}

function PhoneOtpForm() {
  const [phone, setPhone]   = useState('');
  const [otp, setOtp]       = useState('');
  const [step, setStep]     = useState<'phone' | 'otp'>('phone');
  const [loading, setLoading] = useState(false);
  const [error, setError]   = useState('');
  const [info, setInfo]     = useState('');

  const csrfToken = () => (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

  const handleSend = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res = await fetch('/login/otp/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ phone }),
      });
      const json = await res.json();
      if (!res.ok) {
        setError(json.message ?? 'Failed to send OTP.');
      } else {
        setInfo(json.message);
        setStep('otp');
      }
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleVerify = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res = await fetch('/login/otp/verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify({ phone, otp }),
      });
      const json = await res.json();
      if (!res.ok) {
        setError(json.message ?? 'Invalid OTP.');
      } else {
        window.location.href = json.redirect ?? '/';
      }
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      {error && (
        <div className="mb-5 px-4 py-3 rounded-xl text-sm" style={{ background: '#fef2f2', color: '#dc2626', border: '1px solid #fecaca' }}>
          {error}
        </div>
      )}
      {info && step === 'otp' && (
        <div className="mb-5 px-4 py-3 rounded-xl text-sm" style={{ background: '#f0fdf4', color: '#16a34a', border: '1px solid #bbf7d0' }}>
          {info}
        </div>
      )}

      {step === 'phone' ? (
        <form onSubmit={handleSend} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Phone Number</label>
            <input type="tel" value={phone} onChange={e => setPhone(e.target.value)}
              placeholder="01XXXXXXXXX" required autoFocus className="kb-input text-sm" />
          </div>
          <button type="submit" disabled={loading}
            className="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
            {loading ? 'Sending…' : 'Send OTP'}
          </button>
        </form>
      ) : (
        <form onSubmit={handleVerify} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Enter 6-digit OTP</label>
            <input type="text" inputMode="numeric" maxLength={6} value={otp} onChange={e => setOtp(e.target.value)}
              placeholder="••••••" required autoFocus className="kb-input text-sm text-center tracking-[0.5em] text-lg" />
            <p className="text-xs mt-1.5" style={{ color: 'var(--kb-ink-soft)' }}>Sent to {phone}</p>
          </div>
          <button type="submit" disabled={loading}
            className="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
            {loading ? 'Verifying…' : 'Verify & Sign in'}
          </button>
          <button type="button" onClick={() => { setStep('phone'); setOtp(''); setError(''); }}
            className="w-full text-sm text-center hover:underline" style={{ color: 'var(--kb-ink-soft)' }}>
            Change phone number
          </button>
        </form>
      )}
    </>
  );
}

export default function Login({ loginMethod = 'email_password' }: Props) {
  const [activeTab, setActiveTab] = useState<'email' | 'phone'>(
    loginMethod === 'phone_otp' ? 'phone' : 'email'
  );

  return (
    <Layout>
      <Head title="Sign in" />
      <div className="min-h-[60vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md">
          <div className="kb-card p-8" style={{ borderRadius: 20 }}>
            <div className="text-center mb-8">
              <h1 className="text-2xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>Welcome back</h1>
              <p className="text-sm mt-1" style={{ color: 'var(--kb-ink-soft)' }}>Sign in to your account</p>
            </div>

            {loginMethod === 'both' && (
              <div className="flex rounded-xl mb-6 p-1 gap-1" style={{ background: 'var(--kb-surface-2)' }}>
                {(['email', 'phone'] as const).map(tab => (
                  <button key={tab} type="button"
                    onClick={() => setActiveTab(tab)}
                    className="flex-1 py-2 text-sm font-medium rounded-lg transition-all"
                    style={{
                      background: activeTab === tab ? 'white' : 'transparent',
                      color: activeTab === tab ? 'var(--kb-ink)' : 'var(--kb-ink-soft)',
                      boxShadow: activeTab === tab ? '0 1px 3px rgba(0,0,0,0.1)' : 'none',
                    }}>
                    {tab === 'email' ? 'Email & Password' : 'Phone OTP'}
                  </button>
                ))}
              </div>
            )}

            {(loginMethod === 'email_password' || (loginMethod === 'both' && activeTab === 'email'))
              ? <EmailPasswordForm />
              : <PhoneOtpForm />
            }

            <p className="text-center text-sm mt-6" style={{ color: 'var(--kb-ink-soft)' }}>
              Don&apos;t have an account?{' '}
              <Link href="/register" className="font-semibold hover:underline" style={{ color: 'var(--kb-primary)' }}>Create one</Link>
            </p>
          </div>
        </div>
      </div>
    </Layout>
  );
}
