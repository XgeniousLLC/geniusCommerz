# 09. Reports

**Status:** 🟦 Planned
**Phase:** Phase 7 — CAPI / Analytics / Profit
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

All 14 reports listed in SRS Section 12: Sales Overview, Sales by Product/Variant/Category/Brand, Sales by Channel, Sales by Geo, New vs Returning, Profit P&L (per order-item), ROAS/CAC/LTV, Inventory, Courier Performance, Call Center KPIs, Fraud, CAPI Match Rate, Abandoned Cart Funnel, Flash Sale. Common service queried against read replica. CSV + XLSX export. Scheduled email delivery. Does NOT include Meta Marketing API ad-spend pull (v1.1).

## 2. Requirements Covered

- [ ] Section 12 — Sales Overview (GMV, Orders, AOV, CR; compare-to-prev)
- [ ] Section 12 — Sales by Product / Variant / Category / Brand
- [ ] Section 12 — Sales by Channel (organic, paid social, direct, referral, email, SMS, landing pages)
- [ ] Section 12 — Sales by Geo (city / division / district)
- [ ] Section 12 — New vs Returning (mix, AOV differential, cohort retention)
- [ ] Section 12 — Profit P&L (Revenue_net − COGS − shipping − payment fees − RTO losses − ad spend)
- [ ] Section 12 — ROAS / CAC / LTV per campaign / SKU / cohort
- [ ] Section 12 — Inventory (on-hand, movements, aging, reorder point, ABC analysis)
- [ ] Section 12 — Courier Performance (delivery rate, TAT, RTO rate per courier per zone)
- [ ] Section 12 — Call Center KPIs (calls handled, confirmation rate, AHT, callback adherence, RTO per agent)
- [ ] Section 12 — Fraud (score distribution, blacklist hits, score-to-RTO correlation)
- [ ] Section 12 — CAPI Match Rate (client-only / server-only / deduplicated; top failing events)
- [ ] Section 12 — Abandoned Cart (funnel + recovered revenue)
- [ ] Section 12 — Flash Sale (sell-through %, TTS, conversion uplift vs baseline)

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

- **OQ-RPT-001:** Read replica — RDS read replica or same instance with separate connection? → non-blocking (RDS read replica preferred)
- **OQ-RPT-002:** Profit P&L: "allocated_ad_cost by attribution" — how to apportion campaign spend to an order? Last-touch only, or weighted? → **blocking**

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
