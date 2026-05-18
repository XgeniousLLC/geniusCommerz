import { Head, Link } from '@inertiajs/react';
import Layout from '../layouts/Layout';

interface Tier { name: string; min: number; max: number | null; bonus_pct: number }
interface HistoryEntry { type: string; points: number; balance_after: number; description: string; order_number: string | null; created_at: string }
interface UserProps {
  balance: number; taka_value: number; tier: Tier; next_tier: Tier | null;
  total_earned: number; history: HistoryEntry[];
}
interface Settings {
  enabled: boolean; points_per_taka: number; taka_per_point: number;
  min_redemption: number; max_redemption_pct: number; tiers: Tier[];
}
interface Props { settings: Settings; userProps: UserProps | null }

const TIER_COLORS: Record<string, string> = {
  Silver: 'bg-gray-100 text-gray-700',
  Gold: 'bg-yellow-100 text-yellow-800',
  Platinum: 'bg-blue-100 text-blue-800',
};

export default function Loyalty({ settings, userProps }: Props) {
  const tierProgress = userProps && userProps.next_tier
    ? Math.min(100, Math.round(((userProps.total_earned - userProps.tier.min) / ((userProps.next_tier.min - userProps.tier.min) || 1)) * 100))
    : 100;

  const howToEarn = [
    { icon: '🛍️', title: 'Shop', desc: `${(settings.points_per_taka * 10).toFixed(0)} point${settings.points_per_taka * 10 !== 1 ? 's' : ''} per ৳10 spent`, color: 'var(--kb-primary-50)', iconColor: 'var(--kb-primary)' },
    { icon: '⭐', title: 'Review products', desc: 'Write a review after receiving your order', color: '#FFF7ED', iconColor: '#F97316' },
    { icon: '👥', title: 'Refer a friend', desc: 'Earn bonus points when they place their first order', color: '#DCFCE7', iconColor: '#16A34A' },
    { icon: '🎁', title: 'Birthday bonus', desc: 'Special bonus points on your birthday', color: '#FEF3C7', iconColor: '#92400E' },
  ];

  if (!settings.enabled) {
    return (
      <Layout>
        <Head title="Loyalty Program" />
        <div className="max-w-xl mx-auto px-4 py-20 text-center">
          <div className="text-5xl mb-4">⭐</div>
          <h1 className="text-2xl font-bold text-gray-900 mb-2">Loyalty Program Coming Soon</h1>
          <p className="text-gray-500">We're setting up our rewards program. Check back soon!</p>
          <Link href="/shop" className="kb-btn kb-btn-primary mt-6 inline-flex outline-none">Browse Products</Link>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <Head title="Loyalty Program — Earn & Redeem Points" />
      <div className="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-10 space-y-8">

        {/* Hero */}
        <section className="kb-card p-6 md:p-8 text-white overflow-hidden" style={{ background: 'linear-gradient(135deg, #4338CA 0%, #312E81 100%)' }}>
          <div className="grid md:grid-cols-2 items-center gap-6">
            <div>
              {userProps ? (
                <>
                  <div className="text-xs uppercase tracking-wide opacity-70 mb-1">Your Klix Points</div>
                  <div className="text-5xl md:text-6xl font-extrabold">
                    {userProps.balance.toLocaleString()}
                    <span className="text-base font-normal opacity-70 ml-2">≈ ৳{Math.round(userProps.taka_value).toLocaleString()}</span>
                  </div>
                  <p className="text-sm opacity-80 mt-2">
                    Earn {(settings.points_per_taka * 10).toFixed(0)} point{settings.points_per_taka * 10 !== 1 ? 's' : ''} per ৳10 spent. Redeem at checkout for instant discounts.
                  </p>
                  <div className="mt-4 flex gap-2 flex-wrap">
                    <span className="text-xs px-3 py-1 rounded-full font-semibold" style={{ background: 'rgba(255,255,255,.18)' }}>
                      {userProps.tier.name} tier
                    </span>
                    {userProps.tier.bonus_pct > 0 && (
                      <span className="text-xs px-3 py-1 rounded-full font-semibold" style={{ background: 'rgba(255,255,255,.18)' }}>
                        +{userProps.tier.bonus_pct}% bonus on every order
                      </span>
                    )}
                  </div>
                </>
              ) : (
                <>
                  <div className="text-xs uppercase tracking-wide opacity-70 mb-1">Klix Loyalty</div>
                  <div className="text-3xl md:text-4xl font-extrabold">Earn Points, Get Rewards</div>
                  <p className="text-sm opacity-80 mt-2">
                    Sign in to start earning {(settings.points_per_taka * 10).toFixed(0)} point{settings.points_per_taka * 10 !== 1 ? 's' : ''} for every ৳10 you spend.
                  </p>
                  <Link href="/login" className="kb-btn mt-4 inline-flex font-semibold px-5 py-2.5 rounded-lg outline-none" style={{ background: 'rgba(255,255,255,.2)', color: '#fff', border: '1px solid rgba(255,255,255,.3)' }}>
                    Sign in to check balance
                  </Link>
                </>
              )}
            </div>

            {userProps && userProps.next_tier && (
              <div className="kb-card p-4" style={{ background: 'rgba(255,255,255,.1)', border: '1px solid rgba(255,255,255,.2)' }}>
                <div className="flex justify-between text-sm text-white/80 mb-2">
                  <span>{userProps.tier.name}</span>
                  <span>{userProps.next_tier.name}</span>
                </div>
                <div className="w-full h-2.5 rounded-full overflow-hidden" style={{ background: 'rgba(255,255,255,.2)' }}>
                  <div className="h-2.5 rounded-full bg-yellow-400 transition-all" style={{ width: `${tierProgress}%` }} />
                </div>
                <p className="text-xs text-white/70 mt-2">
                  {Math.max(0, userProps.next_tier.min - userProps.total_earned).toLocaleString()} more points to unlock{' '}
                  <span className="text-white font-semibold">{userProps.next_tier.name}</span>
                  {userProps.next_tier.bonus_pct > 0 && ` (+${userProps.next_tier.bonus_pct}% bonus)`}
                </p>
              </div>
            )}

            {userProps && !userProps.next_tier && (
              <div className="kb-card p-4" style={{ background: 'rgba(255,255,255,.1)', border: '1px solid rgba(255,255,255,.2)' }}>
                <p className="text-white/80 text-sm">🏆 You're at the highest tier!</p>
                <p className="text-white font-bold mt-1">{userProps.tier.name} member</p>
                {userProps.tier.bonus_pct > 0 && (
                  <p className="text-white/70 text-xs mt-1">+{userProps.tier.bonus_pct}% bonus points on every order</p>
                )}
              </div>
            )}
          </div>
        </section>

        {/* How to earn */}
        <section>
          <h2 className="text-xl font-bold text-gray-900 mb-4">How to Earn Points</h2>
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-3">
            {howToEarn.map((item, i) => (
              <div key={i} className="kb-card p-5">
                <div className="w-10 h-10 rounded-lg flex items-center justify-center text-xl" style={{ background: item.color }}>
                  {item.icon}
                </div>
                <h3 className="font-semibold mt-3 text-gray-900">{item.title}</h3>
                <p className="text-xs text-gray-500 mt-1">{item.desc}</p>
              </div>
            ))}
          </div>
        </section>

        {/* Tiers */}
        <section>
          <h2 className="text-xl font-bold text-gray-900 mb-4">Membership Tiers</h2>
          <div className="grid sm:grid-cols-3 gap-4">
            {settings.tiers.map(tier => (
              <div key={tier.name} className={`kb-card p-5 ${userProps?.tier.name === tier.name ? 'ring-2' : ''}`}
                style={userProps?.tier.name === tier.name ? { '--tw-ring-color': 'var(--kb-primary)' } as any : {}}>
                <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-sm font-bold ${TIER_COLORS[tier.name] ?? 'bg-purple-100 text-purple-800'}`}>
                  {tier.name}
                  {userProps?.tier.name === tier.name && <span className="ml-1.5">← You</span>}
                </span>
                <p className="text-sm text-gray-600 mt-2">
                  {tier.max ? `${tier.min.toLocaleString()} – ${tier.max.toLocaleString()} pts` : `${tier.min.toLocaleString()}+ pts`}
                </p>
                {tier.bonus_pct > 0
                  ? <p className="text-xs text-green-700 font-semibold mt-1">+{tier.bonus_pct}% bonus points per order</p>
                  : <p className="text-xs text-gray-400 mt-1">Standard earning rate</p>
                }
              </div>
            ))}
          </div>
        </section>

        {/* Points history (logged in users only) */}
        {userProps && userProps.history.length > 0 && (
          <section className="kb-card p-5">
            <h2 className="font-semibold text-lg text-gray-900 mb-4">Points History</h2>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-gray-100 text-left text-xs text-gray-500 font-semibold uppercase tracking-wide">
                    <th className="pb-2 pr-4">Date</th>
                    <th className="pb-2 pr-4">Activity</th>
                    <th className="pb-2 pr-4">Reference</th>
                    <th className="pb-2 text-right">Points</th>
                    <th className="pb-2 text-right">Balance</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-50">
                  {userProps.history.map((entry, i) => (
                    <tr key={i}>
                      <td className="py-2.5 pr-4 text-gray-500 text-xs">{entry.created_at}</td>
                      <td className="py-2.5 pr-4 text-gray-700 max-w-[200px] truncate">{entry.description}</td>
                      <td className="py-2.5 pr-4 text-gray-500 font-mono text-xs">
                        {entry.order_number ? `#${entry.order_number}` : '—'}
                      </td>
                      <td className={`py-2.5 pr-4 text-right font-bold ${entry.points >= 0 ? 'text-green-700' : 'text-red-600'}`}>
                        {entry.points >= 0 ? '+' : ''}{entry.points.toLocaleString()}
                      </td>
                      <td className="py-2.5 text-right font-mono text-gray-700">{entry.balance_after.toLocaleString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </section>
        )}

        {/* CTA for guests */}
        {!userProps && (
          <div className="kb-card p-8 text-center">
            <p className="text-gray-700 font-medium text-lg mb-2">Ready to start earning?</p>
            <p className="text-gray-500 text-sm mb-4">Create an account and earn {(settings.points_per_taka * 10).toFixed(0)} point{settings.points_per_taka * 10 !== 1 ? 's' : ''} for every ৳10 you spend.</p>
            <div className="flex gap-3 justify-center">
              <Link href="/register" className="kb-btn kb-btn-primary px-6 py-2.5 outline-none">Create Account</Link>
              <Link href="/login" className="kb-btn px-6 py-2.5 outline-none" style={{ border: '1px solid var(--kb-border)', color: 'var(--kb-ink)' }}>Sign In</Link>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
}
