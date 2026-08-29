# geniusCommerz

A globally-capable Laravel 12 e-commerce platform. Admin panel in Blade + Alpine.js, storefront in Inertia v3 + React 19 + TypeScript, with dual authentication (`admin` / `web`) guards.

Originally built for the Bangladeshi market, the platform now sells worldwide: country-aware checkout, destination-based tax, shipping zones with live carrier rates, and **94 provider integrations** spanning payments, SMS, fraud, shipping, exchange rates and AI.

---

## Contents

- [At a glance](#at-a-glance)
- [Getting started](#getting-started)
- [Architecture](#architecture)
- [Integrations](#integrations)
- [Commerce features](#commerce-features)
- [Admin panel](#admin-panel)
- [Storefront](#storefront)
- [Configuration](#configuration)
- [Testing](#testing)
- [Before you go live](#before-you-go-live)

---

## At a glance

| Area | Providers |
|---|---:|
| Payment gateways | **39** |
| SMS gateways | **20** |
| Fraud checkers | **11** |
| Shipping carriers | **19** |
| Bangladeshi couriers | **3** |
| Exchange-rate sources | **2** |
| AI providers | **4** |

**Core capabilities**

- Sell into any country: 213 countries with dial codes, subdivisions and postal rules
- Multi-currency with per-order rate freezing and scheduled refresh
- Destination tax zones (VAT / GST / sales tax), inclusive or exclusive pricing
- Shipping zones with weight and order-value bands, plus live carrier rates
- Payment settlement that only ever trusts a verified webhook or server-side verify
- E.164 phone normalisation across every SMS gateway
- Region-aware fraud screening with one shared risk vocabulary
- One-click templates for tax and shipping configuration

---

## Getting started

### Requirements

PHP 8.2+ · MySQL 8 · Node 18+ · Composer

### Install

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# configure DB_* in .env, then:
php artisan migrate
php artisan db:seed

npm run build          # or: npm run dev
```

Default admin from `AdminSeeder`: `admin@example.com` / `password`.

### Scheduler

Exchange-rate refresh runs on Laravel's scheduler. Add the usual cron entry:

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

### Common commands

```bash
php artisan migrate                                  # run migrations
php artisan db:seed --class=AdminSeeder              # admin user
php artisan db:seed --class=GeneralSettingsSeeder    # store defaults
php artisan currency:refresh-rates                   # refresh FX rates now
php artisan view:clear && php artisan cache:clear
./vendor/bin/pest                                    # test suite
npm run build                                        # storefront assets
```

---

## Architecture

### Provider registry

Every integration — payment, SMS, fraud, carrier, courier, FX, AI — is described by a
**definition class** discovered from `app/Integrations/Definitions/`. The definition is the
single source of truth driving the admin cards, the credential form, driver resolution,
and checkout availability.

```
app/Integrations/
  ProviderDefinition.php     slug, group, label, driver, fields, capabilities,
                             currencies, countries, environments, docs
  CredentialField.php        one input on the credential form
  Capability.php             HostedRedirect | DirectCharge | Webhook | Refund | PartialRefund
  ProviderRegistry.php       discovery, lookup, driver resolution, checkout filtering
  Definitions/{Payment,Sms,Fraud,Carrier,Courier,Fx,Ai}/
```

**Adding a provider is two files** — a definition and a driver. Resolution is a hash
lookup (`app()->makeWith(...)`), so it costs the same at 5 providers as at 50.

A definition declares the currencies and countries it can actually serve, so a gateway is
never offered for a payment it would reject at the API.

### Credentials

Stored encrypted (`Crypt::encryptString`) and **scoped per environment**:

```json
{ "shared": {…}, "sandbox": {"secret_key": "sk_test_…"}, "live": {"secret_key": "sk_live_…"} }
```

Switching to sandbox to debug cannot destroy live keys. A blank field on save never wipes
a stored secret. Rows are created lazily on first save, so nothing needs seeding.

### Money

Every order records money twice:

| Columns | Meaning |
|---|---|
| `subtotal`, `shipping_cost`, `tax`, `total` | **Base currency** — what every report sums |
| `presentment_*` + `presentment_currency` + `exchange_rate` | **What the customer was charged** |

`base_currency` is snapshotted per order, so history stays interpretable if the store ever
changes its base currency. Presentment totals are stored, never recomputed from the rate —
rounding is per-line and non-invertible, so a recomputed figure would not reproduce the
invoice the customer agreed to.

Money columns are `decimal(12,4)`. Gateways receive **integer minor units** via
`Currencies::toMinor()`, which reads each currency's ISO 4217 exponent — so JPY (0 dp) and
KWD (3 dp) are correct, not 100× wrong.

`App\Services\PriceBook` is the only place a rate is applied to money that will be charged.
The browser converts for display only.

### Payments

```
Checkout → PaymentService::begin() → driver->charge()
                                       ├─ Redirect     → gateway hosted page
                                       ├─ form POST    → auto-submitting bridge page
                                       ├─ Pending      → handset push (M-Pesa, MoMo…)
                                       └─ Deferred     → cash on delivery
                    ↓
        webhook (signature-verified) ── or ── return URL (server-side verify)
                    ↓
             PaymentService::apply()  → order marked paid
```

Two rules are enforced structurally:

1. **An order is only marked paid from a verified gateway response.** The browser return
   URL triggers a server-to-server verify and nothing else.
2. **Settlement is idempotent.** `webhook_events` has a unique `(provider, event_id)`
   index; claiming that row *is* the replay guard. A later failed attempt cannot un-pay a
   settled order.

`payments` records one row per *attempt*, so a declined card, a retry and a success are all
preserved for reconciliation.

### Tax

`tax_zones` match **country → state → postal pattern**, most specific first — a San
Francisco postal zone beats a statewide California zone. `tax_rates` are additive within a
zone, which is what US state+county and Canadian GST+PST both need.

Products carry a `tax_class` (`standard` / `reduced` / `zero`), because EU rates differ by
goods type. Discounts are spread across lines before tax. Tax-inclusive pricing *extracts*
rather than adds. The breakdown is frozen on the order and read back by the invoice, so the
invoice and the charged amount cannot disagree.

### Shipping

Precedence, highest first:

1. Live courier quote (Bangladeshi city→zone→area couriers)
2. Live carrier rate (EasyPost, DHL, Shiprocket, …) — falls through on a rating outage
3. Configured shipping zone rate (weight and order-value bands, per-kg, free-above)
4. Global flat rate

Weight is derived server-side from real product weights — a client cannot understate it.
Products marked *shipping included* always ship free.

Two contracts, deliberately separate:

- `CourierInterface` — Pathao's Bangladeshi `city → zone → area` tree
- `ShippingRateInterface` — `rates` / `buyLabel` / `track`, for global carriers

Forcing both through one interface would distort each.

---

## Integrations

### Payment gateways (39)

| Region | Gateways |
|---|---|
| **Worldwide** | Stripe · PayPal · Adyen · Paddle¹ · 2Checkout¹ · Authorize.Net · Cash on Delivery |
| **Bangladesh** | SSLCOMMERZ · bKash · Nagad · aamarPay · ShurjoPay |
| **India** | Razorpay · Cashfree · PayU India · PhonePe · Paytm |
| **Pakistan** | Easypaisa² · JazzCash |
| **Gulf / MENA** | PayTabs · Tap Payments · Moyasar · Fawry (Egypt) |
| **Africa** | Paystack · Flutterwave · Monnify · M-Pesa² · MTN MoMo² · Yoco · Peach Payments |
| **Europe** | Mollie · iyzico (Türkiye) · Vipps MobilePay (Nordics) |
| **LatAm** | MercadoPago · Pagar.me (Pix) |
| **SE Asia** | Midtrans · Xendit |
| **North America / APAC** | Square · KakaoPay (Korea) |

¹ Merchant of record — they become the seller and remit EU VAT / US sales tax themselves.
² Handset push, not a redirect: the customer approves on their phone and the order settles from the callback.

**Notes.** Stripe alone exposes ~50 local payment methods. JazzCash, Authorize.Net,
2Checkout and Paytm require a signed browser form POST, handled by an auto-submitting
bridge page. bKash and KakaoPay publish no webhook and declare no `Webhook` capability
rather than implying a signature check that cannot exist.

### SMS gateways (20)

| Region | Gateways |
|---|---|
| **Worldwide** | Twilio · Vonage · MessageBird · Plivo · Amazon SNS · Infobip · Sinch · Telnyx |
| **Bangladesh** | BulkSMSBD · SMS.BD · MRAM |
| **India** | MSG91 · Gupshup · Fast2SMS |
| **Gulf / MENA** | Unifonic · Taqnyat · Cequens |
| **Africa** | Africa's Talking · Termii · Clickatell |

Numbers are normalised to **E.164 once, at the boundary**. Each driver reshapes from there:
Bangladeshi gateways want local `01…`, Fast2SMS wants a bare 10-digit Indian number, most
want E.164 without the `+`, Africa's Talking and Telnyx want it with.

### Fraud checkers (11)

| Region | Checkers |
|---|---|
| **Worldwide** | IPQualityScore · SEON · Sift · MaxMind minFraud |
| **Europe** | Ravelin |
| **India** | Bureau |
| **Gulf** | Uqudo |
| **Africa** | Smile ID · Youverify |
| **Bangladesh** | FraudBD · BDCourier (courier delivery history) |

Every provider is normalised onto one vocabulary — `safe` / `low_risk` / `mid_risk` /
`high_risk` / `unknown` plus a 0–100 score — so the admin UI works unchanged for any of
them. A checker is skipped entirely when it does not serve the destination country: a
Bangladeshi courier-history service cannot score a US order.

*Gulf-native fraud vendors are genuinely scarce; most Gulf merchants run SEON, Sift or
IPQualityScore, all of which cover the region.*

### Shipping carriers (19) and couriers (3)

| Region | Carriers |
|---|---|
| **Worldwide** | DHL Express · FedEx · UPS · EasyPost · Shippo |
| **India** | Delhivery · Shiprocket · Blue Dart |
| **Nigeria / Africa** | Sendbox · GIG Logistics · Kwik Delivery · Bob Go (South Africa) |
| **Brazil** | Melhor Envio · Correios · Loggi |
| **Gulf** | Aramex · SMSA Express · Naqel Express · Torod |
| **Bangladesh** (couriers) | Pathao · RedX · Steadfast |

Aggregators return several rates you buy by id. Direct carriers quote once and book in one
step — for those, `buyLabel()` throws with a clear message rather than returning a
fabricated tracking code.

### Exchange rates (2) and AI (4)

- **FX:** open.er-api.com (free, keyless) · ExchangeRate-API
- **AI:** OpenAI · Google Gemini · Anthropic Claude · DeepSeek — product descriptions, blog
  content, meta descriptions, translation and price suggestions

---

## Commerce features

**Catalogue** — products with variants, attributes, brands, categories, media library with
conversions, tax classes, weights, dimensions, HS codes and country of origin.

**Orders** — full lifecycle, activity timeline, refunds, printable invoices and packing
slips, courier dispatch, bulk actions, admin order creation.

**Customers** — accounts, saved addresses with country/state/postal, order history,
wishlist, reviews, loyalty points with earn and redeem.

**Marketing** — coupons, loyalty programme, cart goals, landing pages, blog with
categories and comments, SEO analysis scoring, sitemap, Google Merchant and Facebook
catalogue feeds.

**Tracking** — GTM, Meta Pixel + Conversions API, GA4 Measurement Protocol, TikTok Pixel +
Events API, with a pixel event log and a fraud gate that can suppress purchase events.

**Accounting** — purchase orders, ad spend, profit reports, sourcing.

**Localisation** — multi-language with polymorphic content translations, multi-currency,
per-country address forms and dial codes.

---

## Admin panel

Cobalt design system (`public/admin/css/cobalt.css` + `public/admin/js/`).

| Page | Purpose |
|---|---|
| **Payment Gateways** | Enable gateways, set checkout order, per-gateway credentials |
| **SMS Gateways** | Enable, set default, send a test message, check balance |
| **Fraud Checks** | Enable, set default, run a live check, recent check history |
| **Shipping Zones** | Zones and rates, ship-from origin, **templates** |
| **Tax Zones** | Zones and rates, inclusive pricing toggle, **templates** |
| **Currencies** | Currencies, rate sources, manual/auto per currency, refresh now |
| **Integrations** | Every provider group in one catalogue |
| **Settings** | General, meta, social, storefront, shipping, cart, legal, tracking, feeds, accounting, storage, notifications |

### Configuration templates

Setting up tax and shipping country by country is the slowest part of going live, so both
pages ship with one-click templates:

**Tax** — EU VAT (27 states) · UK VAT · US sales tax (46 state base rates) · Canada
GST/HST/PST · single-rate countries (Gulf, APAC, Africa and more)

**Shipping** — domestic flat rate · domestic weight bands · Europe · North America · Gulf ·
rest-of-world fallback

Applying a template **skips zones that already exist**, so it can be re-applied later
without duplicating anything or discarding rates you have tuned. Tax rates are a starting
point, not advice — verify them against your own registrations. Shipping prices are
placeholders.

---

## Storefront

Inertia v3 + React 19 + TypeScript, built with Vite.

Home · shop listing with filters · product detail · cart drawer · country-aware checkout ·
order confirmation · order tracking · blog · wishlist · loyalty · account dashboard,
orders, addresses, refunds and reviews · auth with password or phone OTP.

Shared Inertia props carry locale, translated strings, currencies, active currency, site
settings and the authenticated user.

---

## Configuration

Key settings (Admin → Settings, stored in `site_settings`):

| Setting | Meaning |
|---|---|
| `general.store_country` | Where you ship from — drives tax, shipping and dial-code defaults |
| `general.currency` / `general.currency_symbol` | Base currency |
| `general.timezone` | Applied at boot |
| `currencies.enabled` | Multi-currency on the storefront |
| `tax.enabled` | Master switch for tax calculation |
| `accounting.prices_include_tax` | Catalogue prices are gross rather than net |
| `shipping.flat_rate` / `shipping.free_above` | Fallback when no zone matches |
| `shipping.origin_*` | Ship-from address, required for live carrier rates |

Provider credentials live in **Integrations**, encrypted — never in `.env`.

---

## Testing

```bash
./vendor/bin/pest                      # full suite
./vendor/bin/pest --filter=PaymentFlow # one file
```

Tests run against in-memory SQLite, so your development database is untouched.

Coverage focuses on the parts that fail expensively: webhook signature rejection across
every gateway, replayed-webhook idempotency, browser-return-URL never marking an order
paid, minor-unit conversion per currency, the tax matrix (UK, Germany, US state vs
district, zero/reduced classes, inclusive extraction), shipping band selection, E.164
reshaping per SMS gateway, and cross-provider fraud score normalisation.

---

## Before you go live

**Verify each integration in its sandbox first.** Only Stripe, PayPal and cash on delivery
have end-to-end webhook coverage in the test suite. The remaining integrations are written
against documented APIs and tested for structure — request shapes, signature schemes,
currency gating — but not against live accounts. **Webhook signature paths especially fail
open if a field name is wrong**, so test those first. The most intricate schemes are Nagad
(RSA), Paytm (AES checksum), Adyen (per-item HMAC) and 2Checkout (length-prefixed HMAC).

**Sender IDs and templates need registration.** India requires DLT-approved sender IDs and
templates; Saudi requires locally-registered sender names. That happens with the provider,
not here.

**Tax rates change.** The templates are a starting point. Confirm them against your own
registrations, and note that US templates carry state base rates only — counties and cities
add on top, and nexus rules decide where you must collect at all.

**EU B2B reverse charge is not implemented.** If you sell B2B into the EU you will need
VAT-number capture and reverse-charge handling, or use a merchant-of-record gateway
(Paddle, 2Checkout) which handles it for you.

---

## Licence

Proprietary — © Xgenious.
