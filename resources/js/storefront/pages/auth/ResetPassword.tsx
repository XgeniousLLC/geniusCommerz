import { Head, useForm } from '@inertiajs/react';
import Layout from '../../layouts/Layout';

export default function ResetPassword({ token, email }: { token: string; email: string }) {
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

  return (
    <Layout>
      <Head title="Reset Password" />
      <div className="min-h-[60vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md">
          <div className="kb-card p-8" style={{ borderRadius: 20 }}>
            <div className="text-center mb-8">
              <h1 className="text-2xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>Set new password</h1>
              <p className="text-sm mt-1" style={{ color: 'var(--kb-ink-soft)' }}>Choose a strong password for your account.</p>
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
                  placeholder="you@example.com" required
                  className="kb-input text-sm" />
              </div>
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>New password</label>
                <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)}
                  placeholder="Min. 8 characters" required minLength={8}
                  className="kb-input text-sm" />
                {errors.password && <p className="text-xs mt-1 text-red-500">{errors.password}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Confirm password</label>
                <input type="password" value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  placeholder="••••••••" required
                  className="kb-input text-sm" />
              </div>
              <button type="submit" disabled={processing}
                className="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
                {processing ? 'Saving…' : 'Reset password'}
              </button>
            </form>
          </div>
        </div>
      </div>
    </Layout>
  );
}
