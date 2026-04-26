<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MetaInformation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Services\SEOAnalyzerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['brand', 'categories', 'images'])
            ->withCount('variants');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->input('category_id')));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }

        $products = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(25)->withQueryString();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'brands', 'categories'));
    }

    public function create(): View
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::with('children')->orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.create', compact('brands', 'categories', 'attributes'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSku($data['name']);
        }

        $data['specifications'] = $this->decodeSpecifications($data['specifications'] ?? null);

        $product = Product::create(array_merge($data, [
            'is_featured'            => $request->boolean('is_featured'),
            'has_variants'           => $request->boolean('has_variants'),
            'shipping_included'      => $request->boolean('shipping_included'),
            'preorder_enabled'       => $request->boolean('preorder_enabled'),
            'preorder_message'       => $request->input('preorder_message'),
            'preorder_expected_date' => $request->input('preorder_expected_date') ?: null,
            'created_by'             => auth('admin')->id(),
            'updated_by'             => auth('admin')->id(),
        ]));

        $product->categories()->sync($request->input('categories', []));
        $this->syncProductMedia($product, $request);

        if ($request->boolean('has_variants')) {
            $this->storeVariants($product, $request->input('variants', []));
        }

        $this->saveMeta($product, $request);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load(['brand', 'categories', 'variants.variantValues.attributeValue.attribute', 'meta', 'images', 'videos']);
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $categories = Category::with('children')->orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'brands', 'categories', 'attributes'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['specifications'] = $this->decodeSpecifications($data['specifications'] ?? null);

        $product->update(array_merge($data, [
            'is_featured'            => $request->boolean('is_featured'),
            'has_variants'           => $request->boolean('has_variants'),
            'shipping_included'      => $request->boolean('shipping_included'),
            'preorder_enabled'       => $request->boolean('preorder_enabled'),
            'preorder_message'       => $request->input('preorder_message'),
            'preorder_expected_date' => $request->input('preorder_expected_date') ?: null,
            'updated_by'             => auth('admin')->id(),
        ]));

        $product->categories()->sync($request->input('categories', []));
        $this->syncProductMedia($product, $request);

        if ($request->boolean('has_variants')) {
            $this->updateVariants($product, $request->input('variants', []));
        }

        $this->saveMeta($product, $request);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    private function syncProductMedia(Product $product, Request $request): void
    {
        // Images
        $imageIds = array_filter(array_map('intval', (array) $request->input('image_ids', [])));
        $product->images()->detach();
        foreach (array_values($imageIds) as $i => $id) {
            $product->images()->attach($id, ['type' => 'image', 'sort_order' => $i]);
        }

        // Video (single)
        $videoId = (int) $request->input('video_id');
        $product->videos()->detach();
        if ($videoId) {
            $product->videos()->attach($videoId, ['type' => 'video', 'sort_order' => 0]);
        }
    }

    private function generateSku(string $name): string
    {
        $base = strtoupper(preg_replace('/[^A-Z0-9]+/i', '-', Str::ascii($name)));
        $base = trim($base, '-');
        $base = substr($base, 0, 20);
        $suffix = strtoupper(Str::random(6));
        $sku = "{$base}-{$suffix}";

        // Ensure uniqueness
        while (Product::where('sku', $sku)->exists()) {
            $sku = "{$base}-" . strtoupper(Str::random(6));
        }

        return $sku;
    }

    /** @param array<int, array<string, mixed>> $variantsData */
    private function storeVariants(Product $product, array $variantsData): void
    {
        foreach ($variantsData as $i => $row) {
            $variant = ProductVariant::create([
                'product_id'       => $product->id,
                'sku'              => $row['sku'] ?? null,
                'price'            => $row['price'],
                'compare_at_price' => $row['compare_at_price'] ?? null,
                'cost_price'       => $row['cost_price'] ?? null,
                'weight'           => $row['weight'] ?? null,
                'stock_qty'        => isset($row['stock_qty']) && $row['stock_qty'] !== '' ? (int) $row['stock_qty'] : null,
                'is_active'        => true,
                'sort_order'       => $i,
            ]);

            foreach ($row['attribute_value_ids'] ?? [] as $valueId) {
                $value = AttributeValue::find($valueId);
                if ($value) {
                    ProductVariantValue::create([
                        'variant_id' => $variant->id,
                        'attribute_id' => $value->attribute_id,
                        'attribute_value_id' => $value->id,
                    ]);
                }
            }
        }
    }

    /** @param array<int, array<string, mixed>> $variantsData */
    private function updateVariants(Product $product, array $variantsData): void
    {
        $incoming = [];

        foreach ($variantsData as $i => $row) {
            $variantId = $row['id'] ?? null;

            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant && $variant->product_id === $product->id) {
                    $variant->update([
                        'sku'              => $row['sku'] ?? null,
                        'price'            => $row['price'],
                        'compare_at_price' => $row['compare_at_price'] ?? null,
                        'cost_price'       => $row['cost_price'] ?? null,
                        'weight'           => $row['weight'] ?? null,
                        'stock_qty'        => isset($row['stock_qty']) && $row['stock_qty'] !== '' ? (int) $row['stock_qty'] : null,
                        'is_active'        => isset($row['is_active']) ? (bool) $row['is_active'] : true,
                        'sort_order'       => $i,
                    ]);
                    $incoming[] = $variant->id;
                }
            }
        }

        // Remove variants not in the submitted list
        $product->variants()->whereNotIn('id', $incoming)->delete();
    }

    private function decodeSpecifications(?string $json): ?array
    {
        if (!$json) return null;
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) return null;
        return array_values(array_filter($decoded, fn ($row) =>
            is_array($row) && !empty(trim($row['key'] ?? ''))
        ));
    }

    private function saveMeta(Product $product, Request $request): void
    {
        $metaTitle = $request->input('meta_title', '');
        $metaDesc = $request->input('meta_description', '');

        $seoAnalyzer = new SEOAnalyzerService;
        $analysis = $seoAnalyzer->analyzePage(
            $metaTitle ?: $product->name,
            $metaDesc,
            $product->description ?? $product->short_description ?? '',
            $request->input('meta_keywords', '')
        );

        $meta = [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDesc,
            'meta_keywords' => $request->input('meta_keywords', ''),
            'focus_keyword' => $request->input('focus_keyword', ''),
            'canonical_url' => $request->input('canonical_url', ''),
            'robots' => $request->input('robots', 'index,follow'),
            'seo_score' => $analysis['score'] ?? 0,
        ];

        MetaInformation::updateOrCreate(
            ['metable_type' => Product::class, 'metable_id' => $product->id],
            $meta
        );
    }
}
