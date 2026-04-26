# 01. Catalog & Storefront

**Status:** 🟦 Planned
**Phase:** Phase 1 — Catalog & Storefront
**Owner:** Claude Code
**Last updated:** 2026-04-24

## 1. Scope

Product catalog (categories, brands, tags, products, variants, options), storefront SPA (Home, PLP, PDP, Search, Cart, Account), internationalization (en/bn), SEO fundamentals (JSON-LD, sitemap, robots.txt), wishlist, and cart persistence. Does NOT include checkout, payments, or inventory stock adjustments beyond display.

## 2. Requirements Covered

- [ ] REQ-VAR-001 — Up to 3 option axes; Cartesian variants; disable per combo
- [ ] REQ-VAR-002 — Per variant: SKU, barcode, price override, cost_price, weight, image, status, stock_qty, backorder
- [ ] REQ-VAR-003 — Variant picker updates image + price + stock + SKU without reload
- [ ] REQ-VAR-004 — Bulk variant price matrix editor
- [ ] REQ-SF-001 — Persistent cart via signed cart_token cookie; merge on login
- [ ] REQ-SF-002 — Bangla/English toggle; product_translations
- [ ] REQ-SF-003 — JSON-LD (Product, BreadcrumbList, Review aggregate) on relevant pages
- [ ] REQ-SF-004 — sitemap.xml nightly; robots.txt configurable
- [ ] REQ-SF-007 — Wishlist server-side after login; anonymous in localStorage
- [ ] REQ-SF-006 — Live stock indicator via Pusher when stock < 10 (SHOULD — v1.1)

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

- **OQ-CAT-001:** MySQL FULLTEXT search scope at launch — product name + description only, or include brand + tag? → non-blocking

## 8. Sign-off

- [ ] Dev checklist complete
- [ ] Test checklist complete
- [ ] Acceptance demo ready
- [ ] Sharifur approved on _date_
