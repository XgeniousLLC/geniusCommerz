<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\MediaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

/**
 * Gives the demo storefront real photography: one photograph per product, plus the
 * hero image the home page uses for its collection panel.
 *
 * Photos are pulled from the Unsplash CDN by id. Ids are pinned rather than searched
 * because the search API needs a key, and a pinned id is also what makes the seeder
 * reproducible — every run gives the same catalogue, not a random one.
 *
 * Unsplash License: free to use commercially, no attribution required.
 *
 * Needs network access, so it is not part of DatabaseSeeder. Run it after ShopDemoSeeder:
 *   php artisan db:seed --class=ProductImageSeeder
 */
class ProductImageSeeder extends Seeder
{
    /** Cropped to 4:5, the aspect ratio the storefront product card renders. */
    private const VARIANT = '?w=1200&h=1500&fit=crop&crop=entropy&q=80&fm=jpg';

    /** Portrait crop for the 3:4 hero and collection panels. */
    private const HERO_VARIANT = '?w=1200&h=1600&fit=crop&crop=entropy&q=80&fm=jpg';

    /** Home page hero / "Explore the collection" panel. */
    private const HERO = ['1441984904996-e0b6ba687e04', 'Interior of a clothing boutique'];

    /** product slug => [unsplash photo id, alt text] */
    private const PHOTOS = [
        'wireless-noise-cancelling-headphones' => ['1505740420928-5e560c06d30e', 'Over-ear wireless headphones on a yellow background'],
        'smart-watch-pro'                      => ['1523275335684-37898b6baf30', 'Smart watch with a white strap'],
        'mechanical-keyboard-tkl'              => ['1618384887929-16ec33fab9ef', 'Compact mechanical keyboard on a marble desk'],
        'portable-bluetooth-speaker'           => ['1608043152269-423dbba4e7e1', 'Portable Bluetooth speaker on a wooden surface'],
        'laptop-cooling-stand'                 => ['1587614382346-4ec70e388b28', 'Laptop raised on a stand on a tidy desk'],
        'probook-ultra-14'                     => ['1541807084-5c52b6b3adef', 'Slim laptop open on a wooden desk'],

        'classic-cotton-t-shirt' => ['1521572163474-6864f9cf17ab', 'Plain white cotton t-shirt worn by a model'],
        'premium-polo-shirt'     => ['1618354691373-d851c5c3a990', 'Black cotton shirt on a wooden hanger'],
        'oversized-hoodie'       => ['1556821840-3a63f95609a7', 'Grey oversized hoodie worn outdoors'],
        'slim-fit-chino-pants'   => ['1473966968600-fa801b869a1a', 'Tan slim-fit chino trousers'],
        'linen-summer-shirt'     => ['1596755094514-f87e34085b2c', 'Light chambray summer shirt on a hanger'],
        'fleece-zip-jacket'      => ['1591047139829-d91aecb6caea', 'Zip-front jacket hanging against a plain wall'],
        'premium-zip-hoodie'     => ['1578768079052-aa76e52ff62e', 'Zip hoodie worn with the hood up'],
        'dryfit-sports-tshirt'   => ['1622519407650-3df9883f76a5', 'Athletic t-shirt worn by a model'],
        'stretch-chino-shorts'   => ['1617952236317-0bd127407984', 'Casual shorts worn against a blue wall'],

        'trail-running-shoes'      => ['1542291026-7eec264c27ff', 'Red trail running shoe'],
        'classic-canvas-sneakers'  => ['1525966222134-fcfa99b8ae77', 'Burgundy canvas sneaker on a yellow background'],
        'leather-chelsea-boots'    => ['1608256246200-53e635b5b65f', 'Brown leather boots'],
        'sports-sandals'           => ['1603487742131-4160ec999306', 'Twin-strap sandals on a soft rug'],
        'high-top-basketball-shoes' => ['1552346154-21d32810aba3', 'High-top basketball sneaker'],

        'ceramic-mug-set-4-pcs' => ['1509042239860-f550ce710b93', 'Set of ceramic coffee cups beside plants'],
        'minimalist-wall-clock' => ['1563861826100-9cb868fdbe1c', 'Minimalist round wall clock'],
        'bamboo-desk-organiser' => ['1497032628192-86f99bcd76bc', 'Tidy desk with stationery and a coffee cup'],
        'cotton-throw-blanket'  => ['1616627561950-9f746e330187', 'Cotton throw and cushions on a made bed'],
        'led-desk-lamp'         => ['1507473885765-e6ed057f782c', 'Adjustable desk lamp'],
        'scented-soy-candle'    => ['1603006905003-be475563bc59', 'Lit scented candle in a glass jar'],

        'non-slip-yoga-mat-6mm'        => ['1592432678016-e910b452f9a2', 'Rolled yoga mats'],
        'resistance-band-set-5-levels' => ['1584735935682-2f2b69dff9d2', 'Resistance band and dumbbells on a wooden floor'],
        'insulated-water-bottle-750ml' => ['1602143407151-7111542de6e8', 'Green insulated water bottle'],
        'duffel-gym-bag-40l'           => ['1547949003-9792a18a2601', 'Canvas and leather holdall bag'],
    ];

    public function run(): void
    {
        $media = app(MediaService::class);
        $attached = $skipped = $failed = 0;

        foreach (self::PHOTOS as $slug => [$photoId, $alt]) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                $this->command->warn("  no product for [{$slug}]");
                continue;
            }

            // Idempotent: never stack a second copy onto a product that already has one.
            if ($product->images()->exists()) {
                $skipped++;
                continue;
            }

            $tmp = $this->download($photoId, self::VARIANT);

            if ($tmp === null) {
                $this->command->warn("  {$slug}: download failed");
                $failed++;
                continue;
            }

            try {
                $record = $media->storeFile($tmp, "{$slug}.jpg", 'image/jpeg');
                $record->update(['alt' => $alt, 'title' => $product->name]);

                $product->images()->attach($record->id, ['type' => 'image', 'sort_order' => 0]);
                $attached++;
                $this->command->info("  {$slug}");
            } finally {
                @unlink($tmp);
            }
        }

        $this->command->info("Images attached: {$attached}, already had one: {$skipped}, failed: {$failed}");

        $bare = Product::doesntHave('images')->pluck('slug');
        if ($bare->isNotEmpty()) {
            $this->command->warn('Still without an image: '.$bare->implode(', '));
        }

        $this->seedHeroImage($media);
    }

    /**
     * The home page reads this one setting for both its hero slot and the "Explore the
     * collection" panel, so an unset value leaves two visible holes on the landing page.
     */
    private function seedHeroImage(MediaService $media): void
    {
        if (SiteSetting::get('storefront.hero_image_media_id')) {
            $this->command->info('Hero image already set.');

            return;
        }

        [$photoId, $alt] = self::HERO;
        $tmp = $this->download($photoId, self::HERO_VARIANT);

        if ($tmp === null) {
            $this->command->warn('Hero image download failed.');

            return;
        }

        try {
            $record = $media->storeFile($tmp, 'storefront-hero.jpg', 'image/jpeg');
            $record->update(['alt' => $alt, 'title' => 'Storefront hero']);

            SiteSetting::updateOrCreate(
                ['key' => 'storefront.hero_image_media_id'],
                ['value' => (string) $record->id, 'group' => 'storefront']
            );

            $this->command->info('Hero image set.');
        } finally {
            @unlink($tmp);
        }
    }

    /** Fetch a photo to a temp file, or null when the CDN will not serve it. */
    private function download(string $photoId, string $variant): ?string
    {
        try {
            $response = Http::timeout(30)->get("https://images.unsplash.com/photo-{$photoId}{$variant}");

            if (! $response->successful()) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'seed_');
        file_put_contents($tmp, $response->body());

        return $tmp;
    }
}
