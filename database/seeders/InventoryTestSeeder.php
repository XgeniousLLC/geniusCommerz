<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryTestSeeder extends Seeder
{
    public function run(): void
    {
        $sizeAttr  = Attribute::where('slug', 'size')->first();
        $colorAttr = Attribute::where('slug', 'color')->firstOrCreate(
            ['slug' => 'color'], ['name' => 'Color', 'sort_order' => 1]
        );

        $sizes  = AttributeValue::where('attribute_id', $sizeAttr->id)->get()->keyBy('value');
        $colors = [];
        foreach (['Black' => '#000', 'White' => '#fff', 'Red' => '#f00', 'Blue' => '#00f', 'Green' => '#0f0'] as $val => $_) {
            $colors[$val] = AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => $val],
                ['sort_order' => array_search($val, array_keys(['Black','White','Red','Blue','Green']))]
            );
        }

        $nova   = Brand::where('slug', 'nova')->first();
        $zephyr = Brand::where('slug', 'zephyr')->first();
        $crest  = Brand::where('slug', 'crest')->first();

        $clothing   = Category::where('slug', 'clothing')->first();
        $footwear   = Category::where('slug', 'footwear')->first();
        $electronics = Category::where('slug', 'electronics')->first();

        // ── 1. Running Shorts — size variants, XL out of stock ─────────────
        $this->makeVariantProduct(
            name: 'Running Shorts 7"',
            slug: 'running-shorts-7in',
            brand: $nova,
            category: $clothing,
            sku: 'RUN-SHORT-7',
            basePrice: 699,
            comparePrice: 999,
            shortDesc: 'Lightweight 7" running shorts with built-in liner and side pockets.',
            desc: '<p>Made from recycled 88% polyester / 12% elastane. The 4-way stretch fabric moves with you and the quick-dry finish keeps you comfortable on long runs.</p>',
            specs: ['Material' => '88% Polyester, 12% Elastane', 'Length' => '7 inches', 'Liner' => 'Built-in'],
            warranty: null,
            variants: [
                ['size' => 'S',  'sku' => 'RUN-SHORT-S',  'price' => 699, 'compare' => 999, 'stock' => 15],
                ['size' => 'M',  'sku' => 'RUN-SHORT-M',  'price' => 699, 'compare' => 999, 'stock' => 8],
                ['size' => 'L',  'sku' => 'RUN-SHORT-L',  'price' => 699, 'compare' => 999, 'stock' => 3],
                ['size' => 'XL', 'sku' => 'RUN-SHORT-XL', 'price' => 749, 'compare' => 999, 'stock' => 0],
            ],
            sizeAttr: $sizeAttr,
            sizeMap: $sizes,
        );

        // ── 2. Graphic Tee — size variants, low stock on M, S out of stock ──
        $this->makeVariantProduct(
            name: 'Abstract Graphic Tee',
            slug: 'abstract-graphic-tee',
            brand: $zephyr,
            category: $clothing,
            sku: 'GRAPHIC-TEE',
            basePrice: 549,
            comparePrice: 799,
            shortDesc: 'Oversized graphic tee with water-based print on 200gsm cotton.',
            desc: '<p>Limited-run print on premium ring-spun cotton. Each tee is individually quality-checked before packing.</p>',
            specs: ['Material' => '100% Ring-spun Cotton', 'Gsm' => '200gsm', 'Print' => 'Water-based'],
            warranty: null,
            variants: [
                ['size' => 'S',  'sku' => 'GRAPH-TEE-S',  'price' => 549, 'compare' => 799, 'stock' => 0],
                ['size' => 'M',  'sku' => 'GRAPH-TEE-M',  'price' => 549, 'compare' => 799, 'stock' => 2],
                ['size' => 'L',  'sku' => 'GRAPH-TEE-L',  'price' => 549, 'compare' => 799, 'stock' => 20],
                ['size' => 'XL', 'sku' => 'GRAPH-TEE-XL', 'price' => 599, 'compare' => 799, 'stock' => 12],
            ],
            sizeAttr: $sizeAttr,
            sizeMap: $sizes,
        );

        // ── 3. Slim Sneakers — size variants, M (mapped to 41) has big stock ─
        $this->makeVariantProduct(
            name: 'Slim Urban Sneakers',
            slug: 'slim-urban-sneakers',
            brand: $crest,
            category: $footwear,
            sku: 'SLIM-SNEAKER',
            basePrice: 1999,
            comparePrice: 2799,
            shortDesc: 'Low-profile sneakers with a vulcanised cupsole and premium suede toe cap.',
            desc: '<p>Inspired by classic court shoes, the Slim Urban Sneaker blends everyday comfort with a minimalist silhouette. Padded collar and suede toe cap for a premium finish.</p>',
            specs: ['Upper' => 'Canvas + Suede toe cap', 'Sole' => 'Vulcanised rubber', 'Insole' => 'Memory foam'],
            warranty: '6 months',
            variants: [
                ['size' => 'S',  'sku' => 'SLIM-SNK-S',  'price' => 1999, 'compare' => 2799, 'stock' => 4],
                ['size' => 'M',  'sku' => 'SLIM-SNK-M',  'price' => 1999, 'compare' => 2799, 'stock' => 30],
                ['size' => 'L',  'sku' => 'SLIM-SNK-L',  'price' => 1999, 'compare' => 2799, 'stock' => 18],
                ['size' => 'XL', 'sku' => 'SLIM-SNK-XL', 'price' => 2099, 'compare' => 2799, 'stock' => 0],
            ],
            sizeAttr: $sizeAttr,
            sizeMap: $sizes,
        );

        // ── 4. True Wireless Earbuds — simple with limited stock ────────────
        if (!Product::where('slug', 'true-wireless-earbuds')->exists()) {
            $product = Product::create([
                'name'              => 'True Wireless Earbuds Pro',
                'slug'              => 'true-wireless-earbuds',
                'short_description' => 'ANC earbuds with 30-hour total battery life and IPX5 rating.',
                'description'       => '<p>6mm drivers deliver crisp highs and punchy bass. Active noise cancellation blocks up to 25dB of ambient noise. Wireless charging case included.</p>',
                'brand_id'          => $nova->id,
                'status'            => 'active',
                'is_featured'       => true,
                'has_variants'      => false,
                'sku'               => 'TWS-EARBUDS-PRO',
                'price'             => 2999,
                'compare_at_price'  => 3999,
                'stock_qty'         => 5,
                'specifications'    => [
                    ['key' => 'Driver', 'value' => '6mm dynamic'],
                    ['key' => 'ANC', 'value' => 'Up to -25dB'],
                    ['key' => 'Battery', 'value' => '8h + 22h case'],
                    ['key' => 'Water Rating', 'value' => 'IPX5'],
                    ['key' => 'Charging', 'value' => 'USB-C + Qi wireless'],
                ],
                'warranty'          => '1 year warranty',
                'return_policy'     => null,
            ]);
            $product->categories()->sync([$electronics->id]);
        }

        // ── 5. Cargo Shorts — simple, out of stock ──────────────────────────
        if (!Product::where('slug', 'tactical-cargo-shorts')->exists()) {
            $product = Product::create([
                'name'              => 'Tactical Cargo Shorts',
                'slug'              => 'tactical-cargo-shorts',
                'short_description' => 'Six-pocket ripstop cargo shorts with articulated knees.',
                'description'       => '<p>Built for utility and comfort. 65/35 polyester-cotton ripstop resists tears while the gusseted crotch allows full range of motion.</p>',
                'brand_id'          => $zephyr->id,
                'status'            => 'active',
                'is_featured'       => false,
                'has_variants'      => false,
                'sku'               => 'CARGO-SHORT-OOS',
                'price'             => 1299,
                'compare_at_price'  => 1799,
                'stock_qty'         => 0,
                'specifications'    => [
                    ['key' => 'Material', 'value' => '65% Polyester, 35% Cotton ripstop'],
                    ['key' => 'Pockets', 'value' => '6 (including 2 cargo)'],
                    ['key' => 'Fit', 'value' => 'Regular'],
                ],
                'warranty'          => null,
                'return_policy'     => null,
            ]);
            $product->categories()->sync([$clothing->id]);
        }

        $this->command->info('InventoryTestSeeder: 5 test products seeded (3 variant, 2 simple) with mixed stock states.');
    }

    private function makeVariantProduct(
        string    $name,
        string    $slug,
        Brand     $brand,
        Category  $category,
        string    $sku,
        float     $basePrice,
        ?float    $comparePrice,
        string    $shortDesc,
        string    $desc,
        array     $specs,
        ?string   $warranty,
        array     $variants,
        Attribute $sizeAttr,
        $sizeMap,
    ): void {
        if (Product::where('slug', $slug)->exists()) return;

        $product = Product::create([
            'name'              => $name,
            'slug'              => $slug,
            'short_description' => $shortDesc,
            'description'       => $desc,
            'brand_id'          => $brand->id,
            'status'            => 'active',
            'is_featured'       => false,
            'has_variants'      => true,
            'sku'               => null,
            'price'             => $basePrice,
            'compare_at_price'  => $comparePrice,
            'specifications'    => array_map(fn($k, $v) => ['key' => $k, 'value' => $v], array_keys($specs), $specs),
            'warranty'          => $warranty,
        ]);

        $product->categories()->sync([$category->id]);

        foreach ($variants as $i => $row) {
            $av = $sizeMap[$row['size']] ?? null;
            if (!$av) continue;

            $variant = ProductVariant::create([
                'product_id'       => $product->id,
                'sku'              => $row['sku'],
                'price'            => $row['price'],
                'compare_at_price' => $row['compare'],
                'stock_qty'        => $row['stock'],
                'is_active'        => true,
                'sort_order'       => $i,
            ]);

            ProductVariantValue::create([
                'variant_id'         => $variant->id,
                'attribute_id'       => $sizeAttr->id,
                'attribute_value_id' => $av->id,
            ]);
        }
    }
}
