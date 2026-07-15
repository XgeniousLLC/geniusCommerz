<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontPageController extends Controller
{
    // ─── Homepage ────────────────────────────────────────────────────────────

    public function homepage(): View
    {
        $settings = SiteSetting::whereIn('key', [
            'storefront.announce_bar_enabled',
            'storefront.announce_bar_text',
            'storefront.announce_bar_bg',
            'storefront.announce_bar_link',
            'general.hero_title',
            'general.hero_subtitle',
            'storefront.hero_cta_label',
            'storefront.hero_cta_link',
            'storefront.hero_image_media_id',
            'storefront.show_featured_products',
            'storefront.featured_count',
            'storefront.show_new_arrivals',
            'storefront.arrivals_count',
            'storefront.show_categories',
            'storefront.show_blog',
            'storefront.show_sale_banner',
            'storefront.show_brands',
            'storefront.show_track_order',
            'storefront.section_order',
        ])->get()->keyBy('key');

        return view('admin.storefront.homepage', compact('settings'));
    }

    public function updateHomepage(Request $request): RedirectResponse
    {
        $this->saveSettings($request->input('settings', []), [
            'storefront.announce_bar_enabled',
            'storefront.announce_bar_text',
            'storefront.announce_bar_bg',
            'storefront.announce_bar_link',
            'general.hero_title',
            'general.hero_subtitle',
            'storefront.hero_cta_label',
            'storefront.hero_cta_link',
            'storefront.hero_image_media_id',
            'storefront.show_featured_products',
            'storefront.featured_count',
            'storefront.show_new_arrivals',
            'storefront.arrivals_count',
            'storefront.show_categories',
            'storefront.show_blog',
            'storefront.show_sale_banner',
            'storefront.show_brands',
            'storefront.show_track_order',
            'storefront.section_order',
        ]);

        return back()->with('success', 'Homepage settings saved.');
    }

    // ─── Footer ──────────────────────────────────────────────────────────────

    public function footer(): View
    {
        $settings = SiteSetting::whereIn('key', [
            'storefront.copyright',
            'storefront.newsletter_enabled',
            'storefront.newsletter_heading',
            'storefront.facebook_url',
            'storefront.instagram_url',
            'storefront.youtube_url',
            'storefront.tiktok_url',
            'storefront.twitter_url',
            'storefront.linkedin_url',
            'storefront.footer_col1_title',
            'storefront.footer_col1_links',
            'storefront.footer_col2_title',
            'storefront.footer_col2_links',
        ])->get()->keyBy('key');

        $col1Links = json_decode($settings->get('storefront.footer_col1_links')?->value ?? '[]', true) ?: [];
        $col2Links = json_decode($settings->get('storefront.footer_col2_links')?->value ?? '[]', true) ?: [];

        return view('admin.storefront.footer', compact('settings', 'col1Links', 'col2Links'));
    }

    public function updateFooter(Request $request): RedirectResponse
    {
        $this->saveSettings($request->input('settings', []), [
            'storefront.copyright',
            'storefront.newsletter_enabled',
            'storefront.newsletter_heading',
            'storefront.facebook_url',
            'storefront.instagram_url',
            'storefront.youtube_url',
            'storefront.tiktok_url',
            'storefront.twitter_url',
            'storefront.linkedin_url',
            'storefront.footer_col1_title',
            'storefront.footer_col2_title',
        ]);

        // Footer columns stored as JSON arrays
        foreach (['col1', 'col2'] as $col) {
            $links = $request->input("footer_{$col}_links", []);
            $links = is_array($links) ? array_values(array_filter($links, fn($l) => !empty($l['label']) || !empty($l['url']))) : [];
            $key   = "storefront.footer_{$col}_links";
            $this->upsertSetting($key, json_encode($links), 'storefront');
        }

        return back()->with('success', 'Footer settings saved.');
    }

    // ─── Shop ────────────────────────────────────────────────────────────────

    public function shop(): View
    {
        $settings = SiteSetting::whereIn('key', [
            'storefront.shop_per_page',
            'storefront.shop_default_sort',
            'storefront.shop_show_filters',
            'storefront.shop_layout',
            'storefront.shop_show_out_of_stock',
        ])->get()->keyBy('key');

        return view('admin.storefront.shop', compact('settings'));
    }

    public function updateShop(Request $request): RedirectResponse
    {
        $this->saveSettings($request->input('settings', []), [
            'storefront.shop_per_page',
            'storefront.shop_default_sort',
            'storefront.shop_show_filters',
            'storefront.shop_layout',
            'storefront.shop_show_out_of_stock',
        ]);

        return back()->with('success', 'Shop settings saved.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function saveSettings(array $data, array $allowedKeys): void
    {
        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $data)) continue;
            $value = $data[$key] === '' ? null : $data[$key];
            $group = explode('.', $key)[0];
            $this->upsertSetting($key, $value, $group);
        }
    }

    private function upsertSetting(string $key, mixed $value, string $group): void
    {
        $existing = SiteSetting::where('key', $key)->first();
        if ($existing) {
            $existing->value = $value;
            $existing->save();
        } elseif ($value !== null) {
            SiteSetting::create(['key' => $key, 'value' => $value, 'type' => 'text', 'group' => $group]);
        }
    }
}
