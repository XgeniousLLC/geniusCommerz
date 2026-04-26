<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'title'      => 'nullable|string|max:150',
            'body'       => 'nullable|string|max:2000',
        ]);

        $userId = auth()->id();

        // Must have a delivered order containing this product
        $eligible = Order::where('user_id', $userId)
            ->where('status', 'delivered')
            ->whereHas('items', fn ($q) => $q->where('product_id', $data['product_id']))
            ->first();

        if (! $eligible) {
            return back()->with('error', 'Only verified buyers with a delivered order can leave a review.');
        }

        // One review per product per user
        ProductReview::updateOrCreate(
            ['product_id' => $data['product_id'], 'user_id' => $userId],
            array_merge($data, ['order_id' => $eligible->id, 'user_id' => $userId])
        );

        return back()->with('success', 'Review submitted. Thank you!');
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        abort_unless($review->user_id === auth()->id(), 403);
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}
