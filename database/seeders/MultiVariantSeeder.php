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

class MultiVariantSeeder extends Seeder
{
    public function run(): void
    {
        // ── Ensure attributes exist ───────────────────────────────────────────
        $sizeAttr  = Attribute::firstOrCreate(['slug' => 'size'],    ['name' => 'Size',    'sort_order' => 0]);
        $colorAttr = Attribute::firstOrCreate(['slug' => 'color'],   ['name' => 'Color',   'sort_order' => 1]);
        $ramAttr   = Attribute::firstOrCreate(['slug' => 'ram'],     ['name' => 'RAM',     'sort_order' => 2]);
        $storAttr  = Attribute::firstOrCreate(['slug' => 'storage'], ['name' => 'Storage', 'sort_order' => 3]);
        $coreAttr  = Attribute::firstOrCreate(['slug' => 'core'],    ['name' => 'Core',    'sort_order' => 4]);

        $sizes   = $this->vals($sizeAttr,  ['S' => 0, 'M' => 1, 'L' => 2, 'XL' => 3]);
        $colors  = $this->vals($colorAttr, ['Black' => 0, 'White' => 1, 'Navy' => 2, 'Red' => 3, 'Grey' => 4, 'Olive' => 5]);
        $rams    = $this->vals($ramAttr,   ['8GB' => 0, '16GB' => 1, '32GB' => 2]);
        $storages = $this->vals($storAttr, ['256GB SSD' => 0, '512GB SSD' => 1, '1TB SSD' => 2]);
        $cores   = $this->vals($coreAttr,  ['Core i5' => 0, 'Core i7' => 1, 'Core i9' => 2]);

        $nova   = Brand::firstOrCreate(['slug' => 'nova'],   ['name' => 'Nova',   'is_active' => true]);
        $zephyr = Brand::firstOrCreate(['slug' => 'zephyr'], ['name' => 'Zephyr', 'is_active' => true]);
        $crest  = Brand::firstOrCreate(['slug' => 'crest'],  ['name' => 'Crest',  'is_active' => true]);

        $clothing    = Category::where('slug', 'clothing')->first();
        $sports      = Category::where('slug', 'sports-outdoors')->first();
        $electronics = Category::where('slug', 'electronics')->first();

        // ── 1. Premium Hoodie — Size × Color (4×3 = 12 variants) ─────────────
        $hoodieVariants = [];
        foreach (['S' => 599, 'M' => 599, 'L' => 649, 'XL' => 699] as $size => $price) {
            foreach (['Black', 'White', 'Navy'] as $color) {
                $hoodieVariants[] = [
                    'attrs'  => [[$sizeAttr, $sizes[$size]], [$colorAttr, $colors[$color]]],
                    'sku'    => "HOODIE-{$size}-" . strtoupper($color),
                    'price'  => $price,
                    'compare'=> $price + 300,
                    'stock'  => match("{$size}/{$color}") {
                        'XL/White' => 0,
                        'L/White'  => 2,
                        default    => rand(10, 40),
                    },
                ];
            }
        }
        $this->makeProduct(
            'Premium Zip Hoodie',
            'premium-zip-hoodie',
            $nova, $clothing,
            599, 899,
            'Heavyweight zip-up hoodie in 3 colours and 4 sizes.',
            '<p>320gsm French terry brushed interior. YKK zip, ribbed cuffs and hem. Pre-washed to prevent shrinkage.</p>',
            ['Material' => '80% Cotton 20% Polyester', 'GSM' => '320gsm', 'Fit' => 'Regular', 'Zip' => 'YKK'],
            '30-day returns',
            $hoodieVariants,
        );

        // ── 2. Sports T-Shirt — Size × Color (4×3 = 12 variants) ─────────────
        $teeVariants = [];
        foreach (['S', 'M', 'L', 'XL'] as $size) {
            foreach (['Black', 'Red', 'Grey'] as $color) {
                $teeVariants[] = [
                    'attrs'  => [[$sizeAttr, $sizes[$size]], [$colorAttr, $colors[$color]]],
                    'sku'    => "SPORT-TEE-{$size}-" . strtoupper($color),
                    'price'  => 449,
                    'compare'=> 699,
                    'stock'  => match("{$size}/{$color}") {
                        'S/Red'  => 0,
                        'M/Grey' => 3,
                        default  => rand(15, 60),
                    },
                ];
            }
        }
        $this->makeProduct(
            'DryFit Sports T-Shirt',
            'dryfit-sports-tshirt',
            $zephyr, $sports,
            449, 699,
            'Moisture-wicking performance tee for gym and running.',
            '<p>92% polyester / 8% elastane with DryFit mesh panels under the arms. 4-way stretch for unrestricted movement.</p>',
            ['Material' => '92% Polyester 8% Elastane', 'Technology' => 'Moisture-wicking', 'Stretch' => '4-way'],
            null,
            $teeVariants,
        );

        // ── 3. Chino Shorts — Size × Color (4×3 = 12 variants) ──────────────
        $chinoVariants = [];
        foreach (['S' => 799, 'M' => 799, 'L' => 849, 'XL' => 899] as $size => $price) {
            foreach (['Black', 'Olive', 'Navy'] as $color) {
                $chinoVariants[] = [
                    'attrs'  => [[$sizeAttr, $sizes[$size]], [$colorAttr, $colors[$color]]],
                    'sku'    => "CHINO-SHORT-{$size}-" . strtoupper($color),
                    'price'  => $price,
                    'compare'=> $price + 400,
                    'stock'  => match("{$size}/{$color}") {
                        'XL/Olive' => 0,
                        'XL/Navy'  => 0,
                        'S/Black'  => 4,
                        default    => rand(8, 35),
                    },
                ];
            }
        }
        $this->makeProduct(
            'Stretch Chino Shorts',
            'stretch-chino-shorts',
            $crest, $clothing,
            799, 1199,
            'Mid-length chino shorts with stretch canvas and 5-pocket styling.',
            '<p>98% cotton / 2% elastane stretch canvas. 10" inseam. Machine wash cold, hang dry.</p>',
            ['Material' => '98% Cotton 2% Elastane', 'Inseam' => '10 inches', 'Pockets' => '5'],
            null,
            $chinoVariants,
        );

        // ── 4. ProBook Ultra — RAM × Storage × Core (3×3×3 = 27 variants) ────
        $laptopVariants = [];
        $basePrices = [
            'Core i5' => ['8GB' => ['256GB SSD' => 64999, '512GB SSD' => 74999, '1TB SSD' => 89999]],
            'Core i7' => ['8GB' => ['256GB SSD' => 79999, '512GB SSD' => 89999, '1TB SSD' => 104999],
                          '16GB'=> ['256GB SSD' => 94999, '512GB SSD' => 109999,'1TB SSD' => 124999]],
            'Core i9' => ['16GB'=> ['512GB SSD' => 129999, '1TB SSD' => 149999],
                          '32GB'=> ['512GB SSD' => 154999, '1TB SSD' => 179999]],
        ];
        $compareMarkup = 1.15; // 15% off sticker

        $combos = [
            ['Core i5', '8GB',  '256GB SSD', 64999,  0],
            ['Core i5', '8GB',  '512GB SSD', 74999,  12],
            ['Core i5', '8GB',  '1TB SSD',   89999,  8],
            ['Core i7', '8GB',  '256GB SSD', 79999,  5],
            ['Core i7', '8GB',  '512GB SSD', 89999,  20],
            ['Core i7', '8GB',  '1TB SSD',   104999, 15],
            ['Core i7', '16GB', '256GB SSD', 94999,  10],
            ['Core i7', '16GB', '512GB SSD', 109999, 30],
            ['Core i7', '16GB', '1TB SSD',   124999, 18],
            ['Core i9', '16GB', '512GB SSD', 129999, 3],
            ['Core i9', '16GB', '1TB SSD',   149999, 6],
            ['Core i9', '32GB', '512GB SSD', 154999, 2],
            ['Core i9', '32GB', '1TB SSD',   179999, 0],
        ];

        $laptopVariants = [];
        foreach ($combos as [$core, $ram, $stor, $price, $stock]) {
            $laptopVariants[] = [
                'attrs' => [
                    [$coreAttr, $cores[$core]],
                    [$ramAttr,  $rams[$ram]],
                    [$storAttr, $storages[$stor]],
                ],
                'sku'    => 'PROBOOK-' . strtoupper(Str::slug("{$core}-{$ram}-{$stor}", '-')),
                'price'  => $price,
                'compare'=> (int) round($price * $compareMarkup),
                'stock'  => $stock,
            ];
        }

        $this->makeProduct(
            'ProBook Ultra 14',
            'probook-ultra-14',
            $nova, $electronics,
            64999, 74999,
            'Ultra-thin 14" laptop with 12th Gen Intel processors and up to 32GB RAM.',
            '<p>The ProBook Ultra 14 combines a razor-thin aluminium chassis with serious performance. 14" IPS anti-glare display at 2560×1600, Thunderbolt 4, Wi-Fi 6E, and a backlit keyboard.</p>
<ul>
  <li>14" IPS display — 2560×1600, 300 nit, 100% sRGB</li>
  <li>Up to 12-core Intel Core i9 (12th Gen)</li>
  <li>Up to 32GB LPDDR5 RAM</li>
  <li>NVMe PCIe 4.0 SSD (256GB / 512GB / 1TB)</li>
  <li>Thunderbolt 4, USB-C, USB-A, HDMI 2.0, SD card</li>
  <li>All-day battery: up to 14 hours</li>
</ul>',
            [
                'Display'   => '14" IPS 2560×1600 300nit',
                'Processor' => '12th Gen Intel Core (i5 / i7 / i9)',
                'RAM'       => 'Up to 32GB LPDDR5',
                'Storage'   => 'Up to 1TB NVMe PCIe 4.0 SSD',
                'Ports'     => 'Thunderbolt 4, USB-A, HDMI 2.0, SD card',
                'Battery'   => 'Up to 14 hours',
                'Weight'    => '1.38 kg',
                'OS'        => 'Windows 11 Home',
            ],
            '1 year manufacturer warranty',
            $laptopVariants,
        );

        $this->command->info('MultiVariantSeeder: 4 products seeded — 3 clothing (Size×Color) + 1 laptop (Core×RAM×Storage).');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** @return array<string, AttributeValue> */
    private function vals(Attribute $attr, array $valuesWithOrder): array
    {
        $map = [];
        foreach ($valuesWithOrder as $val => $order) {
            $map[$val] = AttributeValue::firstOrCreate(
                ['attribute_id' => $attr->id, 'value' => $val],
                ['sort_order' => $order]
            );
        }
        return $map;
    }

    private function makeProduct(
        string    $name,
        string    $slug,
        Brand     $brand,
        Category  $category,
        float     $basePrice,
        float     $baseCompare,
        string    $shortDesc,
        string    $desc,
        array     $specs,
        ?string   $warranty,
        array     $variants,
    ): void {
        if (Product::where('slug', $slug)->exists()) return;

        $product = Product::create([
            'name'              => $name,
            'slug'              => $slug,
            'short_description' => $shortDesc,
            'description'       => $desc,
            'brand_id'          => $brand->id,
            'status'            => 'active',
            'is_featured'       => true,
            'has_variants'      => true,
            'price'             => $basePrice,
            'compare_at_price'  => $baseCompare,
            'specifications'    => array_map(
                fn($k, $v) => ['key' => $k, 'value' => $v],
                array_keys($specs), $specs
            ),
            'warranty'          => $warranty,
        ]);

        $product->categories()->sync([$category->id]);

        foreach ($variants as $i => $row) {
            $variant = ProductVariant::create([
                'product_id'       => $product->id,
                'sku'              => $row['sku'],
                'price'            => $row['price'],
                'compare_at_price' => (int) round($row['price'] * 1.15),
                'stock_qty'        => $row['stock'],
                'is_active'        => true,
                'sort_order'       => $i,
            ]);

            foreach ($row['attrs'] as [$attr, $av]) {
                ProductVariantValue::create([
                    'variant_id'         => $variant->id,
                    'attribute_id'       => $attr->id,
                    'attribute_value_id' => $av->id,
                ]);
            }
        }
    }
}
