@extends('admin.layouts.admin')
@section('title', 'Create Page')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
#page-content-editor .ql-editor { min-height: 280px; font-size: 0.875rem; }
.page-tab-content { display: none; }
.page-tab-content.active { display: block; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h2 class="display">Create Page</h2>
        <div class="sub">Create a new page with comprehensive meta information</div>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back to Pages
    </a>
</div>

<form id="page-form" action="{{ route('admin.pages.store') }}" method="POST" class="col-gap" style="--gap:18px" novalidate>
    @csrf

    <div class="card pad">
        <div class="card-head" style="margin-bottom:18px">
            <span class="tile sm t-info"><span class="ico" data-ico="file" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Page Information</h3></div>
        </div>
        <div class="col-gap" style="--gap:14px">
            <div class="field">
                <span class="lbl">Title <span style="color:var(--danger)">*</span></span>
                <input class="input" type="text" id="title" name="title" required placeholder="Enter page title" value="{{ old('title') }}">
                @error('title')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <span class="lbl">Slug</span>
                <div class="row" style="gap:8px">
                    <input class="input" type="text" id="slug" name="slug"
                        placeholder="page-url-slug (auto-generated from title)"
                        value="{{ old('slug') }}" readonly
                        style="background:var(--surface-2);flex:1">
                    <button type="button" id="edit-slug-btn" onclick="toggleSlugEdit()" class="icon-btn" style="width:38px;height:38px;flex-shrink:0;border:1px solid var(--border);border-radius:10px">
                        <span class="ico" data-ico="edit" style="width:15px;height:15px"></span>
                    </button>
                </div>
                @error('slug')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                <p class="hint">Auto-generated from title. Click edit to customize.</p>
            </div>
            <div class="field">
                <span class="lbl">Content <span style="color:var(--danger)">*</span></span>
                <div id="page-content-editor" style="background:#fff;border-radius:0 0 8px 8px"></div>
                <input type="hidden" id="content-input" name="content" value="{{ old('content') }}">
                @error('content')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="field" style="margin:0">
                    <span class="lbl">Status <span style="color:var(--danger)">*</span></span>
                    <select class="input" id="status" name="status" required>
                        <option value="">Select status</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    @error('status')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                </div>
                <div class="field" style="margin:0;padding-top:20px">
                    <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
                        <input type="checkbox" name="show_breadcrumb" value="1" {{ old('show_breadcrumb') ? 'checked' : '' }}>
                        Show Breadcrumb
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card pad">
        <div class="card-head" style="margin-bottom:16px">
            <span class="tile sm t-accent"><span class="ico" data-ico="search" style="width:18px;height:18px"></span></span>
            <div class="ct">
                <h3>Meta Information</h3>
                <div class="sub">Optional SEO fields</div>
            </div>
        </div>

        <div style="display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:18px">
            @foreach(['basic-seo' => 'Basic SEO', 'open-graph' => 'Open Graph', 'twitter' => 'Twitter', 'advanced' => 'Advanced'] as $tabKey => $tabLabel)
            <button type="button" id="tab-{{ $tabKey }}" onclick="showPageTab('{{ $tabKey }}')"
                style="padding:8px 14px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:var(--text-muted);margin-bottom:-1px;transition:color .15s,border-color .15s">
                {{ $tabLabel }}
            </button>
            @endforeach
        </div>

        <div id="content-basic-seo" class="page-tab-content active col-gap" style="--gap:14px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="field" style="margin:0">
                    <span class="lbl">Meta Title</span>
                    <input class="input" type="text" id="meta_title" name="meta_title" placeholder="SEO optimized title (50-60 chars)" value="{{ old('meta_title') }}">
                    @error('meta_title')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Focus Keyword</span>
                    <input class="input" type="text" id="focus_keyword" name="focus_keyword" placeholder="main target keyword" value="{{ old('focus_keyword') }}">
                    @error('focus_keyword')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="field">
                <span class="lbl">Meta Description</span>
                <textarea class="input" id="meta_description" name="meta_description" rows="3" style="height:auto;resize:none" placeholder="SEO optimized description (150-160 chars)">{{ old('meta_description') }}</textarea>
                @error('meta_description')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <span class="lbl">Meta Keywords</span>
                <input class="input" type="text" id="meta_keywords" name="meta_keywords" placeholder="keyword1, keyword2, keyword3" value="{{ old('meta_keywords') }}">
                <p class="hint">Separate with commas. Recommended: 3-5 keywords.</p>
            </div>
        </div>

        <div id="content-open-graph" class="page-tab-content col-gap" style="--gap:14px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="field" style="margin:0">
                    <span class="lbl">OG Title</span>
                    <input class="input" type="text" id="og_title" name="og_title" placeholder="Facebook sharing title" value="{{ old('og_title') }}">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">OG Image URL</span>
                    <input class="input" type="url" id="og_image" name="og_image" placeholder="https://example.com/image.jpg" value="{{ old('og_image') }}">
                </div>
            </div>
            <div class="field">
                <span class="lbl">OG Description</span>
                <textarea class="input" id="og_description" name="og_description" rows="3" style="height:auto;resize:none" placeholder="Facebook sharing description">{{ old('og_description') }}</textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="field" style="margin:0">
                    <span class="lbl">OG Type</span>
                    <select class="input" id="og_type" name="og_type">
                        <option value="website" {{ old('og_type') == 'website' ? 'selected' : '' }}>Website</option>
                        <option value="article" {{ old('og_type') == 'article' ? 'selected' : '' }}>Article</option>
                        <option value="product" {{ old('og_type') == 'product' ? 'selected' : '' }}>Product</option>
                    </select>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">OG URL</span>
                    <input class="input" type="url" id="og_url" name="og_url" placeholder="https://example.com/page" value="{{ old('og_url') }}">
                </div>
            </div>
            <div class="field">
                <span class="lbl">Site Name</span>
                <input class="input" type="text" id="og_site_name" name="og_site_name" placeholder="Your Site Name" value="{{ old('og_site_name') }}">
            </div>
        </div>

        <div id="content-twitter" class="page-tab-content col-gap" style="--gap:14px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="field" style="margin:0">
                    <span class="lbl">Card Type</span>
                    <select class="input" id="twitter_card" name="twitter_card">
                        <option value="summary" {{ old('twitter_card') == 'summary' ? 'selected' : '' }}>Summary</option>
                        <option value="summary_large_image" {{ old('twitter_card') == 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                        <option value="app" {{ old('twitter_card') == 'app' ? 'selected' : '' }}>App</option>
                        <option value="player" {{ old('twitter_card') == 'player' ? 'selected' : '' }}>Player</option>
                    </select>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Twitter Image URL</span>
                    <input class="input" type="url" id="twitter_image" name="twitter_image" placeholder="https://example.com/image.jpg" value="{{ old('twitter_image') }}">
                </div>
            </div>
            <div class="field">
                <span class="lbl">Twitter Title</span>
                <input class="input" type="text" id="twitter_title" name="twitter_title" placeholder="Twitter sharing title" value="{{ old('twitter_title') }}">
            </div>
            <div class="field">
                <span class="lbl">Twitter Description</span>
                <textarea class="input" id="twitter_description" name="twitter_description" rows="3" style="height:auto;resize:none" placeholder="Twitter sharing description">{{ old('twitter_description') }}</textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="field" style="margin:0">
                    <span class="lbl">Twitter Site</span>
                    <input class="input" type="text" id="twitter_site" name="twitter_site" placeholder="@yoursite" value="{{ old('twitter_site') }}">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Twitter Creator</span>
                    <input class="input" type="text" id="twitter_creator" name="twitter_creator" placeholder="@creator" value="{{ old('twitter_creator') }}">
                </div>
            </div>
        </div>

        <div id="content-advanced" class="page-tab-content col-gap" style="--gap:14px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="field" style="margin:0">
                    <span class="lbl">Canonical URL</span>
                    <input class="input" type="url" id="canonical_url" name="canonical_url" placeholder="https://example.com/canonical-page" value="{{ old('canonical_url') }}">
                    <p class="hint">Specify to avoid duplicate content issues.</p>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Robots Meta</span>
                    <select class="input" id="robots" name="robots">
                        <option value="index,follow" {{ old('robots') == 'index,follow' ? 'selected' : '' }}>Index, Follow</option>
                        <option value="noindex,follow" {{ old('robots') == 'noindex,follow' ? 'selected' : '' }}>No Index, Follow</option>
                        <option value="index,nofollow" {{ old('robots') == 'index,nofollow' ? 'selected' : '' }}>Index, No Follow</option>
                        <option value="noindex,nofollow" {{ old('robots') == 'noindex,nofollow' ? 'selected' : '' }}>No Index, No Follow</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="gap:12px">
        <button type="submit" onclick="syncPageContent()" class="btn btn-primary">Create Page</button>
        <button type="button" onclick="window.history.back()" class="btn btn-outline">Cancel</button>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
function syncPageContent() {
    var qlEditor = document.querySelector('#page-content-editor .ql-editor');
    var input = document.getElementById('content-input');
    if (qlEditor && input) {
        var html = qlEditor.innerHTML.trim();
        input.value = (html === '<p><br></p>' || html === '') ? '' : html;
    }
}

function showPageTab(tabName) {
    document.querySelectorAll('.page-tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.style.borderBottomColor = 'transparent';
        btn.style.color = 'var(--text-muted)';
    });
    document.getElementById('content-' + tabName).classList.add('active');
    var activeBtn = document.getElementById('tab-' + tabName);
    activeBtn.style.borderBottomColor = 'var(--accent)';
    activeBtn.style.color = 'var(--text)';
}

function toggleSlugEdit() {
    const slugInput = document.getElementById('slug');
    const editBtn = document.getElementById('edit-slug-btn');
    if (slugInput.readOnly) {
        slugInput.readOnly = false;
        slugInput.style.background = '';
        slugInput.focus();
    } else {
        slugInput.readOnly = true;
        slugInput.style.background = 'var(--surface-2)';
    }
}

document.getElementById('title').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput.readOnly) {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-')
            .replace(/-+/g, '-').replace(/^-|-$/g, '');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    showPageTab('basic-seo');
});

var _pageQuill = new Quill('#page-content-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'link'],
            ['clean'],
        ]
    }
});

var _pageContentInitial = document.getElementById('content-input').value;
if (_pageContentInitial) _pageQuill.clipboard.dangerouslyPasteHTML(_pageContentInitial);
</script>
@endpush
