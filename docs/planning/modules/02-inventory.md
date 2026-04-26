# 02. Inventory

**Status:** 🟦 Planned
**Phase:** Phase 1 — Catalog & Storefront (foundation); Phase 6 — Marketing (Purchase Orders)
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Stock movement ledger, warehouse schema, low-stock alerting, backorder support, stock reservation/release lifecycle, Purchase Orders state machine (with moving-average cost), and CSV import/export. Does NOT include courier dispatch or returns restocking (those belong to modules 04 and 05).

## 2. Requirements Covered

- [ ] REQ-INV-001 — Single default warehouse; schema supports multi-warehouse
- [ ] REQ-INV-002 — stock_movements source of truth; variants.stock_qty is derived
- [ ] REQ-INV-003 — Low-stock threshold per variant → Pusher event + Ops task
- [ ] REQ-INV-004 — Backorder flag → accept orders at zero stock with note
- [ ] REQ-INV-005 — Reservation: decrement on create; reverse on Cancelled; restore on returns
- [ ] REQ-INV-006 — Purchase Orders: Draft→Sent→Receiving→Received→Closed; moving-average or FIFO cost
- [ ] REQ-INV-007 — CSV import/export for products + variants with preview + validation
- [ ] REQ-INV-008 — Media library: S3 presigned upload; auto-resize + AVIF/WebP

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

- **OQ-INV-001:** Default cost method — moving average (SRS default) or FIFO? Config per product or global? → non-blocking (default to moving average)

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
