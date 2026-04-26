<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MetaInformation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Attributes ────────────────────────────────────────────────────────
        $sizeAttr  = Attribute::firstOrCreate(['slug' => 'size'],  ['name' => 'Size',  'sort_order' => 0]);
        $colorAttr = Attribute::firstOrCreate(['slug' => 'color'], ['name' => 'Color', 'sort_order' => 1]);

        $sizes  = $this->seedValues($sizeAttr,  ['S', 'M', 'L', 'XL']);
        $colors = $this->seedValues($colorAttr, ['Black', 'White', 'Navy', 'Red', 'Olive']);

        // ── Brands ────────────────────────────────────────────────────────────
        $nova   = Brand::firstOrCreate(['slug' => 'nova'],   ['name' => 'Nova',   'is_active' => true]);
        $zephyr = Brand::firstOrCreate(['slug' => 'zephyr'], ['name' => 'Zephyr', 'is_active' => true]);
        $crest  = Brand::firstOrCreate(['slug' => 'crest'],  ['name' => 'Crest',  'is_active' => true]);

        // ── Categories ────────────────────────────────────────────────────────
        $cats = [];
        foreach ([
            ['name' => 'Electronics',      'slug' => 'electronics'],
            ['name' => 'Clothing',          'slug' => 'clothing'],
            ['name' => 'Footwear',          'slug' => 'footwear'],
            ['name' => 'Home & Living',     'slug' => 'home-living'],
            ['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors'],
        ] as $i => $c) {
            $cats[$c['slug']] = Category::firstOrCreate(
                ['slug' => $c['slug']],
                ['name' => $c['name'], 'is_active' => true, 'sort_order' => $i]
            );
        }

        // ── Products ──────────────────────────────────────────────────────────
        // Electronics (Nova + Zephyr, mostly simple)
        $this->simple('Wireless Noise-Cancelling Headphones', 'electronics', $nova,   2499, 3200, 'WNC-HEADPHONES',
            'Premium over-ear headphones with 40-hour battery life and active noise cancellation.',
            '<p>Experience studio-quality sound anywhere with our Wireless Noise-Cancelling Headphones. Featuring 40mm dynamic drivers, Bluetooth 5.3, and a foldable design for portability.</p><ul><li>40-hour battery</li><li>Quick charge: 10 min → 3 hours playback</li><li>Multi-device pairing</li></ul>',
            'electronics', $cats,
            ['Driver Size' => '40mm', 'Connectivity' => 'Bluetooth 5.3', 'Battery' => '40 hours', 'Weight' => '250g'],
            '1 year manufacturer warranty'
        );
        $this->simple('Smart Watch Pro', 'electronics', $nova, 4999, 6500, 'SMART-WATCH-PRO',
            'Health-tracking smartwatch with GPS, AMOLED display, and 7-day battery.',
            '<p>Stay connected and healthy with the Smart Watch Pro. Tracks heart rate, SpO2, sleep quality, and 50+ workout modes.</p>',
            'electronics', $cats,
            ['Display' => '1.4" AMOLED', 'Battery' => '7 days', 'Water Resistance' => '5ATM', 'GPS' => 'Built-in'],
            '1 year warranty'
        );
        $this->simple('Mechanical Keyboard TKL', 'electronics', $zephyr, 3200, null, 'MECH-KB-TKL',
            'Tenkeyless mechanical keyboard with RGB backlighting and hot-swap switches.',
            '<p>Built for gamers and typists who demand precision. The TKL layout saves desk space while retaining all essential keys.</p>',
            'electronics', $cats,
            ['Switch Type' => 'Tactile Blue', 'Layout' => 'Tenkeyless', 'Backlight' => 'RGB per-key', 'Interface' => 'USB-C'],
            null
        );
        $this->simple('USB-C 7-in-1 Hub', 'electronics', $zephyr, 1499, 1999, 'USB-C-HUB-7',
            'Expand your laptop with 4K HDMI, 3x USB-A, SD card, and 100W PD charging.',
            '<p>One slim hub replaces seven dongles. Compatible with MacBook, iPad Pro, and any USB-C laptop.</p>',
            'electronics', $cats,
            ['Ports' => 'HDMI 4K, 3x USB-A 3.0, SD, microSD, USB-C PD', 'Power Delivery' => '100W', 'Material' => 'Aluminium alloy'],
            '6 months warranty'
        );
        $this->simple('Portable Bluetooth Speaker', 'electronics', $nova, 1899, 2500, 'BT-SPEAKER-P',
            'IPX7 waterproof speaker with 360° sound and 24-hour playtime.',
            '<p>Take the music wherever you go. Drop-proof, waterproof, and dustproof for every adventure.</p>',
            'electronics', $cats,
            ['Output' => '20W', 'Battery' => '24 hours', 'Water Rating' => 'IPX7', 'Charging' => 'USB-C'],
            null
        );
        $this->simple('Laptop Cooling Stand', 'electronics', $crest, 899, 1200, 'LAPTOP-STAND-C',
            'Ergonomic aluminium cooling stand with adjustable height for 13"–17" laptops.',
            '<p>Reduce neck strain and keep your laptop cool with six height settings and a solid aluminium build.</p>',
            'electronics', $cats,
            ['Material' => 'Aluminium', 'Compatible' => '13"–17" laptops', 'Angles' => '6 adjustable levels'],
            null
        );

        // Clothing (variants: Size + Color)
        $this->variant('Classic Cotton T-Shirt', 'clothing', $nova, 499, 699, 'CLASSIC-TEE',
            'Everyday 100% cotton tee available in multiple colours and sizes.',
            '<p>Our best-selling tee — soft, breathable, and built to last wash after wash. Pre-shrunk 180gsm cotton.</p>',
            'clothing', $cats, $sizeAttr, $sizes,
            ['Material' => '100% Cotton', 'Gsm' => '180gsm', 'Fit' => 'Regular'],
            null,
            ['S' => [499, 699], 'M' => [499, 699], 'L' => [499, 699], 'XL' => [549, 699]]
        );
        $this->variant('Premium Polo Shirt', 'clothing', $zephyr, 799, 1099, 'PREMIUM-POLO',
            'Piqué cotton polo with embroidered logo, perfect for casual and smart-casual.',
            '<p>Crafted from 220gsm piqué cotton with a two-button placket and ribbed collar that holds its shape.</p>',
            'clothing', $cats, $sizeAttr, $sizes,
            ['Material' => 'Piqué Cotton', 'Gsm' => '220gsm', 'Collar' => 'Ribbed turndown'],
            null
        );
        $this->variant('Oversized Hoodie', 'clothing', $nova, 1299, 1699, 'OVERSIZED-HOODIE',
            'Fleece-lined hoodie with kangaroo pocket and dropped shoulders.',
            '<p>Your go-to winter hoodie. 320gsm fleece inside keeps you warm while the relaxed fit keeps you stylish.</p>',
            'clothing', $cats, $sizeAttr, $sizes,
            ['Material' => '80% Cotton, 20% Polyester', 'Gsm' => '320gsm', 'Fit' => 'Oversized'],
            '30-day easy returns'
        );
        $this->simple('Slim-Fit Chino Pants', 'clothing', $crest, 1199, 1599, 'SLIM-CHINO',
            'Stretch-cotton chinos with four-pocket construction for a clean everyday look.',
            '<p>Made from 98% cotton / 2% elastane for just enough stretch. Machine washable.</p>',
            'clothing', $cats,
            ['Material' => '98% Cotton, 2% Elastane', 'Fit' => 'Slim', 'Rise' => 'Mid-rise'],
            null
        );
        $this->simple('Linen Summer Shirt', 'clothing', $zephyr, 999, 1399, 'LINEN-SHIRT',
            'Breathable linen shirt for warm weather — relaxed fit with a curved hem.',
            '<p>Pure linen construction gets softer with every wash. Half-placket design for easy on/off.</p>',
            'clothing', $cats,
            ['Material' => '100% Linen', 'Fit' => 'Relaxed', 'Collar' => 'Band collar'],
            null
        );
        $this->variant('Fleece Zip Jacket', 'clothing', $crest, 1599, 2199, 'FLEECE-ZIP',
            'Midlayer fleece jacket with two zip pockets and anti-pill fabric.',
            '<p>The perfect midlayer for layering in cooler months. Anti-pill 280gsm fleece holds its look season after season.</p>',
            'clothing', $cats, $sizeAttr, $sizes,
            ['Material' => 'Anti-pill Fleece', 'Gsm' => '280gsm', 'Pockets' => '2 zip hand pockets'],
            null
        );

        // Footwear (variants: Size)
        $this->variant('Trail Running Shoes', 'footwear', $nova, 2999, 3999, 'TRAIL-RUN-SHOE',
            'Lightweight trail shoes with Vibram outsole and responsive cushioning.',
            '<p>Conquer any terrain. The Trail Running Shoes feature a lugged Vibram outsole for grip on mud and gravel, with a breathable mesh upper.</p>',
            'footwear', $cats, $sizeAttr, $sizes,
            ['Upper' => 'Breathable mesh', 'Outsole' => 'Vibram', 'Drop' => '8mm', 'Weight' => '280g/pair'],
            '6 months warranty'
        );
        $this->variant('Classic Canvas Sneakers', 'footwear', $zephyr, 1299, 1799, 'CANVAS-SNEAKERS',
            'Timeless vulcanised canvas sneakers with a cushioned insole.',
            '<p>Never goes out of style. Hand-stitched canvas upper and natural rubber sole make these a wardrobe staple.</p>',
            'footwear', $cats, $sizeAttr, $sizes,
            ['Upper' => 'Canvas', 'Sole' => 'Natural rubber vulcanised', 'Insole' => 'Cushioned EVA'],
            null
        );
        $this->variant('Leather Chelsea Boots', 'footwear', $crest, 4499, 5999, 'LEATHER-CHELSEA',
            'Full-grain leather Chelsea boots with elastic gusset and leather lining.',
            '<p>Handcrafted from full-grain cowhide with a Goodyear-welt construction for resolability. Ages beautifully.</p>',
            'footwear', $cats, $sizeAttr, $sizes,
            ['Upper' => 'Full-grain leather', 'Lining' => 'Leather', 'Construction' => 'Goodyear welt', 'Sole' => 'Rubber'],
            '1 year manufacturer warranty'
        );
        $this->variant('Sports Sandals', 'footwear', $nova, 899, 1299, 'SPORTS-SANDALS',
            'Adjustable strap sandals with contoured footbed and non-slip outsole.',
            '<p>Built for beach, trail, and everything in between. The EVA midsole absorbs impact while the adjustable hook-and-loop straps ensure a secure fit.</p>',
            'footwear', $cats, $sizeAttr, $sizes,
            ['Upper' => 'Webbing straps', 'Footbed' => 'Contoured EVA', 'Outsole' => 'Non-slip rubber'],
            null
        );
        $this->simple('Memory Foam Slippers', 'footwear', $zephyr, 699, 999, 'MEMORY-FOAM-SLIPPERS',
            'Open-toe slippers with 3cm memory foam insole and non-slip sole.',
            '<p>Step into cloud-like comfort after a long day. The memory foam moulds to your foot shape for a personalised fit.</p>',
            'footwear', $cats,
            ['Insole' => '3cm memory foam', 'Outer' => 'Plush velvet', 'Sole' => 'Non-slip TPR'],
            null
        );
        $this->variant('High-Top Basketball Shoes', 'footwear', $crest, 3499, 4299, 'BBALL-HIGHTOP',
            'High-top court shoes with ankle support strap and herringbone outsole.',
            '<p>Engineered for fast cuts and vertical jumps. The TPU shank plate provides torsional rigidity on hardwood courts.</p>',
            'footwear', $cats, $sizeAttr, $sizes,
            ['Upper' => 'Synthetic leather', 'Midsole' => 'Phylon foam', 'Outsole' => 'Rubber herringbone'],
            null
        );

        // Home & Living (simple, mostly Crest + Nova)
        $this->simple('Ceramic Mug Set (4 pcs)', 'home-living', $crest, 599, 799, 'CERAMIC-MUG-4',
            'Hand-painted 350ml ceramic mugs in four complementary earth tones.',
            '<p>Microwave and dishwasher safe. Each mug is individually hand-painted, so slight variations make every set unique.</p>',
            'home-living', $cats,
            ['Capacity' => '350ml each', 'Material' => 'Ceramic', 'Dishwasher Safe' => 'Yes', 'Pieces' => '4'],
            '30-day return policy'
        );
        $this->simple('Minimalist Wall Clock', 'home-living', $nova, 1199, null, 'WALL-CLOCK-MIN',
            'Silent-sweep 30cm wall clock with brushed aluminium hands.',
            '<p>No ticking noise to distract you. The silent quartz movement and clean face suit any modern interior.</p>',
            'home-living', $cats,
            ['Diameter' => '30cm', 'Movement' => 'Silent quartz', 'Frame' => 'Solid beech wood', 'Battery' => '1x AA'],
            null
        );
        $this->simple('Bamboo Desk Organiser', 'home-living', $crest, 799, 1099, 'BAMBOO-DESK-ORG',
            'Six-compartment bamboo organiser for pens, scissors, and stationery.',
            '<p>Made from sustainably sourced bamboo. The modular design lets you rearrange the compartments to suit your workflow.</p>',
            'home-living', $cats,
            ['Material' => 'Bamboo', 'Compartments' => '6', 'Dimensions' => '25 × 18 × 10 cm'],
            null
        );
        $this->simple('Cotton Throw Blanket', 'home-living', $zephyr, 1599, 2199, 'COTTON-THROW',
            'Woven cotton throw in a classic herringbone pattern — 130×180cm.',
            '<p>100% cotton yarns woven in a timeless herringbone pattern. Machine washable on cold. Perfect for the sofa or bed.</p>',
            'home-living', $cats,
            ['Material' => '100% Cotton', 'Dimensions' => '130 × 180 cm', 'Pattern' => 'Herringbone', 'Care' => 'Machine wash cold'],
            null
        );
        $this->simple('LED Desk Lamp', 'home-living', $nova, 1299, 1799, 'LED-DESK-LAMP',
            'Touch-dimmer LED lamp with USB charging port and 5 colour temperatures.',
            '<p>Flicker-free 10W LED with 5 brightness levels and 5 colour temperatures from warm 2700K to daylight 6500K.</p>',
            'home-living', $cats,
            ['Power' => '10W LED', 'Colour Temperature' => '2700K–6500K', 'USB Port' => 'Yes (5W)', 'Dimmer' => 'Touch'],
            '1 year warranty'
        );
        $this->simple('Scented Soy Candle', 'home-living', $crest, 449, 599, 'SOY-CANDLE-200',
            '200ml hand-poured soy wax candle with 45-hour burn time.',
            '<p>Made with 100% natural soy wax and cotton wicks. Available in Vanilla & Sandalwood fragrance.</p>',
            'home-living', $cats,
            ['Volume' => '200ml', 'Wax' => 'Natural soy', 'Burn Time' => '~45 hours', 'Wick' => 'Cotton'],
            null
        );

        // Sports & Outdoors
        $this->simple('Non-Slip Yoga Mat 6mm', 'sports-outdoors', $nova, 1099, 1499, 'YOGA-MAT-6MM',
            'Eco-friendly TPE yoga mat with alignment guides and carry strap.',
            '<p>6mm thick TPE foam provides joint cushioning while the dual-texture surface grips well on both sides. Lightweight and easy to carry.</p>',
            'sports-outdoors', $cats,
            ['Material' => 'TPE', 'Thickness' => '6mm', 'Dimensions' => '183 × 61 cm', 'Weight' => '900g'],
            null
        );
        $this->simple('Resistance Band Set (5 levels)', 'sports-outdoors', $zephyr, 699, 999, 'RESIST-BAND-5',
            'Five latex resistance bands from extra-light to extra-heavy for full-body workouts.',
            '<p>Each colour-coded band provides a different resistance level. Use alone or combine for progressive overload. Includes carry bag.</p>',
            'sports-outdoors', $cats,
            ['Material' => 'Natural latex', 'Levels' => '5 (extra-light to extra-heavy)', 'Includes' => 'Carry bag'],
            null
        );
        $this->simple('Speed Jump Rope', 'sports-outdoors', $nova, 499, null, 'SPEED-JUMP-ROPE',
            'Adjustable steel wire jump rope with ball-bearing handles — up to 300 skips/min.',
            '<p>Thin steel wire coated in PVC for durability and speed. The swivel ball bearings eliminate rope twisting for uninterrupted flow.</p>',
            'sports-outdoors', $cats,
            ['Material' => 'Steel wire + PVC', 'Handle' => 'Aluminium alloy', 'Adjustable Length' => 'Yes', 'Bearings' => 'Ball-bearing'],
            null
        );
        $this->simple('Insulated Water Bottle 750ml', 'sports-outdoors', $crest, 899, 1199, 'INSUL-BOTTLE-750',
            'Double-wall vacuum-insulated stainless steel bottle — cold 24h, hot 12h.',
            '<p>18/8 food-grade stainless steel with a leak-proof lid. Keeps drinks cold for 24 hours and hot for 12 hours. BPA-free.</p>',
            'sports-outdoors', $cats,
            ['Capacity' => '750ml', 'Material' => '18/8 Stainless Steel', 'Insulation' => 'Double-wall vacuum', 'BPA Free' => 'Yes'],
            null
        );
        $this->simple('Duffel Gym Bag 40L', 'sports-outdoors', $zephyr, 1799, 2399, 'DUFFEL-40L',
            '40L duffel with wet/dry separation, shoe compartment, and padded shoulder strap.',
            '<p>Rugged 600D polyester construction with a waterproof base. The ventilated shoe tunnel keeps footwear separate from clean kit.</p>',
            'sports-outdoors', $cats,
            ['Capacity' => '40L', 'Material' => '600D Polyester', 'Shoe Compartment' => 'Yes', 'Wet Compartment' => 'Yes'],
            null
        );
        $this->simple('Foam Roller 60cm', 'sports-outdoors', $crest, 799, 1099, 'FOAM-ROLLER-60',
            'High-density EVA foam roller for myofascial release and post-workout recovery.',
            '<p>Textured surface targets muscle knots while the 60cm length covers back, quads, and IT band in one pass. Hollow core is lightweight yet strong.</p>',
            'sports-outdoors', $cats,
            ['Material' => 'High-density EVA', 'Length' => '60cm', 'Diameter' => '15cm', 'Core' => 'Hollow ABS'],
            null
        );

        $this->command->info('ShopDemoSeeder: 3 brands, 5 categories, 35 products seeded.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** @return array<string, AttributeValue> */
    private function seedValues(Attribute $attr, array $values): array
    {
        $map = [];
        foreach ($values as $i => $val) {
            $av = AttributeValue::firstOrCreate(
                ['attribute_id' => $attr->id, 'value' => $val],
                ['sort_order' => $i]
            );
            $map[$val] = $av;
        }
        return $map;
    }

    /** @param array<string, AttributeValue> $sizeMap */
    private function variant(
        string   $name,
        string   $catSlug,
        Brand    $brand,
        float    $basePrice,
        ?float   $baseCompare,
        string   $sku,
        string   $shortDesc,
        string   $description,
        string   $primaryCat,
        array    $cats,
        Attribute $sizeAttr,
        array    $sizeMap,
        array    $specs,
        ?string  $warranty,
        array    $pricingOverrides = [],
    ): void {
        $slug = Str::slug($name);
        if (Product::where('slug', $slug)->exists()) return;

        $product = Product::create([
            'name'             => $name,
            'slug'             => $slug,
            'short_description'=> $shortDesc,
            'description'      => $description,
            'brand_id'         => $brand->id,
            'status'           => 'active',
            'is_featured'      => rand(0, 1),
            'has_variants'     => true,
            'sku'              => null,
            'price'            => $basePrice,
            'compare_at_price' => $baseCompare,
            'specifications'   => array_map(fn($k, $v) => ['key' => $k, 'value' => $v], array_keys($specs), $specs),
            'warranty'         => $warranty,
            'return_policy'    => null,
        ]);

        $product->categories()->sync([$cats[$primaryCat]->id]);

        foreach ($sizeMap as $sizeVal => $av) {
            [$price, $compare] = $pricingOverrides[$sizeVal]
                ?? [$basePrice + (in_array($sizeVal, ['L', 'XL']) ? 50 : 0), $baseCompare];

            $variant = ProductVariant::create([
                'product_id'       => $product->id,
                'sku'              => $sku . '-' . strtoupper($sizeVal),
                'price'            => $price,
                'compare_at_price' => $compare,
                'is_active'        => true,
                'sort_order'       => 0,
            ]);

            ProductVariantValue::create([
                'variant_id'          => $variant->id,
                'attribute_id'        => $sizeAttr->id,
                'attribute_value_id'  => $av->id,
            ]);
        }
    }

    private function simple(
        string  $name,
        string  $catSlug,
        Brand   $brand,
        float   $price,
        ?float  $compare,
        string  $sku,
        string  $shortDesc,
        string  $description,
        string  $primaryCat,
        array   $cats,
        array   $specs,
        ?string $warranty,
        ?string $returnPolicy = null,
    ): void {
        $slug = Str::slug($name);
        if (Product::where('slug', $slug)->exists()) return;

        $product = Product::create([
            'name'             => $name,
            'slug'             => $slug,
            'short_description'=> $shortDesc,
            'description'      => $description,
            'brand_id'         => $brand->id,
            'status'           => 'active',
            'is_featured'      => rand(0, 1),
            'has_variants'     => false,
            'sku'              => $sku,
            'price'            => $price,
            'compare_at_price' => $compare,
            'specifications'   => array_map(fn($k, $v) => ['key' => $k, 'value' => $v], array_keys($specs), $specs),
            'warranty'         => $warranty,
            'return_policy'    => $returnPolicy,
        ]);

        $product->categories()->sync([$cats[$primaryCat]->id]);
    }
}
// This file is intentionally left here — inventory test products are in InventoryTestSeeder
