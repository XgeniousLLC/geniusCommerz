<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $integrations = Integration::orderBy('provider')->get();

        return view('admin.integrations.index', compact('integrations'));
    }

    public function edit(Integration $integration): View
    {
        return view('admin.integrations.edit', compact('integration'));
    }

    public function update(Request $request, Integration $integration): RedirectResponse
    {
        $credentials = $request->input('credentials', []);

        // Strip blanks — don't overwrite a saved value with an empty string
        $existing = $integration->credentials ?? [];
        $merged = array_filter(
            array_merge($existing, $credentials),
            fn ($v) => $v !== null && $v !== '',
        );

        $integration->update([
            'credentials' => $merged,
            'is_active' => $request->boolean('is_active'),
            'environment' => $request->input('environment', 'sandbox'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('admin.integrations.index')
            ->with('success', "{$integration->label} credentials saved.");
    }
}
