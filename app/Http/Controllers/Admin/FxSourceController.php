<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * Exchange-rate sources. Managed from the Currencies page, since a rate source is only
 * meaningful alongside the currencies it updates.
 */
class FxSourceController extends ProviderGroupController
{
    protected function group(): string
    {
        return 'fx';
    }

    /** Run the scheduled refresh on demand, so a merchant can see it work immediately. */
    public function refresh(): RedirectResponse
    {
        $exit   = Artisan::call('currency:refresh-rates');
        $output = trim(Artisan::output());

        return back()->with($exit === 0 ? 'success' : 'error', $output ?: 'Rate refresh finished.');
    }
}
