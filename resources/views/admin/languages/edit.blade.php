@extends('admin.layouts.admin')

@section('title', 'Edit Language – ' . $language->name)

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.languages.index') }}" class="hover:text-gray-700">Languages</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">{{ $language->name }}</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $language->name }} <span class="text-gray-400 font-mono text-base">({{ $language->code }})</span></h1>
            <p class="text-gray-600 mt-1">Manage translations for this language</p>
        </div>
        <button type="button" id="aiTranslateBtn"
            onclick="translateWithAi()"
            class="inline-flex items-center gap-2 bg-purple-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span id="aiTranslateBtnLabel">Translate All with AI</span>
        </button>
    </div>
@endsection

@section('content')
<div id="aiMsg" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

<form method="POST" action="{{ route('admin.languages.update', $language) }}">
    @csrf @method('PUT')

    {{-- Settings card --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Language Settings</h2>
        <div class="flex flex-wrap gap-6 items-center">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Display Name</label>
                <input type="text" name="name" value="{{ old('name', $language->name) }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div class="flex items-center gap-6 mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $language->is_active ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" {{ $language->is_default ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">Set as Default</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Translations card --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">String Translations</h2>
            <span class="text-xs text-gray-400">{{ count($strings) }} strings</span>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($strings as $key => $default)
            <div class="px-5 py-3 grid grid-cols-2 gap-4 items-center">
                <div>
                    <p class="text-xs font-mono text-gray-400 mb-0.5">{{ $key }}</p>
                    <p class="text-sm text-gray-700">{{ $default }}</p>
                </div>
                <div>
                    <input type="text"
                        id="str_{{ $key }}"
                        name="strings[{{ $key }}]"
                        value="{{ old('strings.' . $key, $translations[$key] ?? '') }}"
                        placeholder="{{ $default }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
            class="bg-blue-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Save Translations
        </button>
        <a href="{{ route('admin.languages.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</form>

<script>
async function translateWithAi() {
    const btn   = document.getElementById('aiTranslateBtn');
    const label = document.getElementById('aiTranslateBtnLabel');
    const msg   = document.getElementById('aiMsg');

    btn.disabled = true;
    label.textContent = 'Translating...';
    msg.className = 'hidden mb-4 px-4 py-3 rounded-lg text-sm';

    try {
        const res  = await fetch('{{ route('admin.languages.translate-ai', $language) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.error) {
            msg.textContent = data.error;
            msg.className   = 'mb-4 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
        } else {
            // Populate fields from the saved translations via a fresh fetch
            const keys = @json(array_keys($strings));
            // Reload page to show updated translations
            msg.textContent = `Successfully translated ${data.count} strings. Reloading...`;
            msg.className   = 'mb-4 px-4 py-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-800';
            setTimeout(() => window.location.reload(), 1200);
        }
    } catch (e) {
        msg.textContent = 'Request failed: ' + e.message;
        msg.className   = 'mb-4 px-4 py-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
    } finally {
        btn.disabled = false;
        label.textContent = 'Translate All with AI';
    }
}
</script>
@endsection
