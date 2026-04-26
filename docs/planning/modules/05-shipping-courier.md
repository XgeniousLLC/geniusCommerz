# 05. Shipping & Courier

**Status:** 🟦 Planned
**Phase:** Phase 4 — Shipping & Courier
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Shipping zone definitions, rate rules, shipping class overrides, CourierContract implementation for Pathao + Steadfast + RedX, dispatch flow (manual + auto-routing), exponential-backoff retry, daily pickup manifests, dispatch SMS, webhook processing, 30-min reconciliation job, and COD remittance reconciliation. Sundarban is v1.1 (SHOULD).

## 2. Requirements Covered

- [ ] REQ-SHP-001 — Admin-defined zones (Inside Dhaka / Outside Dhaka / Sub-Urban)
- [ ] REQ-SHP-002 — Per-zone: base rate, per-kg surcharge, free-shipping threshold, express rate
- [ ] REQ-SHP-003 — Shipping class per product overrides zone defaults
- [ ] REQ-SHP-004 — Rules: flat, weight-based, tiered price-based
- [ ] REQ-DSP-001 — Ops dispatch flow: select → choose courier → createParcel → persist + print
- [ ] REQ-DSP-002 — Auto-routing rules editable in admin
- [ ] REQ-DSP-003 — Retry failed bookings exponential backoff 3×; manual flag on max
- [ ] REQ-DSP-004 — Daily pickup manifest PDF per courier
- [ ] REQ-DSP-005 — SMS customer on dispatch with tracking URL
- [ ] REQ-DSP-006 — Webhook idempotency via event_id; fire OrderDelivered/OrderReturned
- [ ] REQ-DSP-007 — Reconcile job every 30 min for stale Dispatched/InTransit orders
- [ ] REQ-DSP-008 — COD remittance: upload/fetch courier sheet; match; surface variances
- [ ] REQ-SHP-005 — Live courier rate lookup (SHOULD — v1.1)

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

- **OQ-SHP-001:** Pathao sandbox — do we have test credentials? → **blocking** (need from Sharifur)
- **OQ-SHP-002:** Steadfast uses internal rate table (no live API) — confirm rate data source with Sharifur → **blocking**
- **OQ-SHP-003:** Auto-routing conflict resolution — what happens when an order matches two routing rules? Priority order? → non-blocking

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
