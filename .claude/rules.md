# Rules & Gotchas

## Blade
- `@json()` cannot contain PHP closures with nested brackets — pre-compute first:
  ```blade
  @php $data = $collection->mapWithKeys(fn($x) => [...])->all(); @endphp
  @json($data)
  ```
- Flash is global in layout — never add a second `@if(session('success'))` in views.
- Use inline SVGs, not `<x-dynamic-component :component="'heroicon-o-...'">` — heroicons package not installed.

## Validation
- `alpha_upper` is NOT a valid Laravel rule — use `regex:/^[A-Z0-9]+$/` + `strtoupper()`.

## Database
- MySQL unique index names max 64 chars — always provide explicit short name:
  ```php
  $table->unique(['language_id','translatable_type','translatable_id'], 'ct_lang_model_unique');
  ```

## Settings
- Keys follow `group.key` dot notation. Group must be in `SettingsController::TABS`.
- Boolean toggles need a hidden `value="0"` input before the checkbox so unchecked submits false.
- `currencies.enabled` controls multi-currency on storefront. Toggle at **Settings → Currencies**.

## Multi-currency
- Disabled = `currencies[]` is empty in Inertia props. `usePrice()` falls back to rate=1.
- Cookie: `currency`. Switched via `GET /currency/{code}`.

## Multi-language
- Cookie: `locale`. Switched via `GET /locale/{code}`.
- `ContentTranslation` is polymorphic (Product, Blog). Applied at controller level — merge translated fields over originals.

## AI
- Default provider set via `/admin/integrations/{id}/set-default` or AI Settings page.
- After saving AI credentials, redirect goes back to AI Settings (not generic Integrations).

## Admin routes
- File: `routes/admin.php` — imported via web.php. All names prefixed `admin.*`.
- Auth guard: `admin`. Middleware: `AdminAuth`.

## Storefront routes
- File: `routes/web.php`. Search suggest: `GET /shop/suggest?q=` (min 2 chars, returns 6 results).
