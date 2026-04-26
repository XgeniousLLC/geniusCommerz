# 00. Foundation

**Status:** 🟦 Planned
**Phase:** Phase 0 — Foundation
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Sets up the mono-repo skeleton, CI pipeline, local Docker environment, authentication (Sanctum + Spatie RBAC), global settings, audit logging, media library, and infrastructure primitives (feature flags, DLQ, observability). Does NOT include any storefront UI, business domain logic, or integrations.

## 2. Requirements Covered

- [x] REQ-ADM-001 — SPA at /admin; role-aware nav; policy guards
- [x] REQ-ADM-002 — User invite; force password reset on first login
- [ ] REQ-ADM-003 — Activity/audit log per write
- [ ] REQ-ADM-004 — Settings: general, payments, shipping, tax, templates, integrations, legal
- [ ] REQ-SEC-002 — Inputs validated via Form Requests
- [ ] REQ-SEC-003 — CSP restricts scripts
- [x] REQ-SEC-004 — Passwords bcrypt ≥12; TOTP secrets encrypted
- [x] REQ-SEC-005 — Cookies HttpOnly+Secure+SameSite=Lax; idle 8h / absolute 24h
- [ ] REQ-SEC-006 — Webhook signatures verified
- [ ] REQ-SEC-007 — Secrets in AWS Parameter Store
- [ ] REQ-SEC-010 — Rate-limit OTP, login, coupon, checkout
- [ ] REQ-SEC-011 — Audit log on sensitive models
- [ ] REQ-INV-008 — Media library S3 presigned + AVIF/WebP resize
- [ ] NFR-MNT-001 — PSR-12 + PHPStan L6 + Pint in CI
- [ ] NFR-MNT-002 — TS strict + ESLint + Prettier
- [ ] NFR-REL-003 — DLQ with replay UI
- [ ] NFR-OBS-001 — X-Request-Id propagation
- [ ] NFR-OBS-002 — Structured JSON logs
- [ ] NFR-I18N-001 — en + bn locale setup

## 3. Design Notes

- **Framework:** Laravel 12 (composer.json already at ^12.0 — no change needed vs SRS's "L11")
- **Frontend split:** Storefront = React 18 TSX (`resources/js/storefront/main.tsx`). Admin panel = Blade + Tailwind + Alpine.js (`resources/css/admin.css`, `resources/js/admin.js`). React is NOT used in admin.
- **Domain structure:** `app/Domain/{Catalog,Inventory,Pricing,Checkout,Order,Fulfillment,CRM,Marketing,Platform}` — directories created; business logic lives here, HTTP layer stays in `app/Http`
- **PHPStan baseline:** 109 violations from pre-existing admin panel code are baselined — all new code must be error-free at L6
- **Pre-existing test failures (18):** Old admin panel tests using a custom `admin` guard fail; these are legacy code unrelated to klixbd domain — will be superseded in 0.2
- **Tooling summary:** Pint (PSR-12, Laravel preset), PHPStan L6 + Larastan, ESLint (TS strict + React hooks), Prettier, TypeScript strict

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

## 6. Acceptance Criteria (0.1 only)

- [x] `composer lint` → Pint reports 0 violations across all 69 files
- [x] `composer analyse` → PHPStan L6 reports "No errors" (baseline accounts for legacy code)
- [x] `npm run typecheck` → TypeScript strict reports 0 errors
- [x] `app/Domain/` structure matches the 9 domains in SRS section 3.3
- [x] `.env.example` documents all required env vars for MySQL, Redis, Pusher, all payment + courier + tracking keys
- [x] `Makefile` provides `make install`, `make dev`, `make test`, `make lint`, `make analyse`
- [x] `tests/Pest.php` configured with `RefreshDatabase` for Feature + Unit suites

## 7. Decisions / Open Questions

- **OQ-ARCH-001:** ~~Storefront SSR strategy~~ → **Closed.** Admin = Blade. Storefront = React 18 TSX. See ADR-002.

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
