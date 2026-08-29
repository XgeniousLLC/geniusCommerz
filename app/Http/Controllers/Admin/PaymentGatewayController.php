<?php

namespace App\Http\Controllers\Admin;

use App\Models\Integration;
use App\Services\PriceBook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Payment gateways get their own page because they behave differently from the other
 * provider groups: many are active at once and their order is customer-visible, so there
 * is no single "default" to set.
 *
 * Credentials are still edited through the shared registry-driven integration form.
 */
class PaymentGatewayController extends ProviderGroupController
{
    protected function group(): string
    {
        return 'payment';
    }

    public function index(PriceBook $priceBook): View
    {
        $gateways = $this->cards();

        // Sort by the merchant's chosen checkout order, then the definition's own default.
        usort($gateways, fn ($a, $b) => [
            $a['row']->exists ? $a['row']->sort_order : $a['definition']->sort,
            $a['definition']->label,
        ] <=> [
            $b['row']->exists ? $b['row']->sort_order : $b['definition']->sort,
            $b['definition']->label,
        ]);

        $baseCurrency = $priceBook->baseCurrency();

        return view('admin.payment-gateways.index', [
            'gateways'     => $gateways,
            'baseCurrency' => $baseCurrency,
            'liveCount'    => collect($gateways)->filter(fn ($g) => $g['row']->exists && $g['row']->is_active)->count(),
            // What a customer paying in the base currency would actually be offered.
            'offered'      => array_keys($this->registry->forCheckout($baseCurrency)),
        ]);
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['string'],
        ]);

        foreach (array_values($data['order']) as $position => $provider) {
            if ($this->registry->groupOf($provider) !== 'payment') {
                continue;
            }

            $integration = Integration::forSlug($provider);
            $integration->sort_order = $position + 1;
            $integration->save();
        }

        return back()->with('success', 'Checkout order updated.');
    }

}
