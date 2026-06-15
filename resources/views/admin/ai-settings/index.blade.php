@extends('admin.layouts.admin')

@section('title', 'AI Settings')

@section('content')
@php
$providerMeta = [
    'openai'   => ['name' => 'OpenAI',           'logo' => '/images/ai/openai.svg',   'hint' => 'GPT-4o, GPT-4o-mini, GPT-3.5-turbo',            'color' => '#0EA47F'],
    'gemini'   => ['name' => 'Google Gemini',    'logo' => '/images/ai/gemini.svg',   'hint' => 'gemini-1.5-flash, gemini-1.5-pro',               'color' => '#3B82F6'],
    'claude'   => ['name' => 'Anthropic Claude', 'logo' => '/images/ai/claude.svg',   'hint' => 'claude-haiku-4-5-20251001, claude-sonnet-4-6',   'color' => '#D97757'],
    'deepseek' => ['name' => 'DeepSeek',         'logo' => '/images/ai/deepseek.svg', 'hint' => 'deepseek-chat, deepseek-reasoner',               'color' => '#4D6BFE'],
];
$defaultAi = $integrations->firstWhere('is_default', true);
@endphp

<div class="page-head">
    <div>
        <h2 class="display">AI Settings</h2>
        <div class="sub">Configure AI providers and models used for content generation &amp; translation</div>
    </div>
    @if($defaultAi)
    <div>
        <span class="pill sm success"><span class="dot"></span>{{ $defaultAi->label }}</span>
        <div class="faint" style="font-size:12px;margin-top:4px;text-align:right">active provider</div>
    </div>
    @endif
</div>

{{-- Default provider banner --}}
@if($defaultAi)
@php $defaultModel = $defaultAi->credentials['model'] ?? null; @endphp
<div class="row" style="gap:12px;padding:14px 18px;border-radius:14px;background:var(--accent-soft);border:1px solid var(--border);margin-bottom:22px">
    <span class="tile sm t-accent"><span class="ico" data-ico="check" style="width:18px;height:18px"></span></span>
    <div style="font-size:13.5px;font-weight:600">
        Default provider: <strong>{{ $defaultAi->label }}</strong>
        @if($defaultModel)
        — model: <code style="background:var(--surface-2);padding:1px 7px;border-radius:5px;font-family:monospace;font-size:12.5px">{{ $defaultModel }}</code>
        @endif
    </div>
    <a href="{{ route('admin.integrations.edit', $defaultAi) }}" class="link-btn" style="margin-left:auto">
        <span class="ico" data-ico="gear" style="width:15px;height:15px"></span>Configure
    </a>
</div>
@else
<div class="row" style="gap:12px;padding:14px 18px;border-radius:14px;background:color-mix(in srgb,var(--warning) 10%,transparent);border:1px solid color-mix(in srgb,var(--warning) 30%,transparent);margin-bottom:22px">
    <span class="tile sm" style="background:var(--warning);color:#fff">
        <span class="ico" data-ico="shield" style="width:18px;height:18px"></span>
    </span>
    <div style="font-size:13.5px;font-weight:600;color:var(--text)">
        No default AI provider set. Configure and activate a provider, then set it as default to enable AI features.
    </div>
</div>
@endif

<div class="col-gap">

    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="spark" style="width:18px;height:18px"></span></span>
            <div class="ct">
                <h3>Providers</h3>
                <div class="sub">Add your API key and select a model for each provider. Set one as the default.</div>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px">
            @foreach($integrations as $integration)
            @php
            $meta     = $providerMeta[$integration->provider] ?? ['name' => $integration->label, 'logo' => '', 'hint' => '', 'color' => 'var(--text-muted)'];
            $hasKey   = !empty($integration->credentials['api_key']);
            $model    = $integration->credentials['model'] ?? null;
            $isDefault = $integration->is_default;
            @endphp
            <div class="card" style="padding:18px;display:flex;flex-direction:column;gap:13px;
                {{ $isDefault ? 'border:1.5px solid var(--accent);background:var(--accent-soft)' : 'background:var(--surface-2)' }}">
                <div class="row top" style="gap:12px">
                    <span class="tile" style="background:color-mix(in srgb,{{ $meta['color'] }} 16%,var(--surface));color:{{ $meta['color'] }}">
                        @if($meta['logo'])
                        <img src="{{ $meta['logo'] }}" alt="{{ $meta['name'] }}" style="width:22px;height:22px;object-fit:contain">
                        @else
                        <span class="ico" data-ico="spark" style="width:22px;height:22px"></span>
                        @endif
                    </span>
                    <div class="grow">
                        <div class="row wrap" style="gap:7px">
                            <span style="font-weight:700;font-size:14.5px">{{ $meta['name'] }}</span>
                            <span class="pill sm {{ $integration->is_active ? 'success' : '' }}">
                                <span class="dot"></span>{{ $integration->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($isDefault)
                            <span class="pill sm accent">Default</span>
                            @endif
                        </div>
                        <div class="faint" style="font-size:12px;margin-top:3px">
                            {{ $hasKey ? 'API key saved' : 'No API key' }}
                            @if($model) · <strong style="color:var(--text)">{{ $model }}</strong>
                            @else · no model set
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.integrations.edit', $integration) }}" class="btn btn-outline btn-sm">
                        <span class="ico" data-ico="gear" style="width:15px;height:15px"></span>Configure
                    </a>
                </div>

                <div style="padding-top:12px;border-top:1px solid var(--border)">
                    @if($isDefault)
                    <span style="color:var(--accent);font-weight:700;font-size:13px">
                        <span class="ico" data-ico="check" style="width:15px;height:15px;vertical-align:-3px"></span>
                        All AI features use this provider
                    </span>
                    @elseif(!$hasKey)
                    <a href="{{ route('admin.integrations.edit', $integration) }}" style="color:var(--warning);font-weight:600;font-size:13px;text-decoration:none">
                        <span class="ico" data-ico="shield" style="width:14px;height:14px;vertical-align:-2px"></span>
                        Add API key to activate
                    </a>
                    @elseif($integration->is_active)
                    <form method="POST" action="{{ route('admin.integrations.set-default', $integration) }}">
                        @csrf
                        <button type="submit" class="link-btn" style="font-size:13px;font-weight:600">
                            Set as default
                        </button>
                    </form>
                    @else
                    <span class="faint" style="font-size:13px;font-weight:600">Activate to set as default</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="bolt" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Where AI is used</h3><div class="sub">Features powered by your default provider</div></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px">
            @foreach([
                ['icon' => 'doc',    'title' => 'Product Description',  'desc' => 'Auto-generate rich product descriptions on the product edit page.'],
                ['icon' => 'edit',   'title' => 'Blog Content',         'desc' => 'Generate blog post content from a title and keywords.'],
                ['icon' => 'search', 'title' => 'SEO Meta',             'desc' => 'Generate SEO meta titles and descriptions for pages & products.'],
                ['icon' => 'globe',  'title' => 'Content Translation',  'desc' => 'Translate product and blog content into any configured language.'],
                ['icon' => 'bell',   'title' => 'UI Translation',       'desc' => 'Bulk-translate all storefront UI strings per language.'],
            ] as $feature)
            <div class="row top" style="gap:12px;padding:14px 15px;border-radius:13px;background:var(--surface-2);border:1px solid var(--border)">
                <span class="tile sm muted">
                    <span class="ico" data-ico="{{ $feature['icon'] }}" style="width:18px;height:18px"></span>
                </span>
                <div>
                    <div style="font-weight:700;font-size:13.5px">{{ $feature['title'] }}</div>
                    <div class="faint" style="font-size:12px;margin-top:2px;line-height:1.45">{{ $feature['desc'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
