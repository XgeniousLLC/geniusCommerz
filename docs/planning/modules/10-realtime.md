# 10. Realtime (Pusher)

**Status:** 🟦 Planned
**Phase:** Phase 5 — Call Center + Realtime
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Pusher Channels setup and broadcasting auth for all 8 channel types defined in SRS Section 13: public storefront global, public stock per variant, public flash-sale per sale, private order, private user, private orderboard admin, private inventory admin, and presence call-center agents. Envelope format standardization, throttle/coalesce logic, and 30s fallback polling endpoints. Does NOT include the business events that trigger publishes — those belong to their respective modules.

## 2. Requirements Covered

- [ ] Section 13 — public.storefront.global channel (flash-sale.updated, banner.changed)
- [ ] Section 13 — public.stock.{variant_id} channel (stock.changed, stock.sold-out, stock.restocked) — coalesce max 1/s
- [ ] Section 13 — public.flash-sale.{id} channel (tick, sold-count, sold-out)
- [ ] Section 13 — private-order.{order_id} channel (order.status.changed, order.tracking.updated)
- [ ] Section 13 — private-user.{user_id} channel (notification.created, assignment.changed)
- [ ] Section 13 — private-orderboard.admin channel (order.created, order.status.changed, order.fraud.flagged)
- [ ] Section 13 — private-inventory.admin channel (stock.low, stock.out, po.received)
- [ ] Section 13 — presence-agents.call-center channel (join/leave + capacity)
- [ ] Section 13 — Envelope: { event, timestamp, actor, data }
- [ ] Section 13 — /api/broadcasting/auth enforces Policies
- [ ] Section 13 — Fallback polling: /api/stock/{id} 30s; /api/orders?since=... 10s
- [ ] NFR-PERF-006 — Pusher publish → UI render ≤500ms P95, ≤1.5s P99

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

- **OQ-RT-001:** Pusher app credentials — do we have sandbox Pusher app for local dev? → **blocking** (need from Sharifur)
- **OQ-RT-002:** Stock coalesce — Redis throttle key per variant, or Pusher trigger-level batching? → non-blocking

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
