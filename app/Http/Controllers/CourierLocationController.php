<?php

namespace App\Http\Controllers;

use App\Services\CourierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourierLocationController extends Controller
{
    public function __construct(private CourierService $courier) {}

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

        $params = $request->validate([
            'city_id'     => 'nullable|integer',
            'zone_id'     => 'nullable|integer',
            'area_id'     => 'nullable|integer',
            'item_weight' => 'nullable|numeric|min:0.1',
        ]);

        try {
            $charge = $this->courier->driver()->calculateCharge($params);
        } catch (\Throwable) {
            $charge = null;
        }

        return response()->json(['charge' => $charge]);
    }
}
