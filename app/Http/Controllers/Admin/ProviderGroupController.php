<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProviderRegistry;
use App\Models\Integration;
use Illuminate\Http\RedirectResponse;

/**
 * Shared behaviour for the per-group provider pages (payments, SMS, fraud).
 *
 * Each group gets its own page because they behave differently — payments have many
 * active at once in a customer-visible order, while SMS and fraud have a single default —
 * but enabling, credential checks and card assembly are identical, so they live here.
 */
abstract class ProviderGroupController extends Controller
{
    public function __construct(protected readonly ProviderRegistry $registry) {}

    abstract protected function group(): string;

    /**
     * Every provider in the group paired with its saved row, ordered by the definition's
     * own sort. Rows are created lazily, so an unconfigured provider still gets a card.
     *
     * @return list<array{definition: ProviderDefinition, row: Integration}>
     */
    protected function cards(): array
    {
        $definitions = $this->registry->group($this->group());
        $rows        = Integration::whereIn('provider', array_keys($definitions))->get()->keyBy('provider');

        $cards = [];
        foreach ($definitions as $slug => $definition) {
            $cards[] = [
                'definition' => $definition,
                'row'        => $rows->get($slug) ?? Integration::forSlug($slug),
            ];
        }

        return $cards;
    }

    /** @return array{0: ProviderDefinition, 1: Integration} */
    protected function resolve(string $provider): array
    {
        $definition = $this->registry->find($provider) ?? abort(404, 'Unknown provider.');

        if ($definition->group !== $this->group()) {
            abort(404);
        }

        return [$definition, Integration::forSlug($provider)];
    }

    public function toggle(string $provider): RedirectResponse
    {
        [$definition, $integration] = $this->resolve($provider);

        if (! $definition->isImplemented()) {
            return back()->with('error', "{$definition->label} is not available yet.");
        }

        // Refuse to enable a provider with missing required credentials rather than let
        // it fail later, in front of a customer.
        if (! $integration->is_active && ($missing = $this->missingCredentials($integration, $definition)) !== []) {
            return redirect()
                ->route('admin.integrations.edit', $provider)
                ->with('error', "Add the {$definition->label} credentials first: ".implode(', ', $missing).'.');
        }

        $integration->fill([
            'group'     => $definition->group,
            'label'     => $definition->label,
            'is_active' => ! $integration->is_active,
        ])->save();

        return back()->with('success', "{$definition->label} ".($integration->is_active ? 'enabled' : 'disabled').'.');
    }

    public function setDefault(string $provider): RedirectResponse
    {
        [$definition, $integration] = $this->resolve($provider);

        if (! $integration->exists || ! $integration->is_active) {
            return back()->with('error', "Enable {$definition->label} before making it the default.");
        }

        $integration->setAsDefault();

        return back()->with('success', "{$definition->label} is now the default {$definition->group}.");
    }

    /** @return list<string> labels of required credentials that are not filled in */
    protected function missingCredentials(Integration $integration, ProviderDefinition $definition): array
    {
        $missing = [];

        foreach ($definition->fields as $field) {
            if ($field->required && ! $integration->getCredential($field->key)) {
                $missing[] = $field->label;
            }
        }

        return $missing;
    }
}
