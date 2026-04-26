<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SitemapController extends Controller
{
    public function index(): View
    {
        $exists     = file_exists(public_path('sitemap.xml'));
        $lastUpdate = $exists ? date('d M Y H:i', filemtime(public_path('sitemap.xml'))) : null;

        return view('admin.sitemap.index', compact('exists', 'lastUpdate'));
    }

    public function generate(): RedirectResponse
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $now     = now()->toAtomString();

        $urls = [];

        // Static pages
        foreach (['', '/shop', '/blog', '/track', '/wishlist'] as $path) {
            $urls[] = ['loc' => $baseUrl . $path, 'changefreq' => 'daily', 'priority' => $path === '' ? '1.0' : '0.8'];
        }

        // CMS Pages (published/active)
        Page::where('status', 'published')->get(['slug', 'updated_at'])->each(function ($p) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc'        => $baseUrl . '/page/' . $p->slug,
                'lastmod'    => $p->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.6',
            ];
        });

        // Products
        Product::where('status', 'active')->get(['slug', 'updated_at'])->each(function ($p) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc'        => $baseUrl . '/shop/' . $p->slug,
                'lastmod'    => $p->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ];
        });

        // Categories
        Category::whereNull('parent_id')->get(['slug', 'updated_at'])->each(function ($c) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc'        => $baseUrl . '/shop/c/' . $c->slug,
                'lastmod'    => $c->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.6',
            ];
        });

        // Blog posts
        Blog::published()->get(['slug', 'published_at'])->each(function ($b) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc'        => $baseUrl . '/blog/' . $b->slug,
                'lastmod'    => $b->published_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ];
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            }
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>' . "\n";

        file_put_contents(public_path('sitemap.xml'), $xml);

        return back()->with('success', 'Sitemap generated with ' . count($urls) . ' URLs.');
    }
}
