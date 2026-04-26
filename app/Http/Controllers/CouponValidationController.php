<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponValidationController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->input('code')))->first();

        if (! $coupon) {
            return response()->json(['message' => 'Coupon not found.'], 422);
        }

        if (! $coupon->isValid((float) $request->input('subtotal'), $request->user()?->id)) {
            return response()->json(['message' => 'This coupon is invalid or has expired.'], 422);
        }

        $discount = $coupon->computeDiscount((float) $request->input('subtotal'));

        $message = $coupon->type === 'percent'
            ? "{$coupon->value}% off applied"
            : "৳{$discount} off applied";

        return response()->json([
            'code'     => $coupon->code,
            'discount' => (float) $coupon->value,
            'type'     => $coupon->type,
            'amount'   => $discount,
            'message'  => $message,
        ]);
    }
}
