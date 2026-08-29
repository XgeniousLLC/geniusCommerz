<?php

namespace App\Http\Controllers;

use App\Services\CourierService;
use App\Services\ShippingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierLocationController extends Controller
{
    public function __construct(
        private CourierService $courier,
        private ShippingCalculator $shipping,
    ) {}

    public function cities(): JsonResponse
    {
        if (! $this->courier->hasDefault()) {
            return response()->json(['cities' => []]);
        }

        try {
            $cities = $this->courier->driver()->getCities();
        } catch (\Throwable) {
            $cities = [];
        }

        return response()->json(['cities' => $cities]);
    }

    public function zones(int $cityId): JsonResponse
    {
        if (! $this->courier->hasDefault()) {
            return response()->json(['zones' => []]);
        }

        try {
            $zones = $this->courier->driver()->getZones($cityId);
        } catch (\Throwable) {
            $zones = [];
        }

        return response()->json(['zones' => $zones]);
    }

    public function areas(int $zoneId): JsonResponse
    {
        if (! $this->courier->hasDefault()) {
            return response()->json(['areas' => []]);
        }

        try {
            $areas = $this->courier->driver()->getAreas($zoneId);
        } catch (\Throwable) {
            $areas = [];
        }

        return response()->json(['areas' => $areas]);
    }

    public function charge(Request $request): JsonResponse
    {
        if (! $this->courier->hasDefault()) {
            return response()->json(['charge' => null]);
        }

        $data = $request->validate([
            'city_id'            => 'nullable|integer',
            'zone_id'            => 'nullable|integer',
            'area_id'            => 'nullable|integer',
            'items'              => 'nullable|array',
            'items.*.product_id' => 'nullable|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.quantity'   => 'nullable|integer|min:1',
        ]);

        // Weight is derived from the cart server-side. Taking it from the request would
        // let a customer understate it, and it is the same figure the order is charged on.
        $charge = $this->shipping->courierCharge($data, $data['items'] ?? []);

        return response()->json(['charge' => $charge]);
    }
}
