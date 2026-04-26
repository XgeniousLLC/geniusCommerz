# 08. Admin Panel & RBAC

**Status:** 🟦 Planned
**Phase:** Phase 0 — Foundation (core); distributed across all phases for module-specific admin screens
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Admin panel at /admin — **Blade + Tailwind + Alpine.js** (not a React SPA). 10-role RBAC via Spatie Permission, user invite + force-password-reset, audit log viewer, settings groups (general, payments, shipping zones, tax, SMS/email templates, integrations, legal), global fuzzy search (orders/customers/products), and in-app notifications (Pusher private-user.{id} + email digest). Individual admin screens for catalog, orders, etc. are delivered within their respective modules. React is used only in the storefront.

## 2. Requirements Covered

- [ ] REQ-ADM-001 — SPA at /admin; role-aware nav; policy guards per route
- [ ] REQ-ADM-002 — User invite via email; force password reset first login
- [ ] REQ-ADM-003 — Activity/audit log per write (user, ip, ua, before/after diff)
- [ ] REQ-ADM-004 — Settings: general, payments, shipping zones, tax, templates, integrations, legal
- [ ] REQ-ADM-005 — Global search (orders, customers, products) with fuzzy match
- [ ] REQ-ADM-007 — In-app notifications via Pusher private-user.{id}; email digest
- [ ] REQ-SEC-001 — OWASP ASVS L2 assessed before launch
- [ ] REQ-SEC-005 — Cookies HttpOnly+Secure+SameSite=Lax; idle 8h / absolute 24h
- [ ] REQ-SEC-008 — Data export + delete endpoints behind verified account
- [ ] REQ-TRK-004 — Admin Integrations screen: GTM, sGTM, GA4, Meta Pixel+CAPI, TikTok IDs

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

- **OQ-ADM-001:** TOTP 2FA — use Google Authenticator compatible (TOTP RFC 6238)? Any backup codes needed? → non-blocking (standard TOTP + backup codes)

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
