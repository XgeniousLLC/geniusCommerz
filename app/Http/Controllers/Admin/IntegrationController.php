<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Integrations\ProviderRegistry;
use App\Models\Integration;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    /**
     * Presentation only — the provider lists themselves come from the registry, so
     * adding a provider never means editing this.
     */
    private const GROUP_META = [
        'payment' => ['label' => 'Payments',      'icon' => 'card',    'color' => 't-teal',    'hint' => 'Enable as many as you like — customers choose at checkout'],
        'ai'      => ['label' => 'AI Providers',  'icon' => 'spark',   'color' => 't-violet',  'hint' => 'Only one AI provider can be the default'],
        'courier' => ['label' => 'Couriers',      'icon' => 'truck',   'color' => 't-warning', 'hint' => 'Only one courier can be the default'],
        'carrier' => ['label' => 'Carriers',      'icon' => 'truck',   'color' => 't-info',    'hint' => 'Global carriers rated by weight and destination'],
        'sms'     => ['label' => 'SMS Gateways',  'icon' => 'message', 'color' => 't-pop',     'hint' => 'Only one gateway can be the default'],
        'fraud'   => ['label' => 'Fraud Checks',  'icon' => 'shield',  'color' => 't-info',    'hint' => 'Only one fraud checker can be the default'],
        'fx'      => ['label' => 'Exchange Rates', 'icon' => 'refresh', 'color' => 't-info',   'hint' => 'Only one rate source can be the default'],
    ];

    public function __construct(private readonly ProviderRegistry $registry) {}

    public function index(): View
    {
        $groups = [];

        foreach (self::GROUP_META as $group => $meta) {
            $cards = $this->cards($group);

            if ($cards !== []) {
                $groups[$group] = $meta + ['cards' => $cards];
            }
        }

        $configured = Integration::where('is_active', true)->count();
        $total      = count($this->registry->all());

        return view('admin.integrations.index', compact('groups', 'configured', 'total'));
    }

    public function aiSettings(): View
    {
        return view('admin.ai-settings.index', ['cards' => $this->cards('ai')]);
    }

    public function edit(string $provider): View
    {
        [$definition, $integration] = $this->resolve($provider);

        return view('admin.integrations.edit', compact('definition', 'integration'));
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        [$definition, $integration] = $this->resolve($provider);

        $environment = $definition->supportsEnvironments()
            ? $request->input('environment', 'sandbox')
            : ($definition->environments[0] ?? 'live');

        if (! in_array($environment, $definition->environments, true)) {
            $environment = $definition->environments[0] ?? 'sandbox';
        }

        // Which fields are stored per environment vs shared. Secrets are environment-scoped
        // by default so live keys survive a switch to sandbox.
        $scoped = [];
        foreach ($definition->fields as $field) {
            $scoped[$field->key] = $field->environment !== null || $field->isSecret();
        }

        $integration->fill([
            'group'       => $definition->group,
            'label'       => $definition->label,
            'environment' => $environment,
            'is_active'   => $request->boolean('is_active'),
            'notes'       => $request->input('notes'),
        ]);

        // Blank values are skipped inside mergeCredentials, so an untouched password
        // field never wipes the stored secret.
        $integration->mergeCredentials(
            array_intersect_key($request->input('credentials', []), $scoped),
            $scoped,
            $environment,
        );

        $integration->save();

        $redirect = $definition->group === 'ai'
            ? route('admin.ai-settings.index')
            : route('admin.integrations.index');

        return redirect($redirect)->with('success', "{$definition->label} credentials saved.");
    }

    public function setDefault(string $provider): RedirectResponse
    {
        [$definition, $integration] = $this->resolve($provider);

        if (! $integration->exists || ! $integration->is_active) {
            return back()->with('error', "Please activate {$definition->label} before setting it as default.");
        }

        if (! $integration->supportsDefault()) {
            return back()->with('error', 'This integration does not support default selection.');
        }

        $integration->setAsDefault();

        $redirect = $definition->group === 'ai'
            ? route('admin.ai-settings.index')
            : route('admin.integrations.index');

        return redirect($redirect)
            ->with('success', "{$definition->label} is now the default {$definition->group}.");
    }

    public function testSms(Request $request, string $provider): RedirectResponse
    {
        $request->validate([
            'phone'   => ['required', 'string'],
            'message' => ['required', 'string', 'max:160'],
        ]);

        [$definition] = $this->resolve($provider);

        if ($definition->group !== 'sms') {
            return back()->with('error', 'Not an SMS provider.');
        }

        try {
            $sms  = app(SmsService::class);
            $sent = $sms->driver($definition->slug)->send(
                // Normalised the same way a real order would be, so the test proves the
                // path that customers actually take.
                $sms->normalise($request->input('phone')),
                $request->input('message'),
            );

            return back()->with(
                $sent ? 'success' : 'error',
                $sent ? 'Test SMS sent.' : 'Gateway returned failure — check credentials.',
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'SMS failed: '.$e->getMessage());
        }
    }

    public function smsBalance(string $provider): RedirectResponse
    {
        [$definition] = $this->resolve($provider);

        if ($definition->group !== 'sms') {
            return back()->with('error', 'Not an SMS provider.');
        }

        try {
            $balance = app(SmsService::class)->driver($definition->slug)->balance();

            return $balance === null
                ? back()->with('info', "{$definition->label} does not report a balance.")
                : back()->with('success', "{$definition->label} balance: {$balance}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Balance check failed: '.$e->getMessage());
        }
    }

    /**
     * Every provider in a group, paired with its saved row where one exists.
     *
     * @return list<array{definition: \App\Integrations\ProviderDefinition, row: Integration}>
     */
    private function cards(string $group): array
    {
        $definitions = $this->registry->group($group);

        if ($definitions === []) {
            return [];
        }

        $rows = Integration::whereIn('provider', array_keys($definitions))->get()->keyBy('provider');

        $cards = [];
        foreach ($definitions as $slug => $definition) {
            $cards[] = [
                'definition' => $definition,
                'row'        => $rows->get($slug) ?? Integration::forSlug($slug),
            ];
        }

        return $cards;
    }

    /** @return array{0: \App\Integrations\ProviderDefinition, 1: Integration} */
    private function resolve(string $provider): array
    {
        $definition = $this->registry->find($provider) ?? abort(404, 'Unknown provider.');

        return [$definition, Integration::forSlug($provider)];
    }
}
