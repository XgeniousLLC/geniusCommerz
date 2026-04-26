import { Link, router } from '@inertiajs/react';
import Layout from '../layouts/Layout';

const nav = [
  { href: '/account',         label: 'Dashboard',  icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { href: '/account/orders',  label: 'My Orders',  icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  { href: '/account/reviews', label: 'Reviews',    icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
  { href: '/account/address', label: 'Address',    icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z' },
  { href: '/account/refunds', label: 'Refunds',    icon: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6' },
];

interface Props {
  children: React.ReactNode;
  title: string;
  active: string;
}

export default function AccountLayout({ children, title, active }: Props) {
  const logout = () => router.post('/logout');

  return (
    <Layout>
      <div className="max-w-5xl mx-auto px-4 py-8 lg:py-12">
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">

          {/* Sidebar */}
          <aside className="lg:col-span-1">
            <div className="kb-card overflow-hidden">
              <div className="px-4 py-4 border-b" style={{ borderColor: 'var(--kb-border)', background: 'var(--kb-primary)', color: '#fff' }}>
                <p className="text-xs font-semibold uppercase tracking-wide opacity-70">My Account</p>
              </div>
              <nav className="py-2">
                {nav.map(item => (
                  <Link key={item.href} href={item.href}
                    className={`flex items-center gap-3 px-4 py-2.5 text-sm transition-colors ${active === item.href ? 'font-semibold' : 'hover:bg-gray-50'}`}
                    style={active === item.href ? { color: 'var(--kb-primary)', background: 'var(--kb-primary-50)' } : { color: 'var(--kb-ink)' }}>
                    <svg className="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={item.icon}/>
                    </svg>
                    {item.label}
                  </Link>
                ))}
                <button onClick={logout}
                  className="flex w-full items-center gap-3 px-4 py-2.5 text-sm transition-colors hover:bg-red-50 mt-1 border-t"
                  style={{ color: 'var(--kb-danger)', borderColor: 'var(--kb-border)' }}>
                  <svg className="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                  Sign Out
                </button>
              </nav>
            </div>
          </aside>

          {/* Content */}
          <main className="lg:col-span-3">
            <h1 className="text-xl font-bold mb-5" style={{ color: 'var(--kb-ink)' }}>{title}</h1>
            {children}
          </main>

        </div>
      </div>
    </Layout>
  );
}
