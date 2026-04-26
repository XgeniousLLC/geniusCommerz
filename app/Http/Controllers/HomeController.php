<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
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
            ->take(6)
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Home', [
            'latestPosts'    => $latestPosts,
            'shopCategories' => $shopCategories,
            'heroTitle'      => SiteSetting::get('general.hero_title', ''),
            'heroSub'        => SiteSetting::get('general.hero_subtitle', ''),
        ]);
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
