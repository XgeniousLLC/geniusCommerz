<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FraudBdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FraudController extends Controller
{
    public function check(Request $request, FraudBdService $service): JsonResponse
    {
        $request->validate(['phone' => 'required|string|max:20']);

        $result = $service->check($request->input('phone'));

        if (isset($result['error'])) {
            return response()->json(['success' => false, 'message' => $result['error']], 422);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }
}
