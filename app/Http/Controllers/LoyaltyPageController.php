<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use App\Models\LoyaltySetting;
use App\Services\LoyaltyService;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyPageController extends Controller
{
    public function show(): Response
    {
        $service  = app(LoyaltyService::class);
        $enabled  = $service->isEnabled();
        $user     = auth()->user();

        $settings = [
            'enabled'            => $enabled,
            'points_per_taka'    => (float) LoyaltySetting::get('points_per_taka', 0.1),
            'taka_per_point'     => (float) LoyaltySetting::get('taka_per_point', 0.5),
            'min_redemption'     => (int)   LoyaltySetting::get('min_redemption', 100),
            'max_redemption_pct' => (int)   LoyaltySetting::get('max_redemption_pct', 20),
            'tiers'              => LoyaltySetting::get('tiers', [
                ['name' => 'Silver',   'min' => 0,     'max' => 4999,  'bonus_pct' => 0],
                ['name' => 'Gold',     'min' => 5000,  'max' => 19999, 'bonus_pct' => 5],
                ['name' => 'Platinum', 'min' => 20000, 'max' => null,  'bonus_pct' => 10],
            ]),
        ];

        $userProps = null;

        if ($user && $enabled) {
            $balance     = $service->getBalance($user);
            $tier        = $service->getTier($user);
            $takaValue   = $service->pointsToTaka($balance);
            $tiers       = $settings['tiers'];
            $tierIdx     = array_search($tier['name'], array_column($tiers, 'name'));
            $nextTier    = ($tierIdx !== false && isset($tiers[$tierIdx + 1])) ? $tiers[$tierIdx + 1] : null;
            $totalEarned = (int) LoyaltyPoint::where('user_id', $user->id)->where('type', 'earned')->sum('points');

            $history = LoyaltyPoint::where('user_id', $user->id)
                ->with('order:id,order_number')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(fn($p) => [
                    'type'         => $p->type,
                    'points'       => $p->points,
                    'balance_after'=> $p->balance_after,
                    'description'  => $p->description,
                    'order_number' => $p->order?->order_number,
                    'created_at'   => $p->created_at->format('M d, Y'),
                ]);

            $userProps = [
                'balance'       => $balance,
                'taka_value'    => $takaValue,
                'tier'          => $tier,
                'next_tier'     => $nextTier,
                'total_earned'  => $totalEarned,
                'history'       => $history,
            ];
        }

        return Inertia::render('Loyalty', compact('settings', 'userProps'));
    }
}
