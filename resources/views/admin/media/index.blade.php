@extends($isPicker ? 'admin.layouts.picker' : 'admin.layouts.admin')

@if(!$isPicker)
@section('title', 'Media Library')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Media Library</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
    </div>
@endsection
@endif

@section('content')
@php
$uploadAccept = match($accept) {
    'image'    => 'image/*,.svg',
    'video'    => 'video/*',
    'document' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar',
    default    => 'image/*,.svg,video/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.zip',
};
$preSelectedIds = array_filter(array_map('intval', explode(',', request('selected_ids', ''))));
@endphp

{{-- All Alpine state lives in this single root div --}}
<div x-data="mediaLibrary({
        isPicker: {{ $isPicker ? 'true' : 'false' }},
        folderId: '{{ $activeFolder?->id ?? '' }}',
        preSelectedIds: {{ json_encode(array_values($preSelectedIds)) }}
    })">

    {{-- Hidden file input — wired via init() not @change, more reliable for hidden inputs --}}
    <input type="file" multiple
        x-ref="fileInput"
        accept="{{ $uploadAccept }}"
        style="display:none">

    <div class="flex gap-0 rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm"
         style="min-height: 72vh">

        {{-- ============================================================ --}}
        {{-- FOLDER SIDEBAR                                               --}}
        {{-- ============================================================ --}}
        <div class="w-52 shrink-0 border-r border-gray-200 flex flex-col bg-gray-50">
            <div class="flex items-center justify-between px-3 py-2.5 border-b border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Folders</span>
                <button type="button"
                    @click="newFolderModal = true"
                    title="New folder"
                    class="p-1 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>

            <div class="p-4 space-y-0.5 flex-1 overflow-y-auto">
                <a href="{{ route('admin.media.index', request()->only(['picker','accept','multiple'])) }}"
                   class="flex items-center gap-2 px-2 py-1.5 rounded-md text-sm transition-colors
                          {{ !$activeFolder ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    All Files
                </a>

                @foreach($folders as $folder)
                    @include('admin.media._folder-item', ['folder' => $folder, 'depth' => 0])
                @endforeach
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MAIN AREA                                                     --}}
        {{-- ============================================================ --}}
        <div class="flex-1 min-w-0 flex flex-col">

            {{-- Toolbar --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 bg-white">
                {{-- Upload button --}}
                <button type="button"
                    @click="$refs.fileInput.click()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload
                </button>

                {{-- Search + type filter form --}}
                <form method="GET" action="{{ route('admin.media.index') }}" class="flex items-center gap-2 flex-1 min-w-0">
                    @foreach(request()->only(['picker','accept','multiple','folder_id']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <div class="relative flex-1 max-w-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search files…"
                            class="w-full pl-9 pr-3 h-9 rounded-lg border border-gray-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                    </div>
                    <select name="type" onchange="this.form.submit()"
                        class="h-9 rounded-lg border border-gray-300 bg-white text-sm px-3 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                        <option value="" {{ !request('type') ? 'selected' : '' }}>All types</option>
                        <option value="image"    {{ request('type') === 'image'    ? 'selected' : '' }}>Images</option>
                        <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Documents</option>
                        <option value="video"    {{ request('type') === 'video'    ? 'selected' : '' }}>Videos</option>
                    </select>
                </form>

                <span class="text-xs text-gray-400 shrink-0">{{ $media->total() }} file(s)</span>
            </div>

            {{-- Upload progress --}}
            <div x-show="uploads.length > 0" style="display:none"
                 class="px-4 py-2 bg-blue-50 border-b border-blue-100 space-y-1.5">
                <template x-for="u in uploads" :key="u.uuid">
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-700 truncate w-44" x-text="u.name"></span>
                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-200"
                                 :class="u.status === 'error' ? 'bg-red-500' : 'bg-blue-500'"
                                 :style="`width:${u.progress}%`"></div>
                        </div>
                        <span class="text-xs w-14 text-right font-medium"
                              :class="u.status==='done'?'text-green-600':u.status==='error'?'text-red-600':'text-gray-500'"
                              x-text="u.status==='done'?'Done':u.status==='error'?'Error':u.progress+'%'"></span>
                    </div>
                </template>
            </div>

            {{-- Grid / Empty state — full area is a drop zone --}}
            <div class="flex-1 overflow-y-auto p-4"
                 @dragover.prevent="$el.classList.add('bg-blue-50/30')"
                 @dragleave.prevent="$el.classList.remove('bg-blue-50/30')"
                 @drop.prevent="$el.classList.remove('bg-blue-50/30'); uploadFiles(Array.from($event.dataTransfer.files))">

                {{-- #media-grid is ALWAYS in the DOM so prependItem() works even when starting empty --}}
                <div id="media-grid"
                     class="grid gap-3"
                     style="grid-template-columns: repeat(auto-fill, minmax(130px, 1fr))">

                    @if($media->isEmpty())
                        {{-- Empty state sits inside the grid as a full-width placeholder --}}
                        <div id="empty-state"
                             style="grid-column: 1 / -1; min-height: 50vh"
                             class="flex items-center justify-center">
                            <div class="flex flex-col items-center justify-center gap-3 text-gray-400 text-center
                                        rounded-2xl border-2 border-dashed border-gray-300 px-16 py-14
                                        hover:border-blue-400 hover:bg-blue-50/40 transition-colors cursor-pointer"
                                 @click="$refs.fileInput.click()">
                                <svg class="w-14 h-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-gray-500">Drop files here</p>
                                    <p class="text-xs text-gray-400 mt-1">or click to browse &amp; upload</p>
                                </div>
                                <p class="text-xs text-gray-300">Images · Documents · Videos</p>
                            </div>
                        </div>
                    @else
                        @foreach($media as $item)
                        @php
                        $d = json_encode([
                            'id'                => $item->id,
                            'uuid'              => $item->uuid,
                            'type'              => $item->type,
                            'original_filename' => $item->original_filename,
                            'mime_type'         => $item->mime_type,
                            'size'              => $item->size,
                            'human_size'        => $item->humanSize(),
                            'width'             => $item->width,
                            'height'            => $item->height,
                            'alt'               => $item->alt ?? '',
                            'title'             => $item->title ?? '',
                            'caption'           => $item->caption ?? '',
                            'url'               => $item->getUrl(),
                            'thumb_url'         => $item->getUrl('thumb'),
                            'created_at'        => $item->created_at?->format('M d, Y'),
                        ]);
                        @endphp
                        {{-- Outer wrapper: relative + border only (no overflow-hidden so checkmark isn't clipped) --}}
                        <div data-media-id="{{ $item->id }}"
                             class="cursor-pointer group relative rounded-xl border-2 transition-all duration-150 bg-gray-50 hover:shadow-md"
                             :style="pickerSelected.includes({{ $item->id }})
                                ? 'border-color:#3b82f6; box-shadow:0 0 0 3px rgba(147,197,253,0.5)'
                                : (selected?.id === {{ $item->id }} ? 'border-color:#60a5fa' : 'border-color:#d1d5db')"
                             @click="select({{ $d }})">

                            {{-- Inner wrapper clips image corners — overflow-hidden lives here, not on outer --}}
                            <div class="rounded-[10px] overflow-hidden">
                                @if($item->isImage())
                                    <img src="{{ $item->getUrl('thumb') }}" alt="{{ $item->alt }}"
                                         class="w-full h-24 object-cover bg-gray-200"
                                         loading="lazy">
                                @elseif($item->isVideo())
                                    <div class="w-full h-24 bg-gray-800 flex items-center justify-center">
                                        <svg class="w-9 h-9 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-full h-24 bg-gray-100 flex flex-col items-center justify-center text-gray-400 gap-1">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-xs font-semibold uppercase tracking-wide">{{ strtoupper(pathinfo($item->original_filename, PATHINFO_EXTENSION)) ?: 'FILE' }}</span>
                                    </div>
                                @endif

                                <div class="px-2 py-1.5 bg-white border-t border-gray-100">
                                    <p class="text-xs text-gray-700 truncate leading-tight font-medium" title="{{ $item->original_filename }}">{{ $item->original_filename }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->humanSize() }}</p>
                                </div>
                            </div>

                            {{-- Checkmark: inline position/size/color so it's never affected by overflow or Tailwind compilation --}}
                            @if($isPicker)
                            <div x-show="pickerSelected.includes({{ $item->id }})"
                                 class="flex items-center justify-center pointer-events-none"
                                 style="position:absolute;top:8px;left:8px;z-index:999;width:20px;height:20px;background:#3b82f6;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.35);display:none">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @endif

                </div>

                @if($media->isNotEmpty())
                    <div class="mt-5">{{ $media->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- EDIT / DETAIL PANEL (right, only in library mode)            --}}
        {{-- ============================================================ --}}
        @if(!$isPicker)
        <div x-show="selected"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             style="display:none"
             class="w-72 shrink-0 border-l border-gray-200 bg-white flex flex-col overflow-y-auto">

            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 shrink-0">
                <h3 class="text-sm font-semibold text-gray-900">File Details</h3>
                <button type="button" @click="selected = null"
                    class="p-1 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <template x-if="selected">
                <div class="p-4 space-y-4 flex-1">
                    {{-- Preview --}}
                    <div class="rounded-xl overflow-hidden bg-gray-100 border border-gray-200">
                        <template x-if="selected.type === 'image'">
                            <img :src="selected.thumb_url || selected.url" :alt="selected.alt"
                                 class="w-full h-44 object-contain">
                        </template>
                        <template x-if="selected.type === 'video'">
                            <div class="h-44 bg-gray-800 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </template>
                        <template x-if="selected.type === 'document'">
                            <div class="h-44 flex flex-col items-center justify-center text-gray-400 gap-2">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-bold text-gray-500" x-text="(selected.original_filename.split('.').pop() || 'FILE').toUpperCase()"></span>
                            </div>
                        </template>
                    </div>

                    {{-- File info --}}
                    <div class="space-y-0.5 pb-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900 break-all" x-text="selected.original_filename"></p>
                        <p class="text-xs text-gray-500"
                           x-text="selected.human_size + (selected.width ? ' · ' + selected.width + '×' + selected.height + 'px' : '')"></p>
                        <p class="text-xs text-gray-400" x-text="selected.mime_type"></p>
                        <p class="text-xs text-gray-400" x-text="selected.created_at"></p>
                    </div>

                    {{-- SEO fields --}}
                    <div class="space-y-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">SEO Metadata</p>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Alt Text <span class="text-gray-400 font-normal">(describes the image)</span>
                            </label>
                            <input type="text" x-model="editAlt" maxlength="500"
                                placeholder="e.g. Blue running shoes"
                                class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                            <p class="text-xs text-gray-400 mt-0.5 text-right" x-text="editAlt.length + ' / 500'"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" x-model="editTitle" maxlength="500"
                                placeholder="Descriptive title…"
                                class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Caption</label>
                            <textarea x-model="editCaption" rows="3" maxlength="1000"
                                placeholder="Optional caption…"
                                class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 resize-none"></textarea>
                        </div>

                        <button type="button" @click="saveMetadata()"
                            :disabled="saving"
                            class="w-full py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            <span x-text="saving ? 'Saving…' : 'Save Metadata'"></span>
                        </button>

                        <div x-show="saved" style="display:none"
                             x-transition class="text-center text-xs text-green-600 font-semibold py-1">
                            ✓ Saved successfully
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="copyUrl()"
                            class="w-full py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copy URL
                        </button>
                        <a :href="selected.url" target="_blank" rel="noopener"
                            class="w-full py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Open Original
                        </a>
                        <button type="button" @click="deleteItem()"
                            class="w-full py-2 border border-red-200 text-red-600 text-sm rounded-lg hover:bg-red-50 flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete File
                        </button>
                    </div>
                </div>
            </template>
        </div>
        @endif

    </div>{{-- end flex row --}}

    {{-- ============================================================ --}}
    {{-- NEW FOLDER MODAL — inside x-data scope                      --}}
    {{-- ============================================================ --}}
    <div x-show="newFolderModal"
         style="display:none; background: rgba(0,0,0,0.5)"
         @click.self="newFolderModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div @click.stop
             class="bg-white rounded-2xl shadow-2xl p-6 w-80">
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Folder</h3>
            <form method="POST" action="{{ route('admin.media.folders.store') }}">
                @csrf
                @if($activeFolder)
                    <input type="hidden" name="parent_id" value="{{ $activeFolder->id }}">
                @endif
                <input type="text" name="name" required autofocus
                    placeholder="Folder name"
                    class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                <div class="flex justify-end gap-3">
                    <button type="button" @click="newFolderModal = false"
                        class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
function mediaLibrary(config) {
    return {
        selected:       null,
        editAlt:        '',
        editTitle:      '',
        editCaption:    '',
        saving:         false,
        saved:          false,
        uploads:        [],
        newFolderModal: false,
        pickerSelected: config.preSelectedIds || [],

        init() {
            // Wire file input via native JS — Alpine @change on hidden inputs is unreliable
            this.$nextTick(() => {
                const input = this.$refs.fileInput;
                if (!input) return;
                input.addEventListener('change', (e) => {
                    const files = Array.from(e.target.files || []);
                    e.target.value = '';
                    if (files.length > 0) this.uploadFiles(files);
                });
            });
        },

        select(item) {
            if (config.isPicker) {
                const pickerId = new URLSearchParams(window.location.search).get('picker_id') || '';
                const isSelected = this.pickerSelected.includes(item.id);
                if (isSelected) {
                    this.pickerSelected = this.pickerSelected.filter(id => id !== item.id);
                } else {
                    this.pickerSelected = [...this.pickerSelected, item.id];
                }
                window.parent.postMessage({
                    type: 'media-selected',
                    media: item,
                    pickerId,
                    action: isSelected ? 'remove' : 'add',
                }, '*');
                return;
            }
            this.selected    = item;
            this.editAlt     = item.alt     || '';
            this.editTitle   = item.title   || '';
            this.editCaption = item.caption || '';
        },

        saveMetadata() {
            if (!this.selected || this.saving) return;
            this.saving = true;
            this.saved  = false;
            fetch(`/admin/media/${this.selected.id}`, {
                method:  'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    alt:     this.editAlt,
                    title:   this.editTitle,
                    caption: this.editCaption,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Object.assign(this.selected, data.media);
                    this.saved = true;
                    setTimeout(() => { this.saved = false; }, 2500);
                }
            })
            .finally(() => { this.saving = false; });
        },

        deleteItem() {
            if (!this.selected || !confirm('Delete this file permanently?')) return;
            fetch(`/admin/media/${this.selected.id}`, {
                method:  'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-media-id="${this.selected.id}"]`)?.remove();
                    this.selected = null;
                }
            });
        },

        copyUrl() {
            if (!this.selected) return;
            navigator.clipboard.writeText(this.selected.url);
        },

        uploadFiles(files) {
            if (!files || !files.length) return;
            [...files].forEach(f => this.uploadFile(f));
        },

        uploadFile(file) {
            const CHUNK = 2 * 1024 * 1024;
            const uuid  = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
                const r = Math.random() * 16 | 0;
                return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
            });
            const total = Math.max(1, Math.ceil(file.size / CHUNK));
            this.uploads.push({ name: file.name, progress: 0, status: 'uploading', uuid });

            // Use index assignment — Object.assign on a proxy item doesn't trigger Alpine reactivity
            const set = (fields) => {
                const idx = this.uploads.findIndex(u => u.uuid === uuid);
                if (idx !== -1) this.uploads[idx] = { ...this.uploads[idx], ...fields };
            };

            const send = async (i) => {
                const start = i * CHUNK;
                const fd    = new FormData();
                fd.append('chunk',     file.slice(start, start + CHUNK), file.name);
                fd.append('uuid',      uuid);
                fd.append('index',     i);
                fd.append('total',     total);
                fd.append('name',      file.name);
                fd.append('mime',      file.type || 'application/octet-stream');
                fd.append('folder_id', config.folderId || '');
                fd.append('_token',    document.querySelector('meta[name=csrf-token]').content);

                const resp = await fetch('{{ route('admin.media.chunk') }}', { method: 'POST', body: fd });
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                const data = await resp.json();

                set({ progress: Math.round(((i + 1) / total) * 100) });

                if (data.complete) {
                    set({ status: 'done', progress: 100 });
                    this.prependItem(data.media);
                    setTimeout(() => { this.uploads = this.uploads.filter(u => u.uuid !== uuid); }, 3000);
                } else {
                    await send(i + 1);
                }
            };

            send(0).catch((err) => {
                console.error('Upload failed:', err);
                set({ status: 'error' });
            });
        },

        prependItem(media) {
            document.getElementById('empty-state')?.remove();
            const grid = document.getElementById('media-grid');
            if (!grid) return;
            const div = document.createElement('div');
            div.setAttribute('data-media-id', media.id);
            div.className = 'cursor-pointer group relative rounded-xl overflow-hidden border-2 border-gray-200 hover:border-blue-300 transition-all bg-gray-50 hover:shadow-md';
            if (media.type === 'image') {
                div.innerHTML = `<img src="${media.thumb_url||media.url}" class="w-full h-24 object-cover bg-gray-200" loading="lazy">
                    <div class="px-2 py-1.5 bg-white border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 truncate">${media.original_filename}</p>
                        <p class="text-xs text-gray-400 mt-0.5">${media.human_size}</p>
                    </div>`;
            } else {
                div.innerHTML = `<div class="w-full h-24 bg-gray-100 flex items-center justify-center text-3xl">${media.type==='video'?'🎬':'📄'}</div>
                    <div class="px-2 py-1.5 bg-white border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-700 truncate">${media.original_filename}</p>
                        <p class="text-xs text-gray-400 mt-0.5">${media.human_size}</p>
                    </div>`;
            }
            div.addEventListener('click', () => this.select(media));
            grid.prepend(div);
        },
    };
}
</script>
@endpush
@endsection
