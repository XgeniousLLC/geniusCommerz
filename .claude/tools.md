# Tools & Components

## Blade Components (`x-admin.*`)
```blade
<x-admin.card>
<x-admin.button type="submit" variant="outline|secondary">
<x-admin.input type="text" name="..." value="..." />
<x-admin.select name="...">
<x-admin.form-group>
<x-admin.modal id="...">
<x-admin.alert type="success|info|warning|danger">
<x-admin.media-picker name="..." accept="image|video" :multiple="true" :value="...">
<x-admin.tooltip text="..." />
<x-admin.table>
<x-admin.seo-score :score="...">
<x-admin.meta-preview>
<x-admin.character-counter>
<x-admin.sidebar-link :href="..." :active="..." icon="...">
```
Flash messages render globally in `admin.layouts.admin` — **never add `@if(session('success'))` in individual views**.

## Storefront Hooks
```ts
useT()        // UI string translation — const t = useT(); t('key', { var: val })
usePrice()    // currency conversion  — const fmt = usePrice(); fmt(amount)
```

## Storefront Shared Props (via `HandleInertiaRequests`)
```ts
locale, strings, languages[], currencies[], multiCurrencyEnabled, activeCurrency
site.{ name, logoUrl, faviconUrl, phone, email, mainNav, announceBar }
auth.user | null
cart
```

## Storefront Components (`resources/js/storefront/components/`)
| Component | Purpose |
|---|---|
| `Header.tsx` | Sticky header with SearchBox, locale/currency switchers, cart, wishlist |
| `BottomNav.tsx` | Mobile-only fixed bottom nav — Home, Shop, Cart, Wishlist, Account |
| `Footer.tsx` | Site footer |
| `CartDrawer.tsx` | Slide-over cart panel |
| `SearchBox.tsx` | Debounced autocomplete (300ms, 2-char min) → `GET /shop/suggest?q=` |
| `LocaleSwitcher.tsx` | Cookie-based locale → `GET /locale/{code}` |
| `CurrencySwitcher.tsx` | Cookie-based currency → `GET /currency/{code}` |

## Tracking / Conversions
Settings → Tracking tab holds all keys. `app.blade.php` injects client-side scripts.

| Platform | Client-side | Server-side |
|---|---|---|
| GTM | `tracking.gtm_id` | — |
| Meta (Facebook) | `tracking.meta_pixel_id` | `tracking.meta_capi_token` → CAPI Purchase event in `CheckoutController::trackPurchase` |
| GA4 | via GTM | `tracking.ga4_measurement_id` + `tracking.ga4_api_secret` → MP Purchase event |
| TikTok | `tracking.tiktok_pixel_id` → `app.blade.php` fires PageView | `tracking.tiktok_access_token` → `TikTokService::sendPurchase()` called in `CheckoutController` |

## AI Integration
- Provider config: **Admin → AI Settings** (`/admin/ai-settings`)
- `AiService` resolves the `is_default` integration from `AI_PROVIDERS`
- Routes: `POST /admin/ai/product-description`, `blog-content`, `meta-description`, `translate-content`, `save-content-translation`
- Logo assets: `public/images/ai/{openai,claude,gemini,deepseek}.svg`
