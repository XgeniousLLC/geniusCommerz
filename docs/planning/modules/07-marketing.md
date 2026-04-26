# 07. Marketing

**Status:** 🟦 Planned
**Phase:** Phase 6 — Marketing
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Coupons (all types + auto-apply + bulk generator), flash sales (scheduled + Pusher countdown + atomic unit limit), bundles (fixed + dynamic + free-gift), landing page builder (drag-drop + A/B split + inline order form), abandoned cart recovery (SMS/email/retarget pipeline), loyalty points ledger (tiers + earn triggers + abuse protection), referrals, reviews (moderation + photo + post-delivery flow), and email/SMS campaigns (segment builder + automations). LPB version history is v1.1 (SHOULD).

## 2. Requirements Covered

- [ ] REQ-LPB-001 — Drag-drop editor; sections: hero, features, price, reviews, FAQ, video, timer, inline order form
- [ ] REQ-LPB-002 — Per-page slug (/l/<slug>), SEO, optional Pixel ID override
- [ ] REQ-LPB-003 — Inline minimal order form (Name, Phone, Address, Qty) → checkout API
- [ ] REQ-LPB-004 — A/B test split with lift report
- [ ] REQ-LPB-005 — Capture UTM/fbclid; persist on session + order
- [ ] REQ-LPB-006 — Version history + rollback (SHOULD — v1.1)
- [ ] REQ-CHK-011 — Abandoned: SMS+Messenger 30m, email 6h, retarget 24h; recovery link pre-fills
- [ ] REQ-SF-008 — Reviews: star distribution, photo upload ≤3, verified-purchase badge; gated to delivered
- [ ] Section 10.1 — Coupons: percent/fixed/free-shipping, auto-apply, bulk single-use generator
- [ ] Section 10.2 — Flash sales: scheduled window, Pusher tick, atomic unit limit
- [ ] Section 10.3 — Bundles: fixed PDP, dynamic Buy-X-get-Y, free-gift-with-purchase
- [ ] Section 10.4 — Loyalty: points ledger, tiers, earn triggers, expiry, redemption cap, abuse protection
- [ ] Section 10.4 — Referrals: referral code + URL, referrer gets points on invitee first delivered order
- [ ] Section 10.8 — Email/SMS campaigns: RFM segment builder, A/B subject, automations (welcome, win-back, review, birthday)

## 3. Design Notes

_To be filled in when module starts._

## 4. Dev Checklist

- [ ] Migrations written and reviewed
- [ ] Models + relationships
- [ ] Services / Actions (business logic)
- [ ] Controllers + FormRequests + Policies
- [ ] Routes registered + API doc snippet updated
- [ ] Jobs / Listeners / Events
- [ ] Frontend components + pages
- [ ] Feature flag gate (if any)
- [ ] Code review self-pass (PSR-12, PHPStan L6, ESLint clean)

## 5. Test Checklist

- [ ] Unit tests (≥ 80% for services here)
- [ ] Feature/HTTP tests per endpoint + policy case
- [ ] Integration tests for external calls (recorded cassettes)
- [ ] Frontend component tests
- [ ] E2E happy path (Playwright)
- [ ] Regression tests for fixed bugs
- [ ] Perf check if hot path (note target vs measured)

## 6. Acceptance Criteria

_To be filled in when module starts._

## 7. Decisions / Open Questions

- **OQ-MKT-001:** LPB drag-drop — build custom or embed GrapeJS/similar OSS? Custom gives more control; OSS saves weeks. → **blocking**
- **OQ-MKT-002:** Abandoned cart SMS provider — SSL Wireless primary with Alpha SMS failover. Confirm sender ID "KLIXBD" is approved. → **blocking** (need from Sharifur)
- **OQ-MKT-003:** Loyalty tier thresholds (Silver/Gold/Platinum) — what are the point thresholds? SRS leaves open. → non-blocking

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
