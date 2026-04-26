@extends('admin.layouts.admin')

@section('title', 'New Blog Post')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.blogs.index') }}" class="hover:text-gray-700">Blog Posts</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">New Post</li>
</ol>
@endsection

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">New Blog Post</h1>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-container { font-size: 0.875rem; border-bottom-left-radius: 0.375rem; border-bottom-right-radius: 0.375rem; }
    .ql-toolbar { border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem; }
    .ql-editor { min-height: 280px; }
    @media (min-width: 1024px) {
        #blog-grid { grid-template-columns: 70% 1fr; }
    }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.blogs.store') }}" id="blog-form">
    @csrf
    <div class="grid grid-cols-1 gap-6" id="blog-grid">

        {{-- Left: main content --}}
        <div class="space-y-6">

            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Post Content</h3>
                <div class="space-y-4">

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
                        <x-admin.input type="text" name="title" id="blog-title" value="{{ old('title') }}" required />
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                        <x-admin.input type="text" name="slug" id="blog-slug" value="{{ old('slug') }}"
                            placeholder="auto-generated from title" />
                        @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Excerpt</label>
                        <textarea name="excerpt" rows="2"
                            placeholder="Short summary shown in listings (max 500 chars)"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('excerpt') }}</textarea>
                        @error('excerpt')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group x-data="aiBlogGen()">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Content</label>
                            <button type="button" @click="open = !open"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-purple-700 border border-purple-200 rounded-lg px-2.5 py-1 hover:bg-purple-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Generate with AI
                            </button>
                        </div>
                        <div x-show="open" x-transition class="mb-3 border border-purple-200 rounded-xl p-4 bg-purple-50 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tone</label>
                                    <select x-model="tone" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                                        <option value="informative">Informative</option>
                                        <option value="professional">Professional</option>
                                        <option value="conversational">Conversational</option>
                                        <option value="persuasive">Persuasive</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Length</label>
                                    <select x-model="length" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                                        <option value="short">Short (~300 words)</option>
                                        <option value="medium">Medium (~600 words)</option>
                                        <option value="long">Long (~1000 words)</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Keywords <span class="font-normal text-gray-400">(optional, comma-separated)</span></label>
                                <input type="text" x-model="keywords" placeholder="e.g. skincare, summer, tips"
                                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="generate()" :disabled="loading"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-60 transition-colors">
                                    <span x-show="!loading">Generate</span>
                                    <span x-show="loading">Generating…</span>
                                </button>
                                <span x-show="error" x-text="error" class="text-xs text-red-600"></span>
                            </div>
                        </div>
                        <div id="content-editor" class="bg-white"></div>
                        <textarea name="content" id="content-input" class="hidden">{{ old('content') }}</textarea>
                    </x-admin.form-group>

                </div>
            </x-admin.card>

            {{-- FAQ --}}
            <x-admin.card x-data="blogFaq({{ json_encode(old('faqs', [])) }})">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">FAQ</h3>
                    <button type="button" @click="add()"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add question
                    </button>
                </div>
                <div class="space-y-4" x-show="items.length > 0">
                    <template x-for="(item, i) in items" :key="i">
                        <div class="border border-gray-200 rounded-lg p-3 space-y-2 relative">
                            <button type="button" @click="remove(i)"
                                class="absolute top-2 right-2 text-gray-300 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Question</label>
                                <input type="text" :name="`faqs[${i}][question]`" x-model="item.question"
                                    placeholder="Enter question"
                                    class="w-full rounded-md border-gray-300 text-sm h-9 px-3 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Answer</label>
                                <textarea :name="`faqs[${i}][answer]`" x-model="item.answer"
                                    rows="3" placeholder="Enter answer"
                                    class="w-full rounded-md border-gray-300 text-sm px-3 py-2 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                            </div>
                        </div>
                    </template>
                </div>
                <p x-show="items.length === 0" class="text-sm text-gray-400 text-center py-4">
                    No FAQ items yet. Click "Add question" to start.
                </p>
            </x-admin.card>

        </div>

        {{-- Right sidebar --}}
        <div class="space-y-6">

            {{-- Cover Image --}}
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Cover Image</h3>
                <x-admin.media-picker
                    name="cover_media_id"
                    accept="image"
                    label="Upload Cover"
                    :value="old('cover_media_id')" />
                <p class="text-xs text-gray-400 mt-2">Recommended: 1200×630px. Shown in listings and social previews.</p>
            </x-admin.card>

            {{-- SEO --}}
            @include('admin.blogs._seo', ['meta' => null, 'blog' => null])

            {{-- Publish --}}
            <x-admin.card x-data="{ status: '{{ old('status', 'draft') }}' }">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Publish</h3>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <x-admin.select name="status" x-model="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </x-admin.select>
                    </x-admin.form-group>
                    <div x-show="status === 'published'" x-collapse>
                        <x-admin.form-group>
                            <label class="block text-sm font-medium text-gray-700">Published At</label>
                            <input type="datetime-local" name="published_at"
                                value="{{ old('published_at') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm h-9 px-3" />
                            <p class="text-xs text-gray-400 mt-1">Leave blank to use current time.</p>
                        </x-admin.form-group>
                    </div>
                </div>
            </x-admin.card>

            {{-- Post Settings --}}
            <x-admin.card x-data="quickAddBlogCat()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Post Settings</h3>
                </div>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <button type="button" @click="open = !open"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                                x-text="open ? 'Cancel' : '+ New category'"></button>
                        </div>
                        <div x-show="open" x-collapse class="mb-2">
                            <div class="flex gap-2">
                                <input type="text" x-model="name" placeholder="Category name"
                                    @keydown.enter.prevent="save()"
                                    class="flex-1 rounded-md border-gray-300 text-sm h-9 px-3 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                <button type="button" @click="save()" :disabled="saving"
                                    class="px-3 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
                                    x-text="saving ? '...' : 'Add'"></button>
                            </div>
                            <p x-show="error" x-text="error" class="text-red-500 text-xs mt-1"></p>
                        </div>
                        <x-admin.select name="blog_category_id" id="blog-cat-select">
                            <option value="">— No category —</option>
                            @foreach(\App\Models\BlogCategory::orderBy('name')->get() as $cat)
                                <option value="{{ $cat->id }}" {{ old('blog_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Author</label>
                        <x-admin.select name="author_admin_id" id="author-admin-select">
                            <option value="">— Custom name —</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}"
                                    {{ old('author_admin_id', auth('admin')->id()) == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Custom Author Name</label>
                        <x-admin.input type="text" name="author_name"
                            value="{{ old('author_name') }}"
                            placeholder="Override name (leave blank to use selected admin's name)" />
                        <p class="text-xs text-gray-400 mt-1">Only used if no admin is selected above.</p>
                    </x-admin.form-group>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="hidden" name="enable_toc" value="0">
                        <input type="checkbox" name="enable_toc" id="enable-toc" value="1"
                            {{ old('enable_toc') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <label for="enable-toc" class="text-sm font-medium text-gray-700">Show Table of Contents</label>
                    </div>
                </div>
            </x-admin.card>

            <div class="flex space-x-3">
                <x-admin.button type="submit" class="flex-1 justify-center">Publish Post</x-admin.button>
                <x-admin.button href="{{ route('admin.blogs.index') }}" variant="outline">Cancel</x-admin.button>
            </div>

        </div>
    </div>
</form>

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
        open: false,
        loading: false,
        error: '',
        tone: 'informative',
        length: 'medium',
        keywords: '',
        async generate() {
            this.loading = true;
            this.error   = '';
            const title = document.querySelector('[name="title"]')?.value || '';
            if (!title) { this.error = 'Please enter a title first.'; this.loading = false; return; }
            try {
                const res = await fetch('{{ route('admin.ai.blog-content') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
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
