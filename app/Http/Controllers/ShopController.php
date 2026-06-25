<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ContentTranslation;
use App\Models\Language;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->buildListing($request);
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Product::where('status', 'active')
            ->where('name', 'like', '%' . $q . '%')
            ->with('images')
            ->select('id', 'name', 'slug', 'price', 'compare_at_price')
            ->limit(6)
            ->get()
            ->map(fn ($p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'slug'             => $p->slug,
                'price'            => $p->price,
                'compare_at_price' => $p->compare_at_price,
                'image'            => $p->images->first()?->getUrl('thumb') ?? $p->images->first()?->getUrl(),
            ]);

        return response()->json($results);
    }

    public function category(Request $request, Category $category): Response
    {
        return $this->buildListing($request, activeCategory: $category);
    }

    public function brand(Request $request, Brand $brand): Response
    {
        return $this->buildListing($request, activeBrand: $brand);
    }

    public function show(Product $product, Request $request): Response
    {
        if ($product->status !== 'active') {
            abort(404);
        }

        $product->load(['images', 'videos', 'variants.variantValues.attributeValue.attribute', 'categories', 'brand', 'meta']);

        $related = Product::with(['images', 'variants'])
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->whereHas('categories', fn ($q) =>
                $q->whereIn('categories.id', $product->categories->pluck('id'))
            )
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $productData = $this->productFull($product);
        $productData = $this->applyContentTranslation($productData, $product, $request);

        return Inertia::render('Shop/Show', [
            'product' => $productData,
            'related' => $related->map(fn ($p) => $this->productCard($p)),
        ]);
    }

    private function applyContentTranslation(array $data, Product $product, Request $request): array
    {
        $locale = $request->cookie('locale');
        if (! $locale) return $data;

        $lang = Language::where('code', $locale)->where('is_active', true)->first();
        if (! $lang) return $data;

        $ct = ContentTranslation::where('language_id', $lang->id)
            ->where('translatable_type', Product::class)
            ->where('translatable_id', $product->id)
            ->first();

        if (! $ct) return $data;

        foreach (['name', 'short_description', 'description'] as $field) {
            if (! empty($ct->fields[$field])) {
                $data[$field] = $ct->fields[$field];
            }
        }

        return $data;
    }

    private function buildListing(
        Request   $request,
        ?Category $activeCategory = null,
        ?Brand    $activeBrand    = null,
    ): Response {
        $query = Product::with(['images', 'variants', 'categories'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'active');

        if ($activeCategory) {
            $query->whereHas('categories', fn ($sq) =>
                $sq->where('categories.id', $activeCategory->id)
            );
        } elseif ($request->filled('category')) {
            $query->whereHas('categories', fn ($sq) =>
                $sq->where('slug', $request->input('category'))
            );
        }

        if ($activeBrand) {
            $query->where('brand_id', $activeBrand->id);
        } elseif ($request->filled('brand')) {
            $query->whereHas('brand', fn ($sq) =>
                $sq->where('slug', $request->input('brand'))
            );
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('short_description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        if ($request->boolean('in_stock')) {
            $query->where(function ($sq) {
                $sq->where(function ($q2) {
                    $q2->where('has_variants', false)
                       ->where(fn ($q3) => $q3->whereNull('stock_qty')->orWhere('stock_qty', '>', 0));
                })->orWhere(function ($q2) {
                    $q2->where('has_variants', true)
                       ->whereHas('variants', fn ($q3) =>
                           $q3->where('is_active', true)
                              ->where(fn ($q4) => $q4->whereNull('stock_qty')->orWhere('stock_qty', '>', 0))
                       );
                });
            });
        }

        if ($request->boolean('on_sale')) {
            $query->whereNotNull('compare_at_price')->whereColumn('price', '<', 'compare_at_price');
        }

        if ($request->filled('min_rating')) {
            $query->whereHas('reviews', fn ($sq) =>
                $sq->havingRaw('AVG(rating) >= ?', [(float) $request->input('min_rating')])
            );
        }

        match ($request->input('sort', 'newest')) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'featured'   => $query->orderByDesc('is_featured')->orderByDesc('created_at'),
            'rating'     => $query->orderByDesc('reviews_avg_rating'),
            'discount'   => $query->orderByRaw('CASE WHEN compare_at_price > 0 THEN (compare_at_price - price) / compare_at_price ELSE 0 END DESC'),
            default      => $query->orderByDesc('created_at'),
        };

        $products   = $query->paginate(24)->withQueryString();
        $categories = Category::where('is_active', true)->whereNull('parent_id')
            ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('sort_order')->get(['id','name','slug']);
        $brands     = Brand::withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')->get(['id','name','slug']);

        return Inertia::render('Shop/Index', [
            'products'            => $products->through(fn ($p) => $this->productCard($p)),
            'categories'          => $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'products_count' => $c->products_count]),
            'brands'              => $brands->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'slug' => $b->slug, 'products_count' => $b->products_count]),
            'filters'             => $request->only(['q','min_price','max_price','sort','in_stock','on_sale','min_rating']),
            'activeCategorySlug'  => $activeCategory?->slug,
            'activeCategoryName'  => $activeCategory?->name,
            'activeBrandSlug'     => $activeBrand?->slug,
            'activeBrandName'     => $activeBrand?->name,
        ]);
    }

    private function productCard(Product $p): array
    {
        $firstImage = $p->images->first();

        return [
            'id'               => $p->id,
            'name'             => $p->name,
            'slug'             => $p->slug,
            'price'            => (float) $p->price,
            'compare_at_price' => $p->compare_at_price ? (float) $p->compare_at_price : null,
            'has_variants'     => $p->has_variants,
            'image_url'        => $firstImage?->getUrl('thumb'),
            'is_featured'      => $p->is_featured,
            'in_stock'         => $this->isInStock($p),
            'avg_rating'       => $p->reviews_avg_rating ? round((float) $p->reviews_avg_rating, 1) : null,
            'reviews_count'    => (int) ($p->reviews_count ?? 0),
            'category_name'    => $p->categories->first()?->name,
        ];
    }

    private function isInStock(Product $p): bool
    {
        if ($p->has_variants) {
            return $p->variants->where('is_active', true)->contains(fn ($v) =>
                $v->stock_qty === null || $v->stock_qty > 0
            );
        }
        return $p->stock_qty === null || $p->stock_qty > 0;
    }

    private function productFull(Product $p): array
    {
        return [
            'id'                => $p->id,
            'name'              => $p->name,
            'slug'              => $p->slug,
            'short_description' => $p->short_description,
            'description'       => $p->description,
            'price'             => (float) $p->price,
            'compare_at_price'  => $p->compare_at_price ? (float) $p->compare_at_price : null,
            'sku'               => $p->sku,
            'has_variants'      => $p->has_variants,
            'in_stock'          => $this->isInStock($p),
            'stock_qty'         => ! $p->has_variants ? $p->stock_qty : null,
            'brand'             => $p->brand ? [
                'id'   => $p->brand->id,
                'name' => $p->brand->name,
                'slug' => $p->brand->slug,
            ] : null,
            'categories' => $p->categories->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ]),
            'images'   => $p->images->map(fn ($m) => [
                'id'    => $m->id,
                'url'   => $m->getUrl(),
                'thumb' => $m->getUrl('thumb'),
            ]),
            'variants' => $p->variants->where('is_active', true)->map(fn ($v) => [
                'id'               => $v->id,
                'price'            => (float) $v->price,
                'compare_at_price' => $v->compare_at_price ? (float) $v->compare_at_price : null,
                'sku'              => $v->sku,
                'stock_qty'        => $v->stock_qty,
                'in_stock'         => $v->stock_qty === null || $v->stock_qty > 0,
                'label'            => $v->label(),
                'options'          => $v->variantValues->map(fn ($vv) => [
                    'attribute' => $vv->attributeValue->attribute->name,
                    'value'     => $vv->attributeValue->value,
                ]),
            ])->values(),
            'shipping_included' => (bool) $p->shipping_included,
            'warranty'          => $p->warranty,
            'return_policy'     => $p->return_policy,
            'reviews'           => $p->reviews->map(fn ($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'title'      => $r->title,
                'body'       => $r->body,
                'user_name'  => $r->user->name,
                'created_at' => $r->created_at->format('M d, Y'),
            ]),
            'reviews_count'     => $p->reviews->count(),
            'reviews_avg'       => $p->reviews->count() ? round($p->reviews->avg('rating'), 1) : null,
            'specifications'         => $p->specifications ?? [],
            'preorder_enabled'       => (bool) $p->preorder_enabled,
            'preorder_message'       => $p->preorder_message,
            'preorder_expected_date' => $p->preorder_expected_date?->toDateString(),
            'videos' => $p->videos->map(fn ($m) => [
                'id'  => $m->id,
                'url' => $m->getUrl(),
            ]),
            'faqs'         => $p->faqs ?? [],
            'trust_badges' => $p->trust_badges ?? null,
            'meta' => $p->meta ? [
                'meta_title'       => $p->meta->meta_title,
                'meta_description' => $p->meta->meta_description,
            ] : null,
        ];
    }
}
