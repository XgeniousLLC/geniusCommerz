import { Head, Link } from '@inertiajs/react';
import Layout from '../layouts/Layout';
import { usePrice } from '../usePrice';

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

const TIER_STYLE: Record<string, { bg: string; color: string }> = {
  Silver:   { bg: 'var(--av-paper-2)', color: 'var(--av-muted)' },
  Gold:     { bg: 'rgba(149,97,58,.12)', color: 'var(--av-cognac)' },
  Platinum: { bg: 'var(--av-ink)', color: 'var(--av-paper)' },
};

export default function Loyalty({ settings, userProps }: Props) {
  const fmt = usePrice();
  const tierProgress = userProps && userProps.next_tier
    ? Math.min(100, Math.round(((userProps.total_earned - userProps.tier.min) / ((userProps.next_tier.min - userProps.tier.min) || 1)) * 100))
    : 100;

  const howToEarn = [
    { title: 'Shop', desc: `${(settings.points_per_taka * 10).toFixed(0)} pts per ৳10 spent` },
    { title: 'Review', desc: 'Write a review after delivery' },
    { title: 'Refer', desc: 'Bonus when friends order first time' },
    { title: 'Birthday', desc: 'Special birthday bonus' },
  ];

  const W = { maxWidth: 'var(--av-maxw)', margin: '0 auto', padding: '0 var(--av-gutter)' };

  if (!settings.enabled) {
    return (
      <Layout>
        <Head title="Loyalty" />
        <div style={{ ...W, padding: '80px var(--av-gutter)', textAlign: 'center' }}>
          <div style={{ fontFamily: 'var(--av-display)', fontSize: 40, fontWeight: 400, color: 'var(--av-ink)', marginBottom: 12 }}>Loyalty coming soon</div>
          <p style={{ fontSize: 13.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 20 }}>We're setting up rewards. Check back soon.</p>
          <Link href="/shop" className="av-btn av-btn-primary av-btn-md" style={{ textDecoration: 'none' }}>Browse collection</Link>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <Head title="Loyalty — Earn & Redeem" />
      <div style={{ ...W, padding: '32px var(--av-gutter) 64px' }}>

        {/* Hero */}
        <section style={{ border: '1px solid var(--av-line)', background: 'var(--av-ink)', color: 'var(--av-paper)', padding: '32px 28px', marginBottom: 28 }}>
          <div style={{ display: 'grid', gridTemplateColumns: userProps?.next_tier || userProps && !userProps.next_tier ? '1fr 1fr' : '1fr', gap: 24, alignItems: 'center' }} className="av-loyalty-hero">
            <div>
              {userProps ? (
                <>
                  <div style={{ fontSize: 10, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>Your balance</div>
                  <div style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(36px,5vw,56px)', fontWeight: 400, lineHeight: 1, letterSpacing: '-0.02em' }}>
                    {userProps.balance.toLocaleString()} <span style={{ fontFamily: 'var(--av-sans)', fontSize: 14, letterSpacing: '0.12em', textTransform: 'uppercase', fontWeight: 500, color: 'rgba(244,239,229,.6)' }}>pts</span>
                    <span style={{ fontFamily: 'var(--av-sans)', fontSize: 13, fontWeight: 400, color: 'rgba(244,239,229,.6)', marginLeft: 10 }}>≈ {fmt(userProps.taka_value)}</span>
                  </div>
                  <p style={{ fontSize: 13, color: 'rgba(244,239,229,.62)', fontFamily: 'var(--av-sans)', marginTop: 10, lineHeight: 1.6 }}>
                    Earn {(settings.points_per_taka * 10).toFixed(0)} pts per ৳10 spent. Redeem at checkout.
                  </p>
                  <div style={{ marginTop: 14, display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                    <span style={{ fontSize: 11, letterSpacing: '0.08em', textTransform: 'uppercase', padding: '5px 10px', background: 'rgba(244,239,229,.12)', border: '1px solid rgba(244,239,229,.18)', fontFamily: 'var(--av-sans)', color: 'var(--av-paper)' }}>
                      {userProps.tier.name}
                    </span>
                    {userProps.tier.bonus_pct > 0 && (
                      <span style={{ fontSize: 11, letterSpacing: '0.08em', textTransform: 'uppercase', padding: '5px 10px', background: 'rgba(244,239,229,.12)', border: '1px solid rgba(244,239,229,.18)', fontFamily: 'var(--av-sans)', color: 'var(--av-paper)' }}>
                        +{userProps.tier.bonus_pct}% bonus
                      </span>
                    )}
                  </div>
                </>
              ) : (
                <>
                  <div style={{ fontSize: 10, letterSpacing: '0.22em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500, marginBottom: 10 }}>Loyalty</div>
                  <div style={{ fontFamily: 'var(--av-display)', fontSize: 'clamp(28px,4vw,38px)', fontWeight: 400, lineHeight: 1.1, letterSpacing: '-0.012em' }}>Earn points, get rewards</div>
                  <p style={{ fontSize: 13.5, color: 'rgba(244,239,229,.62)', fontFamily: 'var(--av-sans)', marginTop: 10, lineHeight: 1.6 }}>
                    Sign in to earn {(settings.points_per_taka * 10).toFixed(0)} pts per ৳10.
                  </p>
                  <Link href="/login" className="av-btn av-btn-secondary av-btn-sm" style={{ marginTop: 16, background: 'transparent', color: 'var(--av-paper)', borderColor: 'rgba(244,239,229,.3)', textDecoration: 'none' }}>
                    Sign in
                  </Link>
                </>
              )}
            </div>

            {userProps && userProps.next_tier && (
              <div style={{ border: '1px solid rgba(244,239,229,.18)', background: 'rgba(244,239,229,.07)', padding: 18 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 11, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'rgba(244,239,229,.7)', fontFamily: 'var(--av-sans)', marginBottom: 10 }}>
                  <span>{userProps.tier.name}</span>
                  <span>{userProps.next_tier.name}</span>
                </div>
                <div style={{ width: '100%', height: 2, background: 'rgba(244,239,229,.18)', overflow: 'hidden' }}>
                  <div style={{ height: 2, background: 'var(--av-cognac)', width: `${tierProgress}%`, transition: 'width .4s' }} />
                </div>
                <p style={{ fontSize: 11.5, color: 'rgba(244,239,229,.62)', fontFamily: 'var(--av-sans)', marginTop: 10, lineHeight: 1.5 }}>
                  {Math.max(0, userProps.next_tier.min - userProps.total_earned).toLocaleString()} pts to <span style={{ color: 'var(--av-paper)' }}>{userProps.next_tier.name}</span>
                  {userProps.next_tier.bonus_pct > 0 && ` (+${userProps.next_tier.bonus_pct}% bonus)`}
                </p>
              </div>
            )}

            {userProps && !userProps.next_tier && (
              <div style={{ border: '1px solid rgba(244,239,229,.18)', background: 'rgba(244,239,229,.07)', padding: 18, textAlign: 'center' }}>
                <p style={{ color: 'var(--av-paper)', fontFamily: 'var(--av-sans)', fontSize: 13, margin: 0 }}>Highest tier reached</p>
                <p style={{ color: 'var(--av-paper)', fontFamily: 'var(--av-display)', fontSize: 18, marginTop: 4 }}>{userProps.tier.name}</p>
                {userProps.tier.bonus_pct > 0 && <p style={{ color: 'rgba(244,239,229,.62)', fontSize: 11.5, fontFamily: 'var(--av-sans)', marginTop: 6 }}>+{userProps.tier.bonus_pct}% bonus on every order</p>}
              </div>
            )}
          </div>
        </section>

        {/* How to earn */}
        <section style={{ marginBottom: 28 }}>
          <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 14px', letterSpacing: '-0.01em' }}>How to earn</h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 12 }} className="av-loyalty-earn">
            {howToEarn.map((item, i) => (
              <div key={i} style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
                <h3 style={{ fontSize: 13, fontWeight: 500, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', margin: '0 0 4px' }}>{item.title}</h3>
                <p style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', margin: 0, lineHeight: 1.5 }}>{item.desc}</p>
              </div>
            ))}
          </div>
        </section>

        {/* Tiers */}
        <section style={{ marginBottom: 28 }}>
          <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 14px', letterSpacing: '-0.01em' }}>Tiers</h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 14 }} className="av-loyalty-tiers">
            {settings.tiers.map(tier => {
              const active = userProps?.tier.name === tier.name;
              const style = TIER_STYLE[tier.name] ?? { bg: 'var(--av-paper-2)', color: 'var(--av-muted)' };
              return (
                <div key={tier.name} style={{ border: `1px solid ${active ? 'var(--av-ink)' : 'var(--av-line-soft)'}`, background: 'var(--av-paper)', padding: 18, position: 'relative' }}>
                  {active && <div style={{ position: 'absolute', top: 12, right: 12, fontSize: 10, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', fontWeight: 500 }}>You</div>}
                  <span style={{ display: 'inline-flex', alignItems: 'center', padding: '5px 10px', fontSize: 11, letterSpacing: '0.08em', textTransform: 'uppercase', fontWeight: 500, fontFamily: 'var(--av-sans)', background: style.bg, color: style.color }}>
                    {tier.name}
                  </span>
                  <p style={{ fontSize: 13, color: 'var(--av-ink)', fontFamily: 'var(--av-sans)', margin: '10px 0 0' }}>
                    {tier.max ? `${tier.min.toLocaleString()} – ${tier.max.toLocaleString()} pts` : `${tier.min.toLocaleString()}+ pts`}
                  </p>
                  {tier.bonus_pct > 0
                    ? <p style={{ fontSize: 11.5, color: 'var(--av-cognac)', fontFamily: 'var(--av-sans)', marginTop: 6, fontWeight: 500 }}>+{tier.bonus_pct}% bonus</p>
                    : <p style={{ fontSize: 11.5, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginTop: 6 }}>Standard rate</p>
                  }
                </div>
              );
            })}
          </div>
        </section>

        {/* History */}
        {userProps && userProps.history.length > 0 && (
          <section style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 18 }}>
            <h2 style={{ fontFamily: 'var(--av-display)', fontSize: 18, fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 14px' }}>Points history</h2>
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', fontSize: 13, fontFamily: 'var(--av-sans)', borderCollapse: 'collapse' }}>
                <thead>
                  <tr style={{ borderBottom: '1px solid var(--av-line)', textAlign: 'left', fontSize: 10, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--av-muted)', fontWeight: 500 }}>
                    <th style={{ padding: '8px 10px 8px 0' }}>Date</th>
                    <th style={{ padding: '8px 10px' }}>Activity</th>
                    <th style={{ padding: '8px 10px' }}>Ref</th>
                    <th style={{ padding: '8px 10px', textAlign: 'right' }}>Points</th>
                    <th style={{ padding: '8px 0 8px 10px', textAlign: 'right' }}>Balance</th>
                  </tr>
                </thead>
                <tbody>
                  {userProps.history.map((entry, i) => (
                    <tr key={i} style={{ borderBottom: '1px solid var(--av-line-soft)' }}>
                      <td style={{ padding: '10px 10px 10px 0', color: 'var(--av-muted)', fontSize: 11.5, whiteSpace: 'nowrap' }}>{entry.created_at}</td>
                      <td style={{ padding: '10px', color: 'var(--av-ink)', maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{entry.description}</td>
                      <td style={{ padding: '10px', color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', fontSize: 11.5 }}>{entry.order_number ? `#${entry.order_number}` : '—'}</td>
                      <td style={{ padding: '10px', textAlign: 'right', fontWeight: 500, color: entry.points >= 0 ? 'var(--av-cognac)' : '#b94040' }}>
                        {entry.points >= 0 ? '+' : ''}{entry.points.toLocaleString()}
                      </td>
                      <td style={{ padding: '10px 0 10px 10px', textAlign: 'right', color: 'var(--av-ink)', fontWeight: 500 }}>{entry.balance_after.toLocaleString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </section>
        )}

        {!userProps && (
          <div style={{ border: '1px solid var(--av-line-soft)', background: 'var(--av-paper)', padding: 32, textAlign: 'center', marginTop: 28 }}>
            <p style={{ fontFamily: 'var(--av-display)', fontSize: 20, fontWeight: 400, color: 'var(--av-ink)', margin: '0 0 8px' }}>Ready to start?</p>
            <p style={{ fontSize: 13, color: 'var(--av-muted)', fontFamily: 'var(--av-sans)', marginBottom: 16 }}>Create an account and earn {(settings.points_per_taka * 10).toFixed(0)} pts per ৳10.</p>
            <div style={{ display: 'flex', gap: 10, justifyContent: 'center' }}>
              <Link href="/register" className="av-btn av-btn-primary av-btn-sm" style={{ textDecoration: 'none' }}>Create account</Link>
              <Link href="/login" className="av-btn av-btn-secondary av-btn-sm" style={{ textDecoration: 'none' }}>Sign in</Link>
            </div>
          </div>
        )}
      </div>

      <style>{`
        @media(max-width: 860px){
          .av-loyalty-hero{ grid-template-columns: 1fr !important; }
          .av-loyalty-earn{ grid-template-columns: 1fr 1fr !important; }
          .av-loyalty-tiers{ grid-template-columns: 1fr !important; }
        }
        @media(max-width: 540px){
          .av-loyalty-earn{ grid-template-columns: 1fr !important; }
        }
      `}</style>
    </Layout>
  );
}
