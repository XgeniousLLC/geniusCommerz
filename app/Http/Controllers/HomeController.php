<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $latestPosts = Blog::published()
            ->with('blogCategory')
            ->orderByDesc('published_at')
            ->take(3)
            ->get()
            ->map(fn($p) => $this->blogCard($p));

        $shopCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name', 'slug']);

        $featuredProducts = Product::where('is_featured', true)
            ->where('status', 'active')
            ->with(['images', 'variants'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->map(fn($p) => $this->productCard($p));

        $newArrivals = Product::where('status', 'active')
            ->with(['images', 'variants'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get()
            ->map(fn($p) => $this->productCard($p));

        return Inertia::render('Home', [
            'latestPosts'      => $latestPosts,
            'shopCategories'   => $shopCategories,
            'featuredProducts' => $featuredProducts,
            'newArrivals'      => $newArrivals,
            'heroTitle'        => SiteSetting::get('general.hero_title', ''),
            'heroSub'          => SiteSetting::get('general.hero_subtitle', ''),
        ]);
    }

    private function productCard(Product $p): array
    {
        $firstImage = $p->images->first();
        $inStock = $p->has_variants
            ? $p->variants->where('is_active', true)->contains(fn ($v) => $v->stock_qty === null || $v->stock_qty > 0)
            : ($p->stock_qty === null || $p->stock_qty > 0);

        return [
            'id'               => $p->id,
            'name'             => $p->name,
            'slug'             => $p->slug,
            'price'            => (float) $p->price,
            'compare_at_price' => $p->compare_at_price ? (float) $p->compare_at_price : null,
            'has_variants'     => $p->has_variants,
            'image_url'        => $firstImage?->getUrl('thumb'),
            'is_featured'      => $p->is_featured,
            'in_stock'         => $inStock,
        ];
    }

    private function blogCard($post): array
    {
        return [
            'id'                   => $post->id,
            'title'                => $post->title,
            'slug'                 => $post->slug,
            'excerpt'              => $post->excerpt,
            'cover_url'            => $post->cover_url,
            'category_name'        => $post->category_name,
            'author_display_name'  => $post->author_display_name,
            'read_time'            => $post->read_time,
            'published_at'         => $post->published_at?->format('d M Y'),
            'blogCategory'         => $post->blogCategory ? ['id' => $post->blogCategory->id, 'name' => $post->blogCategory->name, 'slug' => $post->blogCategory->slug] : null,
        ];
    }
}
