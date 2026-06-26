<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltySetting;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoyaltyController extends Controller
{
    public function settings(): View
    {
        $settings = [
            'enabled'            => LoyaltySetting::get('enabled', false),
            'points_per_taka'    => LoyaltySetting::get('points_per_taka', 0.1),
            'taka_per_point'     => LoyaltySetting::get('taka_per_point', 0.5),
            'min_redemption'     => LoyaltySetting::get('min_redemption', 100),
            'max_redemption_pct' => LoyaltySetting::get('max_redemption_pct', 20),
            'tiers'              => LoyaltySetting::get('tiers', [
                ['name' => 'Silver',   'min' => 0,     'max' => 4999,  'bonus_pct' => 0],
                ['name' => 'Gold',     'min' => 5000,  'max' => 19999, 'bonus_pct' => 5],
                ['name' => 'Platinum', 'min' => 20000, 'max' => null,  'bonus_pct' => 10],
            ]),
        ];

        return view('admin.loyalty.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'enabled'            => 'boolean',
            'points_per_taka'    => 'required|numeric|min:0',
            'taka_per_point'     => 'required|numeric|min:0',
            'min_redemption'     => 'required|integer|min:1',
            'max_redemption_pct' => 'required|integer|min:1|max:100',
            'tiers'              => 'nullable|array',
            'tiers.*.name'       => 'required|string|max:50',
            'tiers.*.min'        => 'required|integer|min:0',
            'tiers.*.max'        => 'nullable|integer|min:0',
            'tiers.*.bonus_pct'  => 'required|integer|min:0|max:100',
        ]);

        LoyaltySetting::set('enabled',            (bool) $request->input('enabled', false), 'boolean');
        LoyaltySetting::set('points_per_taka',    (float) $request->input('points_per_taka'), 'decimal');
        LoyaltySetting::set('taka_per_point',     (float) $request->input('taka_per_point'), 'decimal');
        LoyaltySetting::set('min_redemption',     (int) $request->input('min_redemption'), 'number');
        LoyaltySetting::set('max_redemption_pct', (int) $request->input('max_redemption_pct'), 'number');
        LoyaltySetting::set('tiers',              $request->input('tiers', []), 'json');

        return back()->with('success', 'Loyalty settings updated.');
    }

    public function index(Request $request): View
    {
        $query = LoyaltyPoint::with(['user', 'order'])
            ->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $transactions = $query->paginate(30)->withQueryString();
        $users = \App\Models\User::orderBy('name')->select('id','name','email')->get();

        return view('admin.loyalty.index', compact('transactions', 'users'));
    }

    public function adjust(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'points'      => 'required|integer|min:1',
            'action'      => 'required|in:add,deduct',
            'description' => 'required|string|max:255',
        ]);

        $user   = User::findOrFail($request->input('user_id'));
        $points = (int) $request->input('points');
        if ($request->input('action') === 'deduct') {
            $points = -$points;
        }

        app(LoyaltyService::class)->adjust(
            $user,
            $points,
            $request->input('description')
        );

        return back()->with('success', 'Points adjusted successfully.');
    }
}
