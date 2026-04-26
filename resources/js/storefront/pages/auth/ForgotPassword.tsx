import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../layouts/Layout';

export default function ForgotPassword({ status }: { status?: string }) {
  const { data, setData, post, processing, errors } = useForm({ email: '' });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/forgot-password');
  }

  return (
    <Layout>
      <Head title="Forgot Password" />
      <div className="min-h-[60vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md">
          <div className="kb-card p-8" style={{ borderRadius: 20 }}>
            <div className="text-center mb-8">
              <h1 className="text-2xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>Forgot password?</h1>
              <p className="text-sm mt-1" style={{ color: 'var(--kb-ink-soft)' }}>
                Enter your email and we'll send a reset link.
              </p>
            </div>

            {status && (
              <div className="mb-5 px-4 py-3 rounded-xl text-sm" style={{ background: '#f0fdf4', color: '#16a34a', border: '1px solid #bbf7d0' }}>
                {status}
              </div>
            )}

            {errors.email && (
              <div className="mb-5 px-4 py-3 rounded-xl text-sm" style={{ background: '#fef2f2', color: '#dc2626', border: '1px solid #fecaca' }}>
                {errors.email}
              </div>
            )}

            <form onSubmit={submit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Email address</label>
                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                  placeholder="you@example.com" required autoFocus
                  className="kb-input text-sm" />
              </div>
              <button type="submit" disabled={processing}
                className="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
                {processing ? 'Sending…' : 'Send reset link'}
              </button>
            </form>

            <p className="text-center text-sm mt-6" style={{ color: 'var(--kb-ink-soft)' }}>
              Remembered it?{' '}
              <Link href="/login" className="font-semibold hover:underline" style={{ color: 'var(--kb-primary)' }}>Sign in</Link>
            </p>
          </div>
        </div>
      </div>
    </Layout>
  );
}
