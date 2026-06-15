# Cobalt Admin Panel — Sprint Plan

Template source: `/Users/sharifur/Downloads/eCommerce Dashboard/template/`  
Design system: **Cobalt** (single `assets/css/styles.css`, `assets/js/ui.js`, `assets/js/layout.js`, `assets/js/icons.js`)  
Stack: Laravel 12 Blade + Alpine.js  
Total pages: 18 HTML files → mapped to existing + 3 new modules

---

## Legend
- `[ ]` todo  `[x]` done  `[-]` skip/out-of-scope
- **NEW** = new module (DB migration + controller + views required)
- **REDESIGN** = existing controller untouched, only views updated

---

## Sprint 0 — Foundation
> Cobalt assets wired into admin layout. All pages get the new shell.

| Ticket | Task | Notes |
|--------|------|-------|
| `S0-01` | `[x]` Copy Cobalt CSS → `public/admin/css/cobalt.css` | Copy `assets/css/styles.css` verbatim |
| `S0-02` | `[x]` Copy Cobalt JS → `public/admin/js/` | `ui.js`, `layout.js`, `icons.js` |
| `S0-03` | `[x]` Rewrite `admin/layouts/admin.blade.php` | Cobalt `<div class="app">` shell, inject sidebar + topbar via layout.js |
| `S0-04` | `[x]` Rewrite `admin/layouts/partials/admin-sidebar.blade.php` | Match Cobalt sidebar nav items to existing routes |
| `S0-05` | `[x]` Rewrite `admin/layouts/partials/admin-header.blade.php` | Match Cobalt topbar (search, notifications, avatar) |
| `S0-06` | `[x]` Remove old admin CSS/JS references | Clean up TailwindCSS / old styles from layout |

---

## Sprint 1 — Dashboard
> `dashboard.html` → `admin/dashboard.blade.php`

| Ticket | Task | Notes |
|--------|------|-------|
| `S1-01` | `[x]` **REDESIGN** dashboard KPI stat grid | Revenue, Orders, AOV, New Customers cards |
| `S1-02` | `[x]` **REDESIGN** Recent Orders mini-table | Last 5 orders with status pills |
| `S1-03` | `[x]` **REDESIGN** Orders by status bar chart | Status breakdown bars |
| `S1-04` | `[x]` **REDESIGN** Quick Actions panel | Links: Add Product, Review Orders, Create coupon, Settings |
| `S1-05` | `[x]` **REDESIGN** Low Stock alert banner | Warning card with `--warning` color |

---

## Sprint 2 — Catalog
> Products, Product Form, Categories, Brands, Attributes

| Ticket | Task | Notes |
|--------|------|-------|
| `S2-01` | `[x]` **REDESIGN** `admin/products/index.blade.php` | `products.html` — table with thumb, variants pill, stock, status |
| `S2-02` | `[x]` **REDESIGN** `admin/products/create.blade.php` | `product-form.html` — two-column layout, image upload, variants |
| `S2-03` | `[x]` **REDESIGN** `admin/products/edit.blade.php` | Same as create |
| `S2-04` | `[x]` **REDESIGN** `admin/categories/index.blade.php` | `categories.html` — tree/table, parent badges |
| `S2-05` | `[x]` **REDESIGN** `admin/brands/index.blade.php` | `brands.html` — logo thumb, product count |
| `S2-06` | `[x]` **REDESIGN** `admin/brands/create.blade.php` + `edit.blade.php` | Inline modal or separate page |
| `S2-07` | `[x]` **REDESIGN** `admin/attributes/index.blade.php` + create + edit | `attributes.html` — expandable rows for values |

---

## Sprint 3 — Orders
> Orders list + Order detail

| Ticket | Task | Notes |
|--------|------|-------|
| `S3-01` | `[x]` **REDESIGN** `admin/orders/index.blade.php` | `orders.html` — status filter tabs, table |
| `S3-02` | `[x]` **REDESIGN** `admin/orders/show.blade.php` | `order-detail.html` — timeline, line items, customer panel |

---

## Sprint 4 — Customers & Loyalty
> Users list + Loyalty settings

| Ticket | Task | Notes |
|--------|------|-------|
| `S4-01` | `[x]` **REDESIGN** `admin/users/index.blade.php` | `customers.html` — avatar initials, order count, LTV |
| `S4-02` | `[x]` **REDESIGN** `admin/loyalty/settings.blade.php` + transactions | `loyalty.html` — tiers, points rules |

---

## Sprint 5 — New Modules (NEW)
> Three modules not in current codebase — need migration + controller + views

### S5-A: Sourcing
> Track product cost price vs sell price, AI suggest price. `sourcing.html`

| Ticket | Task | Notes |
|--------|------|-------|
| `S5-A-01` | `[x]` Migration: `sourcing_items` table | `id, product_id, supplier, cost_price, sell_price, moq, lead_days, status (sourced/listed)` |
| `S5-A-02` | `[x]` Model + Controller: `SourcingController` | `index`, `store`, `update`, `destroy` |
| `S5-A-03` | `[x]` Route: `GET /admin/sourcing` → `admin.sourcing.index` | Add to `routes/admin.php` |
| `S5-A-04` | `[x]` View: `admin/sourcing/index.blade.php` | KPI stats + table, AI "Suggest" button calls existing AI endpoint |
| `S5-A-05` | `[x]` Sidebar nav entry | Under Catalog section |

### S5-B: Purchase Orders
> Restock inventory, track supplier shipments. `purchase-orders.html`

| Ticket | Task | Notes |
|--------|------|-------|
| `S5-B-01` | `[x]` Migration: `purchase_orders` + `purchase_order_items` | Pre-existed in `2026_05_18_000002_create_accounting_tables.php` |
| `S5-B-02` | `[x]` Model + Controller: `PurchaseOrderController` | Pre-existed; added status filter to index |
| `S5-B-03` | `[x]` Route: `GET /admin/accounting/purchases` → `admin.accounting.purchases.*` | Pre-existed |
| `S5-B-04` | `[x]` View: `admin/accounting/purchases/{index,create,show}.blade.php` | Full Cobalt rewrite: stat-grid, seg tabs, table, form, receive form |
| `S5-B-05` | `[x]` Sidebar nav entry | In Finance section (pre-existed) |

### S5-C: Ad Spend
> Marketing channel ROI dashboard. `ad-spend.html` (read-only view, no DB needed — integrations-driven)

| Ticket | Task | Notes |
|--------|------|-------|
| `S5-C-01` | `[x]` Controller: `AdSpendController@index` | Pre-existed |
| `S5-C-02` | `[x]` Route: `GET /admin/accounting/ad-spend` → `admin.accounting.ad-spend.*` | Pre-existed |
| `S5-C-03` | `[x]` View: `admin/accounting/ad-spend/index.blade.php` | Full Cobalt rewrite: stat-grid + modal form + records table |
| `S5-C-04` | `[x]` Sidebar nav entry | In Finance section (pre-existed) |

---

## Sprint 6 — Reports
> `reports.html` → `admin/reports/index.blade.php`

| Ticket | Task | Notes |
|--------|------|-------|
| `S6-01` | `[x]` **REDESIGN** reports page | `reports.html` — new `admin/reports/index` overview with KPIs, best sellers, category bars, all-reports grid |

---

## Sprint 7 — Team & Configuration
> Team/Admins + Roles, Settings, Integrations, AI Settings

| Ticket | Task | Notes |
|--------|------|-------|
| `S7-01` | `[x]` **REDESIGN** `admin/admins/index.blade.php` | `team.html` — Members tab: avatar, role pill, 2FA badge, last active |
| `S7-02` | `[x]` **REDESIGN** `admin/roles/index.blade.php` | `team.html` Roles tab — permissions matrix |
| `S7-03` | `[x]` **REDESIGN** `admin/settings/index.blade.php` | `settings.html` — sidebar tab nav, grouped inputs |
| `S7-04` | `[x]` **REDESIGN** `admin/integrations/index.blade.php` | `integrations.html` — provider cards, connect/disconnect |
| `S7-05` | `[x]` **REDESIGN** `admin/ai-settings/index.blade.php` | `ai-settings.html` — model selector, API key input |

---

## Sprint 8 — Remaining Admin Pages (Cobalt Adaptation)
> No new controllers or migrations — pure view rewrites to Cobalt design pattern.
> Pattern: `.page-head` · `.stat-grid` · `.card flush` + `.table` · `.card pad` + `.field`/`.lbl`/`.input` · `.pill sm {color}` · remove all Tailwind classes · remove `@section('breadcrumbs')`.

### S8-A: Content
| Ticket | Task | Files |
|--------|------|-------|
| `S8-A-01` | `[x]` Blogs index | `admin/blogs/index.blade.php` |
| `S8-A-02` | `[x]` Blog create/edit | `admin/blogs/{create,edit}.blade.php` |
| `S8-A-03` | `[x]` Blog Categories index | `admin/blog-categories/index.blade.php` |
| `S8-A-04` | `[x]` Pages index | `admin/pages/index.blade.php` |
| `S8-A-05` | `[x]` Pages create/edit | `admin/pages/{create,edit}.blade.php` |

### S8-B: Commerce
| Ticket | Task | Files |
|--------|------|-------|
| `S8-B-01` | `[x]` Coupons index | `admin/coupons/index.blade.php` |
| `S8-B-02` | `[x]` Coupons create/edit | `admin/coupons/{create,edit}.blade.php` + `_form.blade.php` |
| `S8-B-03` | `[x]` Refunds index | `admin/refunds/index.blade.php` |
| `S8-B-04` | `[x]` Refund show | `admin/refunds/show.blade.php` |

### S8-C: Storefront Tools
| Ticket | Task | Files |
|--------|------|-------|
| `S8-C-01` | `[x]` Media index | `admin/media/index.blade.php` |
| `S8-C-02` | `[x]` Menus index | `admin/menus/index.blade.php` |
| `S8-C-03` | `[x]` Menu create/edit | `admin/menus/{create,edit}.blade.php` + `_item.blade.php` |

### S8-D: Localization
| Ticket | Task | Files |
|--------|------|-------|
| `S8-D-01` | `[x]` Languages index | `admin/languages/index.blade.php` |
| `S8-D-02` | `[x]` Language edit | `admin/languages/edit.blade.php` |
| `S8-D-03` | `[x]` Currencies index | `admin/currencies/index.blade.php` |

### S8-E: System
| Ticket | Task | Files |
|--------|------|-------|
| `S8-E-01` | `[x]` Order Settings | `admin/order-settings/index.blade.php` |
| `S8-E-02` | `[x]` Sitemap | `admin/sitemap/index.blade.php` |
| `S8-E-03` | `[x]` Audit Log | `admin/audit/index.blade.php` |
| `S8-E-04` | `[x]` Failed Jobs | `admin/failed-jobs/index.blade.php` |
