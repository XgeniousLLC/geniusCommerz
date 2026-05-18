<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\ContentTranslation;
use App\Models\Language;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $categorySlug   = request('category');
        $activeCategory = $categorySlug ? BlogCategory::where('slug', $categorySlug)->first() : null;

        $query = Blog::published()->with('blogCategory')->orderByDesc('published_at');

        if ($activeCategory) {
            $query->where('blog_category_id', $activeCategory->id);
        }

        $featured   = (clone $query)->first();
        $posts      = (clone $query)->skip(1)->take(12)->get();
        $categories = BlogCategory::whereHas('blogs', fn($q) => $q->where('status', 'published'))
            ->withCount(['blogs' => fn($q) => $q->where('status', 'published')])
            ->orderBy('name')->get();

        return Inertia::render('blog/Index', [
            'featured'       => $featured ? $this->blogCard($featured) : null,
            'posts'          => $posts->map(fn($p) => $this->blogCard($p)),
            'categories'     => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'blogs_count' => $c->blogs_count]),
            'activeCategory' => $activeCategory ? ['id' => $activeCategory->id, 'name' => $activeCategory->name, 'slug' => $activeCategory->slug] : null,
            'totalCount'     => Blog::published()->count(),
        ]);
    }

    public function category(BlogCategory $category): Response
    {
        $query = Blog::published()->with('blogCategory')
            ->where('blog_category_id', $category->id)
            ->orderByDesc('published_at');

        $featured   = (clone $query)->first();
        $posts      = (clone $query)->skip(1)->take(12)->get();
        $categories = BlogCategory::whereHas('blogs', fn($q) => $q->where('status', 'published'))
            ->withCount(['blogs' => fn($q) => $q->where('status', 'published')])
            ->orderBy('name')->get();

        return Inertia::render('blog/Index', [
            'featured'       => $featured ? $this->blogCard($featured) : null,
            'posts'          => $posts->map(fn($p) => $this->blogCard($p)),
            'categories'     => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'blogs_count' => $c->blogs_count]),
            'activeCategory' => ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug],
            'totalCount'     => Blog::published()->count(),
        ]);
    }

    public function show(Blog $blog, Request $request): Response
    {
        if ($blog->status !== 'published') {
            abort(404);
        }

        $blog->load('metaInformation', 'coverMedia', 'blogCategory');

        $related = Blog::published()->with('blogCategory')
            ->where('id', '!=', $blog->id)
            ->when($blog->blog_category_id, fn($q) => $q->where('blog_category_id', $blog->blog_category_id))
            ->orderByDesc('published_at')->take(3)->get();

        if ($related->count() < 3) {
            $related = Blog::published()->with('blogCategory')
                ->where('id', '!=', $blog->id)
                ->orderByDesc('published_at')->take(3)->get();
        }

        $categories = BlogCategory::whereHas('blogs', fn($q) => $q->where('status', 'published'))
            ->withCount(['blogs' => fn($q) => $q->where('status', 'published')])
            ->orderBy('name')->get();

        $comments = $blog->comments()
            ->with(['replies' => fn($q) => $q->with('replies')])
            ->approved()->topLevel()->latest()->get();

        $recent = Blog::published()
            ->where('id', '!=', $blog->id)
            ->orderByDesc('published_at')->take(4)->get();

        $blogData = $this->blogFull($blog);
        $blogData = $this->applyBlogTranslation($blogData, $blog, $request);

        return Inertia::render('blog/Show', [
            'blog'       => $blogData,
            'related'    => $related->map(fn($p) => $this->blogCard($p)),
            'recent'     => $recent->map(fn($p) => $this->blogCard($p)),
            'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'blogs_count' => $c->blogs_count]),
            'comments'   => $comments->map(fn($c) => $this->commentData($c)),
        ]);
    }

    private function applyBlogTranslation(array $data, Blog $blog, Request $request): array
    {
        $locale = $request->cookie('locale');
        if (! $locale) return $data;

        $lang = Language::where('code', $locale)->where('is_active', true)->first();
        if (! $lang) return $data;

        $ct = ContentTranslation::where('language_id', $lang->id)
            ->where('translatable_type', Blog::class)
            ->where('translatable_id', $blog->id)
            ->first();

        if (! $ct) return $data;

        foreach (['title', 'excerpt', 'content'] as $field) {
            if (! empty($ct->fields[$field])) {
                $data[$field] = $ct->fields[$field];
            }
        }

        return $data;
    }

    private function blogCard($post): array
    {
        return [
            'id'                  => $post->id,
            'title'               => $post->title,
            'slug'                => $post->slug,
            'excerpt'             => $post->excerpt,
            'cover_url'           => $post->cover_url,
            'category_name'       => $post->category_name,
            'author_display_name' => $post->author_display_name,
            'read_time'           => $post->read_time,
            'published_at'        => $post->published_at?->format('d M Y'),
            'blog_category_id'    => $post->blog_category_id,
            'blogCategory'        => $post->blogCategory ? ['id' => $post->blogCategory->id, 'name' => $post->blogCategory->name, 'slug' => $post->blogCategory->slug] : null,
        ];
    }

    private function blogFull($blog): array
    {
        $meta = $blog->metaInformation;
        return array_merge($this->blogCard($blog), [
            'content'    => $blog->content,
            'enable_toc' => $blog->enable_toc,
            'faqs'       => $blog->faqs,
            'metaInformation' => $meta ? [
                'meta_title'       => $meta->meta_title,
                'meta_description' => $meta->meta_description,
                'robots'           => $meta->robots,
                'og_title'         => $meta->og_title,
                'og_description'   => $meta->og_description,
                'og_image'         => $meta->og_image,
                'canonical_url'    => $meta->canonical_url,
            ] : null,
        ]);
    }

    private function commentData($comment): array
    {
        return [
            'id'         => $comment->id,
            'name'       => $comment->name,
            'body'       => $comment->body,
            'created_at' => $comment->created_at->diffForHumans(),
            'replies'    => $comment->replies->map(fn($r) => [
                'id'         => $r->id,
                'name'       => $r->name,
                'body'       => $r->body,
                'created_at' => $r->created_at->diffForHumans(),
                'replies'    => [],
            ])->all(),
        ];
    }
}
