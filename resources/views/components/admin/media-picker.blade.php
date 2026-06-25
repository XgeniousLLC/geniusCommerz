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

    <template x-if="!multiple">
        <input type="hidden" :name="name" :value="selected?.id ?? ''">
    </template>
    <template x-if="multiple">
        <template x-for="item in items" :key="item.id">
            <input type="hidden" :name="name + '[]'" :value="item.id">
        </template>
    </template>

    {{-- Single image --}}
    <template x-if="!multiple">
        <div style="display:inline-flex;flex-direction:column;gap:8px">
            <template x-if="selected">
                <div style="position:relative;width:80px;height:80px;border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border)">
                    <template x-if="selected.type === 'image'">
                        <img :src="selected.thumb_url || selected.url" :alt="selected.alt || ''"
                             style="width:100%;height:100%;object-fit:cover">
                    </template>
                    <template x-if="selected.type !== 'image'">
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:28px"
                             x-text="selected.type === 'video' ? '🎬' : '📄'"></div>
                    </template>
                    <button type="button" @click.stop="selected = null"
                        style="position:absolute;top:4px;right:4px;width:18px;height:18px;background:var(--danger);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;padding:0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="open()" class="btn btn-outline btn-sm">
                <span class="ico" data-ico="image" style="width:16px;height:16px"></span>
                <span x-text="selected ? 'Change Image' : label"></span>
            </button>
        </div>
    </template>

    {{-- Multi image --}}
    <template x-if="multiple">
        <div>
            <template x-if="items.length > 0">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,80px);gap:8px;margin-bottom:8px">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div
                            draggable="true"
                            @dragstart="dragStart(index)"
                            @dragover.prevent="dragOver(index)"
                            @dragend="dragEnd()"
                            :style="`position:relative;width:80px;height:80px;border-radius:var(--radius-sm);overflow:hidden;border:1px solid var(--border);cursor:grab;opacity:${dragIndex === index ? 0.4 : 1};transition:opacity .15s`">
                            <template x-if="item.type === 'image'">
                                <img :src="item.thumb_url || item.url" :alt="item.alt || ''"
                                     style="width:100%;height:100%;object-fit:cover;pointer-events:none">
                            </template>
                            <template x-if="item.type !== 'image'">
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:24px"
                                     x-text="item.type === 'video' ? '🎬' : '📄'"></div>
                            </template>
                            {{-- Featured badge on first image --}}
                            <template x-if="index === 0">
                                <span style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.55);color:#fff;font-size:9px;letter-spacing:.06em;text-align:center;padding:2px 0;line-height:1.4;pointer-events:none">FEATURED</span>
                            </template>
                            {{-- Drag handle dots --}}
                            <span style="position:absolute;top:4px;left:4px;display:grid;grid-template-columns:repeat(2,4px);gap:2px;pointer-events:none;opacity:.7">
                                <span style="width:3px;height:3px;border-radius:50%;background:#fff"></span>
                                <span style="width:3px;height:3px;border-radius:50%;background:#fff"></span>
                                <span style="width:3px;height:3px;border-radius:50%;background:#fff"></span>
                                <span style="width:3px;height:3px;border-radius:50%;background:#fff"></span>
                                <span style="width:3px;height:3px;border-radius:50%;background:#fff"></span>
                                <span style="width:3px;height:3px;border-radius:50%;background:#fff"></span>
                            </span>
                            <button type="button" @click.stop="removeItem(item.id)"
                                style="position:absolute;top:4px;right:4px;width:18px;height:18px;background:var(--danger);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;padding:0">
                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6L6 18M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </template>
            <button type="button" @click="open()" class="btn btn-outline btn-sm">
                <span class="ico" data-ico="image" style="width:16px;height:16px"></span>
                <span x-text="label"></span>
            </button>
        </div>
    </template>

    {{-- Modal --}}
    <style>.mp-overlay{display:flex;background:rgba(0,0,0,.6);position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;padding:16px}[x-cloak]{display:none!important}</style>
    <template x-teleport="body">
        <div class="mp-overlay" x-show="isOpen" x-cloak
             @keydown.escape.window="close()">
            <div @click.stop
                 style="background:var(--surface);border-radius:var(--radius-card);box-shadow:var(--shadow-lg);display:flex;flex-direction:column;overflow:hidden;width:100%;max-width:1050px;height:82vh;max-height:820px">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--border);flex-shrink:0">
                    <span style="font-weight:700;font-size:14px" x-text="label"></span>
                    <button type="button" @click="close()" class="icon-btn">
                        <span class="ico" data-ico="x" style="width:18px;height:18px"></span>
                    </button>
                </div>
                <iframe :src="iframeUrl" style="flex:1;width:100%;border:0" title="Media Picker"></iframe>
            </div>
        </div>
    </template>

</div>
