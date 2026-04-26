# 04. Orders

**Status:** 🟦 Planned
**Phase:** Phase 2 — Quick Checkout + COD
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Order state machine (15 states), atomic creation transaction, order code generation, event fan-out on create (CAPI, SMS, Pusher, call task, invoice), admin order list with saved views, bulk actions, per-order timeline, PDF invoice + thermal label + packing slip generation. Does NOT include courier driver calls (module 05) or payment refund processing (module 03).

## 2. Requirements Covered

- [ ] REQ-ORD-001 — Atomic order creation: insert order + items, decrement stock, stock_movements, timeline in DB tx
- [ ] REQ-ORD-002 — Order code: KLX-YYMM-XXXXX (sequence-generated, unique)
- [ ] REQ-ORD-003 — Events on create: OrderCreated → SendCapiPurchase, SendOrderSms, NotifyOpsPusher, AssignCallTask, GenerateInvoicePdf
- [ ] REQ-ORD-004 — Admin order list: server-side pagination + 12 filter dimensions + saved views
- [ ] REQ-ORD-005 — Bulk actions: assign agent, dispatch, print labels, cancel, export CSV
- [ ] REQ-ORD-006 — Returns: customer-initiated RMA → admin approve → reverse pickup → restock → refund
- [ ] REQ-ORD-007 — Refund via original source; dual approval ≥৳10k
- [ ] REQ-ORD-008 — Per-order timeline with actor + diff for every transition
- [ ] REQ-ORD-009 — PDF invoice (brand + BIN + items + VAT + totals); 80mm thermal label + A5 packing slip

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

- **OQ-ORD-001:** PDF invoice engine — Laravel DomPDF or Snappy (wkhtmltopdf)? Snappy is heavier but better CSS support for thermal labels. → non-blocking (default DomPDF)
- **OQ-ORD-002:** Dual-approval refund workflow — in-app approval request + email notification, or just in-app? → non-blocking

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
