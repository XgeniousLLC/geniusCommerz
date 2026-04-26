# klixbd — Phase Plan

**Last updated:** 2026-04-24
**Stack:** Laravel 11 · MySQL 8 · Redis 7 · React 18 · Tailwind 3 · Pusher

Status legend: 🟦 Planned  🟨 In Progress  🟪 In Testing  🟧 Awaiting Approval  🟩 Approved / Done  ⬛ Blocked

Effort legend: S = ≤3d  M = 4–7d  L = 8–14d  XL = 15d+

---

## Phase 0 — Foundation (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 0.1 | Repo scaffold + local env | 🟩 Approved / Done | S | NFR-MNT-001/002, NFR-OBS-001/002 | Mono-repo, Pint, PHPStan L6, ESLint, Prettier, Pest; CI/CD by Sharifur |
| 0.2 | Auth + RBAC (Sanctum + Spatie) | 🟩 Approved / Done | M | REQ-ADM-001/002, REQ-SEC-004/005 | 10 roles, invite flow; no 2FA |
| 0.3 | Settings + Audit log + Media | 🟩 Approved / Done | M | REQ-ADM-003/004, REQ-SEC-011, REQ-INV-008 | integrations table (encrypted), media S3, audit_logs |
| 0.4 | Horizon + DLQ infra | 🟩 Approved / Done | S | NFR-REL-003 | Horizon queues, failed-jobs + replay UI, circuit breaker |

---

## Phase 1 — Catalog & Storefront (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 1.1 | Catalog (categories, brands, products, variants) | 🟧 Awaiting Approval | L | REQ-VAR-001/002/003/004 | 3-axis variants, Cartesian combos, bulk price matrix |
| 1.2 | Inventory foundation | 🟦 Planned | M | REQ-INV-001/002/003/004/005 | stock_movements source-of-truth, low-stock Pusher |
| 1.3 | Storefront SPA (Home, PLP, PDP, Search, Cart) | 🟦 Planned | XL | REQ-SF-001/002/003/004/007/008 | SSR PLP, JSON-LD, i18n en/bn, wishlist, reviews |
| 1.4 | CSV import/export + media library | 🟦 Planned | M | REQ-INV-007/008 | Async import job, S3 presigned, AVIF/WebP resize |

---

## Phase 2 — Quick Checkout + COD (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 2.1 | Checkout session + OTP + Fraud screening | 🟦 Planned | L | REQ-CHK-001/002/003/004/005/006/007/008/009/010/011 | FraudBD/FraudChecker, blacklist, partial-advance, idempotency |
| 2.2 | Order state machine + events + documents | 🟦 Planned | L | REQ-ORD-001/002/003/004/005/006/007/008/009 | 15-state machine, atomic tx, PDF invoice, 80mm label |

---

## Phase 3 — Payments (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 3.1 | Payment driver contract + COD + bKash | 🟦 Planned | M | PaymentDriverContract, bKash Tokenized | Grant→create→execute→query+poll |
| 3.2 | Nagad + SSLCOMMERZ | 🟦 Planned | M | Nagad PGW, SSLCOMMERZ v4 | Hosted redirects, verify callbacks, webhook security |
| 3.3 | Refunds | 🟦 Planned | S | REQ-ORD-006/007 | Original-source refund, dual-approval ≥৳10k |

---

## Phase 4 — Shipping & Courier (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 4.1 | Shipping zones + rules + classes | 🟦 Planned | S | REQ-SHP-001/002/003/004 | Flat, weight-based, tiered; free-shipping threshold |
| 4.2 | Courier drivers (Pathao, Steadfast, RedX) + dispatch | 🟦 Planned | L | REQ-DSP-001/002/003/004/005/006/007 | CourierContract, auto-routing, manifests, webhooks |
| 4.3 | COD remittance reconciliation | 🟦 Planned | M | REQ-DSP-008 | Upload/fetch courier sheet, variance surface |

---

## Phase 5 — Call Center + Realtime (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 5.1 | Pusher channels + broadcasting auth | 🟦 Planned | S | Section 13 (all channels) | Public/private/presence; ≤500ms P95 |
| 5.2 | Call center module | 🟦 Planned | L | REQ-CC-001/002/003/004/010/011/012/013 | My Queue, round-robin assign, dispositions, wallboard |
| 5.3 | Admin in-app notifications | 🟦 Planned | S | REQ-ADM-007 | private-user.{id} Pusher, email digest |

---

## Phase 6 — Marketing (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 6.1 | Coupons + flash sales + bundles | 🟦 Planned | M | Section 10.1/10.2/10.3 | Atomic validator, Pusher countdown, oversell guard |
| 6.2 | Landing page builder | 🟦 Planned | L | REQ-LPB-001/002/003/004/005 | Drag-drop sections, A/B split, inline order form |
| 6.3 | Abandoned cart recovery | 🟦 Planned | M | REQ-CHK-011, Section 10.6 | SMS+Messenger 30m / email 6h / retarget 24h |
| 6.4 | Loyalty + referrals | 🟦 Planned | M | Section 10.4 | Points ledger, tiers, earn triggers, abuse protection |
| 6.5 | Reviews + post-delivery flow | 🟦 Planned | S | REQ-SF-008, Section 10.7 | Moderation, photo upload, SMS/email request 3d post-delivery |
| 6.6 | Purchase Orders | 🟦 Planned | M | REQ-INV-006 | Draft→Received state machine, moving-average cost |
| 6.7 | Email/SMS campaigns | 🟦 Planned | M | Section 10.8 | Segment builder, A/B subject, automations, opt-out |

---

## Phase 7 — CAPI / Analytics / Profit (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 7.1 | GTM + Meta Pixel + CAPI pipeline | 🟦 Planned | L | REQ-TRK-001/002/003/004/005/006/007/008 | Event match quality ≥7.5, capi_event_log, consent mode v2 |
| 7.2 | GA4 Measurement Protocol | 🟦 Planned | S | REQ-TRK-005/006 | Server-side events, dataLayer shape |
| 7.3 | Reports (all) | 🟦 Planned | L | Section 12 (all reports) | Read-replica, CSV/XLSX export, profit P&L per order-item |

---

## Phase 8 — Hardening & Launch (MUST)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 8.1 | Security hardening | 🟦 Planned | M | REQ-SEC-001–011, NFR-AVAL-003 | OWASP ASVS L2, CSP, Parameter Store, circuit breakers |
| 8.2 | Performance validation (k6) | 🟦 Planned | M | NFR-PERF-001–006, NFR-SCAL-001 | PDP 500rps, place-order 100rps, search 200rps |
| 8.3 | IaC + CI/CD (Terraform + GitHub Actions) | 🟦 Planned | L | Section 20.1/20.2 | ECS, RDS Multi-AZ, blue/green deploy, DR runbook |
| 8.4 | E2E tests (Playwright) | 🟦 Planned | M | Section 20.3 | Browse→PDP→Checkout→COD; flash sale; admin dispatch |

---

## Phase 9 — v1.1 (SHOULD)

| # | Module | Status | Effort | SRS Requirements | Notes |
|---|--------|--------|--------|------------------|-------|
| 9.1 | AamarPay + Rocket payment drivers | 🟦 Planned | S | Section 5.3 SHOULD | Aggregator redundancy |
| 9.2 | Sundarban courier driver | 🟦 Planned | S | Section 8.2 SHOULD | Outside-Dhaka + rural, partial webhook |
| 9.3 | Meta Marketing API (ad spend pull) | 🟦 Planned | S | Section 10.9 SHOULD | Nightly spend pull for ROAS |
| 9.4 | TikTok Events API | 🟦 Planned | S | Section 14 SHOULD | Server-side TikTok attribution |
| 9.5 | Meta Messenger abandoned cart | 🟦 Planned | S | Section 10.6 SHOULD | 24h window Messenger message |
| 9.6 | Live courier rate lookup | 🟦 Planned | S | REQ-SHP-005 | Pathao live; fallback to internal table |
| 9.7 | Address pin (Barikoi/Mapbox) | 🟦 Planned | S | Section 15 SHOULD | Dhaka address geolocation |
| 9.8 | sGTM (server-side GTM) | 🟦 Planned | M | Section 14.1 SHOULD | sgtm.klixbd.com first-party tagging |
| 9.9 | Accounting export (Tally/QuickBooks CSV) | 🟦 Planned | S | Section 15 SHOULD | Month-end journal |
| 9.10 | Landing page version history + rollback | 🟦 Planned | S | REQ-LPB-006 | Versioned sections JSON |
| 9.11 | Pentest + SAST/DAST CI | 🟦 Planned | M | REQ-SEC-012/013 | ZAP on staging, PHPStan/ESLint security rules |
| 9.12 | Call recording integration | 🟦 Planned | S | REQ-CC-014 | SIP/WebRTC recording URL on task |
