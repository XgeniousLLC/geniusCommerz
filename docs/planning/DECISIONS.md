# klixbd — Architectural Decision Records

**Format:** ADR-NNN | Date | Status | Decision | Context | Consequences

---

| ADR | Date | Status | Decision |
|-----|------|--------|----------|
| ADR-001 | 2026-04-24 | Proposed | Modular monolith (Laravel 11 domains) over microservices |
| ADR-002 | 2026-04-24 | Decided | Admin panel = Blade + Tailwind + Alpine.js; Storefront = React 18 TSX |
| ADR-003 | 2026-04-24 | Proposed | Money stored as BDT paisa (integer) everywhere |
| ADR-004 | 2026-04-24 | Proposed | MySQL FULLTEXT at launch; migrate to Meilisearch at >5k SKUs |
| ADR-005 | 2026-04-24 | Proposed | Sanctum for API auth; session cookie for storefront |
| ADR-006 | 2026-04-24 | Proposed | Secrets in AWS Parameter Store, never in .env on disk |
| ADR-007 | 2026-04-24 | Proposed | stock_movements as event log; variants.stock_qty is derived/cached |
| ADR-008 | 2026-04-24 | Proposed | Laravel Pennant for all feature flags — merge early, ship safely |
| ADR-009 | 2026-04-24 | Proposed | All courier + payment integrations implement a typed Contract interface |
| ADR-010 | 2026-04-24 | Proposed | capi_event_log retained 180 days then truncated |

---

## ADR-001 — Modular Monolith

**Date:** 2026-04-24
**Status:** Proposed

**Decision:** Use a single Laravel 11 application with domain directories under `app/Domain/*` (Catalog, Inventory, Pricing, Checkout, Order, Fulfillment, CRM, Marketing, Platform).

**Context:** Team is small; microservices would add operational overhead without scale benefit at launch.

**Consequences:** Domain boundaries enforced by convention and PHPStan; can extract to services later if needed.

---

## ADR-002 — Frontend technology split

**Date:** 2026-04-24
**Status:** Decided

**Decision:** Admin panel = Blade templates + Tailwind CSS + Alpine.js. Storefront = React 18 (TSX) compiled via Vite. No React in admin.

**Context:** Admin panel is an internal tool used by ops/agents/managers — server-rendered Blade is simpler, faster to build, and easier to maintain. React is reserved for the customer-facing storefront where interactivity (variant picker, cart drawer, checkout, realtime stock) justifies the complexity.

**Consequences:** Two separate Vite entry points (`storefront/main.tsx`, `admin.js`). No Inertia.js needed. Admin views live in `resources/views/admin/`. OQ-ARCH-001 closed.

---

## ADR-003 — Money as BDT paisa

**Date:** 2026-04-24
**Status:** Proposed

**Decision:** All monetary values stored as integer BDT paisa. UI formats as ৳1,234.

**Context:** SRS section 1.3 convention. Avoids floating-point rounding errors.

**Consequences:** All arithmetic must use integer math. PHP `Money` library or custom `Paisa` value object recommended.

---

## ADR-004 — MySQL FULLTEXT → Meilisearch

**Date:** 2026-04-24
**Status:** Proposed

**Decision:** Ship with MySQL FULLTEXT for product search. Migrate to Meilisearch (Laravel Scout driver) when SKU count exceeds 5,000.

**Context:** SRS section 3.2. Meilisearch adds infra complexity not justified at launch.

**Consequences:** Scout abstraction used from day one so migration is a driver swap, not a rewrite.

---

## ADR-005 — Auth strategy

**Date:** 2026-04-24
**Status:** Proposed

**Decision:** Storefront = session cookie auth. Admin SPA = Sanctum SPA session. Native/API = Sanctum token. Spatie Permission for RBAC.

**Context:** SRS section 3.2; REQ-ADM-001/002. Need 10 distinct roles with policy-level guards.

**Consequences:** Two auth guards (`web`, `sanctum`). All admin routes policy-gated, not just middleware-gated.
