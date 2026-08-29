<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class GeneralSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'general.site_name',        'value' => 'geniusCommerz',        'type' => 'text',    'group' => 'general', 'description' => 'Public-facing site name'],
            ['key' => 'general.site_tagline',      'value' => '',                     'type' => 'text',    'group' => 'general', 'description' => 'Short tagline shown in header/footer'],
            ['key' => 'general.admin_email',       'value' => 'admin@geniuscommerz.com','type' => 'text',    'group' => 'general', 'description' => 'Default From address for system emails'],
            ['key' => 'general.timezone',          'value' => 'Asia/Dhaka',           'type' => 'text',    'group' => 'general', 'description' => 'Store timezone'],
            ['key' => 'general.store_country',     'value' => 'BD',                   'type' => 'text',    'group' => 'general', 'description' => 'ISO 3166-1 alpha-2 country the store ships from'],
            ['key' => 'general.currency',          'value' => 'USD',                  'type' => 'text',    'group' => 'general', 'description' => 'ISO 4217 currency code'],
            ['key' => 'general.currency_symbol',   'value' => '$',                    'type' => 'text',    'group' => 'general', 'description' => 'Symbol shown in prices'],
            ['key' => 'general.phone',             'value' => '',                     'type' => 'text',    'group' => 'general', 'description' => 'Customer-facing support phone'],
            ['key' => 'general.address',           'value' => '',                     'type' => 'text',    'group' => 'general', 'description' => 'Business address'],
            ['key' => 'general.maintenance_mode',  'value' => false,                  'type' => 'boolean', 'group' => 'general', 'description' => 'Put storefront in maintenance mode'],
            ['key' => 'general.order_prefix',      'value' => 'GC',                   'type' => 'text',    'group' => 'general', 'description' => 'Prefix prepended to order numbers'],
            ['key' => 'legal.privacy_policy_url',  'value' => '/privacy',             'type' => 'text',    'group' => 'legal',   'description' => 'Privacy policy page URL or slug'],
            ['key' => 'legal.terms_url',           'value' => '/terms',               'type' => 'text',    'group' => 'legal',   'description' => 'Terms & conditions page URL or slug'],
            ['key' => 'legal.refund_policy_url',   'value' => '/refund-policy',       'type' => 'text',    'group' => 'legal',   'description' => 'Refund policy page URL or slug'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
