# 11. Tracking (GTM + Meta Pixel + CAPI + GA4)

**Status:** 🟦 Planned
**Phase:** Phase 7 — CAPI / Analytics / Profit
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Dual-pipe tracking: client pipe (GTM Web Container → Meta Pixel + GA4 + TikTok Pixel) and server pipe (Laravel Listeners → CAPI + GA4 Measurement Protocol). Covers all 13 events in SRS Section 14.2. Cookie consent banner (Consent Mode v2). capi_event_log table + fanout job architecture. Admin integrations screen for all tracking credentials. TikTok Events API and sGTM are v1.1 (SHOULD).

## 2. Requirements Covered

- [ ] REQ-SF-005 — Pixel + CAPI events for PageView, ViewContent, Search, AddToCart, InitiateCheckout
- [ ] REQ-TRK-001 — user_data SHA-256 hashed (email, phone, first/last_name, city, country). EMQ ≥7.5
- [ ] REQ-TRK-002 — Persist fbp, fbc on checkout_session; attach to all related server events
- [ ] REQ-TRK-003 — external_id — stable hashed customer_id for cross-device attribution
- [ ] REQ-TRK-004 — Admin Integrations screen: GTM, sGTM, GA4, Meta Pixel+CAPI, TikTok IDs
- [ ] REQ-TRK-005 — dataLayer uses GA4 enhanced ecommerce shape
- [ ] REQ-TRK-006 — Server pipeline: EnqueueAnalyticsEvent → capi queue → fanout jobs. Retries 3×. capi_event_log
- [ ] REQ-TRK-007 — Cookie banner (Necessary/Preferences/Analytics/Marketing); consent persisted
- [ ] REQ-TRK-008 — Consent Mode v2 for GA4
- [ ] Section 14.2 — All 13 events (PageView, ViewContent, Search, AddToCart, InitiateCheckout, AddPaymentInfo, Lead, Purchase, Purchase-Delivered, Subscribe, CompleteRegistration, ReviewSubmitted)

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

- **OQ-TRK-001:** event_id generation — client-side UUID (checkout_session scoped) or server-generated? SRS says "client-side or checkout_session scoped". → non-blocking (client-side UUID, fallback to session id)
- **OQ-TRK-002:** Cookie banner library — build custom or use Cookiebot/CookieYes? Custom gives full control for Consent Mode v2 integration. → non-blocking
- **OQ-TRK-003:** Meta CAPI token — do we have a System User token for the Business Manager? → **blocking** (need from Sharifur)

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
