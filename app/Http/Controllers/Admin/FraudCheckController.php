<?php

namespace App\Http\Controllers\Admin;

use App\Models\FraudCheckCache;
use App\Services\FraudBdService;
use App\Services\FraudScorer;
use App\Services\FraudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FraudCheckController extends ProviderGroupController
{
    protected function group(): string
    {
        return 'fraud';
    }

    public function index(FraudService $fraud): View
    {
        return view('admin.fraud.index', [
            'checkers'  => $this->cards(),
            'active'    => $fraud->active()?->name(),
            'recent'    => FraudCheckCache::latest('updated_at')->limit(10)->get(),
        ]);
    }

    /** Run a real check from the admin page, so credentials can be proven before an order depends on them. */
    public function test(Request $request, FraudService $fraud): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30']]);

        $driver = $fraud->active();

        if (! $driver) {
            return back()->with('error', 'No fraud checker is enabled and set as default.');
        }

        try {
            $raw = $driver->check($data['phone'], ['ip' => $request->ip()]);
        } catch (\Throwable $e) {
            return back()->with('error', $driver->name().': '.$e->getMessage());
        }

        if (isset($raw['error'])) {
            return back()->with('error', $raw['error']);
        }

        $result = match (true) {
            $driver instanceof \App\Services\BdCourierFraudService => FraudScorer::scoreBdCourier($raw),
            $driver instanceof FraudBdService                      => FraudScorer::fromFraudBd($raw),
            default                                                => $raw,
        };

        return back()->with('success', sprintf(
            '%s: %s (%d/100) for %s',
            $driver->name(),
            FraudScorer::riskLabel($result['risk_level'] ?? 'unknown'),
            $result['risk_score'] ?? 0,
            $data['phone'],
        ));
    }
}
