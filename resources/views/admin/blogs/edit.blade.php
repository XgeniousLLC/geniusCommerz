@extends('admin.layouts.admin')
@section('title', 'Edit Blog Post')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
.ql-container { font-size: 0.875rem; }
.ql-editor { min-height: 280px; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h2 class="display">Edit Post</h2>
    </div>
    <div class="row" style="gap:8px">
        @if($blog->status === 'published')
        <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="btn btn-outline">
            <span class="ico" data-ico="external" style="width:15px;height:15px"></span>View
        </a>
        @endif
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline">
            <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.blogs.update', $blog) }}" id="blog-form">
    @csrf @method('PUT')
    <div style="display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start">

        {{-- Left --}}
        <div class="col-gap" style="--gap:18px">

            <div class="card pad">
                <h3 style="font-weight:700;font-size:14px;margin-bottom:16px">Post Content</h3>
                <div class="col-gap" style="--gap:14px">

                    <div class="field">
                        <span class="lbl">Title <span style="color:var(--danger)">*</span></span>
                        <input class="input" type="text" name="title" id="blog-title" value="{{ old('title', $blog->title) }}" required>
                        @error('title')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                    </div>

                    <div class="field" x-data="{ editing: {{ $errors->has('slug') ? 'true' : 'false' }}, slug: '{{ old('slug', $blog->slug) }}' }">
                        <span class="lbl">Slug</span>
                        <div x-show="!editing" class="row" style="gap:8px;height:40px;padding:0 12px;border:1px solid var(--border);border-radius:10px;background:var(--surface-2)">
                            <span style="flex:1;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" x-text="slug"></span>
                            <button type="button" @click="editing=true" class="icon-btn" style="width:24px;height:24px">
                                <span class="ico" data-ico="edit" style="width:13px;height:13px"></span>
                            </button>
                        </div>
                        <div x-show="editing" class="row" style="gap:8px">
                            <input class="input" type="text" name="slug" id="blog-slug"
                                x-model="slug"
                                x-ref="slugInput"
                                x-init="$watch('editing', v => v && $nextTick(() => $refs.slugInput.focus()))"
                                style="flex:1">
                            <button type="button" @click="editing=false" class="icon-btn" style="width:38px;height:38px;border:1px solid var(--border);border-radius:10px">
                                <span class="ico" data-ico="check" style="width:14px;height:14px;color:var(--success)"></span>
                            </button>
                        </div>
                        @error('slug')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <span class="lbl">Excerpt</span>
                        <textarea class="input" name="excerpt" rows="2" style="height:auto;resize:none" placeholder="Short summary shown in listings (max 500 chars)">{{ old('excerpt', $blog->excerpt) }}</textarea>
                        @error('excerpt')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                    </div>

                    <div class="field" x-data="aiBlogGen()">
                        <div class="between" style="margin-bottom:6px">
                            <span class="lbl" style="margin:0">Content</span>
                            <button type="button" @click="open = !open"
                                class="btn btn-sm" style="font-size:12px;color:#7c3aed;border-color:#7c3aed">
                                <span class="ico" data-ico="bolt" style="width:13px;height:13px"></span>Generate with AI
                            </button>
                        </div>
                        <div x-show="open" class="card pad" style="margin-bottom:10px;background:color-mix(in srgb,#7c3aed 6%,transparent);border-color:color-mix(in srgb,#7c3aed 25%,transparent)">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
                                <div class="field" style="margin:0">
                                    <span class="lbl" style="font-size:11px">Tone</span>
                                    <select x-model="tone" class="input" style="height:34px">
                                        <option value="informative">Informative</option>
                                        <option value="professional">Professional</option>
                                        <option value="conversational">Conversational</option>
                                        <option value="persuasive">Persuasive</option>
                                    </select>
                                </div>
                                <div class="field" style="margin:0">
                                    <span class="lbl" style="font-size:11px">Length</span>
                                    <select x-model="length" class="input" style="height:34px">
                                        <option value="short">Short (~300 words)</option>
                                        <option value="medium">Medium (~600 words)</option>
                                        <option value="long">Long (~1000 words)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="field" style="margin-bottom:10px">
                                <span class="lbl" style="font-size:11px">Keywords <span class="faint">(optional)</span></span>
                                <input type="text" x-model="keywords" placeholder="e.g. skincare, summer, tips" class="input" style="height:34px">
                            </div>
                            <div class="row" style="gap:10px">
                                <button type="button" @click="generate()" :disabled="loading"
                                    class="btn btn-primary" style="background:#7c3aed;border-color:#7c3aed;font-size:13px">
                                    <span x-show="!loading">Generate</span>
                                    <span x-show="loading">Generating…</span>
                                </button>
                                <span x-show="error" x-text="error" style="font-size:12px;color:var(--danger)"></span>
                            </div>
                        </div>
                        <div id="content-editor" style="background:#fff"></div>
                        <textarea name="content" id="content-input" style="display:none">{{ old('content', $blog->content) }}</textarea>
                    </div>

                </div>
            </div>

            {{-- FAQ --}}
            @php $faqInit = old('faqs', $blog->faqs ?? []); @endphp
            <div class="card pad" x-data="blogFaq({{ json_encode($faqInit) }})">
                <div class="between" style="margin-bottom:14px">
                    <h3 style="font-weight:700;font-size:14px">FAQ</h3>
                    <button type="button" @click="add()" class="btn btn-sm btn-outline" style="font-size:12px">
                        <span class="ico" data-ico="plus" style="width:13px;height:13px"></span>Add question
                    </button>
                </div>
                <div class="col-gap" style="--gap:12px" x-show="items.length > 0">
                    <template x-for="(item, i) in items" :key="i">
                        <div style="border:1px solid var(--border);border-radius:10px;padding:12px;position:relative" class="col-gap" style="--gap:10px">
                            <button type="button" @click="remove(i)"
                                class="icon-btn" style="position:absolute;top:8px;right:8px;width:26px;height:26px">
                                <span class="ico" data-ico="x" style="width:13px;height:13px"></span>
                            </button>
                            <div class="field" style="margin:0">
                                <span class="lbl" style="font-size:11px">Question</span>
                                <input class="input" style="height:34px" type="text" :name="`faqs[${i}][question]`" x-model="item.question" placeholder="Enter question">
                            </div>
                            <div class="field" style="margin:0">
                                <span class="lbl" style="font-size:11px">Answer</span>
                                <textarea class="input" style="height:auto;resize:none" :name="`faqs[${i}][answer]`" x-model="item.answer" rows="3" placeholder="Enter answer"></textarea>
                            </div>
                        </div>
                    </template>
                </div>
                <p x-show="items.length === 0" class="faint" style="font-size:13px;text-align:center;padding:20px 0">
                    No FAQ items yet. Click "Add question" to start.
                </p>
            </div>

        </div>

        {{-- Right sidebar --}}
        <div class="col-gap" style="--gap:18px">

            <div class="card pad">
                <h3 style="font-weight:700;font-size:14px;margin-bottom:14px">Cover Image</h3>
                <x-admin.media-picker name="cover_media_id" accept="image" label="Upload Cover" :value="old('cover_media_id', $blog->cover_media_id)" />
                <p class="faint" style="font-size:12px;margin-top:8px">Recommended: 1200×630px.</p>
            </div>

            @include('admin.blogs._seo', ['meta' => $meta, 'blog' => $blog])

            <div class="card pad" x-data="{ status: '{{ old('status', $blog->status) }}' }">
                <h3 style="font-weight:700;font-size:14px;margin-bottom:14px">Publish</h3>
                <div class="col-gap" style="--gap:12px">
                    <div class="field">
                        <span class="lbl">Status</span>
                        <select class="input" name="status" x-model="status">
                            <option value="draft"     {{ old('status', $blog->status) === 'draft'     ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $blog->status) === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    <div x-show="status === 'published'" class="field">
                        <span class="lbl">Published At</span>
                        <input class="input" type="datetime-local" name="published_at"
                            value="{{ old('published_at', $blog->published_at?->format('Y-m-d\TH:i')) }}">
                        <p class="hint">Leave blank to keep existing date.</p>
                    </div>
                </div>
            </div>

            <div class="card pad" x-data="quickAddBlogCat()">
                <div class="between" style="margin-bottom:14px">
                    <h3 style="font-weight:700;font-size:14px">Post Settings</h3>
                </div>
                <div class="col-gap" style="--gap:12px">
                    <div class="field">
                        <div class="between" style="margin-bottom:4px">
                            <span class="lbl" style="margin:0">Category</span>
                            <button type="button" @click="open = !open" class="link-btn" style="font-size:12px"
                                x-text="open ? 'Cancel' : '+ New category'"></button>
                        </div>
                        <div x-show="open" class="row" style="gap:8px;margin-bottom:8px">
                            <input class="input" type="text" x-model="name" placeholder="Category name"
                                @keydown.enter.prevent="save()" style="flex:1;height:34px">
                            <button type="button" @click="save()" :disabled="saving"
                                class="btn btn-primary btn-sm" x-text="saving ? '...' : 'Add'"></button>
                        </div>
                        <p x-show="error" x-text="error" style="font-size:12px;color:var(--danger)"></p>
                        <select class="input" name="blog_category_id" id="blog-cat-select">
                            <option value="">— No category —</option>
                            @foreach(\App\Models\BlogCategory::orderBy('name')->get() as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('blog_category_id', $blog->blog_category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <span class="lbl">Author</span>
                        <select class="input" name="author_admin_id" id="author-admin-select">
                            <option value="">— Custom name —</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}"
                                    {{ old('author_admin_id', $blog->author_admin_id) == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <span class="lbl">Custom Author Name</span>
                        <input class="input" type="text" name="author_name"
                            value="{{ old('author_name', $blog->author_name) }}"
                            placeholder="Override name">
                        <p class="hint">Only used if no admin is selected above.</p>
                    </div>

                    <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
                        <input type="hidden" name="enable_toc" value="0">
                        <input type="checkbox" name="enable_toc" id="enable-toc" value="1"
                            {{ old('enable_toc', $blog->enable_toc) ? 'checked' : '' }}>
                        Show Table of Contents
                    </label>
                </div>
            </div>

            <div class="row" style="gap:10px">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Update Post</button>
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline">Cancel</a>
            </div>

        </div>
    </div>
</form>

@php $activeLanguages = \App\Models\Language::where('is_active', true)->get(); @endphp
@if($activeLanguages->isNotEmpty())
<div style="margin-top:20px" x-data="blogContentTranslator('blog', {{ $blog->id }})">
    <div class="card pad">
        <div class="between" style="margin-bottom:16px">
            <h3 style="font-weight:700;font-size:14px">Content Translations</h3>
            <span class="faint" style="font-size:12px">Translate blog content per language</span>
        </div>

        <div class="row" style="gap:6px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);flex-wrap:wrap">
            @foreach($activeLanguages as $lang)
            <button type="button"
                @click="activeTab = '{{ $lang->code }}'"
                :class="activeTab === '{{ $lang->code }}' ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline'"
                class="btn btn-sm">
                {{ $lang->name }}
            </button>
            @endforeach
        </div>

        @foreach($activeLanguages as $lang)
        <div x-show="activeTab === '{{ $lang->code }}'" class="col-gap" style="--gap:12px">
            <div class="field">
                <span class="lbl">Title</span>
                <input class="input" type="text" x-model="tabs['{{ $lang->code }}'].title" placeholder="{{ $blog->title }}">
            </div>
            <div class="field">
                <span class="lbl">Excerpt</span>
                <textarea class="input" x-model="tabs['{{ $lang->code }}'].excerpt" rows="2" style="height:auto;resize:none" placeholder="{{ $blog->excerpt }}"></textarea>
            </div>
            <div class="field">
                <span class="lbl">Content</span>
                <textarea class="input mono" x-model="tabs['{{ $lang->code }}'].content" rows="8" style="height:auto;resize:none;font-size:12px" placeholder="Translated HTML content..."></textarea>
            </div>
            <div class="row" style="gap:10px;align-items:center">
                <button type="button" @click="aiTranslate('{{ $lang->code }}', {{ $lang->id }})"
                    :disabled="loading === '{{ $lang->code }}'"
                    class="btn btn-sm" style="color:#7c3aed;border-color:#7c3aed">
                    <span class="ico" data-ico="bolt" style="width:13px;height:13px"></span>
                    <span x-text="loading === '{{ $lang->code }}' ? 'Translating...' : 'AI Translate'"></span>
                </button>
                <button type="button" @click="saveTranslation('{{ $lang->code }}', {{ $lang->id }})"
                    :disabled="saving === '{{ $lang->code }}'"
                    class="btn btn-sm btn-primary">
                    <span x-text="saving === '{{ $lang->code }}' ? 'Saving...' : 'Save'"></span>
                </button>
                <span x-show="saved === '{{ $lang->code }}'" style="font-size:12px;color:var(--success)">Saved</span>
            </div>
            <p x-show="errors['{{ $lang->code }}']" x-text="errors['{{ $lang->code }}']" style="font-size:12px;color:var(--danger)"></p>
        </div>
        @endforeach
    </div>
</div>

@php
$ctBlogTabs = $activeLanguages->mapWithKeys(function($lang) use ($blog) {
    $existing = $blog->contentTranslations()->where('language_id', $lang->id)->first();
    $fields = $existing?->fields ?? [];
    return [$lang->code => [
        'title'   => $fields['title'] ?? '',
        'excerpt' => $fields['excerpt'] ?? '',
        'content' => $fields['content'] ?? '',
    ]];
})->all();
@endphp
<script>
function blogContentTranslator(type, id) {
    return {
        activeTab: '{{ $activeLanguages->first()?->code }}',
        loading: null, saving: null, saved: null, errors: {},
        tabs: @json($ctBlogTabs),

        async aiTranslate(code, langId) {
            this.loading = code; this.errors[code] = null;
            const original = {
                title:   document.querySelector('[name="title"]')?.value || '',
                excerpt: document.querySelector('[name="excerpt"]')?.value || '',
                content: document.getElementById('content-input')?.value || '',
            };
            try {
                const res  = await fetch('{{ route('admin.ai.translate-content') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ language_id: langId, translatable_type: 'blog', translatable_id: id, fields: original }),
                });
                const data = await res.json();
                if (data.error) { this.errors[code] = data.error; return; }
                this.tabs[code] = { ...this.tabs[code], ...data.fields };
                this.saved = code;
                setTimeout(() => { if (this.saved === code) this.saved = null; }, 3000);
            } catch (e) { this.errors[code] = e.message; }
            finally { this.loading = null; }
        },

        async saveTranslation(code, langId) {
            this.saving = code; this.errors[code] = null;
            try {
                const res  = await fetch('{{ route('admin.ai.save-content-translation') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ language_id: langId, translatable_type: 'blog', translatable_id: id, fields: this.tabs[code] }),
                });
                const data = await res.json();
                if (data.error) { this.errors[code] = data.error; return; }
                this.saved = code;
                setTimeout(() => { if (this.saved === code) this.saved = null; }, 3000);
            } catch (e) { this.errors[code] = e.message; }
            finally { this.saving = null; }
        },
    };
}
</script>
@endif

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const _csrf = document.querySelector('meta[name="csrf-token"]').content;

function blogFaq(initial) {
    return {
        items: Array.isArray(initial) && initial.length ? initial : [],
        add() { this.items.push({ question: '', answer: '' }); },
        remove(i) { this.items.splice(i, 1); }
    };
}

function quickAddBlogCat() {
    return {
        open: false, name: '', saving: false, error: '',
        save() {
            if (!this.name.trim()) return;
            this.saving = true; this.error = '';
            fetch('{{ route("admin.blog-categories.quick-create") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ name: this.name.trim() })
            })
            .then(r => r.json().then(d => r.ok ? d : Promise.reject(d)))
            .then(cat => {
                const sel = document.getElementById('blog-cat-select');
                const opt = new Option(cat.name, cat.id, true, true);
                sel.add(opt);
                this.name = ''; this.open = false;
            })
            .catch(d => { this.error = d.errors?.name?.[0] ?? d.message ?? 'Error'; })
            .finally(() => { this.saving = false; });
        }
    };
}

const contentQuill = new Quill('#content-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'link', 'image'],
            ['clean'],
        ]
    }
});

const contentInitial = document.getElementById('content-input').value;
if (contentInitial) contentQuill.clipboard.dangerouslyPasteHTML(contentInitial);

document.getElementById('blog-form').addEventListener('formdata', function (e) {
    e.formData.set('content', contentQuill.root.innerHTML === '<p><br></p>' ? '' : contentQuill.root.innerHTML);
});

contentQuill.on('text-change', function () {
    document.getElementById('content-input').value = contentQuill.root.innerHTML;
});

function aiBlogGen() {
    return {
        open: false, loading: false, error: '',
        tone: 'informative', length: 'medium', keywords: '',
        async generate() {
            this.loading = true; this.error = '';
            const title = document.querySelector('[name="title"]')?.value || '';
            if (!title) { this.error = 'Please enter a title first.'; this.loading = false; return; }
            try {
                const res = await fetch('{{ route('admin.ai.blog-content') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
                    body: JSON.stringify({ title, tone: this.tone, length: this.length, keywords: this.keywords }),
                });
                const json = await res.json();
                if (json.error) { this.error = json.error; return; }
                contentQuill.setText('');
                contentQuill.clipboard.dangerouslyPasteHTML('<p>' + json.text.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>') + '</p>');
                this.open = false;
            } catch (e) {
                this.error = 'Network error.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush

@endsection
