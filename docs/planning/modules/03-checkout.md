# 03. Checkout & Payments

**Status:** 🟦 Planned
**Phase:** Phase 2 — Quick Checkout + COD; Phase 3 — Payments
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Quick checkout session lifecycle, OTP verification, fraud screening (FraudBD/FraudChecker), blacklist enforcement, partial-advance flow for high-risk orders, payment driver contract, COD, bKash (Tokenized), Nagad, SSLCOMMERZ, payment webhook handling, refund flows, and abandoned cart session creation. Does NOT include courier dispatch, order timeline beyond creation, or marketing attribution reporting.

## 2. Requirements Covered

- [ ] REQ-CHK-001 — checkout_sessions row on entry (uuid, snapshot, TTL 60m, idempotency_key)
- [ ] REQ-CHK-002 — Price freeze: snapshot locked; catalog changes don't affect in-flight sessions
- [ ] REQ-CHK-003 — Stock reserved at order create (Redis lock on variant_id)
- [ ] REQ-CHK-004 — OTP policy admin-configurable; 6-digit, TTL 5min, 3 attempts, rate limits
- [ ] REQ-CHK-005 — Fraud: FraudBD/FraudChecker; ≥80→reject; 50–79→Pending Review; <50→allow
- [ ] REQ-CHK-006 — Blacklist: phone, IP, email, device fingerprint; propagate <30s
- [ ] REQ-CHK-007 — High-risk: partial-advance (default 20%) before confirm
- [ ] REQ-CHK-008 — Idempotency-Key header; replay returns stored response
- [ ] REQ-CHK-009 — PSP failure → resumable session; fallback to COD/SSLCOMMERZ
- [ ] REQ-CHK-010 — Capture fbp, fbc, utm_*, fbclid, gclid, landing_page_id, referrer
- [ ] REQ-CHK-011 — Abandoned: 15min → abandoned_carts row
- [ ] REQ-SEC-009 — Card PAN never touches klixbd servers (PCI SAQ A)
- [ ] REQ-ORD-006 — Returns: RMA → approve → reverse pickup → restock → refund
- [ ] REQ-ORD-007 — Refund via original source; dual approval ≥৳10k

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

- **OQ-CHK-001:** FraudBD vs FraudChecker — use both in parallel (average score) or primary+fallback? → **blocking**
- **OQ-CHK-002:** Partial-advance payment link — send via SMS only, or also show inline? → non-blocking
- **OQ-CHK-003:** bKash Tokenized — do we have merchant credentials for sandbox? → **blocking** (need from Sharifur)

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
