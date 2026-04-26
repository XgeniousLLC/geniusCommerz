import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../layouts/Layout';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: false });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/login');
  }

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

            {errors.email && (
              <div className="mb-5 px-4 py-3 rounded-xl text-sm" style={{ background: '#fef2f2', color: '#dc2626', border: '1px solid #fecaca' }}>
                {errors.email}
              </div>
            )}

            <form onSubmit={submit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Email</label>
                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                  placeholder="you@example.com" required autoFocus
                  className="kb-input text-sm" />
              </div>
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Password</label>
                <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)}
                  placeholder="••••••••" required
                  className="kb-input text-sm" />
              </div>
              <div className="flex items-center gap-2">
                <input type="checkbox" id="remember" checked={data.remember}
                  onChange={(e) => setData('remember', e.target.checked)} className="rounded" />
                <label htmlFor="remember" className="text-sm" style={{ color: 'var(--kb-ink-muted)' }}>Remember me</label>
              </div>
              <button type="submit" disabled={processing}
                className="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
                Sign in
              </button>
            </form>

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
