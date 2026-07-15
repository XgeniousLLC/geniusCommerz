<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Media;
use App\Models\Menu;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    private static function resolveTranslations(Request $request): array
    {
        $defaults = config('storefront_strings', []);

        $cookieLocale = $request->cookie('locale');
        $lang = null;

        if ($cookieLocale) {
            $lang = Language::where('code', $cookieLocale)->where('is_active', true)->first();
        }

        if (! $lang) {
            $lang = Language::default();
        }

        if (! $lang) {
            return ['locale' => 'en', 'strings' => $defaults];
        }

        $overrides = $lang->translationsMap();
        $merged    = array_merge($defaults, array_filter($overrides, fn ($v) => $v !== ''));

        return ['locale' => $lang->code, 'strings' => $merged];
    }

    private static function resolveMainNav(): array
    {
        $menu = Menu::where('location', 'main_nav')
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')])
            ->first();

        if (!$menu) return [];

        $items = $menu->items->toArray();

        // Build a simple two-level tree: root items + their children
        $byParent = collect($items)->groupBy('parent_id');
        return collect($byParent->get(null) ?? [])
            ->map(fn ($item) => [
                'label'    => $item['label'],
                'url'      => $item['url'],
                'target'   => $item['target'] ?? '_self',
                'children' => collect($byParent->get($item['id']) ?? [])
                    ->map(fn ($child) => [
                        'label'  => $child['label'],
                        'url'    => $child['url'],
                        'target' => $child['target'] ?? '_self',
                    ])->values()->toArray(),
            ])->values()->toArray();
    }

    private static function resolveSaleAlertProducts(): array
    {
        $ids = json_decode(SiteSetting::get('storefront.sale_alert_product_ids', '[]'), true);
        if (empty($ids) || !is_array($ids)) return [];

        return Product::whereIn('id', $ids)
            ->where('status', 'active')
            ->with('images')
            ->get(['id', 'name'])
            ->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'image' => $p->images->first()?->getUrl('thumb'),
            ])->values()->toArray();
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $logoMediaId    = SiteSetting::get('general.logo_media_id');
        $faviconMediaId = SiteSetting::get('general.favicon_media_id');
        $logoUrl        = $logoMediaId    ? Media::find((int) $logoMediaId)?->getUrl('thumb')  : null;
        $faviconUrl     = $faviconMediaId ? Media::find((int) $faviconMediaId)?->getUrl()      : null;

        $i18n                  = self::resolveTranslations($request);
        $multiLanguageEnabled  = (bool) SiteSetting::get('general.multilingual_enabled', '0');
        $languages             = $multiLanguageEnabled
            ? Language::where('is_active', true)->orderByDesc('is_default')->orderBy('name')
                ->get(['name', 'code'])->toArray()
            : [];

        $multiCurrencyEnabled = (bool) SiteSetting::get('currencies.enabled', '0');
        $currencies     = $multiCurrencyEnabled
            ? Currency::where('is_active', true)->orderByDesc('is_default')->orderBy('code')
                ->get(['code', 'symbol', 'name', 'rate', 'is_default'])->toArray()
            : [];
        $currencyCode        = $request->cookie('currency');
        $defaultCurrencyCode = SiteSetting::get('general.currency', 'BDT');
        $activeCurrency      = collect($currencies)->firstWhere('code', $currencyCode)
            ?? collect($currencies)->firstWhere('is_default', true)
            ?? collect($currencies)->firstWhere('code', $defaultCurrencyCode)
            ?? ($currencies[0] ?? ['code' => $defaultCurrencyCode, 'symbol' => '৳', 'name' => 'Default', 'rate' => 1.0, 'is_default' => true]);

        return array_merge(parent::share($request), [
            'locale'               => $i18n['locale'],
            'strings'              => $i18n['strings'],
            'languages'            => $languages,
            'multiLanguageEnabled' => $multiLanguageEnabled,
            'currencies'           => $currencies,
            'multiCurrencyEnabled' => $multiCurrencyEnabled,
            'activeCurrency'       => $activeCurrency,
            'site' => [
                'name'        => SiteSetting::get('general.site_name', config('app.name')),
                'tagline'     => SiteSetting::get('general.site_tagline', ''),
                'logoUrl'     => $logoUrl,
                'faviconUrl'  => $faviconUrl,
                'announceBar' => SiteSetting::get('general.announce_bar', ''),
                'phone'       => SiteSetting::get('general.phone', ''),
                'email'       => SiteSetting::get('general.admin_email', ''),
                'address'     => SiteSetting::get('general.address', ''),
                'copyright'   => SiteSetting::get('storefront.copyright', '') ?: SiteSetting::get('general.copyright_text', ''),
                'productWhatsappEnabled' => (bool) SiteSetting::get('storefront.product_whatsapp_enabled'),
                'productWhatsappNumber'  => SiteSetting::get('storefront.product_whatsapp_number', ''),
                'productWhatsappMessage' => SiteSetting::get('storefront.product_whatsapp_message', 'Hi, I want to order: {product}'),
                'productCallEnabled'     => (bool) SiteSetting::get('storefront.product_call_enabled'),
                'productCallNumber'      => SiteSetting::get('storefront.product_call_number', ''),
                'globalReturnPolicy'     => SiteSetting::get('storefront.global_return_policy', ''),
                'trustBadges'            => json_decode(SiteSetting::get('storefront.trust_badges', '[]'), true) ?: [
                    ['title' => 'Authentic',     'sub' => 'Verified quality'],
                    ['title' => 'Free Returns',  'sub' => '7-day policy'],
                    ['title' => 'Fast Delivery', 'sub' => 'Nationwide'],
                    ['title' => 'Secure Pay',    'sub' => 'SSL encrypted'],
                ],
                'shippingCost'           => (float) SiteSetting::get('shipping.flat_rate', 60),
                'freeShippingAbove'      => (float) SiteSetting::get('shipping.free_above', 0),
                'cartGoals'              => json_decode(SiteSetting::get('cart.goals', '[]'), true) ?: [],
                'mainNav'                => self::resolveMainNav(),
                'visitorCounterEnabled'  => (bool) SiteSetting::get('storefront.visitor_counter_enabled'),
                'visitorCounterMin'      => (int) SiteSetting::get('storefront.visitor_counter_min', 5),
                'visitorCounterMax'      => (int) SiteSetting::get('storefront.visitor_counter_max', 20),
                'saleAlertEnabled'       => (bool) SiteSetting::get('storefront.sale_alert_enabled'),
                'saleAlertIntervalMin'   => (int) SiteSetting::get('storefront.sale_alert_interval_min', 10),
                'saleAlertIntervalMax'   => (int) SiteSetting::get('storefront.sale_alert_interval_max', 30),
                'saleAlertProducts'      => self::resolveSaleAlertProducts(),
                // Footer
                'newsletterEnabled'      => (bool) SiteSetting::get('storefront.newsletter_enabled', '1'),
                'newsletterHeading'      => SiteSetting::get('storefront.newsletter_heading', 'Subscribe to our newsletter'),
                'socialLinks'            => [
                    'facebook'  => SiteSetting::get('storefront.facebook_url', ''),
                    'instagram' => SiteSetting::get('storefront.instagram_url', ''),
                    'youtube'   => SiteSetting::get('storefront.youtube_url', ''),
                    'tiktok'    => SiteSetting::get('storefront.tiktok_url', ''),
                    'twitter'   => SiteSetting::get('storefront.twitter_url', ''),
                    'linkedin'  => SiteSetting::get('storefront.linkedin_url', ''),
                ],
                'footerCol1'             => [
                    'title' => SiteSetting::get('storefront.footer_col1_title', 'Help'),
                    'links' => json_decode(SiteSetting::get('storefront.footer_col1_links', '[]'), true) ?: [],
                ],
                'footerCol2'             => [
                    'title' => SiteSetting::get('storefront.footer_col2_title', 'Company'),
                    'links' => json_decode(SiteSetting::get('storefront.footer_col2_links', '[]'), true) ?: [],
                ],
                // Shop
                'shopPerPage'            => (int) SiteSetting::get('storefront.shop_per_page', 12),
                'shopDefaultSort'        => SiteSetting::get('storefront.shop_default_sort', 'newest'),
                'shopLayout'             => SiteSetting::get('storefront.shop_layout', 'grid'),
                'shopShowFilters'        => (bool) SiteSetting::get('storefront.shop_show_filters', '1'),
                'shopShowOutOfStock'     => (bool) SiteSetting::get('storefront.shop_show_out_of_stock', '1'),
                // Homepage sections
                'showFeaturedProducts'   => (bool) SiteSetting::get('storefront.show_featured_products', '1'),
                'showNewArrivals'        => (bool) SiteSetting::get('storefront.show_new_arrivals', '1'),
                'showCategories'         => (bool) SiteSetting::get('storefront.show_categories', '1'),
                'showBlog'               => (bool) SiteSetting::get('storefront.show_blog', '1'),
                'showSaleBanner'         => (bool) SiteSetting::get('storefront.show_sale_banner', '1'),
                'showBrands'             => (bool) SiteSetting::get('storefront.show_brands', '1'),
                'showTrackOrder'         => (bool) SiteSetting::get('storefront.show_track_order', '1'),
            ],
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id'    => $request->user()->id,
                        'name'  => $request->user()->name,
                        'email' => $request->user()->email,
                    ]
                    : null,
            ],
            'flash' => [
                'success'         => $request->session()->get('success'),
                'comment_success' => $request->session()->get('comment_success'),
                'error'           => $request->session()->get('error'),
            ],
        ]);
    }
}
