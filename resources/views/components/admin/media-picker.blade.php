@props([
    'name',
    'accept'   => 'image',
    'multiple' => false,
    'label'    => 'Add Image',
    'value'    => null,
])

@php
$ids = $value
    ? (is_array($value) ? array_filter($value) : array_filter([$value]))
    : [];
$ids = array_values(array_map('intval', $ids));

// Resolve pre-selected media server-side so the initial state is synchronous
$preItems = $ids
    ? \App\Models\Media::whereIn('id', $ids)->get()->map(fn($m) => [
        'id'                => $m->id,
        'type'              => $m->type,
        'original_filename' => $m->original_filename,
        'mime_type'         => $m->mime_type,
        'size'              => $m->size,
        'human_size'        => $m->humanSize(),
        'width'             => $m->width,
        'height'            => $m->height,
        'alt'               => $m->alt ?? '',
        'title'             => $m->title ?? '',
        'caption'           => $m->caption ?? '',
        'url'               => $m->getUrl(),
        'thumb_url'         => $m->getUrl('thumb'),
    ])->keyBy('id')->only($ids)->values()->all()
    : [];
@endphp

<div x-data="mediaPicker({
        name:     '{{ $name }}',
        accept:   '{{ $accept }}',
        multiple: {{ $multiple ? 'true' : 'false' }},
        preItems: {{ json_encode($preItems) }},
        label:    '{{ $label }}',
    })">

    {{-- Hidden input (single mode) --}}
    <template x-if="!multiple">
        <input type="hidden" :name="name" :value="selected?.id ?? ''">
    </template>

    {{-- Hidden inputs (multiple mode) --}}
    <template x-if="multiple">
        <template x-for="item in items" :key="item.id">
            <input type="hidden" :name="name + '[]'" :value="item.id">
        </template>
    </template>

    {{-- Single-image UI --}}
    <template x-if="!multiple">
        <div class="inline-flex flex-col gap-2">

            {{-- Thumbnail --}}
            <template x-if="selected">
                <div class="relative group w-20 h-20 rounded-xl overflow-hidden border border-gray-200 bg-gray-100 shadow-sm">
                    <template x-if="selected.type === 'image'">
                        <img :src="selected.thumb_url || selected.url" :alt="selected.alt || ''"
                             class="w-full h-full object-cover">
                    </template>
                    <template x-if="selected.type !== 'image'">
                        <div class="w-full h-full flex items-center justify-center text-3xl"
                             x-text="selected.type === 'video' ? '🎬' : '📄'"></div>
                    </template>
                    <button type="button" @click.stop="selected = null"
                        class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full
                               opacity-0 group-hover:opacity-100 transition-opacity
                               flex items-center justify-center text-xs font-bold leading-none shadow">
                        ×
                    </button>
                </div>
            </template>

            {{-- Button --}}
            <button type="button" @click="open()"
                class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg
                       text-sm text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm w-fit">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-text="selected ? 'Change Image' : label"></span>
            </button>

        </div>
    </template>

    {{-- Multi-image UI --}}
    <template x-if="multiple">
        <div>
            <template x-if="items.length > 0">
            <div style="display:grid; grid-template-columns:repeat(auto-fill,80px); gap:8px" class="mb-2">
                <template x-for="item in items" :key="item.id">
                    <div class="relative group rounded-lg overflow-hidden border border-gray-200 bg-gray-100"
                         style="width:80px; height:80px">
                        <template x-if="item.type === 'image'">
                            <img :src="item.thumb_url || item.url" :alt="item.alt || ''"
                                 class="w-full h-full object-cover">
                        </template>
                        <template x-if="item.type !== 'image'">
                            <div class="w-full h-full flex items-center justify-center text-2xl"
                                 x-text="item.type === 'video' ? '🎬' : '📄'"></div>
                        </template>
                        <button type="button" @click.stop="removeItem(item.id)"
                            title="Remove"
                            style="position:absolute;top:4px;right:4px;width:18px;height:18px;background:#ef4444;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;padding:0;box-shadow:0 1px 3px rgba(0,0,0,.3)">
                            <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
            </template>
            <button type="button" @click="open()"
                class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg
                       text-sm text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-text="label"></span>
            </button>
        </div>
    </template>

    {{-- Modal — teleported to <body> to escape sidebar stacking context --}}
    <template x-teleport="body">
        <div x-show="isOpen"
             style="display:none; background: rgba(0,0,0,0.6)"
             class="fixed inset-0 z-[99999] flex items-center justify-center p-4"
             @keydown.escape.window="close()">
            <div @click.stop
                 class="bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden w-full"
                 style="max-width: 1050px; height: 82vh; max-height: 820px;">

                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 shrink-0">
                    <h3 class="font-semibold text-gray-900 text-sm" x-text="label"></h3>
                    <button type="button" @click="close()"
                        class="text-gray-400 hover:text-gray-700 p-1 rounded transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <iframe :src="iframeUrl" class="flex-1 w-full border-0" title="Media Picker"></iframe>
            </div>
        </div>
    </template>

</div>
