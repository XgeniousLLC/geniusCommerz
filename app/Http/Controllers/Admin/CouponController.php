<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $query = Coupon::withCount('orders');

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->input('search') . '%');
        }

        $coupons = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        $products   = Product::orderBy('name')->get(['id','name']);
        $categories = Category::orderBy('name')->get(['id','name']);

        return view('admin.coupons.create', compact('products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'              => 'required|string|max:50|unique:coupons,code',
            'description'       => 'nullable|string|max:255',
            'type'              => 'required|in:fixed,percent',
            'value'             => 'required|numeric|min:0.01',
            'minimum_order'     => 'nullable|numeric|min:0',
            'maximum_discount'  => 'nullable|numeric|min:0',
            'applies_to'        => 'required|in:all,specific_products,specific_categories',
            'product_ids'       => 'nullable|array',
            'product_ids.*'     => 'integer|exists:products,id',
            'category_ids'      => 'nullable|array',
            'category_ids.*'    => 'integer|exists:categories,id',
            'usage_limit'       => 'nullable|integer|min:1',
            'per_customer_limit'=> 'nullable|integer|min:1',
            'is_active'         => 'boolean',
            'is_auto_apply'     => 'boolean',
            'starts_at'         => 'nullable|date',
            'expires_at'        => 'nullable|date',
        ]);

        if (!empty($data['starts_at']) && !empty($data['expires_at']) && $data['expires_at'] <= $data['starts_at']) {
            return back()->withInput()->withErrors(['expires_at' => 'Expiry date must be after start date.']);
        }

        $data['code']           = strtoupper($data['code']);
        $data['is_active']      = $request->boolean('is_active', true);
        $data['is_auto_apply']  = $request->boolean('is_auto_apply');

        $coupon = Coupon::create($data);

        if ($data['applies_to'] === 'specific_products') {
            $coupon->products()->sync($request->input('product_ids', []));
        } elseif ($data['applies_to'] === 'specific_categories') {
            $coupon->categories()->sync($request->input('category_ids', []));
        }

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        $products   = Product::orderBy('name')->get(['id','name']);
        $categories = Category::orderBy('name')->get(['id','name']);
        $coupon->load('products', 'categories');

        return view('admin.coupons.edit', compact('coupon', 'products', 'categories'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $request->validate([
            'code'              => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description'       => 'nullable|string|max:255',
            'type'              => 'required|in:fixed,percent',
            'value'             => 'required|numeric|min:0.01',
            'minimum_order'     => 'nullable|numeric|min:0',
            'maximum_discount'  => 'nullable|numeric|min:0',
            'applies_to'        => 'required|in:all,specific_products,specific_categories',
            'product_ids'       => 'nullable|array',
            'product_ids.*'     => 'integer|exists:products,id',
            'category_ids'      => 'nullable|array',
            'category_ids.*'    => 'integer|exists:categories,id',
            'usage_limit'       => 'nullable|integer|min:1',
            'per_customer_limit'=> 'nullable|integer|min:1',
            'is_active'         => 'boolean',
            'is_auto_apply'     => 'boolean',
            'starts_at'         => 'nullable|date',
            'expires_at'        => 'nullable|date',
        ]);

        if (!empty($data['starts_at']) && !empty($data['expires_at']) && $data['expires_at'] <= $data['starts_at']) {
            return back()->withInput()->withErrors(['expires_at' => 'Expiry date must be after start date.']);
        }

        $data['code']           = strtoupper($data['code']);
        $data['is_active']      = $request->boolean('is_active');
        $data['is_auto_apply']  = $request->boolean('is_auto_apply');

        $coupon->update($data);

        $coupon->products()->sync(
            $data['applies_to'] === 'specific_products' ? $request->input('product_ids', []) : []
        );
        $coupon->categories()->sync(
            $data['applies_to'] === 'specific_categories' ? $request->input('category_ids', []) : []
        );

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }
}
