# geniusCommerz — Feature Reference for Landing Pages

The source of truth for any landing page, product page or marketing copy about
geniusCommerz. Every name, count and rule below is verified against the codebase.

**How to use this file.** Copy facts from here verbatim. If you want to state something
that is not in this document, mark it unverified rather than inventing it — a wrong
gateway name or an invented statistic is worse on a free-software page than saying less.
Section 24 lists what the platform deliberately does *not* do; include those honestly.

---

## 1. One-line positioning

> A free, self-hosted Laravel eCommerce platform with 98 integrations built in — sell
> across borders without paying a commission.

**Longer:** geniusCommerz is a self-hosted eCommerce platform for merchants selling
internationally. It ships with 39 payment gateways, 20 SMS gateways, 19 shipping carriers
and 11 fraud checkers, and handles the parts of cross-border commerce most free carts leave
to you: per-country address forms, currency conversion recorded on every order,
destination-based tax and shipping zones. MIT licensed, no commission, no per-order fee.

---

## 2. Verified counts

| Metric | Value |
|---|---|
| Payment gateways | **39** |
| SMS gateways | **20** |
| Shipping carriers | **19** |
| Bangladeshi couriers | **3** |
| Fraud checkers | **11** |
| AI providers | **4** |
| Exchange-rate sources | **2** |
| **Total integrations** | **98** |
| Countries supported | **213** |
| Currencies supported | **155** |
| Automated tests | **236** |
| Commission taken | **0%** |

---

## 3. Technology

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL 8 |
| Admin panel | Blade + Alpine.js (Cobalt design system) |
| Storefront | Inertia v3 + React 19 + TypeScript |
| Build | Vite |
| Queues | Laravel Horizon |
| Tests | Pest |
| Auth | Dual guard — admin and customer fully separated |
| Permissions | Role-based access control |

---

## 4. Payment gateways (39)

Enable as many as you want and order them for checkout. Each declares the currencies and
countries it can settle, so it is never shown for a payment it would reject.

### Worldwide (7)
| Gateway | Notes |
|---|---|
| **Stripe** | Cards, wallets, ~50 local methods through one integration |
| **PayPal** | Orders v2, 23 settlement currencies |
| **Adyen** | Enterprise coverage via Pay by Link |
| **Authorize.Net** | Accept Hosted; large US install base |
| **Paddle** | Merchant of record — remits EU VAT and US sales tax for you |
| **2Checkout** | Merchant of record (Verifone) |
| **Cash on Delivery** | A real driver, so checkout has one code path |

### Bangladesh (5)
**SSLCOMMERZ** · **bKash** · **Nagad** · **aamarPay** · **ShurjoPay**

### India (5)
**Razorpay** · **Cashfree** · **PayU India** · **PhonePe** · **Paytm**
All support UPI, cards and netbanking.

### Pakistan (2)
**Easypaisa** · **JazzCash**

### Gulf & MENA (4)
**PayTabs** · **Tap Payments** (KNET, mada, Benefit, Apple Pay) · **Moyasar** (mada, STC Pay) ·
**Fawry** (Egypt — includes cash at Fawry outlets)

### Africa (7)
**Paystack** · **Flutterwave** · **Monnify** · **M-Pesa** (Kenya) · **MTN MoMo** ·
**Yoco** (South Africa) · **Peach Payments**

### Europe (3)
**Mollie** (iDEAL, Bancontact, SEPA) · **iyzico** (Türkiye) · **Vipps MobilePay** (Nordics)

### LatAm (2)
**MercadoPago** · **Pagar.me** (Pix is the default method)

### Southeast Asia (2)
**Midtrans** (Indonesia) · **Xendit** (Indonesia, Philippines)

### North America & APAC (2)
**Square** (US, CA, GB, AU, JP) · **KakaoPay** (South Korea)

### Three flow types worth explaining in copy
- **Hosted redirect** — most gateways. Customer leaves, pays, returns.
- **Signed form post** — JazzCash, Authorize.Net, 2Checkout, Paytm. Handled by an
  auto-submitting bridge page, because a signed form cannot become a redirect.
- **Handset push** — M-Pesa, MTN MoMo, Easypaisa. The customer approves on their phone;
  the order settles from the callback.

---

## 5. SMS gateways (20)

Used for order confirmations, status updates and login OTP.

| Region | Gateways |
|---|---|
| Worldwide (8) | Twilio · Vonage · MessageBird · Plivo · Amazon SNS · Infobip · Sinch · Telnyx |
| Bangladesh (3) | BulkSMSBD · SMS.BD · MRAM |
| India (3) | MSG91 · Gupshup · Fast2SMS |
| Gulf & MENA (3) | Unifonic · Taqnyat · Cequens |
| Africa (3) | Africa's Talking · Termii · Clickatell |

**How numbers are handled.** Normalised to E.164 once, then reshaped per gateway —
Bangladeshi gateways receive a local `01…` number, Fast2SMS a bare 10-digit Indian number,
Africa's Talking and Telnyx international format with the `+`, most others without it.

**Admin can** send a test message and check the account balance from the SMS page.

---

## 6. Fraud checkers (11)

| Region | Checkers |
|---|---|
| Worldwide (4) | IPQualityScore · SEON · Sift · MaxMind minFraud |
| Europe (1) | Ravelin |
| India (1) | Bureau — mobile tenure, porting history, network signals |
| Gulf (1) | Uqudo |
| Africa (2) | Smile ID · Youverify |
| Bangladesh (2) | FraudBD · BDCourier — courier delivery history |

**One risk scale.** Providers score on incompatible scales — Sift returns 0–1, SEON and
minFraud 0–100, Ravelin a recommendation, Smile ID a verdict. All are normalised to
`safe / low risk / mid risk / high risk / unknown` plus a 0–100 score, so the admin reads
the same whichever you use.

**Region-aware.** A checker is skipped entirely when it does not serve the destination
country — a Bangladeshi courier-history service is never asked to score a US order.

**Optional pixel gate.** A flagged customer can suppress the purchase events sent to Meta,
TikTok and GA4, keeping fraudulent orders out of your ad-platform optimisation data.

---

## 7. Shipping carriers (19) and couriers (3)

| Region | Carriers |
|---|---|
| Worldwide (5) | DHL Express · FedEx · UPS · EasyPost · Shippo |
| India (3) | Delhivery · Shiprocket · Blue Dart |
| Nigeria & Africa (4) | Sendbox · GIG Logistics · Kwik Delivery · Bob Go (South Africa) |
| Brazil (3) | Melhor Envio · Correios · Loggi |
| Gulf (4) | Aramex · SMSA Express · Naqel Express · Torod |
| Bangladesh couriers (3) | Pathao · RedX · Steadfast |

**Rate precedence** — highest first:
1. Live courier quote (Bangladeshi couriers, city → zone → area)
2. Live carrier rate — falls through on a rating outage rather than blocking checkout
3. Configured shipping zone rate
4. Global flat rate

Products marked *shipping included* always ship free. Cart weight is calculated
server-side from real product weights, so a browser cannot understate it.

**Shipping zones** match country → state → postal pattern, most specific wins. Each zone
holds rates with a base price, optional per-kg charge, weight bands, order-value bands, a
free-above threshold and a delivery estimate.

---

## 8. Multi-currency

**155 currencies** with correct decimal handling — JPY and KRW have none, KWD and BHD have
three, and gateways receive integer minor units accordingly.

### How money is recorded
Every order stores its money **twice**:
- **Base currency** — `subtotal`, `shipping_cost`, `tax`, `total`. Every report sums these.
- **Presentment** — what the customer actually saw, plus the exchange rate used, frozen at
  order time.

The base currency is snapshotted per order, so history stays interpretable even if the
store later changes its base currency. Presentment totals are stored, never recomputed —
rounding is per-line and non-invertible, so a recomputed figure would not reproduce the
invoice the customer agreed to.

### Exchange rates
- Per currency: **manual** or **automatic**
- Automatic rates refresh **hourly** from a free keyless source, or ExchangeRate-API
- A move greater than **15%** in one refresh is **rejected and flagged**, not applied — one
  bad API response cannot reprice the catalogue
- A failed fetch **keeps the previous rate**; it never zeroes one
- A **markup percentage** per currency covers FX spread and cross-currency gateway fees
- Rates can be **locked** per currency
- A **staleness badge** appears when a rate has not refreshed in over a day

### Protecting the customer's quote
- The rate is **pinned to the browsing session** (45 minutes), so a scheduled refresh cannot
  change a total mid-visit
- At checkout, if the rate has drifted more than **0.5%** since the page was drawn, the
  order is **re-quoted** rather than silently charged the new rate

### Storefront
Cookie-based currency switcher. Conversion is display-only in the browser; the server is
always authoritative for what is charged.

---

## 9. Tax

- **Zones** match country → state → postal pattern, most specific wins. A San Francisco
  postal zone beats a statewide California zone beats a country-wide US zone.
- **Rates stack** within a zone — how US state-plus-county and Canadian GST-plus-PST work.
- **Tax classes** per product: `standard`, `reduced`, `zero`. A zone that says nothing
  about a class does not tax it, which is what makes zero-rated goods genuinely zero.
- **Inclusive or exclusive pricing.** Inclusive extracts the tax from the listed price
  rather than adding it.
- **Discounts apply before tax**, spread proportionally across lines.
- **Shipping taxability** is set per rate.
- The breakdown is **frozen onto the order**, and the invoice reads it back — so the
  invoice and the amount charged can never disagree.

### Tax templates (one click)
| Template | Creates |
|---|---|
| EU VAT | 27 member states with standard rates |
| UK VAT | 20% standard plus 5% reduced |
| US sales tax | 46 state-level base rates |
| Canada GST/HST/PST | 13 provinces — HST combined, GST+PST stacked |
| Single-rate countries | 15 countries incl. Australia, NZ, Singapore, UAE, Saudi, South Africa, India, Japan |

Re-applying a template **skips zones you already have**, so tuned rates survive.

### Shipping templates
Domestic flat rate · domestic weight bands · Europe (27) · North America · Gulf ·
rest-of-world fallback. Built around your configured store country.

---

## 10. Cart, discounts and promotions

### Coupons
| Rule | Detail |
|---|---|
| Discount type | **Fixed amount** or **percentage** |
| Minimum order | Coupon only valid above this subtotal |
| Maximum discount | Caps a percentage coupon at a currency amount |
| Total usage limit | Across all customers |
| Per-customer limit | How many times one customer may use it |
| Scheduling | Start date and expiry date |
| Product scoping | Restrict to specific products |
| Category scoping | Restrict to specific categories |
| Auto-apply | Applies without the customer entering a code |
| Active toggle | Enable or disable without deleting |

The discount can never exceed the subtotal, and usage is counted on the order.

### Cart goals (progress incentives)
A progress bar in the cart drawer — "add $20 more for free shipping". Each goal is a
spend threshold with one of three rewards:
- **Free shipping**
- **Percentage discount**
- **Fixed amount off**

Goals carry a custom label and are configured in Settings → Cart. Multiple thresholds can
stack up as the customer adds items.

### Loyalty points
| Setting | Meaning |
|---|---|
| Enabled | Master switch |
| Earn rate | Points awarded per unit of currency spent |
| Redemption rate | Currency value of one point |
| Minimum redemption | Points needed before redeeming |
| Maximum redemption % | Cap on how much of an order points may cover |
| Tiers | Named customer tiers |

Points are earned on order and redeemable at checkout, with the discount applied before
tax so tax is charged on what the customer actually pays.

### Other pricing levers
- **Compare-at price** — shows a strike-through original
- **Cost price** — feeds profit reporting
- **Per-variant pricing**
- **Free shipping per product** — the *shipping included* flag
- **Free-above threshold** per shipping rate
- **Pre-order** — enable per product with a message and expected date

---

## 11. Catalogue

Products with variants and attributes · brands · nested categories · media library with
image conversions · tax class · weight · dimensions · HS code and country of origin (for
customs) · SKU · stock tracking · featured flag · pre-order · FAQs per product · trust
badges per product · SEO meta per product · draft/active/archived status.

---

## 12. Orders

Full lifecycle: pending → confirmed → processing → shipped → delivered, plus cancelled and
refunded. Payment status: unpaid, pending, paid, failed, partially refunded, refunded.

Activity timeline per order · refunds · printable invoice and packing slip · courier
dispatch with consignment tracking · bulk status actions · admin-created orders ·
order source tracking (website, landing page, walk-in) · internal admin notes ·
customer-facing order tracking page.

---

## 13. Customers

Accounts with dual-guard auth · email/password or **phone OTP login** · saved addresses
with country, state and postal code · order history · wishlist · product reviews with
ratings · loyalty balance · refund requests · lifetime-value reporting.

---

## 14. Marketing and SEO

Coupons · cart goals · loyalty · landing pages per product · blog with categories and
comments · **SEO analysis scoring** (0–100, graded excellent to critical) · meta
information on products, blogs and pages · sitemap generation · **Google Merchant Center
feed** · **Facebook catalogue feed** · announcement bar · configurable trust badges.

---

## 15. Tracking and analytics

| Platform | Client-side | Server-side |
|---|---|---|
| Google Tag Manager | Yes | — |
| Meta | Pixel | Conversions API |
| GA4 | via GTM | Measurement Protocol |
| TikTok | Pixel | Events API |

Includes a **pixel event log** showing every event sent, its success or failure, and the
reason — including events deliberately blocked by the fraud gate.

All events report the **currency the customer was actually charged in**, so ad-platform
ROAS stays correct on international sales.

---

## 16. Accounting and reporting

Purchase orders with supplier tracking and receiving · ad spend by channel · profit
reports · product sourcing with cost versus sell price and AI price suggestions ·
sales reports · customer lifetime value · refund analysis · demand trends · coupon usage ·
top products · inventory reports · payment-method breakdown.

---

## 17. Localisation

Multi-language with polymorphic content translations (products, blogs) · translatable
storefront strings · cookie-based locale switching · multi-currency · per-country address
forms · international dial-code selector · store timezone applied at boot · RTL-capable.

---

## 18. AI features (4 providers)

**OpenAI · Anthropic Claude · Google Gemini · DeepSeek** — one set as default.

Generates product descriptions · blog content · meta descriptions · content translation ·
sell-price suggestions from cost price.

---

## 19. Admin panel

Dedicated pages: Dashboard · Products · Categories · Brands · Attributes · Orders ·
Refunds · Coupons · Customers · Team & roles · Blogs · Pages · Media · Menus · Loyalty ·
Reports · **Payment Gateways** · **SMS Gateways** · **Fraud Checks** · **Shipping Zones** ·
**Tax Zones** · **Currencies** · Integrations · AI Settings · Languages · Sitemap ·
Audit log · Failed jobs · Settings.

Settings tabs: general, meta, social, storefront, payment, shipping, cart, legal, tracking,
feeds, currencies, accounting, storage, notifications.

---

## 20. Storefront

Home · shop listing with filters · product detail · cart drawer · country-aware checkout ·
order confirmation · order tracking · blog · wishlist · loyalty · static pages ·
account dashboard, orders, addresses, refunds and reviews · login, register, password
reset, phone OTP · mobile bottom navigation · debounced search autocomplete ·
locale and currency switchers.

---

## 21. Payment settlement — worth its own section in copy

1. The customer picks a gateway; the order is created as **pending**
2. They are sent to pay — redirect, signed form post, or handset prompt
3. The gateway notifies the server by **webhook**; the **signature is verified** before
   anything is written
4. Only then is the order marked **paid**

**The return URL never marks an order paid.** Anyone can visit it, so arriving back only
triggers a server-to-server verification. Repeat webhooks are dropped by a unique event
index, so an order cannot be paid twice, and a later failed attempt cannot un-pay a settled
order. Every payment attempt is recorded, so retries and declines are preserved for
reconciliation.

---

## 22. For developers

**Adding a provider is two files** — a definition (credentials, capabilities, supported
currencies and countries) and a driver. The definition drives the admin cards, the
credential form, driver resolution and checkout availability. No registry edits, no
seeders.

**Credentials are encrypted at rest and scoped per environment**, so sandbox testing cannot
overwrite live keys.

**Provider rows are created lazily** on first save — nothing needs seeding.

Full source, no obfuscation, no encoded files, no phone-home.

---

## 23. Security and correctness

- Encrypted provider credentials, separated by environment
- Webhook signature verification on every gateway that publishes one
- Idempotent settlement — a replayed webhook changes nothing
- Server-side price re-resolution at checkout; client prices are never trusted
- Server-side shipping weight calculation
- Coupon validity checked server-side
- Role-based access control with an audit log
- Two-factor capable admin auth
- CSRF protection, with webhooks correctly outside it

---

## 24. What it does not do — state these honestly

- **EU B2B reverse charge is not implemented.** Recommend a merchant-of-record gateway
  (Paddle, 2Checkout) for B2B sales into the EU.
- **No automated importer** from Shopify, WooCommerce or other platforms.
- **Only Stripe, PayPal and cash on delivery have end-to-end webhook test coverage.** The
  remaining gateways are written against each provider's documented API and tested for
  structure, not against live accounts. Webhook signature checks fail *open* if a field
  name is wrong — every gateway must be sandbox-tested before enabling.
- **Sender IDs must be registered with the provider.** India requires DLT-approved sender
  IDs and templates; Saudi Arabia requires locally-registered sender names.
- **Tax templates are a starting point, not tax advice.** Rates change; US templates carry
  state base rates only, and nexus rules decide where you must collect at all.
- **Not a hosted service.** There is no account and no dashboard Xgenious controls.

---

## 25. Copy rules

**Do:** state counts as numbers · name providers exactly as written above · give stack
versions · name target users · state the licence and that there is no commission · keep
paragraphs to three sentences or fewer.

**Do not:** write "revolutionise", "seamless", "empower", "unlock", "game-changing", "take
your business to the next level" · use exclamation marks or emoji · invent testimonials,
ratings, install counts or sales figures · claim it beats WooCommerce or Shopify at
everything — WooCommerce has a far larger plugin ecosystem and Shopify is a managed service
with support, and acknowledging that makes the rest of the page more credible.

**Comparison framing that is accurate:** Shopify charges monthly and takes a cut unless you
use Shopify Payments. WooCommerce is free, but each regional gateway, courier and tax setup
is typically a separate paid extension. geniusCommerz ships those in the box on a common
integration layer, and takes no commission.

---

## 26. Confirm before publishing

1. **The licence.** This document assumes MIT, matching other Xgenious free software. The
   repository README currently says "Proprietary — © Xgenious". **These contradict; resolve
   before any page goes live.**
2. Final URLs — live demo, user manual, whether the GitHub repository is public
3. What support is free versus paid
4. Anything about migration from other platforms

---

## 27. SEO

**Meta title (≤60):** geniusCommerz — Free Self-Hosted eCommerce Platform

**Meta description (≤155):** Free self-hosted Laravel eCommerce with 39 payment gateways,
19 shipping carriers and multi-country tax. MIT licensed. No fees, no commission.

**Keywords:** free ecommerce platform, self-hosted ecommerce, laravel ecommerce script,
open source ecommerce, multi-currency ecommerce, multi-country ecommerce, free shopping
cart software, ecommerce payment gateway integration, bkash nagad ecommerce, razorpay
ecommerce, paystack ecommerce, mpesa ecommerce, woocommerce alternative, shopify
alternative self hosted, MIT licensed ecommerce, free ecommerce for developers
