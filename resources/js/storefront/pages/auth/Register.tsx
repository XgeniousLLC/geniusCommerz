import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '../../layouts/Layout';

export default function Register() {
  const { data, setData, post, processing, errors } = useForm({ name: '', email: '', password: '', password_confirmation: '' });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/register');
  }

  return (
    <Layout>
      <Head title="Create account" />
      <div className="min-h-[60vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md">
          <div className="kb-card p-8" style={{ borderRadius: 20 }}>
            <div className="text-center mb-8">
              <h1 className="text-2xl font-extrabold" style={{ color: 'var(--kb-ink)' }}>Create account</h1>
              <p className="text-sm mt-1" style={{ color: 'var(--kb-ink-soft)' }}>Join us to leave comments</p>
            </div>

            {Object.keys(errors).length > 0 && (
              <div className="mb-5 px-4 py-3 rounded-xl text-sm" style={{ background: '#fef2f2', color: '#dc2626', border: '1px solid #fecaca' }}>
                <ul className="space-y-0.5">
                  {Object.values(errors).map((e, i) => <li key={i}>{e}</li>)}
                </ul>
              </div>
            )}

            <form onSubmit={submit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Full name</label>
                <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)}
                  placeholder="Your name" required autoFocus className="kb-input text-sm" />
              </div>
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Email</label>
                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                  placeholder="you@example.com" required className="kb-input text-sm" />
              </div>
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Password</label>
                <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)}
                  placeholder="Min. 8 characters" required className="kb-input text-sm" />
              </div>
              <div>
                <label className="block text-xs font-semibold mb-1.5" style={{ color: 'var(--kb-ink)' }}>Confirm password</label>
                <input type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)}
                  placeholder="Repeat password" required className="kb-input text-sm" />
              </div>
              <button type="submit" disabled={processing}
                className="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
                Create account
              </button>
            </form>

            <p className="text-center text-sm mt-6" style={{ color: 'var(--kb-ink-soft)' }}>
              Already have an account?{' '}
              <Link href="/login" className="font-semibold hover:underline" style={{ color: 'var(--kb-primary)' }}>Sign in</Link>
            </p>
          </div>
        </div>
      </div>
    </Layout>
  );
}
