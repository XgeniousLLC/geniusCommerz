@extends('admin.layouts.admin')
@section('title', 'New Blog Post')

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
        <h2 class="display">New Blog Post</h2>
    </div>
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Blog Posts
    </a>
</div>

<form method="POST" action="{{ route('admin.blogs.store') }}" id="blog-form">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start">

        {{-- Left: main content --}}
        <div class="col-gap" style="--gap:18px">

            <div class="card pad">
                <h3 style="font-weight:700;font-size:14px;margin-bottom:16px">Post Content</h3>
                <div class="col-gap" style="--gap:14px">

                    <div class="field">
                        <span class="lbl">Title <span style="color:var(--danger)">*</span></span>
                        <input class="input" type="text" name="title" id="blog-title" value="{{ old('title') }}" required>
                        @error('title')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <span class="lbl">Slug</span>
                        <input class="input" type="text" name="slug" id="blog-slug" value="{{ old('slug') }}" placeholder="auto-generated from title">
                        @error('slug')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <span class="lbl">Excerpt</span>
                        <textarea class="input" name="excerpt" rows="2" style="height:auto;resize:none" placeholder="Short summary shown in listings (max 500 chars)">{{ old('excerpt') }}</textarea>
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
                                <span class="lbl" style="font-size:11px">Keywords <span class="faint">(optional, comma-separated)</span></span>
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
                        <textarea name="content" id="content-input" style="display:none">{{ old('content') }}</textarea>
                    </div>

                </div>
            </div>

            {{-- FAQ --}}
            <div class="card pad" x-data="blogFaq({{ json_encode(old('faqs', [])) }})">
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
                <x-admin.media-picker name="cover_media_id" accept="image" label="Upload Cover" :value="old('cover_media_id')" />
                <p class="faint" style="font-size:12px;margin-top:8px">Recommended: 1200×630px.</p>
            </div>

            @include('admin.blogs._seo', ['meta' => null, 'blog' => null])

            <div class="card pad" x-data="{ status: '{{ old('status', 'draft') }}' }">
                <h3 style="font-weight:700;font-size:14px;margin-bottom:14px">Publish</h3>
                <div class="col-gap" style="--gap:12px">
                    <div class="field">
                        <span class="lbl">Status</span>
                        <select class="input" name="status" x-model="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div x-show="status === 'published'" class="field">
                        <span class="lbl">Published At</span>
                        <input class="input" type="datetime-local" name="published_at" value="{{ old('published_at') }}">
                        <p class="hint">Leave blank to use current time.</p>
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
                                <option value="{{ $cat->id }}" {{ old('blog_category_id') == $cat->id ? 'selected' : '' }}>
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
                                    {{ old('author_admin_id', auth('admin')->id()) == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <span class="lbl">Custom Author Name</span>
                        <input class="input" type="text" name="author_name" value="{{ old('author_name') }}" placeholder="Override name">
                        <p class="hint">Only used if no admin is selected above.</p>
                    </div>

                    <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
                        <input type="hidden" name="enable_toc" value="0">
                        <input type="checkbox" name="enable_toc" id="enable-toc" value="1" {{ old('enable_toc') ? 'checked' : '' }}>
                        Show Table of Contents
                    </label>
                </div>
            </div>

            <div class="row" style="gap:10px">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Publish Post</button>
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline">Cancel</a>
            </div>

        </div>
    </div>
</form>
@endsection

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
    if (window.blogSeoInstance) window.blogSeoInstance.recalculate();
});

document.getElementById('blog-title').addEventListener('input', function () {
    const slug = this.value.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-');
    const slugField = document.getElementById('blog-slug');
    if (!slugField._userEdited) slugField.value = slug;
});

document.getElementById('blog-slug').addEventListener('input', function () {
    this._userEdited = this.value !== '';
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
