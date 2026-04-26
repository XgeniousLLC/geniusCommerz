@extends('admin.layouts.admin')

@section('title', 'AI Settings')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">AI Settings</li>
    </ol>
@endsection

@section('page-header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">AI Settings</h1>
        <p class="text-gray-600 mt-1">Configure AI providers and models used for content generation and translation</p>
    </div>
@endsection

@section('content')
@php
    $providerMeta = [
        'openai'   => ['name' => 'OpenAI',           'logo' => '/images/ai/openai.svg',   'hint' => 'GPT-4o, GPT-4o-mini, GPT-3.5-turbo'],
        'gemini'   => ['name' => 'Google Gemini',    'logo' => '/images/ai/gemini.svg',   'hint' => 'gemini-1.5-flash, gemini-1.5-pro'],
        'claude'   => ['name' => 'Anthropic Claude', 'logo' => '/images/ai/claude.svg',   'hint' => 'claude-haiku-4-5-20251001, claude-sonnet-4-6'],
        'deepseek' => ['name' => 'DeepSeek',         'logo' => '/images/ai/deepseek.svg', 'hint' => 'deepseek-chat, deepseek-reasoner'],
    ];
@endphp

<div class="space-y-6">

    {{-- Default provider banner --}}
    @php $defaultAi = $integrations->firstWhere('is_default', true); @endphp
    @if($defaultAi)
    <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span>Current default AI provider: <strong>{{ $defaultAi->label }}</strong>
            @php $model = $defaultAi->credentials['model'] ?? null; @endphp
            @if($model) — model: <code class="bg-blue-100 px-1 rounded">{{ $model }}</code> @endif
        </span>
    </div>
    @else
    <div class="flex items-center gap-3 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
        <svg class="w-5 h-5 text-yellow-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>No default AI provider set. Configure and activate a provider, then set it as default to enable AI features.</span>
    </div>
    @endif

    {{-- Provider cards --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-1">Providers</h3>
        <p class="text-sm text-gray-500 mb-5">Add your API key and select a model for each provider. Set one as the default to use it across all AI features.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($integrations as $integration)
            @php $meta = $providerMeta[$integration->provider] ?? ['name' => $integration->label, 'logo' => '🔌', 'hint' => '', 'color' => 'gray']; @endphp
            @php $hasKey = !empty($integration->credentials['api_key']); $model = $integration->credentials['model'] ?? null; @endphp
            <div class="border rounded-xl p-4 {{ $integration->is_default ? 'border-blue-400 bg-blue-50/40' : 'border-gray-200 bg-white' }} hover:shadow-sm transition-shadow">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <img src="{{ $meta['logo'] }}" alt="{{ $meta['name'] }}" class="w-8 h-8 rounded-lg object-contain shrink-0">
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $meta['name'] }}</span>
                                @if($integration->is_active)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                                @endif
                                @if($integration->is_default)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">Default</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">
                                @if($hasKey)
                                    API key saved ·
                                @else
                                    No API key ·
                                @endif
                                @if($model)
                                    <span class="text-gray-600 font-medium">{{ $model }}</span>
                                @else
                                    no model set
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.integrations.edit', $integration) }}"
                       class="shrink-0 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 border border-blue-200 rounded-lg px-2.5 py-1.5 hover:bg-blue-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                        Configure
                    </a>
                </div>

                @if(!$integration->is_default && $integration->is_active)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <form method="POST" action="{{ route('admin.integrations.set-default', $integration) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-gray-500 hover:text-blue-700 font-medium underline underline-offset-2">
                            Set as default
                        </button>
                    </form>
                </div>
                @elseif(!$hasKey)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.integrations.edit', $integration) }}" class="text-xs text-yellow-600 hover:text-yellow-800 font-medium">
                        ⚠ Add API key to activate
                    </a>
                </div>
                @elseif($integration->is_default)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-blue-600 font-medium">✓ All AI features use this provider</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </x-admin.card>

    {{-- Usage guide --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Where AI is used</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach([
                [
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                    'title' => 'Product Description',
                    'desc'  => 'Auto-generate rich product descriptions on the product edit page.',
                ],
                [
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>',
                    'title' => 'Blog Content',
                    'desc'  => 'Generate blog post content from a title and keywords.',
                ],
                [
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
                    'title' => 'SEO Meta',
                    'desc'  => 'Generate SEO meta titles and descriptions for pages/products.',
                ],
                [
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>',
                    'title' => 'Content Translation',
                    'desc'  => 'Translate product and blog content into any configured language.',
                ],
                [
                    'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l1.9-5.7a8.5 8.5 0 113.8 3.8L3 21"/>',
                    'title' => 'UI Translation',
                    'desc'  => 'Bulk-translate all storefront UI strings per language.',
                ],
            ] as $feature)
            <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 border border-gray-100">
                <div class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-gray-200">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $feature['svg'] !!}</svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-800">{{ $feature['title'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $feature['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </x-admin.card>

</div>
@endsection
