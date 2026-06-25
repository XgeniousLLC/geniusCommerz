@extends($isPicker ? 'admin.layouts.picker' : 'admin.layouts.admin')

@if(!$isPicker)
@section('title', 'Media Library')
@endif

@section('content')
@php
$uploadAccept = match($accept) {
    'image'    => '.jpg,.jpeg,.png,.webp,.avif,.gif,.svg,.bmp,.tiff,.tif,.ico,.heic,.heif',
    'video'    => '.mp4,.mov,.avi,.mkv,.webm,.m4v,.wmv,.flv,.3gp,.ogv',
    'document' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar',
    default    => '.jpg,.jpeg,.png,.webp,.avif,.gif,.svg,.mp4,.mov,.avi,.mkv,.webm,.m4v,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.zip',
};
$preSelectedIds = array_filter(array_map('intval', explode(',', request('selected_ids', ''))));
@endphp

@if(!$isPicker)
<div class="page-head">
    <div>
        <h2 class="display">Media Library</h2>
    </div>
</div>
@endif

<div x-data="mediaLibrary({
    isPicker: {{ $isPicker ? 'true' : 'false' }},
    folderId: '{{ $activeFolder?->id ?? '' }}',
    preSelectedIds: {{ json_encode(array_values($preSelectedIds)) }}
})">

    <input type="file" multiple x-ref="fileInput" accept="{{ $uploadAccept }}" style="display:none">

    <div style="display:flex;gap:0;border-radius:12px;border:1px solid var(--border);background:var(--surface);overflow:hidden;min-height:72vh;box-shadow:var(--shadow-sm,0 1px 3px rgba(0,0,0,.06))">

        {{-- Folder sidebar --}}
        <div style="width:200px;flex-shrink:0;border-right:1px solid var(--border);display:flex;flex-direction:column;background:var(--surface-2)">
            <div class="between" style="padding:8px 12px;border-bottom:1px solid var(--border)">
                <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em">Folders</span>
                <button type="button" @click="newFolderModal = true" class="icon-btn" style="width:24px;height:24px" title="New folder">
                    <span class="ico" data-ico="plus" style="width:13px;height:13px"></span>
                </button>
            </div>

            <div style="padding:8px;flex:1;overflow-y:auto" class="col-gap" style="--gap:2px">
                <a href="{{ route('admin.media.index', request()->only(['picker','accept','multiple'])) }}"
                   style="display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:6px;font-size:13px;text-decoration:none;transition:background .12s;{{ !$activeFolder ? 'background:color-mix(in srgb,var(--accent) 10%,transparent);color:var(--accent);font-weight:600' : 'color:var(--text-muted)' }}">
                    <span class="ico" data-ico="list" style="width:14px;height:14px;flex-shrink:0"></span>
                    All Files
                </a>
                @foreach($folders as $folder)
                    @include('admin.media._folder-item', ['folder' => $folder, 'depth' => 0])
                @endforeach
            </div>
        </div>

        {{-- Main area --}}
        <div style="flex:1;min-width:0;display:flex;flex-direction:column">

            {{-- Toolbar --}}
            <div class="row" style="gap:10px;padding:10px 14px;border-bottom:1px solid var(--border);background:var(--surface)">
                <button type="button" @click="$refs.fileInput.click()" class="btn btn-primary" style="flex-shrink:0">
                    <span class="ico" data-ico="upload" style="width:15px;height:15px"></span>Upload
                </button>

                <form method="GET" action="{{ route('admin.media.index') }}" class="row" style="gap:8px;flex:1;min-width:0">
                    @foreach(request()->only(['picker','accept','multiple','folder_id']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <div class="search-field" style="flex:1;max-width:300px">
                        <span class="ico" data-ico="search" style="width:14px;height:14px"></span>
                        <input type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search files…"
                            class="input" style="height:36px">
                    </div>
                    <select name="type" onchange="this.form.submit()" class="input" style="height:36px;width:auto;flex-shrink:0;min-width:110px">
                        <option value="" {{ !request('type') ? 'selected' : '' }}>All types</option>
                        <option value="image"    {{ request('type') === 'image'    ? 'selected' : '' }}>Images</option>
                        <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Documents</option>
                        <option value="video"    {{ request('type') === 'video'    ? 'selected' : '' }}>Videos</option>
                    </select>
                </form>

                <span class="faint" style="font-size:12.5px;flex-shrink:0">{{ $media->total() }} file(s)</span>
            </div>

            {{-- Upload progress --}}
            <div x-show="uploads.length > 0" style="display:none;padding:8px 14px;background:color-mix(in srgb,var(--info) 6%,transparent);border-bottom:1px solid color-mix(in srgb,var(--info) 15%,transparent)" class="col-gap" style="--gap:6px">
                <template x-for="u in uploads" :key="u.uuid">
                    <div class="row" style="gap:10px">
                        <span style="font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;width:160px" x-text="u.name"></span>
                        <div style="flex:1;height:4px;background:var(--border);border-radius:2px;overflow:hidden">
                            <div style="height:100%;border-radius:2px;transition:width .2s"
                                 :style="`width:${u.progress}%;background:${u.status === 'error' ? 'var(--danger)' : 'var(--accent)'}`"></div>
                        </div>
                        <span style="font-size:11.5px;width:50px;text-align:right;font-weight:600"
                              :style="`color:${u.status==='done'?'var(--success)':u.status==='error'?'var(--danger)':'var(--text-muted)'}`"
                              x-text="u.status==='done'?'Done':u.status==='error'?'Error':u.progress+'%'"></span>
                    </div>
                </template>
            </div>

            {{-- Grid + drop zone --}}
            <div style="flex:1;overflow-y:auto;padding:14px"
                 @dragover.prevent="$el.style.background='color-mix(in srgb,var(--accent) 4%,transparent)'"
                 @dragleave.prevent="$el.style.background=''"
                 @drop.prevent="$el.style.background=''; uploadFiles(Array.from($event.dataTransfer.files))">

                <div id="media-grid" style="display:grid;gap:10px;grid-template-columns:repeat(auto-fill,minmax(130px,1fr))">

                    @if($media->isEmpty())
                        <div id="empty-state" style="grid-column:1/-1;min-height:50vh;display:flex;align-items:center;justify-content:center">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:12px;border:2px dashed var(--border);border-radius:16px;padding:60px 40px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s"
                                 @click="$refs.fileInput.click()"
                                 @mouseenter="$el.style.borderColor='var(--accent)';$el.style.background='color-mix(in srgb,var(--accent) 4%,transparent)'"
                                 @mouseleave="$el.style.borderColor='var(--border)';$el.style.background=''">
                                <span class="ico" data-ico="upload" style="width:48px;height:48px;color:var(--border)"></span>
                                <div>
                                    <div style="font-size:14px;font-weight:600;color:var(--text-muted)">Drop files here</div>
                                    <div class="faint" style="font-size:12px;margin-top:4px">or click to browse &amp; upload</div>
                                </div>
                                <div class="faint" style="font-size:11px">Images · Documents · Videos</div>
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
                        <div data-media-id="{{ $item->id }}"
                             style="cursor:pointer;position:relative;border-radius:10px;border:2px solid;transition:all .15s;background:var(--surface-2)"
                             :style="pickerSelected.includes({{ $item->id }})
                                ? 'border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 25%,transparent)'
                                : (selected?.id === {{ $item->id }} ? 'border-color:var(--accent)' : 'border-color:var(--border)')"
                             @click="select({{ $d }})">

                            <div style="border-radius:8px;overflow:hidden">
                                @if($item->isImage())
                                    <img src="{{ $item->getUrl('thumb') }}" alt="{{ $item->alt }}"
                                         style="width:100%;height:96px;object-fit:cover;background:var(--border);display:block"
                                         loading="lazy">
                                @elseif($item->isVideo())
                                    <div style="width:100%;height:96px;background:#1f2937;display:flex;align-items:center;justify-content:center">
                                        <span class="ico" data-ico="play" style="width:36px;height:36px;color:rgba(255,255,255,.8)"></span>
                                    </div>
                                @else
                                    <div style="width:100%;height:96px;background:var(--surface-2);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:var(--text-muted)">
                                        <span class="ico" data-ico="file" style="width:32px;height:32px"></span>
                                        <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em">{{ strtoupper(pathinfo($item->original_filename, PATHINFO_EXTENSION)) ?: 'FILE' }}</span>
                                    </div>
                                @endif
                                <div style="padding:6px 8px;background:var(--surface);border-top:1px solid var(--border)">
                                    <p style="font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $item->original_filename }}">{{ $item->original_filename }}</p>
                                    <p class="faint" style="font-size:11px;margin-top:2px">{{ $item->humanSize() }}</p>
                                </div>
                            </div>

                            @if($isPicker)
                            <div x-show="pickerSelected.includes({{ $item->id }})"
                                 style="position:absolute;top:8px;left:8px;z-index:999;width:20px;height:20px;background:var(--accent);border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.35);display:none;align-items:center;justify-content:center;pointer-events:none">
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
                    <div style="margin-top:16px">{{ $media->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Detail panel (library mode only) --}}
        @if(!$isPicker)
        <div x-show="selected"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             style="display:none;width:260px;flex-shrink:0;border-left:1px solid var(--border);background:var(--surface);display:flex;flex-direction:column;overflow-y:auto">

            <div class="between" style="padding:10px 14px;border-bottom:1px solid var(--border);flex-shrink:0">
                <h3 style="font-weight:700;font-size:13.5px">File Details</h3>
                <button type="button" @click="selected = null" class="icon-btn" style="width:26px;height:26px">
                    <span class="ico" data-ico="x" style="width:13px;height:13px"></span>
                </button>
            </div>

            <template x-if="selected">
                <div style="padding:14px" class="col-gap" style="--gap:14px">
                    {{-- Preview --}}
                    <div style="border-radius:10px;overflow:hidden;background:var(--surface-2);border:1px solid var(--border)">
                        <template x-if="selected.type === 'image'">
                            <img :src="selected.thumb_url || selected.url" :alt="selected.alt"
                                 style="width:100%;height:160px;object-fit:contain;display:block">
                        </template>
                        <template x-if="selected.type === 'video'">
                            <div style="height:160px;background:#1f2937;display:flex;align-items:center;justify-content:center">
                                <span class="ico" data-ico="play" style="width:48px;height:48px;color:#fff"></span>
                            </div>
                        </template>
                        <template x-if="selected.type === 'document'">
                            <div style="height:160px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:var(--text-muted)">
                                <span class="ico" data-ico="file" style="width:48px;height:48px"></span>
                                <span style="font-size:14px;font-weight:700;text-transform:uppercase" x-text="(selected.original_filename.split('.').pop() || 'FILE').toUpperCase()"></span>
                            </div>
                        </template>
                    </div>

                    {{-- File info --}}
                    <div style="padding-bottom:12px;border-bottom:1px solid var(--border)" class="col-gap" style="--gap:2px">
                        <p style="font-size:13px;font-weight:600;word-break:break-all" x-text="selected.original_filename"></p>
                        <p class="faint" style="font-size:12px" x-text="selected.human_size + (selected.width ? ' · ' + selected.width + '×' + selected.height + 'px' : '')"></p>
                        <p class="faint" style="font-size:11.5px" x-text="selected.mime_type"></p>
                        <p class="faint" style="font-size:11.5px" x-text="selected.created_at"></p>
                    </div>

                    {{-- SEO fields --}}
                    <div class="col-gap" style="--gap:10px">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted)">SEO Metadata</div>
                        <div class="field" style="margin:0">
                            <span class="lbl" style="font-size:11px">Alt Text <span class="faint">(describes the image)</span></span>
                            <input type="text" x-model="editAlt" maxlength="500" placeholder="e.g. Blue running shoes" class="input" style="height:34px">
                            <p class="hint tnum" style="text-align:right" x-text="editAlt.length + ' / 500'"></p>
                        </div>
                        <div class="field" style="margin:0">
                            <span class="lbl" style="font-size:11px">Title</span>
                            <input type="text" x-model="editTitle" maxlength="500" placeholder="Descriptive title…" class="input" style="height:34px">
                        </div>
                        <div class="field" style="margin:0">
                            <span class="lbl" style="font-size:11px">Caption</span>
                            <textarea x-model="editCaption" rows="3" maxlength="1000" placeholder="Optional caption…" class="input" style="height:auto;resize:none;font-size:13px"></textarea>
                        </div>
                        <button type="button" @click="saveMetadata()" :disabled="saving" class="btn btn-primary" style="width:100%;justify-content:center">
                            <span x-text="saving ? 'Saving…' : 'Save Metadata'"></span>
                        </button>
                        <div x-show="saved" style="display:none;text-align:center;font-size:12px;font-weight:600;color:var(--success);padding:4px 0">
                            Saved successfully
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="col-gap" style="--gap:6px;padding-top:10px;border-top:1px solid var(--border)">
                        <button type="button" @click="copyUrl()" class="btn btn-outline" style="width:100%;justify-content:center">
                            <span class="ico" data-ico="copy" style="width:14px;height:14px"></span>Copy URL
                        </button>
                        <a :href="selected.url" target="_blank" rel="noopener"
                            class="btn btn-outline" style="width:100%;justify-content:center;text-decoration:none">
                            <span class="ico" data-ico="external" style="width:14px;height:14px"></span>Open Original
                        </a>
                        <button type="button" @click="deleteItem()"
                            class="btn btn-outline" style="width:100%;justify-content:center;color:var(--danger);border-color:var(--danger)">
                            <span class="ico" data-ico="trash" style="width:14px;height:14px"></span>Delete File
                        </button>
                    </div>
                </div>
            </template>
        </div>
        @endif

    </div>

    {{-- New Folder Modal --}}
    <div x-show="newFolderModal"
         style="display:none;background:rgba(0,0,0,.5);position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center"
         @click.self="newFolderModal = false">
        <div @click.stop class="card pad" style="width:320px">
            <h3 style="font-weight:700;font-size:15px;margin-bottom:16px">New Folder</h3>
            <form method="POST" action="{{ route('admin.media.folders.store') }}" class="col-gap" style="--gap:12px">
                @csrf
                @if($activeFolder)
                    <input type="hidden" name="parent_id" value="{{ $activeFolder->id }}">
                @endif
                <input type="text" name="name" required autofocus placeholder="Folder name" class="input">
                <div class="row" style="gap:8px;justify-content:flex-end">
                    <button type="button" @click="newFolderModal = false" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>

</div>

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
            div.style.cssText = 'cursor:pointer;position:relative;border-radius:10px;border:2px solid var(--border);background:var(--surface-2)';
            if (media.type === 'image') {
                div.innerHTML = `
                    <div style="border-radius:8px;overflow:hidden">
                        <img src="${media.thumb_url||media.url}" style="width:100%;height:96px;object-fit:cover;background:var(--border);display:block" loading="lazy">
                        <div style="padding:6px 8px;background:var(--surface);border-top:1px solid var(--border)">
                            <p style="font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${media.original_filename}</p>
                            <p style="font-size:11px;color:var(--text-muted);margin-top:2px">${media.human_size}</p>
                        </div>
                    </div>`;
            } else {
                const icon = media.type === 'video' ? '▶' : '📄';
                div.innerHTML = `
                    <div style="border-radius:8px;overflow:hidden">
                        <div style="width:100%;height:96px;background:var(--surface-2);display:flex;align-items:center;justify-content:center;font-size:32px">${icon}</div>
                        <div style="padding:6px 8px;background:var(--surface);border-top:1px solid var(--border)">
                            <p style="font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${media.original_filename}</p>
                            <p style="font-size:11px;color:var(--text-muted);margin-top:2px">${media.human_size}</p>
                        </div>
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
