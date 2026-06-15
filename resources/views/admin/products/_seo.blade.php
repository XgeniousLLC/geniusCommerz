@php
    $metaTitle = old('meta_title',       $meta?->meta_title       ?? '');
    $metaDesc  = old('meta_description', $meta?->meta_description ?? '');
    $focusKw   = old('focus_keyword',    $meta?->focus_keyword    ?? '');
    $metaKw    = old('meta_keywords',    $meta?->meta_keywords    ?? '');
    $canonical = old('canonical_url',    $meta?->canonical_url    ?? '');
    $robots    = old('robots',           $meta?->robots           ?? 'index,follow');
    $savedScore = $meta?->seo_score ?? 0;
@endphp

<div class="card pad" x-data="productSeo()" x-init="init()">
    <button type="button" @click="open = !open" class="between" style="width:100%;background:none;border:none;cursor:pointer;padding:0">
        <div class="row" style="gap:10px">
            <span class="tile sm t-success"><span class="ico" data-ico="globe" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>SEO</h3></div>
            @if($savedScore > 0)
                @php $pill = $savedScore >= 90 ? 'success' : ($savedScore >= 70 ? 'info' : ($savedScore >= 50 ? 'warning' : 'danger')); @endphp
                <span class="pill sm {{ $pill }}">{{ $savedScore }}/100</span>
            @endif
        </div>
        <span class="ico" data-ico="chevDown" style="width:18px;height:18px;color:var(--text-faint);transition:transform .2s"
              :style="open ? 'transform:rotate(180deg)' : ''"></span>
    </button>

    <div x-show="open" x-collapse style="margin-top:18px">

        <div style="padding:14px;background:var(--surface-2);border-radius:var(--radius-card);border:1px solid var(--border);margin-bottom:16px">
            <div class="row" style="gap:14px;margin-bottom:12px">
                <div style="position:relative;width:56px;height:56px;flex-shrink:0">
                    <svg style="width:56px;height:56px;transform:rotate(-90deg)" viewBox="0 0 36 36">
                        <path stroke="var(--border-strong)" stroke-width="3" fill="transparent"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path :stroke="scoreColorHex" stroke-width="3" fill="transparent"
                              :stroke-dasharray="score + ', 100'"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
                        <span style="font-size:13px;font-weight:700" x-text="score"></span>
                    </div>
                </div>
                <div>
                    <div style="font-weight:700;font-size:14px" x-text="grade.charAt(0).toUpperCase() + grade.slice(1)"></div>
                    <div class="faint" style="font-size:12px">Live SEO score</div>
                </div>
            </div>
            <div class="stack" style="gap:6px">
                <template x-for="(chk, key) in checks" :key="key">
                    <div class="row" style="gap:6px;align-items:flex-start">
                        <span class="ico" style="width:14px;height:14px;flex-shrink:0;margin-top:1px"
                              :data-ico="chk.status === 'good' ? 'check' : 'x'"
                              :style="chk.status === 'good' ? 'color:var(--success)' : (chk.status === 'warning' ? 'color:var(--warning)' : 'color:var(--danger)')"></span>
                        <span style="font-size:12.5px" x-text="chk.message"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="field" style="margin-bottom:14px">
            <div class="between" style="margin-bottom:4px">
                <span class="lbl">Meta title</span>
                <span style="font-size:12px;font-weight:700"
                      :style="titleLen > 60 ? 'color:var(--danger)' : (titleLen >= 50 ? 'color:var(--success)' : 'color:var(--text-faint)')"
                      x-text="titleLen + ' / 60'"></span>
            </div>
            <input class="input" type="text" name="meta_title" id="seo-meta-title"
                x-model="metaTitle" @input="recalculate()"
                value="{{ $metaTitle }}" placeholder="Defaults to product name if blank">
            <div style="height:4px;background:var(--surface-3);border-radius:99px;margin-top:6px;overflow:hidden">
                <div style="height:100%;border-radius:99px;transition:width .2s"
                     :style="'width:' + Math.min((titleLen/60)*100,100) + '%;background:' + (titleLen > 60 ? 'var(--danger)' : titleLen >= 50 ? 'var(--success)' : 'var(--warning)')"></div>
            </div>
        </div>

        <div class="field" style="margin-bottom:14px">
            <div class="between" style="margin-bottom:4px">
                <span class="lbl">Meta description</span>
                <span style="font-size:12px;font-weight:700"
                      :style="descLen > 160 ? 'color:var(--danger)' : (descLen >= 150 ? 'color:var(--success)' : 'color:var(--text-faint)')"
                      x-text="descLen + ' / 160'"></span>
            </div>
            <textarea class="textarea" name="meta_description" id="seo-meta-description" rows="3"
                x-model="metaDesc" @input="recalculate()"
                placeholder="150–160 characters for best results">{{ $metaDesc }}</textarea>
            <div style="height:4px;background:var(--surface-3);border-radius:99px;margin-top:6px;overflow:hidden">
                <div style="height:100%;border-radius:99px;transition:width .2s"
                     :style="'width:' + Math.min((descLen/160)*100,100) + '%;background:' + (descLen > 160 ? 'var(--danger)' : descLen >= 150 ? 'var(--success)' : 'var(--warning)')"></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px" class="grid-2">
            <div class="field">
                <span class="lbl">Focus keyword</span>
                <input class="input" type="text" name="focus_keyword" value="{{ $focusKw }}" placeholder="primary target keyword">
            </div>
            <div class="field">
                <span class="lbl">Meta keywords</span>
                <input class="input" type="text" name="meta_keywords" value="{{ $metaKw }}" placeholder="kw1, kw2, kw3">
            </div>
            <div class="field">
                <span class="lbl">Canonical URL</span>
                <input class="input" type="text" name="canonical_url" value="{{ $canonical }}" placeholder="Leave blank for default">
            </div>
            <div class="field">
                <span class="lbl">Robots</span>
                <div class="select-wrap">
                    <select class="select" name="robots">
                        @foreach(['index,follow','noindex,nofollow','noindex,follow','index,nofollow'] as $opt)
                            <option value="{{ $opt }}" {{ $robots === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <template x-if="suggestions.length > 0">
            <div style="padding:12px;background:var(--info-soft);border:1px solid var(--info);border-radius:var(--radius-card)">
                <div style="font-size:12px;font-weight:700;color:var(--info);margin-bottom:6px">Suggestions</div>
                <div class="stack" style="gap:3px">
                    <template x-for="s in suggestions" :key="s">
                        <div style="font-size:12.5px;color:var(--info)" x-text="'· ' + s"></div>
                    </template>
                </div>
            </div>
        </template>

    </div>
</div>

@once
@push('scripts')
<script>
function productSeo() {
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

        get scoreColorHex() {
            if (this.score >= 90) return 'var(--success)';
            if (this.score >= 70) return 'var(--info)';
            if (this.score >= 50) return 'var(--warning)';
            if (this.score >= 30) return 'var(--pop)';
            return 'var(--danger)';
        },

        init() { this.recalculate(); },

        recalculate() {
            let s = 0; const checks = {}; const sugg = [];

            const tl = this.titleLen;
            if (tl >= 50 && tl <= 60)      { s += 25; checks.title = {status:'good',    message:'Title length optimal (50-60)'}; }
            else if (tl > 0 && tl < 70)    { s += 12; checks.title = {status:'warning', message:'Title length could be better'}; sugg.push(tl < 50 ? 'Lengthen title to 50-60 chars' : 'Shorten title'); }
            else                           {          checks.title = {status:'error',   message:'Title missing or too long'}; sugg.push('Add a meta title (50-60 chars)'); }

            const dl = this.descLen;
            if (dl >= 150 && dl <= 160)    { s += 25; checks.desc = {status:'good',    message:'Description length optimal (150-160)'}; }
            else if (dl > 0 && dl < 320)   { s += 12; checks.desc = {status:'warning', message:'Description could be better'}; sugg.push(dl < 150 ? 'Lengthen to 150-160 chars' : 'Shorten to 160 chars'); }
            else                           {          checks.desc = {status:'error',   message:'Description missing'}; sugg.push('Add meta description (150-160 chars)'); }

            const fk = document.querySelector('[name="focus_keyword"]')?.value?.trim() ?? '';
            if (fk && this.metaTitle.toLowerCase().includes(fk.toLowerCase())) {
                s += 20; checks.kw_title = {status:'good', message:'Focus keyword in meta title'};
            } else if (fk) {
                checks.kw_title = {status:'warning', message:'Keyword not in title'}; sugg.push('Include focus keyword in title');
            }

            if (fk && this.metaDesc.toLowerCase().includes(fk.toLowerCase())) {
                s += 15; checks.kw_desc = {status:'good', message:'Keyword in description'};
            } else if (fk) {
                checks.kw_desc = {status:'warning', message:'Keyword not in description'}; sugg.push('Include keyword in description');
            }

            const kwField = document.querySelector('[name="meta_keywords"]')?.value?.trim() ?? '';
            if (kwField) {
                const kwArr = kwField.split(',').map(k => k.trim()).filter(Boolean);
                if (kwArr.length >= 1 && kwArr.length <= 5) { s += 15; checks.kw_count = {status:'good', message:'Keyword count is good'}; }
                else { s += 5; checks.kw_count = {status:'warning', message:'Aim for 3-5 keywords'}; sugg.push('Use 3–5 meta keywords'); }
            }

            this.score = Math.min(s, 100);
            this.checks = checks;
            this.suggestions = sugg;
            this.grade = this.score >= 90 ? 'excellent' : this.score >= 70 ? 'good' : this.score >= 50 ? 'average' : this.score >= 30 ? 'poor' : 'critical';
        },
    };
}
</script>
@endpush
@endonce
