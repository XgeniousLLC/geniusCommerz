<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudCheckCache;
use App\Services\BdCourierFraudService;
use App\Services\FraudBdService;
use App\Services\FraudScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FraudController extends Controller
{
    private const TTL_HOURS = 24;

    public function check(Request $request, BdCourierFraudService $bdCourier, FraudBdService $fraudBd): JsonResponse
    {
        $request->validate(['phone' => 'required|string|max:20']);

        $phone = $request->input('phone');
        $force = (bool) $request->input('force', false);

        // Serve from cache unless forced or expired
        if (! $force) {
            $cached = FraudCheckCache::where('phone', $phone)->first();

            if ($cached && ! $cached->isExpired()) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'phone'       => $phone,
                        'provider'    => $cached->provider,
                        'risk_level'  => $cached->risk_level,
                        'risk_score'  => $cached->risk_score,
                        'couriers'    => $cached->couriers ?? [],
                        'summary'     => $cached->summary  ?? null,
                        'reports'     => $cached->reports  ?? [],
                        'from_cache'  => true,
                        'cached_at'   => $cached->updated_at->toIso8601String(),
                        'expires_at'  => $cached->expires_at->toIso8601String(),
                    ],
                ]);
            }
        }

        // Live API call
        $result = $bdCourier->isConfigured()
            ? FraudScorer::scoreBdCourier($bdCourier->check($phone))
            : FraudScorer::fromFraudBd($fraudBd->check($phone));

        if (isset($result['error'])) {
            return response()->json(['success' => false, 'message' => $result['error']], 422);
        }

        // Persist to cache
        FraudCheckCache::updateOrCreate(
            ['phone' => $phone],
            [
                'provider'           => $result['provider'] ?? ($bdCourier->isConfigured() ? 'bdcourier' : 'fraudbd'),
                'risk_level'         => $result['risk_level'],
                'risk_score'         => $result['risk_score'],
                'fraud_report_count' => count($result['reports'] ?? []),
                'summary'            => $result['summary']  ?? null,
                'couriers'           => $result['couriers'] ?? [],
                'reports'            => $result['reports']  ?? [],
                'expires_at'         => now()->addHours(self::TTL_HOURS),
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => array_merge($result, ['from_cache' => false]),
        ]);
    }
}
