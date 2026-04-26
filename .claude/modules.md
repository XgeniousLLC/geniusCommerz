# Modules

## Admin Controllers → Views
| Module | Controller | View path |
|---|---|---|
| Dashboard | `DashboardController` | `admin/dashboard` |
| Products | `ProductController` | `admin/products/{index,create,edit}` |
| Categories | `CategoryController` | `admin/categories` |
| Brands | `BrandController` | `admin/brands` |
| Attributes | `AttributeController` | `admin/attributes` |
| Orders | `OrderController` | `admin/orders/{index,show}` |
| Refunds | `RefundController` | `admin/refunds` |
| Coupons | `CouponController` | `admin/coupons` |
| Users | `UserController` | `admin/users/{index,create,edit,show}` |
| Admins | `AdminController` | `admin/admins` |
| Blogs | `Admin\BlogController` | `admin/blogs/{index,create,edit}` |
| Blog Categories | `BlogCategoryController` | `admin/blog-categories` |
| Pages | `Admin\PageController` | `admin/pages/{index,create,edit}` |
| Media | `MediaController` | `admin/media` |
| Menus | `MenuController` | `admin/menus` |
| Loyalty | `LoyaltyController` | `admin/loyalty` |
| Reports | `ReportController` | `admin/reports` |
| Settings | `SettingsController` | `admin/settings` — tabs: `general, meta, social, storefront, payment, shipping, cart, legal, tracking, feeds, currencies` |
| Order Settings | `OrderSettingsController` | `admin/order-settings` |
| Integrations | `IntegrationController` | `admin/integrations` |
| AI Settings | `IntegrationController::aiSettings` | `admin/ai-settings` |
| Languages | `LanguageController` | `admin/languages/{index,edit}` |
| Currencies | `CurrencyController` | `admin/currencies` |
| Sitemap | `SitemapController` | `admin/sitemap` |
| Roles | `RoleController` | `admin/roles` |
| Audit Log | `AuditLogController` | `admin/audit` |
| Failed Jobs | `FailedJobController` | `admin/failed-jobs` |

## Storefront Pages (Inertia/React → `resources/js/storefront/pages/`)
| Page | File |
|---|---|
| Home | `Home.tsx` |
| Shop listing | `Shop/Index.tsx` |
| Product detail | `Shop/Show.tsx` |
| Blog listing | `blog/Index.tsx` |
| Blog post | `blog/Show.tsx` |
| Checkout | `Checkout.tsx` |
| Order confirmed | `OrderConfirmed.tsx` |
| Track order | `Track.tsx` |
| Wishlist | `Wishlist.tsx` |
| Loyalty | `Loyalty.tsx` |
| Static page | `Page.tsx` |
| Auth | `auth/{Login,Register,ForgotPassword,ResetPassword}.tsx` |
| Account | `Account/{Dashboard,Orders,OrderShow,Address,Refunds,Reviews}.tsx` |

## Services (`app/Services/`)
| Service | Purpose |
|---|---|
| `AiService` + `Ai/*Driver` | Driver-based AI — resolves default Integration |
| `CourierService` + `Couriers/*` | Pathao / RedX / Steadfast |
| `SmsService` + `Sms/*` | BulkSmsBD / SmsBD / Twilio |
| `LoyaltyService` | Earn/redeem points |
| `MediaService` | Upload, conversions, deletion |
| `SEOAnalyzerService` | Score 0–100, grade: excellent/good/average/poor/critical |
| `FraudBdService` | FraudBD API |

## Key Models & Relationships
```
Product   → belongsToMany Category, Brand
          → hasMany ProductVariant, ProductReview
          → morphOne MetaInformation
          → morphMany ContentTranslation
          → belongsToMany Media (images, videos)

Order     → belongsTo User → hasMany OrderItem, OrderActivity → hasOne Refund
User      → hasMany Order, UserAddress, ProductReview, LoyaltyPoint, Refund
Blog      → belongsTo BlogCategory, Admin → hasMany Comment → morphOne MetaInformation → morphMany ContentTranslation
Language  → hasMany Translation
Currency  → setAsDefault() clears others first
Integration → constants: AI_PROVIDERS, COURIER_PROVIDERS, SMS_PROVIDERS
SiteSetting → static get(key, default) / group(tab)
```

## Database Tables
```
admins, users, user_addresses
pages, meta_information, site_settings
products, product_variants, product_variant_values
categories, brands, attributes, attribute_values
category_product (pivot), product_media (pivot)
orders, order_items, order_activities
refunds, product_reviews, coupons
blogs, blog_categories, comments
media, media_folders, menus, menu_items
loyalty_settings, loyalty_points
integrations, languages, translations, content_translations, currencies
audit_logs, failed_jobs, roles
```
