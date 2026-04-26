# klixbd — Requirements Backlog

**Last updated:** 2026-04-24
**Source:** klixbd_SRS_v1.0.pdf

Status legend: 🟦 Planned  🟨 In Progress  🟪 In Testing  🟩 Done  ⬛ Blocked

Sorted by Priority → Module.

---

## MUST — v1.0

| ID | Priority | Module | Summary | Status | Module Doc |
|----|----------|--------|---------|--------|------------|
| REQ-ADM-001 | MUST | admin-rbac | SPA at /admin; role-aware nav; policy guards per route | 🟦 Planned | 08-admin-rbac.md |
| REQ-ADM-002 | MUST | admin-rbac | User invite via email; force password reset first login | 🟦 Planned | 08-admin-rbac.md |
| REQ-ADM-003 | MUST | admin-rbac | Activity/audit log per write (user, ip, ua, before/after diff) | 🟦 Planned | 08-admin-rbac.md |
| REQ-ADM-004 | MUST | admin-rbac | Settings: general, payments, shipping zones, tax, templates, integrations, legal | 🟦 Planned | 08-admin-rbac.md |
| REQ-ADM-005 | MUST | admin-rbac | Global search (orders, customers, products) with fuzzy match | 🟦 Planned | 08-admin-rbac.md |
| REQ-ADM-006 | ~~MUST~~ | admin-rbac | ~~Feature flag panel (Laravel Pennant)~~ | ⛔ Dropped | — |
| REQ-ADM-007 | MUST | admin-rbac | In-app notifications via Pusher private-user.{id}; email digest for supervisors | 🟦 Planned | 08-admin-rbac.md |
| REQ-CHK-001 | MUST | checkout | checkout_sessions row on entry (uuid, cart snapshot, pricing snapshot, TTL 60 min, idempotency_key) | 🟦 Planned | 03-checkout.md |
| REQ-CHK-002 | MUST | checkout | Price freeze: snapshot locked; catalog changes do not affect in-flight sessions | 🟦 Planned | 03-checkout.md |
| REQ-CHK-003 | MUST | checkout | Stock reserved at order create (Redis lock on variant_id); released on Cancelled before Dispatched | 🟦 Planned | 03-checkout.md |
| REQ-CHK-004 | MUST | checkout | OTP policy admin-configurable: off/COD only/all/above threshold. 6-digit, TTL 5min, 3 attempts, rate limits | 🟦 Planned | 03-checkout.md |
| REQ-CHK-005 | MUST | checkout | Fraud: call FraudBD/FraudChecker; score 0–100; ≥80→reject; 50–79→Pending Review; <50→allow | 🟦 Planned | 03-checkout.md |
| REQ-CHK-006 | MUST | checkout | Blacklist: phone, IP, email, device fingerprint. Writes propagate <30s | 🟦 Planned | 03-checkout.md |
| REQ-CHK-007 | MUST | checkout | High-risk: require partial-advance (configurable %, default 20%) via bKash/Nagad before confirm | 🟦 Planned | 03-checkout.md |
| REQ-CHK-008 | MUST | checkout | Idempotency-Key header on place-order; replays return stored response | 🟦 Planned | 03-checkout.md |
| REQ-CHK-009 | MUST | checkout | PSP failure → user-friendly error + session resumable; fallback to COD/SSLCOMMERZ without losing state | 🟦 Planned | 03-checkout.md |
| REQ-CHK-010 | MUST | checkout | Capture fbp, fbc, utm_*, fbclid, gclid, landing_page_id, referrer on session + persist on order | 🟦 Planned | 03-checkout.md |
| REQ-CHK-011 | MUST | checkout | Abandoned: sessions with phone/email captured, not completed after 15 min → abandoned_carts row | 🟦 Planned | 03-checkout.md |
| REQ-CC-001 | MUST | call-center | "My Queue" — orders assigned to agent, sorted by age, with status chips | 🟦 Planned | 06-call-center.md |
| REQ-CC-002 | MUST | call-center | Call action reveals masked phone; optional SIP/WebRTC integration | 🟦 Planned | 06-call-center.md |
| REQ-CC-003 | MUST | call-center | Call screen shows order summary + fraud_score + customer history + address | 🟦 Planned | 06-call-center.md |
| REQ-CC-004 | MUST | call-center | Disposition required to leave queue | 🟦 Planned | 06-call-center.md |
| REQ-CC-010 | MUST | call-center | Round-robin auto-assign to online agents with capacity caps (configurable) | 🟦 Planned | 06-call-center.md |
| REQ-CC-011 | MUST | call-center | Rule-based routing (high-value → senior; Bangla-pref → Bangla agents) | 🟦 Planned | 06-call-center.md |
| REQ-CC-012 | MUST | call-center | Presence via presence-agents.call-center Pusher channel | 🟦 Planned | 06-call-center.md |
| REQ-CC-013 | MUST | call-center | Supervisor wallboard (live queue, longest wait, per-agent occupancy, RTO today) | 🟦 Planned | 06-call-center.md |
| REQ-DSP-001 | MUST | shipping-courier | Ops selects order(s) → Dispatch → choose courier → createParcel → persist + print | 🟦 Planned | 05-shipping-courier.md |
| REQ-DSP-002 | MUST | shipping-courier | Auto-routing rules editable (e.g., Inside Dhaka → Pathao; Outside+≤2kg → Steadfast) | 🟦 Planned | 05-shipping-courier.md |
| REQ-DSP-003 | MUST | shipping-courier | Retry failed bookings with exponential backoff (3×); after max → manual flag | 🟦 Planned | 05-shipping-courier.md |
| REQ-DSP-004 | MUST | shipping-courier | Daily pickup manifest PDF per courier | 🟦 Planned | 05-shipping-courier.md |
| REQ-DSP-005 | MUST | shipping-courier | SMS customer on dispatch with tracking URL | 🟦 Planned | 05-shipping-courier.md |
| REQ-DSP-006 | MUST | shipping-courier | Webhook idempotency via event_id; map to internal states; fire OrderDelivered/OrderReturned | 🟦 Planned | 05-shipping-courier.md |
| REQ-DSP-007 | MUST | shipping-courier | Reconcile job every 30 min: poll getStatus for Dispatched/InTransit orders with last update >6h | 🟦 Planned | 05-shipping-courier.md |
| REQ-DSP-008 | MUST | shipping-courier | COD remittance — upload/fetch courier sheet; match consignments; surface variances | 🟦 Planned | 05-shipping-courier.md |
| REQ-INV-001 | MUST | inventory | Single default warehouse at launch; schema supports multi-warehouse | 🟦 Planned | 02-inventory.md |
| REQ-INV-002 | MUST | inventory | stock_movements is source of truth (qty_delta, reason, reference); variants.stock_qty is derived | 🟦 Planned | 02-inventory.md |
| REQ-INV-003 | MUST | inventory | Low-stock threshold per variant → Pusher event + Ops task | 🟦 Planned | 02-inventory.md |
| REQ-INV-004 | MUST | inventory | Backorder flag → accept orders at zero stock with "dispatch in N days" note | 🟦 Planned | 02-inventory.md |
| REQ-INV-005 | MUST | inventory | Reservation: decrement on create; reverse on Cancelled (pre-dispatch); restore on returns | 🟦 Planned | 02-inventory.md |
| REQ-INV-006 | MUST | inventory | Purchase Orders: Draft→Sent→Receiving→Received→Closed. Moving-average or FIFO cost | 🟦 Planned | 02-inventory.md |
| REQ-INV-007 | MUST | inventory | CSV import/export for products + variants with preview + validation | 🟦 Planned | 02-inventory.md |
| REQ-INV-008 | MUST | inventory | Media library: S3 presigned upload; auto-resize (200/800/1600) + AVIF/WebP; alt + title per asset | 🟦 Planned | 02-inventory.md |
| REQ-LPB-001 | MUST | marketing | Drag-drop editor; sections: hero, features, price, reviews, FAQ, video, timer, inline order form | 🟦 Planned | 07-marketing.md |
| REQ-LPB-002 | MUST | marketing | Per-page slug (/l/<slug>), SEO, optional Pixel ID override | 🟦 Planned | 07-marketing.md |
| REQ-LPB-003 | MUST | marketing | Inline minimal order form (Name, Phone, Address, Qty) → checkout API | 🟦 Planned | 07-marketing.md |
| REQ-LPB-004 | MUST | marketing | A/B test split with lift report | 🟦 Planned | 07-marketing.md |
| REQ-LPB-005 | MUST | marketing | Capture UTM/fbclid; persist on session + order | 🟦 Planned | 07-marketing.md |
| REQ-ORD-001 | MUST | orders | Order creation atomic: insert order + items, decrement stock, write stock_movements + timeline in DB tx | 🟦 Planned | 04-orders.md |
| REQ-ORD-002 | MUST | orders | Order code: KLX-YYMM-XXXXX (sequence-generated, unique) | 🟦 Planned | 04-orders.md |
| REQ-ORD-003 | MUST | orders | Events on create: OrderCreated → SendCapiPurchase, SendOrderSms, NotifyOpsPusher, AssignCallTask, GenerateInvoicePdf | 🟦 Planned | 04-orders.md |
| REQ-ORD-004 | MUST | orders | Admin order list: server-side pagination, filters (status, date, courier, payment, city, campaign, agent, risk, amount, product, phone). Saved views | 🟦 Planned | 04-orders.md |
| REQ-ORD-005 | MUST | orders | Bulk actions: assign agent, dispatch, print labels, cancel, export CSV | 🟦 Planned | 04-orders.md |
| REQ-ORD-006 | MUST | orders | Returns: customer-initiated → RMA → admin approve → reverse pickup → receive → optional restock → refund | 🟦 Planned | 04-orders.md |
| REQ-ORD-007 | MUST | orders | Refund via original source; COD → bKash/Nagad; ≥৳10,000 requires dual approval | 🟦 Planned | 04-orders.md |
| REQ-ORD-008 | MUST | orders | Per-order timeline with actor + diff for every transition | 🟦 Planned | 04-orders.md |
| REQ-ORD-009 | MUST | orders | PDF invoice on confirm (brand + BIN + items + VAT + totals); 80mm thermal label + A5 packing slip | 🟦 Planned | 04-orders.md |
| REQ-SEC-001 | MUST | security | OWASP ASVS L2 assessed before launch | 🟦 Planned | 08-admin-rbac.md |
| REQ-SEC-002 | MUST | security | Inputs validated via Form Requests; output escaped by default | 🟦 Planned | 00-foundation.md |
| REQ-SEC-003 | MUST | security | CSP restricts scripts to self + approved vendors (GTM, Pusher, CDN) | 🟦 Planned | 00-foundation.md |
| REQ-SEC-004 | MUST | security | Passwords bcrypt cost ≥12; TOTP secrets encrypted | 🟦 Planned | 00-foundation.md |
| REQ-SEC-005 | MUST | security | Admin 2FA mandatory; cookies HttpOnly+Secure+SameSite=Lax; idle 8h / absolute 24h | 🟦 Planned | 08-admin-rbac.md |
| REQ-SEC-006 | MUST | security | Webhook signatures verified; unsigned rejected | 🟦 Planned | 00-foundation.md |
| REQ-SEC-007 | MUST | security | Secrets in AWS Parameter Store; never in .env on disk | 🟦 Planned | 00-foundation.md |
| REQ-SEC-008 | MUST | security | Data export + delete endpoints behind verified account | 🟦 Planned | 08-admin-rbac.md |
| REQ-SEC-009 | MUST | security | Card PAN never touches klixbd servers (PCI SAQ A only — hosted redirect) | 🟦 Planned | 03-checkout.md |
| REQ-SEC-010 | MUST | security | Rate-limit OTP, login, coupon apply, checkout submit | 🟦 Planned | 00-foundation.md |
| REQ-SEC-011 | MUST | security | Audit log on sensitive models (products, prices, coupons, orders, users) | 🟦 Planned | 00-foundation.md |
| REQ-SF-001 | MUST | storefront | Persistent cart via signed cart_token cookie; merge on login | 🟦 Planned | 01-catalog.md |
| REQ-SF-002 | MUST | storefront | Bangla/English toggle persists via "locale" cookie; product_translations | 🟦 Planned | 01-catalog.md |
| REQ-SF-003 | MUST | storefront | JSON-LD (Product, BreadcrumbList, Review aggregate) on relevant pages | 🟦 Planned | 01-catalog.md |
| REQ-SF-004 | MUST | storefront | sitemap.xml nightly; robots.txt configurable (blocks all on staging) | 🟦 Planned | 01-catalog.md |
| REQ-SF-005 | MUST | storefront | Pixel + CAPI events for PageView, ViewContent, Search, AddToCart, InitiateCheckout | 🟦 Planned | 11-tracking-capi.md |
| REQ-SF-007 | MUST | storefront | Wishlist stored server-side after login; anonymous in localStorage | 🟦 Planned | 01-catalog.md |
| REQ-SF-008 | MUST | storefront | Reviews: star distribution, photo upload (≤3), verified-purchase badge; gated to delivered orders | 🟦 Planned | 07-marketing.md |
| REQ-SHP-001 | MUST | shipping | Admin-defined zones (default Inside Dhaka / Outside Dhaka / Sub-Urban) | 🟦 Planned | 05-shipping-courier.md |
| REQ-SHP-002 | MUST | shipping | Per-zone: base rate, per-kg surcharge, free-shipping threshold, optional express rate | 🟦 Planned | 05-shipping-courier.md |
| REQ-SHP-003 | MUST | shipping | Shipping class per product overrides zone defaults | 🟦 Planned | 05-shipping-courier.md |
| REQ-SHP-004 | MUST | shipping | Rules: flat, weight-based, tiered price-based | 🟦 Planned | 05-shipping-courier.md |
| REQ-TRK-001 | MUST | tracking | user_data SHA-256 hashed: email, phone, first/last_name, city, country. EMQ ≥7.5 | 🟦 Planned | 11-tracking-capi.md |
| REQ-TRK-002 | MUST | tracking | Persist fbp, fbc on checkout_session; attach to all related server events | 🟦 Planned | 11-tracking-capi.md |
| REQ-TRK-003 | MUST | tracking | external_id — stable hashed customer_id for cross-device attribution | 🟦 Planned | 11-tracking-capi.md |
| REQ-TRK-004 | MUST | tracking | Admin Integrations screen holds GTM Web ID, sGTM URL, GA4 ID, Meta Pixel ID + CAPI token, TikTok IDs | 🟦 Planned | 11-tracking-capi.md |
| REQ-TRK-005 | MUST | tracking | dataLayer uses GA4 enhanced ecommerce shape (view_item, add_to_cart, begin_checkout, purchase) | 🟦 Planned | 11-tracking-capi.md |
| REQ-TRK-006 | MUST | tracking | Server pipeline: EnqueueAnalyticsEvent → capi queue → fanout jobs. Retries 3×. Log to capi_event_log | 🟦 Planned | 11-tracking-capi.md |
| REQ-TRK-007 | MUST | tracking | Cookie banner (Necessary/Preferences/Analytics/Marketing); consent persisted; drop PII when marketing declined | 🟦 Planned | 11-tracking-capi.md |
| REQ-TRK-008 | MUST | tracking | Consent Mode v2 for GA4 | 🟦 Planned | 11-tracking-capi.md |
| REQ-VAR-001 | MUST | catalog | Up to 3 option axes (Size, Color, Material). Variants = Cartesian; disable per combo | 🟦 Planned | 01-catalog.md |
| REQ-VAR-002 | MUST | catalog | Per variant: SKU, barcode, price override, cost_price, weight, image, status, stock_qty, backorder | 🟦 Planned | 01-catalog.md |
| REQ-VAR-003 | MUST | catalog | Variant picker updates image + price + stock + SKU without reload | 🟦 Planned | 01-catalog.md |
| REQ-VAR-004 | MUST | catalog | Bulk variant price matrix editor | 🟦 Planned | 01-catalog.md |

---

## NFRs — MUST

| ID | Category | Target | Summary | Status |
|----|----------|--------|---------|--------|
| NFR-PERF-001 | Latency | P95 ≤400ms | API for PDP/PLP/Cart/Checkout GETs | 🟦 Planned |
| NFR-PERF-002 | Latency | P95 ≤800ms | Place-order including fraud API (ext budget 1.5s) | 🟦 Planned |
| NFR-PERF-003 | FE | LCP ≤2.5s / INP ≤200ms | Storefront mobile P75 | 🟦 Planned |
| NFR-PERF-004 | Throughput | 500 rps | Storefront reads, <1% errors | 🟦 Planned |
| NFR-PERF-005 | Throughput | 100 rps | Place-order, <1% errors | 🟦 Planned |
| NFR-PERF-006 | Realtime | ≤500ms P95 | Pusher publish → UI render | 🟦 Planned |
| NFR-SCAL-001 | Scale | 10× horizontal | Stateless app tier; Redis sessions; autoscale queues | 🟦 Planned |
| NFR-AVAL-001 | Uptime | ≥99.5% | Monthly excl. scheduled maintenance ≤4h off-peak | 🟦 Planned |
| NFR-AVAL-002 | DR | RTO 4h / RPO 30m | RDS Multi-AZ + snapshots + S3 replication | 🟦 Planned |
| NFR-AVAL-003 | Resilience | All integrations | Circuit breakers — vendor outage cannot fail order creation | 🟦 Planned |
| NFR-REL-001 | Retries | 3× exponential | External calls via Http pool + Guzzle middleware | 🟦 Planned |
| NFR-REL-002 | Idempotency | All mutating externals | Payments, courier booking, CAPI | 🟦 Planned |
| NFR-REL-003 | DLQ | With replay UI | Failed jobs → dead-letter table + Ops alert | 🟦 Planned |
| NFR-OBS-001 | Tracing | Request-Id end-to-end | X-Request-Id propagated to logs + outbound calls | 🟦 Planned |
| NFR-OBS-002 | Logs | Structured JSON | Severity, context, request_id, user_id | 🟦 Planned |
| NFR-OBS-003 | Metrics | Custom CloudWatch | orders/min, checkout success %, capi-match % | 🟦 Planned |
| NFR-OBS-004 | Alerts | Defined thresholds | Orders drop >50% 10m; checkout err >2% 5m; capi-match <60% 30m; queue >5k | 🟦 Planned |
| NFR-MNT-001 | Code | PSR-12 + PHPStan L6 + Pint | CI blocks on violation | 🟦 Planned |
| NFR-MNT-002 | FE | TS strict + ESLint + Prettier | Shared component library | 🟦 Planned |
| NFR-MNT-003 | Tests | ≥70% / ≥80% services | Overall + domain services | 🟦 Planned |
| NFR-MNT-004 | ~~Flags~~ | ~~Laravel Pennant~~ | ~~Progressive rollout~~ | ⛔ Dropped |
| NFR-UX-001 | A11y | WCAG 2.1 AA | Key flows (PDP, cart, checkout, account) | 🟦 Planned |
| NFR-I18N-001 | Locale | en + bn | Laravel translations + i18next; Asia/Dhaka UI; libphonenumber | 🟦 Planned |

---

## SHOULD — v1.1

| ID | Priority | Module | Summary | Status | Module Doc |
|----|----------|--------|---------|--------|------------|
| REQ-CC-014 | SHOULD | call-center | Call recording URL attached to order (consent required) | 🟦 Planned | 06-call-center.md |
| REQ-LPB-006 | SHOULD | marketing | Landing page version history + rollback | 🟦 Planned | 07-marketing.md |
| REQ-SHP-005 | SHOULD | shipping | Live courier rate lookup; fallback to internal table | 🟦 Planned | 05-shipping-courier.md |
| REQ-SF-006 | SHOULD | storefront | Live stock indicator via Pusher (public.stock.{variant_id}) when stock <10 | 🟦 Planned | 01-catalog.md |
| REQ-SEC-012 | SHOULD | security | Pentest pre-launch + annually; dep audits in CI | 🟦 Planned | — |
| REQ-SEC-013 | SHOULD | security | SAST (PHPStan/Psalm + ESLint security) in CI; DAST (ZAP) on staging | 🟦 Planned | — |
