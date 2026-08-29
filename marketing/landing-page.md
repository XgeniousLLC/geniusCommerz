# geniusCommerz — Landing Page Copy

Ready-to-publish copy for `xgenious.com/free-software/genius-commerz`, in the same
spec-first house style as the Genius School Management page. Every number below is
verified against the codebase; a `<!-- fact -->` note marks anything you must confirm
before publishing (URLs, licence choice, repository links).

---

## Hero

**H1**
> geniusCommerz — Free Multi-Country eCommerce Platform

**Subheadline**
> Self-hosted Laravel eCommerce with 98 built-in integrations — 39 payment gateways,
> 20 SMS gateways, 19 shipping carriers and 11 fraud checkers. Sell in 213 countries with
> destination tax, multi-currency and country-aware checkout. MIT licensed. No per-order
> fee, no expiry, no paid tiers.

**Badges** (inline, comma-separated in house style)
> MIT License · Laravel 12 + React 19 + Inertia 3 · PHP 8.2+ · Self-hosted · Free forever

**Links** <!-- fact: confirm final URLs before publishing -->
> Live demo · Documentation · GitHub · User manual

---

## Section: What it is

geniusCommerz is a complete, self-hosted eCommerce platform. You install it on your own
server, own the database, and pay nobody a percentage of your sales.

It was built for a Bangladeshi merchant and then extended to sell internationally — which
means it handles the parts of cross-border commerce that most free carts leave to you:
per-country address formats, currency conversion recorded on every order, destination-based
tax, shipping zones, and 98 provider integrations across seven categories.

**Not a hosted service.** There is no account to create and no dashboard we control. You
clone it, configure it, and run it.

---

## Section: By the numbers

| | |
|---|---|
| **39** | Payment gateways |
| **20** | SMS gateways |
| **19** | Shipping carriers |
| **11** | Fraud checkers |
| **213** | Countries with dial codes, subdivisions and postal rules |
| **155** | Currencies with correct decimal handling |
| **4** | AI providers |
| **236** | Automated tests |

---

## Section: Payment gateways (39)

Enable as many as you like and drag them into the order customers see. Each gateway
declares the currencies and countries it can actually settle, so it is never offered for a
payment it would reject.

**Worldwide** — Stripe, PayPal, Adyen, Authorize.Net, Paddle, 2Checkout, Cash on Delivery

**Bangladesh** — SSLCOMMERZ, bKash, Nagad, aamarPay, ShurjoPay

**India** — Razorpay, Cashfree, PayU India, PhonePe, Paytm

**Pakistan** — Easypaisa, JazzCash

**Gulf & MENA** — PayTabs, Tap Payments, Moyasar, Fawry

**Africa** — Paystack, Flutterwave, Monnify, M-Pesa, MTN MoMo, Yoco, Peach Payments

**Europe** — Mollie, iyzico, Vipps MobilePay

**LatAm** — MercadoPago, Pagar.me (Pix)

**Southeast Asia** — Midtrans, Xendit

**North America & APAC** — Square, KakaoPay

Stripe alone adds roughly 50 local payment methods — iDEAL, Klarna, SEPA, Bancontact,
Alipay and more — through a single integration. Paddle and 2Checkout act as merchant of
record, which means they become the seller and remit EU VAT and US sales tax for you.

---

## Section: Shipping (19 carriers + 3 couriers)

**Worldwide** — DHL Express, FedEx, UPS, EasyPost, Shippo
**India** — Delhivery, Shiprocket, Blue Dart
**Nigeria & Africa** — Sendbox, GIG Logistics, Kwik Delivery, Bob Go
**Brazil** — Melhor Envio, Correios, Loggi
**Gulf** — Aramex, SMSA Express, Naqel Express, Torod
**Bangladesh** — Pathao, RedX, Steadfast

Shipping resolves in a fixed order: a live courier quote, then a live carrier rate, then
your configured zone rate, then a flat rate. Cart weight is calculated server-side from
real product weights, so it cannot be understated by the browser. A carrier outage falls
through to your zone rates rather than blocking checkout.

Zones match on country, then state, then postal pattern, with weight bands, order-value
bands, per-kg charges and free-above thresholds.

---

## Section: SMS gateways (20)

**Worldwide** — Twilio, Vonage, MessageBird, Plivo, Amazon SNS, Infobip, Sinch, Telnyx
**Bangladesh** — BulkSMSBD, SMS.BD, MRAM
**India** — MSG91, Gupshup, Fast2SMS
**Gulf & MENA** — Unifonic, Taqnyat, Cequens
**Africa** — Africa's Talking, Termii, Clickatell

Phone numbers are normalised to E.164 once, then reshaped per gateway — Bangladeshi
gateways receive a local `01…` number, Fast2SMS a bare 10-digit Indian number, most others
international format. Order confirmations, status updates and login OTP all use it.

---

## Section: Fraud screening (11)

**Worldwide** — IPQualityScore, SEON, Sift, MaxMind minFraud
**Europe** — Ravelin
**India** — Bureau
**Gulf** — Uqudo
**Africa** — Smile ID, Youverify
**Bangladesh** — FraudBD, BDCourier

Every provider is reduced to one risk scale — safe, low, mid or high risk with a score out
of 100 — so the admin reads the same whichever you use. A checker is skipped when it does
not serve the destination country, so a Bangladeshi courier-history service is never asked
to score a US order.

---

## Section: Selling internationally

**Country-aware checkout.** 213 countries. The address form adapts: a state dropdown where
one exists, free text where it does not, the right postal label (ZIP, Postcode, PIN), and
no postal field at all for the countries that have no postal system — UAE, Hong Kong,
Qatar, Panama.

**Multi-currency done properly.** Every order stores money twice: in your base currency,
which every report sums, and in the currency the customer actually saw, with the exchange
rate frozen at order time. Reports stay correct and old orders stay reconstructible years
later. Rates refresh hourly, reject an implausible move, and are pinned per browsing
session so a refresh never changes a total mid-visit.

**Destination tax.** VAT, GST and sales tax by country, state or postal pattern. Multiple
rates stack, which is how US state-plus-county and Canadian GST-plus-PST actually work.
Products carry a tax class, because EU rates differ for food, books and children's
clothing. Inclusive or exclusive pricing. The breakdown is frozen onto the order, so the
invoice and the amount charged can never disagree.

**Configuration templates.** Tax and shipping setup is the slowest part of going live, so
both ship with one-click templates — EU VAT across 27 member states, UK VAT, US sales tax
across 46 states, Canada GST/HST/PST, plus shipping presets for Europe, North America, the
Gulf and a rest-of-world fallback. Re-applying a template skips what you already have, so
rates you have tuned survive.

---

## Section: The rest of the platform

**Catalogue** — products with variants and attributes, brands, categories, media library
with conversions, tax classes, weights, dimensions, HS codes and country of origin

**Orders** — full lifecycle, activity timeline, refunds, printable invoices and packing
slips, courier dispatch, bulk actions, admin order creation

**Customers** — accounts, saved addresses, order history, wishlist, reviews, loyalty points
with earn and redeem

**Marketing** — coupons, cart goals, landing pages, blog with categories and comments, SEO
scoring, sitemap, Google Merchant and Facebook catalogue feeds

**Tracking** — GTM, Meta Pixel and Conversions API, GA4 Measurement Protocol, TikTok Pixel
and Events API, with a pixel event log

**AI** — OpenAI, Anthropic Claude, Google Gemini and DeepSeek for product descriptions,
blog content, meta descriptions, translation and price suggestions

**Accounting** — purchase orders, ad spend tracking, profit reports, sourcing

**Localisation** — multi-language with polymorphic content translations, multi-currency,
RTL-capable

---

## Section: Built for developers

| | |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Admin | Blade + Alpine.js (Cobalt design system) |
| Storefront | Inertia v3 + React 19 + TypeScript, built with Vite |
| Database | MySQL 8 |
| Queue | Laravel Horizon |
| Tests | Pest — 236 passing |
| Auth | Dual guard, admin and customer separated |

**Adding a provider is two files.** Every integration is described by a definition class —
its credentials, capabilities, supported currencies and countries — plus a driver. That
definition then drives the admin cards, the credential form, driver resolution and checkout
availability. No registry edits, no seeders, no scattered constants.

**Credentials are encrypted and scoped per environment**, so sandbox testing can never
overwrite your live keys — a mistake that is easy to make and expensive to discover.

---

## Section: How payment settlement works

This is where most self-hosted carts get it wrong, so it is worth stating plainly:

1. The customer picks a gateway and the order is created as *pending*
2. They are sent to the gateway — by redirect, by a signed form post, or by a prompt on
   their phone for mobile money
3. The gateway notifies your server by webhook, and the signature is verified before
   anything is written
4. Only then is the order marked paid

**The return URL never marks an order paid.** A customer arriving back from a hosted page
only triggers a server-to-server verification — the redirect itself proves nothing, and
anyone can visit it. Repeated webhooks are dropped by a unique event index, so an order
cannot be paid twice, and a later failed attempt cannot un-pay a settled order.

---

## Section: Who it is for

- Merchants selling across borders who do not want a percentage of revenue taken
- Agencies building stores for clients in South Asia, the Gulf, Africa or LatAm
- Businesses in markets that Shopify and WooCommerce serve poorly on local payments
- Developers who want the full source and a real integration layer to extend
- Anyone replacing a SaaS cart with something they host and own

---

## Section: What free actually means

- **MIT licensed** <!-- fact: confirm this is the licence you are releasing under -->
- Full source code, no obfuscation, no encoded files
- No per-order, per-product or per-user fee
- No expiry, no trial, no paid tier holding features back
- No commission on your sales
- Self-hosted — your server, your database, your data
- Modify it, rebrand it, use it commercially

---

## Section: Getting started

```bash
git clone https://github.com/XgeniousLLC/geniusCommerz.git
cd geniusCommerz

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
# set DB_* in .env
php artisan migrate && php artisan db:seed
```

Default admin: `admin@example.com` / `password` — change it immediately.

Then: set your store country and currency, apply a tax template, apply a shipping template,
and enable a payment gateway. Full documentation covers deployment on AWS, DigitalOcean,
cPanel, Docker and plain VPS.

---

## Section: Honest notes

We would rather you find this here than in production:

**Test each gateway in sandbox before enabling it.** Stripe, PayPal and cash on delivery
have end-to-end webhook coverage in the test suite. The remaining integrations are written
against each provider's documented API and tested for structure, but not against live
accounts. Webhook signature paths in particular fail *open* if a field name is wrong.

**Sender IDs need registering with the provider.** India requires DLT-approved sender IDs
and templates; Saudi Arabia requires locally-registered sender names.

**Tax templates are a starting point, not tax advice.** Rates change. The US template
carries state base rates only — counties and cities add their own, and nexus rules decide
where you must collect at all.

**EU B2B reverse charge is not implemented.** If you sell B2B into the EU, use a
merchant-of-record gateway such as Paddle or 2Checkout, which handles it for you.

---

## Section: FAQ

**Is it really free?**
Yes. MIT licensed, full source, no paid tier. We build custom software for a living;
releasing this costs us nothing and puts our work in front of people who may later need
that.

**Do you take a commission on sales?**
No. It runs on your server and talks directly to your payment gateway. We are not in the
transaction path and could not take a cut if we wanted to.

**Do I need all 39 payment gateways?**
No — enable the ones your customers use. Most stores run two or three. The rest are there
so you are not blocked when you expand into a new market.

**Can I use it commercially?**
Yes. Sell through it, build client stores on it, rebrand it. MIT permits all of that.

**Do you offer support?**
The documentation and issue tracker are free. Paid installation, customisation and support
are available through Xgenious. <!-- fact: confirm what you want to offer here -->

**How is this different from WooCommerce or Shopify?**
Shopify charges monthly and takes a cut unless you use their payments. WooCommerce is free
but each regional payment gateway, courier and tax setup is a separate paid extension.
geniusCommerz ships those in the box, with a common integration layer, and costs nothing.

**Can I migrate my existing store?**
There is no automated importer yet. Products and customers can be imported via the admin;
order history would need a custom migration. <!-- fact: confirm before publishing -->

---

## Closing

> **geniusCommerz — free, self-hosted, and built to sell across borders.**
> Clone it, configure it, keep 100% of what you earn.
>
> Live demo · Documentation · GitHub

---

## SEO keywords

free ecommerce platform, self-hosted ecommerce, laravel ecommerce script, open source
ecommerce, multi-currency ecommerce, multi-country ecommerce, free shopping cart software,
laravel shopping cart, ecommerce with payment gateway integration, bkash nagad ecommerce,
razorpay ecommerce, paystack ecommerce, mpesa ecommerce, self hosted online store,
woocommerce alternative, shopify alternative self hosted, MIT licensed ecommerce

## Meta description (155 chars)

> Free self-hosted Laravel eCommerce platform with 39 payment gateways, 19 shipping
> carriers and multi-country tax. MIT licensed. No fees, no commission.
