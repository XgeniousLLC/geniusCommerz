{{-- Blog SEO panel. Expects: $meta (MetaInformation|null), $blog (Blog|null) --}}
@php
    $metaTitle  = old('meta_title',       $meta?->meta_title       ?? '');
    $metaDesc   = old('meta_description', $meta?->meta_description ?? '');
    $focusKw    = old('focus_keyword',    $meta?->focus_keyword    ?? '');
    $metaKw     = old('meta_keywords',    $meta?->meta_keywords    ?? '');
    $canonical  = old('canonical_url',    $meta?->canonical_url    ?? '');
    $robots     = old('robots',           $meta?->robots           ?? 'index,follow');
    $savedScore = $meta?->seo_score ?? 0;
@endphp

<div class="card pad" x-data="blogSeo()" x-init="init()">

    {{-- Header / toggle --}}
    <button type="button" @click="open = !open"
        style="width:100%;background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;padding:0">
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-weight:700;font-size:14px">SEO</span>
            @if($savedScore > 0)
                @php
                    $scoreClass = match(true) {
                        $savedScore >= 90 => 'green',
                        $savedScore >= 70 => 'blue',
                        $savedScore >= 50 => 'yellow',
                        $savedScore >= 30 => 'orange',
                        default           => 'red',
                    };
                @endphp
                <span class="pill sm {{ $scoreClass }}">{{ $savedScore }}/100</span>
            @endif
        </div>
        <span class="ico" data-ico="chevronDown"
              style="width:16px;height:16px;color:var(--text-muted);transition:transform .2s"
              :style="open ? 'transform:rotate(180deg)' : ''"></span>
    </button>

    <div x-show="open" x-collapse style="margin-top:16px">

        {{-- Score ring + checks --}}
        <div style="background:var(--surface-2);border-radius:10px;padding:14px;margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px">
                <div style="position:relative;width:52px;height:52px;flex-shrink:0">
                    <svg style="width:52px;height:52px;transform:rotate(-90deg)" viewBox="0 0 36 36">
                        <path stroke="var(--border)" stroke-width="3" fill="transparent"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path stroke="currentColor" :class="scoreColor" stroke-width="3" fill="transparent"
                              :stroke-dasharray="score + ', 100'"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
                        <span style="font-size:12px;font-weight:700" x-text="score"></span>
                    </div>
                </div>
                <div>
                    <div style="font-weight:700;font-size:13px" x-text="grade.charAt(0).toUpperCase() + grade.slice(1)"></div>
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">Live SEO score</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:5px">
                <template x-for="(chk, key) in checks" :key="key">
                    <div style="display:flex;align-items:flex-start;gap:7px;font-size:12px">
                        <span :style="chk.status==='good' ? 'color:var(--success)' : chk.status==='warning' ? 'color:var(--warning)' : 'color:var(--danger)'"
                              style="font-size:14px;line-height:1.2;flex-shrink:0"
                              x-text="chk.status==='good' ? '✓' : '⚠'"></span>
                        <span style="color:var(--text-muted)" x-text="chk.message"></span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Meta Title --}}
        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="field" style="margin:0">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span class="lbl">Meta Title</span>
                    <span style="font-size:11px" :class="titleCountColor" x-text="titleLen + ' / 60'"></span>
                </div>
                <input type="text" name="meta_title" id="seo-meta-title" class="input"
                    x-model="metaTitle" @input="recalculate()"
                    value="{{ $metaTitle }}"
                    placeholder="Defaults to post title if blank">
                <div style="height:3px;border-radius:99px;background:var(--border);overflow:hidden;margin-top:4px">
                    <div style="height:100%;border-radius:99px;transition:width .2s"
                         :style="'width:' + Math.min((titleLen/60)*100,100) + '%;background:' + (titleLen>=50&&titleLen<=60 ? 'var(--success)' : titleLen>0 ? 'var(--warning)' : 'var(--border)')"></div>
                </div>
            </div>

            {{-- Meta Description --}}
            <div class="field" style="margin:0">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span class="lbl">Meta Description</span>
                    <span style="font-size:11px" :class="descCountColor" x-text="descLen + ' / 160'"></span>
                </div>
                <textarea name="meta_description" id="seo-meta-description" class="input" rows="3"
                    x-model="metaDesc" @input="recalculate()"
                    placeholder="150–160 characters for best results">{{ $metaDesc }}</textarea>
                <div style="height:3px;border-radius:99px;background:var(--border);overflow:hidden;margin-top:4px">
                    <div style="height:100%;border-radius:99px;transition:width .2s"
                         :style="'width:' + Math.min((descLen/160)*100,100) + '%;background:' + (descLen>=150&&descLen<=160 ? 'var(--success)' : descLen>0 ? 'var(--warning)' : 'var(--border)')"></div>
                </div>
            </div>

            {{-- Focus Keyword + Meta Keywords --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field" style="margin:0">
                    <span class="lbl">Focus Keyword</span>
                    <input type="text" name="focus_keyword" class="input"
                        value="{{ $focusKw }}" placeholder="primary target keyword"
                        @input="recalculate()">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Meta Keywords</span>
                    <input type="text" name="meta_keywords" class="input"
                        value="{{ $metaKw }}" placeholder="kw1, kw2, kw3">
                </div>
            </div>

            {{-- Canonical + Robots --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="field" style="margin:0">
                    <span class="lbl">Canonical URL</span>
                    <input type="text" name="canonical_url" class="input"
                        value="{{ $canonical }}" placeholder="Leave blank for default">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Robots</span>
                    <select name="robots" class="input">
                        @foreach(['index,follow','noindex,nofollow','noindex,follow','index,nofollow'] as $opt)
                            <option value="{{ $opt }}" {{ $robots === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Suggestions --}}
            <template x-if="suggestions.length > 0">
                <div style="background:color-mix(in srgb,var(--info) 8%,transparent);border:1px solid color-mix(in srgb,var(--info) 20%,transparent);border-radius:8px;padding:12px">
                    <div style="font-size:11.5px;font-weight:700;color:var(--info);margin-bottom:6px">Suggestions</div>
                    <ul style="display:flex;flex-direction:column;gap:4px;list-style:none;margin:0;padding:0">
                        <template x-for="s in suggestions" :key="s">
                            <li style="font-size:12px;color:var(--text-muted);display:flex;gap:6px">
                                <span style="color:var(--info)">·</span><span x-text="s"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>

    </div>
</div>

@once
@push('scripts')
<script>
function blogSeo() {
    return {
        open: {{ $savedScore > 0 ? 'true' : 'false' }},
        metaTitle: @json($metaTitle),
        metaDesc:  @json($metaDesc),
        score: {{ $savedScore }},
        grade: 'poor',
        checks: {},
        suggestions: [],

        get titleLen() { return this.metaTitle.length; },
        get descLen()  { return this.metaDesc.length; },

        get titleCountColor() {
            if (this.titleLen === 0)                        return '';
            if (this.titleLen >= 50 && this.titleLen <= 60) return 'color:var(--success)';
            if (this.titleLen < 70)                         return 'color:var(--warning)';
            return 'color:var(--danger)';
        },
        get descCountColor() {
            if (this.descLen === 0)                           return '';
            if (this.descLen >= 150 && this.descLen <= 160)   return 'color:var(--success)';
            if (this.descLen < 320)                           return 'color:var(--warning)';
            return 'color:var(--danger)';
        },
        get scoreColor() {
            if (this.score >= 90) return 'text-green-500';
            if (this.score >= 70) return 'text-blue-500';
            if (this.score >= 50) return 'text-yellow-500';
            if (this.score >= 30) return 'text-orange-500';
            return 'text-red-500';
        },

        init() { this.recalculate(); },

        recalculate() {
            const title   = this.metaTitle.trim();
            const desc    = this.metaDesc.trim();
            const content = document.getElementById('content-input')?.value ?? '';
            const focus   = document.querySelector('[name="focus_keyword"]')?.value?.trim() ?? '';

            let score = 0;
            const checks = {};
            const suggestions = [];

            if (title.length >= 50 && title.length <= 60) {
                checks.title = { status: 'good', message: 'Title length is good (' + title.length + ' chars)' };
                score += 20;
            } else if (title.length > 0) {
                checks.title = { status: 'warning', message: 'Title should be 50–60 chars (currently ' + title.length + ')' };
                suggestions.push('Adjust title to 50–60 characters');
                score += 10;
            } else {
                checks.title = { status: 'error', message: 'Meta title is missing' };
                suggestions.push('Add a meta title');
            }

            if (desc.length >= 150 && desc.length <= 160) {
                checks.desc = { status: 'good', message: 'Description length is good (' + desc.length + ' chars)' };
                score += 20;
            } else if (desc.length > 0) {
                checks.desc = { status: 'warning', message: 'Description should be 150–160 chars (currently ' + desc.length + ')' };
                suggestions.push('Adjust description to 150–160 characters');
                score += 10;
            } else {
                checks.desc = { status: 'error', message: 'Meta description is missing' };
                suggestions.push('Add a meta description');
            }

            const words = content.replace(/<[^>]*>/g, '').split(/\s+/).filter(w => w).length;
            if (words >= 300) {
                checks.content = { status: 'good', message: 'Content length is good (' + words + ' words)' };
                score += 20;
            } else if (words > 0) {
                checks.content = { status: 'warning', message: 'Content should have 300+ words (' + words + ' currently)' };
                suggestions.push('Add more content (target 300+ words)');
                score += 10;
            } else {
                checks.content = { status: 'error', message: 'No content added yet' };
            }

            if (focus) {
                const inTitle = title.toLowerCase().includes(focus.toLowerCase());
                const inDesc  = desc.toLowerCase().includes(focus.toLowerCase());
                if (inTitle && inDesc) {
                    checks.keyword = { status: 'good', message: 'Focus keyword in title & description' };
                    score += 20;
                } else {
                    checks.keyword = { status: 'warning', message: 'Focus keyword not in ' + (!inTitle ? 'title' : 'description') };
                    suggestions.push('Include focus keyword in ' + (!inTitle ? 'title' : 'description'));
                    score += 10;
                }
            } else {
                checks.keyword = { status: 'warning', message: 'No focus keyword set' };
                suggestions.push('Set a focus keyword');
            }

            const hasImage = document.querySelector('[name="cover_image"]')?.value?.trim();
            if (hasImage) {
                checks.image = { status: 'good', message: 'Cover image is set' };
                score += 20;
            } else {
                checks.image = { status: 'warning', message: 'No cover image set' };
                suggestions.push('Add a cover image');
            }

            this.score       = Math.min(100, score);
            this.checks      = checks;
            this.suggestions = suggestions;
            this.grade       = score >= 90 ? 'excellent' : score >= 70 ? 'good' : score >= 50 ? 'average' : score >= 30 ? 'poor' : 'critical';
        }
    };
}
</script>
@endpush
@endonce
