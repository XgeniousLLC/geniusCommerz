<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function googleMerchant(): Response
    {
        if (! SiteSetting::get('feeds.google_merchant_enabled')) {
            abort(404);
        }

        $siteName = SiteSetting::get('general.site_name', config('app.name'));
        $baseUrl  = rtrim(config('app.url'), '/');

        $products = Product::where('status', 'active')
            ->with(['images', 'brand', 'categories'])
            ->orderByDesc('updated_at')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>' . e($siteName) . '</title>' . "\n";
        $xml .= '<link>' . $baseUrl . '</link>' . "\n";
        $xml .= '<description>Product feed for ' . e($siteName) . '</description>' . "\n";

        foreach ($products as $product) {
            $imageUrl = $product->images->first()?->getUrl('medium') ?? '';
            $price    = number_format((float) $product->price, 2, '.', '');
            $category = $product->categories->first()?->name ?? '';
            $brand    = $product->brand?->name ?? '';

            $inStock = $product->has_variants
                ? $product->variants->where('is_active', true)->contains(fn($v) => $v->stock_qty === null || $v->stock_qty > 0)
                : ($product->stock_qty === null || $product->stock_qty > 0);

            $xml .= '<item>' . "\n";
            $xml .= '  <g:id>' . $product->id . '</g:id>' . "\n";
            $xml .= '  <g:title><![CDATA[' . $product->name . ']]></g:title>' . "\n";
            $xml .= '  <g:description><![CDATA[' . strip_tags($product->short_description ?? $product->name) . ']]></g:description>' . "\n";
            $xml .= '  <g:link>' . $baseUrl . '/shop/' . $product->slug . '</g:link>' . "\n";
            if ($imageUrl) {
                $xml .= '  <g:image_link>' . $imageUrl . '</g:image_link>' . "\n";
            }
            $xml .= '  <g:price>' . $price . ' BDT</g:price>' . "\n";
            if ($product->compare_at_price && (float)$product->compare_at_price > (float)$product->price) {
                $xml .= '  <g:sale_price>' . $price . ' BDT</g:sale_price>' . "\n";
                $xml .= '  <g:price>' . number_format((float) $product->compare_at_price, 2, '.', '') . ' BDT</g:price>' . "\n";
            }
            $xml .= '  <g:availability>' . ($inStock ? 'in_stock' : 'out_of_stock') . '</g:availability>' . "\n";
            $xml .= '  <g:condition>new</g:condition>' . "\n";
            if ($brand) {
                $xml .= '  <g:brand><![CDATA[' . $brand . ']]></g:brand>' . "\n";
            }
            if ($category) {
                $xml .= '  <g:product_type><![CDATA[' . $category . ']]></g:product_type>' . "\n";
            }
            if ($product->sku) {
                $xml .= '  <g:mpn>' . e($product->sku) . '</g:mpn>' . "\n";
            }
            $xml .= '</item>' . "\n";
        }

        $xml .= '</channel>' . "\n";
        $xml .= '</rss>' . "\n";

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function facebookCatalog(): Response
    {
        if (! SiteSetting::get('feeds.facebook_catalog_enabled')) {
            abort(404);
        }

        $baseUrl = rtrim(config('app.url'), '/');

        $products = Product::where('status', 'active')
            ->with(['images', 'brand', 'categories'])
            ->orderByDesc('updated_at')
            ->get();

        $items = $products->map(function (Product $product) use ($baseUrl) {
            $imageUrl = $product->images->first()?->getUrl('medium') ?? '';
            $price    = number_format((float) $product->price * 100, 0, '.', '');
            $category = $product->categories->first()?->name ?? '';

            $inStock = $product->has_variants
                ? $product->variants->where('is_active', true)->contains(fn($v) => $v->stock_qty === null || $v->stock_qty > 0)
                : ($product->stock_qty === null || $product->stock_qty > 0);

            $item = [
                'id'               => (string) $product->id,
                'title'            => $product->name,
                'description'      => strip_tags($product->short_description ?? $product->name),
                'availability'     => $inStock ? 'in stock' : 'out of stock',
                'condition'        => 'new',
                'price'            => $price . ' BDT',
                'link'             => $baseUrl . '/shop/' . $product->slug,
                'image_link'       => $imageUrl,
                'brand'            => $product->brand?->name ?? '',
                'google_product_category' => $category,
            ];

            if ($product->sku) {
                $item['retailer_id'] = $product->sku;
            }

            if ($product->compare_at_price && (float)$product->compare_at_price > (float)$product->price) {
                $item['sale_price'] = $price . ' BDT';
                $item['price']      = number_format((float) $product->compare_at_price * 100, 0, '.', '') . ' BDT';
            }

            return $item;
        })->values()->all();

        return response()->json([
            'data' => $items,
        ], 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
