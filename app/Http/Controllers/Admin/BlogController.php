<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Blog;
use App\Models\MetaInformation;
use App\Services\SEOAnalyzerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $blogs = Blog::orderByDesc('created_at')->paginate(20);
        return view('admin.blogs.index', compact('blogs'));
    }

    public function create(): View
    {
        $admins = Admin::orderBy('name')->get(['id', 'name']);
        return view('admin.blogs.create', compact('admins'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:blogs,slug',
            'excerpt'          => 'nullable|string|max:500',
            'content'          => 'nullable|string',
            'enable_toc'       => 'nullable|boolean',
            'cover_media_id'   => 'nullable|integer|exists:media,id',
            'blog_category_id' => 'nullable|integer|exists:blog_categories,id',
            'author_admin_id'  => 'nullable|integer|exists:admins,id',
            'author_name'      => 'nullable|string|max:100',
            'status'           => 'required|in:draft,published',
            'published_at'     => 'nullable|date',
            'faqs'             => 'nullable|array',
            'faqs.*.question'  => 'required_with:faqs|string|max:500',
            'faqs.*.answer'    => 'required_with:faqs|string',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $data['enable_toc'] = $request->boolean('enable_toc');

        if ($data['status'] === 'published') {
            $data['published_at'] = !empty($data['published_at'])
                ? \Carbon\Carbon::parse($data['published_at'])
                : now();
        }

        if (!empty($data['author_admin_id'])) {
            $admin = Admin::find($data['author_admin_id']);
            $data['author_name'] = $admin?->name ?? ($data['author_name'] ?: 'Admin');
        } elseif (empty($data['author_name'])) {
            $data['author_name'] = 'Admin';
        }

        $data['faqs'] = $this->cleanFaqs($request->input('faqs', []));
        $data['created_by'] = Auth::guard('admin')->id();

        $blog = Blog::create($data);

        $this->saveMeta($request, $blog);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post created.');
    }

    public function edit(Blog $blog): View
    {
        $blog->load('metaInformation');
        $meta = $blog->metaInformation;
        $admins = Admin::orderBy('name')->get(['id', 'name']);
        return view('admin.blogs.edit', compact('blog', 'meta', 'admins'));
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:blogs,slug,' . $blog->id,
            'excerpt'          => 'nullable|string|max:500',
            'content'          => 'nullable|string',
            'enable_toc'       => 'nullable|boolean',
            'cover_media_id'   => 'nullable|integer|exists:media,id',
            'blog_category_id' => 'nullable|integer|exists:blog_categories,id',
            'author_admin_id'  => 'nullable|integer|exists:admins,id',
            'author_name'      => 'nullable|string|max:100',
            'status'           => 'required|in:draft,published',
            'published_at'     => 'nullable|date',
            'faqs'             => 'nullable|array',
            'faqs.*.question'  => 'required_with:faqs|string|max:500',
            'faqs.*.answer'    => 'required_with:faqs|string',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $data['enable_toc'] = $request->boolean('enable_toc');

        if ($data['status'] === 'published') {
            if (!empty($data['published_at'])) {
                $data['published_at'] = \Carbon\Carbon::parse($data['published_at']);
            } elseif (!$blog->published_at) {
                $data['published_at'] = now();
            }
        }

        if (!empty($data['author_admin_id'])) {
            $admin = Admin::find($data['author_admin_id']);
            $data['author_name'] = $admin?->name ?? ($data['author_name'] ?: $blog->author_name);
        } elseif (empty($data['author_name'])) {
            $data['author_name'] = $blog->author_name ?: 'Admin';
        }

        $data['faqs'] = $this->cleanFaqs($request->input('faqs', []));

        $blog->update($data);

        $this->saveMeta($request, $blog);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog post updated.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $blog->metaInformation()->delete();
        $blog->delete();
        return redirect()->route('admin.blogs.index')->with('success', 'Blog post deleted.');
    }

    private function cleanFaqs(array $faqs): array
    {
        return array_values(array_filter($faqs, fn($f) => !empty($f['question']) && !empty($f['answer'])));
    }

    private function saveMeta(Request $request, Blog $blog): void
    {
        $metaTitle = $request->input('meta_title', '');
        $metaDesc  = $request->input('meta_description', '');

        $analyzer = new SEOAnalyzerService;
        $analysis = $analyzer->analyzePage(
            $metaTitle ?: $blog->title,
            $metaDesc,
            strip_tags($blog->content ?? ''),
            $request->input('focus_keyword', '')
        );

        MetaInformation::updateOrCreate(
            ['metable_type' => Blog::class, 'metable_id' => $blog->id],
            [
                'meta_title'       => $metaTitle,
                'meta_description' => $metaDesc,
                'meta_keywords'    => $request->input('meta_keywords', ''),
                'focus_keyword'    => $request->input('focus_keyword', ''),
                'canonical_url'    => $request->input('canonical_url', ''),
                'robots'           => $request->input('robots', 'index,follow'),
                'seo_score'        => $analysis['score'] ?? 0,
            ]
        );
    }
}
