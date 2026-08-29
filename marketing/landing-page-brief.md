# Agent Brief — geniusCommerz Free Software Landing Page

Hand this file to an AI agent. It contains everything needed to produce the page without
access to the codebase. **Every number and provider name below is verified — use them
verbatim and do not add, estimate or embellish any figure.**

---

## 1. The task

Produce a landing page for **geniusCommerz**, a free self-hosted eCommerce platform, to be
published at `xgenious.com/free-software/genius-commerz`.

**Deliverables**
1. Page copy as markdown, section by section, ready to paste into the CMS
2. A meta title (≤60 chars) and meta description (≤155 chars)
3. An SEO keyword list
4. A list of every claim the human must confirm before publishing

**Audience** — two groups, in this order of priority:
- Merchants selling across borders who don't want a percentage of revenue taken
- Developers and agencies evaluating whether to build on it

Write for someone deciding whether to spend an afternoon installing it. They are technical
enough to read a stack line and want to know what's actually included.

---

## 2. House style — match this, it is not optional

The reference is `xgenious.com/free-software/genius-school-management` and the other free
software pages on that site. Their pattern:

- **Spec-first.** Concrete detail over persuasion. Maximum actionable information, minimum
  marketing language.
- **Stack always stated with versions**, e.g. "Laravel 12 + React 19 + Inertia 3".
- **Counts as hard numbers** — "39 payment gateways", not "many payment gateways".
- **Target users named explicitly** in a short list.
- **Licence and pricing stated plainly** — "MIT licensed. No per-order fee, no expiry, no
  paid tiers."
- **Links inline** rather than large call-to-action buttons: demo, documentation, source.
- **Keyword block** at the end for SEO.

**Do not write:** "revolutionise", "seamless", "empower", "game-changing", "unlock",
"take your business to the next level". No exclamation marks. No emoji. No invented
customer testimonials. No fabricated ratings, install counts or sales figures.

---

## 3. Verified facts — the only numbers you may use

### Provider counts (total 98)

| Category | Count |
|---|---|
| Payment gateways | 39 |
| SMS gateways | 20 |
| Shipping carriers | 19 |
| Fraud checkers | 11 |
| AI providers | 4 |
| Bangladeshi couriers | 3 |
| Exchange-rate sources | 2 |

### Other verified figures

- 213 countries with dial codes, subdivisions and postal rules
- 155 currencies with correct decimal handling
- 236 automated tests passing
- 0% commission taken

### Stack

Laravel 12 · PHP 8.2+ · MySQL 8 · React 19 · Inertia v3 · TypeScript · Vite · Alpine.js
(admin) · Laravel Horizon (queues) · Pest (tests) · dual auth guard (admin / customer)

### Payment gateways (39) — exact names, grouped by market

- **Worldwide (7):** Stripe, PayPal, Adyen, Authorize.Net, Paddle, 2Checkout, Cash on Delivery
- **Bangladesh (5):** SSLCOMMERZ, bKash, Nagad, aamarPay, ShurjoPay
- **India (5):** Razorpay, Cashfree, PayU India, PhonePe, Paytm
- **Africa (7):** Paystack, Flutterwave, Monnify, M-Pesa, MTN MoMo, Yoco, Peach Payments
- **Gulf & MENA (4):** PayTabs, Tap Payments, Moyasar, Fawry
- **Europe (3):** Mollie, iyzico, Vipps MobilePay
- **LatAm (2):** MercadoPago, Pagar.me
- **Southeast Asia (2):** Midtrans, Xendit
- **Pakistan (2):** Easypaisa, JazzCash
- **APAC / North America (2):** Square, KakaoPay

Facts you may state about these: Stripe adds roughly 50 local payment methods through one
integration. Paddle and 2Checkout are merchant of record — they become the seller and remit
EU VAT and US sales tax. Pagar.me defaults to Pix. M-Pesa and MTN MoMo are handset-approval
flows, not redirects.

### Shipping carriers (19) and couriers (3)

- **Worldwide:** DHL Express, FedEx, UPS, EasyPost, Shippo
- **India:** Delhivery, Shiprocket, Blue Dart
- **Nigeria & Africa:** Sendbox, GIG Logistics, Kwik Delivery, Bob Go
- **Brazil:** Melhor Envio, Correios, Loggi
- **Gulf:** Aramex, SMSA Express, Naqel Express, Torod
- **Bangladesh couriers:** Pathao, RedX, Steadfast

### SMS gateways (20)

- **Worldwide:** Twilio, Vonage, MessageBird, Plivo, Amazon SNS, Infobip, Sinch, Telnyx
- **Bangladesh:** BulkSMSBD, SMS.BD, MRAM
- **India:** MSG91, Gupshup, Fast2SMS
- **Gulf & MENA:** Unifonic, Taqnyat, Cequens
- **Africa:** Africa's Talking, Termii, Clickatell

### Fraud checkers (11)

- **Worldwide:** IPQualityScore, SEON, Sift, MaxMind minFraud
- **Europe:** Ravelin · **India:** Bureau · **Gulf:** Uqudo
- **Africa:** Smile ID, Youverify · **Bangladesh:** FraudBD, BDCourier

### AI (4) and exchange rates (2)

OpenAI, Anthropic Claude, Google Gemini, DeepSeek · open.er-api.com, ExchangeRate-API

---

## 4. The differentiators — lead with these, they are what make it worth installing

Most free carts leave cross-border commerce to the merchant. These are the specific things
this platform does. Explain each in plain terms; do not just list a feature name.

1. **Country-aware checkout.** The address form adapts per country: a state dropdown where
   one exists, free text where it doesn't, the right postal label (ZIP / Postcode / PIN),
   and no postal field at all for the UAE, Hong Kong, Qatar and Panama, which have no
   postal system.

2. **Multi-currency that survives an audit.** Every order stores money twice — in the base
   currency, which every report sums, and in the currency the customer actually saw, with
   the exchange rate frozen at order time. Old orders stay reconstructible after rates move.
   Rates refresh hourly, reject an implausible move, and are pinned per browsing session so
   a refresh can't change a total mid-visit.

3. **Destination-based tax.** Matches on country, then state, then postal pattern — most
   specific wins. Rates stack, which is how US state-plus-county and Canadian GST-plus-PST
   actually work. Products carry a tax class because EU rates differ for food, books and
   children's clothing. Inclusive or exclusive pricing. The breakdown is frozen onto the
   order, so the invoice and the amount charged can't disagree.

4. **Configuration templates.** One click applies EU VAT across 27 member states, UK VAT,
   US sales tax across 46 states, or Canada GST/HST/PST — plus shipping presets for Europe,
   North America, the Gulf and a rest-of-world fallback. Re-applying skips what already
   exists, so tuned rates survive.

5. **Payment settlement done correctly.** Worth a short section of its own. The order is
   created as *pending*; the customer is sent to pay; the gateway notifies the server by
   webhook; the signature is verified; only then is the order marked paid. **The return URL
   never marks an order paid** — anyone can visit it, so it only triggers a server-to-server
   verification. Repeat webhooks are dropped by a unique event index.

6. **Two files to add a provider.** A definition describing credentials, capabilities and
   supported currencies, plus a driver. The definition then drives the admin screens,
   credential form and checkout availability. Credentials are encrypted and scoped per
   environment, so sandbox testing can't overwrite live keys.

7. **Shipping rate precedence.** Live courier quote, then live carrier rate, then your zone
   rate, then flat rate. Cart weight is calculated server-side from real product weights, so
   the browser can't understate it. A carrier outage falls through rather than blocking
   checkout.

---

## 5. Required sections, in order

1. **Hero** — H1, subheadline naming the counts, licence/stack badges, links to demo,
   documentation and source.
2. **What it is** — two short paragraphs. State plainly that it is self-hosted and not a
   hosted service; there is no account and no dashboard Xgenious controls.
3. **By the numbers** — the verified figures as a compact block.
4. **Payment gateways** — grouped by market as listed above.
5. **Shipping** — carriers by region, plus the rate precedence explained.
6. **SMS and fraud** — grouped by region.
7. **Selling internationally** — differentiators 1–4.
8. **How payment settlement works** — differentiator 5, as a numbered flow.
9. **The rest of the platform** — catalogue, orders, customers, marketing, tracking,
   accounting, localisation. One line each, no elaboration.
10. **Built for developers** — stack table and differentiator 6.
11. **Who it is for** — 4–6 bullets naming actual roles.
12. **What free means** — licence, no fees, no commission, full source, self-hosted.
13. **Getting started** — the install commands (section 7 below).
14. **Honest notes** — the caveats in section 6. Do not omit or soften these.
15. **FAQ** — 5–7 questions. Must include: is it really free, do you take commission, how
    does it compare to WooCommerce and Shopify, is there support.
16. **Closing** — one line plus the three links. No hard-sell.
17. **SEO keywords** and meta description.

---

## 6. Caveats that must appear — do not omit or soften

These build more trust than they cost on a free-software page.

- **Test each gateway in its sandbox before enabling it.** Stripe, PayPal and cash on
  delivery have end-to-end webhook coverage in the test suite. The rest are written against
  each provider's documented API and tested for structure, not against live accounts —
  and webhook signature checks fail *open* if a field name is wrong.
- **Sender IDs need registering with the provider.** India requires DLT-approved sender IDs
  and templates; Saudi Arabia requires locally-registered sender names.
- **Tax templates are a starting point, not tax advice.** Rates change. The US template
  carries state base rates only — counties add their own, and nexus rules decide where you
  must collect at all.
- **EU B2B reverse charge is not implemented.** Recommend a merchant-of-record gateway
  (Paddle, 2Checkout) for B2B sales into the EU.

---

## 7. Install commands — reproduce exactly

```bash
git clone https://github.com/XgeniousLLC/geniusCommerz.git
cd geniusCommerz

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
# set your DB_* values in .env
php artisan migrate && php artisan db:seed
```

Requirements: PHP 8.2+, MySQL 8, Node 18+. Deployment documentation covers AWS,
DigitalOcean, cPanel, Docker and plain VPS. Default admin is `admin@example.com` /
`password` — tell the reader to change it immediately.

---

## 8. Comparison framing — accurate, not disparaging

- **Shopify:** charges monthly and takes a cut of sales unless you use Shopify Payments.
- **WooCommerce:** free, but each regional gateway, courier and tax setup is typically a
  separate paid extension.
- **geniusCommerz:** ships those in the box on a common integration layer, costs nothing,
  takes no commission.

State the difference factually. Do not claim it is better at everything — WooCommerce has a
far larger plugin ecosystem and Shopify is a managed service with support, and a reader who
knows that will trust the rest of the page more if you don't pretend otherwise.

---

## 9. Must be confirmed with the human before publishing

Flag these in the output rather than guessing. **The first is blocking.**

1. **The licence.** This brief assumes MIT, matching the other Xgenious free software.
   The repository README currently says "Proprietary — © Xgenious". These contradict each
   other, and the licence is the single most load-bearing claim on the page. Do not publish
   until it is settled.
2. **Final URLs** — live demo, user manual, and whether the GitHub repository is public.
3. **Support offer** — what is free (documentation, issue tracker) versus paid
   (installation, customisation).
4. **Migration story** — there is currently no automated importer from other platforms.
   Confirm before stating anything about migration.

---

## 10. Output rules

- Write the copy, not a description of the copy.
- British or American spelling — pick one and hold it throughout.
- Keep paragraphs to 3 sentences or fewer; this page is scanned before it is read.
- Every number must come from section 3. If you want to state a figure that isn't there,
  flag it as unverified instead of inventing it.
- End with the confirmation list from section 9.
