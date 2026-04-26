{{--
  Reusable SEO panel for product create/edit.
  Expects: $meta (MetaInformation|null), $product (Product|null for edit)
--}}
@php
    $metaTitle    = old('meta_title',       $meta?->meta_title       ?? '');
    $metaDesc     = old('meta_description', $meta?->meta_description ?? '');
    $focusKw      = old('focus_keyword',    $meta?->focus_keyword    ?? '');
    $metaKw       = old('meta_keywords',    $meta?->meta_keywords    ?? '');
    $canonical    = old('canonical_url',    $meta?->canonical_url    ?? '');
    $robots       = old('robots',           $meta?->robots           ?? 'index,follow');
    $savedScore   = $meta?->seo_score ?? 0;
@endphp

<x-admin.card x-data="productSeo()" x-init="init()">
    {{-- Header toggle --}}
    <button type="button" @click="open = !open"
        class="flex items-center justify-between w-full text-left">
        <div class="flex items-center space-x-3">
            <h3 class="text-base font-semibold text-gray-900">SEO</h3>
            @if($savedScore > 0)
                @php
                    $scoreBadge = match(true) {
                        $savedScore >= 90 => 'bg-green-100 text-green-700',
                        $savedScore >= 70 => 'bg-blue-100 text-blue-700',
                        $savedScore >= 50 => 'bg-yellow-100 text-yellow-700',
                        $savedScore >= 30 => 'bg-orange-100 text-orange-700',
                        default           => 'bg-red-100 text-red-700',
                    };
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $scoreBadge }}">
                    Score: {{ $savedScore }}/100
                </span>
            @endif
        </div>
        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-collapse class="mt-5 space-y-5">

        {{-- Live score ring --}}
        <div class="p-3 rounded-lg bg-gray-50 space-y-3">
            <div class="flex items-center gap-3">
                <div class="relative flex-shrink-0" style="width:56px;height:56px">
                    <svg style="width:56px;height:56px;transform:rotate(-90deg)" viewBox="0 0 36 36">
                        <path stroke="currentColor" class="text-gray-200" stroke-width="3" fill="transparent"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path stroke="currentColor" :class="scoreColor" stroke-width="3" fill="transparent"
                              :stroke-dasharray="score + ', 100'"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-sm font-bold text-gray-800" x-text="score"></span>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700" x-text="grade.charAt(0).toUpperCase() + grade.slice(1)"></p>
                    <p class="text-xs text-gray-500 mt-0.5">Live SEO score</p>
                </div>
            </div>
            <div class="space-y-1.5">
                <template x-for="(chk, key) in checks" :key="key">
                    <div class="flex items-start gap-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"
                             :class="chk.status==='good' ? 'text-green-500' : chk.status==='warning' ? 'text-yellow-500' : 'text-red-500'"
                             fill="currentColor" viewBox="0 0 20 20">
                            <template x-if="chk.status==='good'">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </template>
                            <template x-if="chk.status!=='good'">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </template>
                        </svg>
                        <span class="text-xs text-gray-600" x-text="chk.message"></span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Meta Title --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-medium text-gray-700">Meta Title</label>
                <span class="text-xs" :class="titleCountColor" x-text="titleLen + ' / 60'"></span>
            </div>
            <input type="text" name="meta_title" id="seo-meta-title"
                x-model="metaTitle"
                @input="recalculate()"
                value="{{ $metaTitle }}"
                placeholder="Defaults to product name if blank"
                class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" />
            <div class="mt-1 h-1 rounded bg-gray-100 overflow-hidden">
                <div class="h-full rounded transition-all duration-200"
                     :class="titleCountColor.replace('text-','bg-')"
                     :style="'width:' + Math.min((titleLen/60)*100,100) + '%'"></div>
            </div>
        </div>

        {{-- Meta Description --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                <span class="text-xs" :class="descCountColor" x-text="descLen + ' / 160'"></span>
            </div>
            <textarea name="meta_description" id="seo-meta-description" rows="3"
                x-model="metaDesc"
                @input="recalculate()"
                placeholder="150–160 characters for best results"
                class="mt-0.5 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ $metaDesc }}</textarea>
            <div class="mt-1 h-1 rounded bg-gray-100 overflow-hidden">
                <div class="h-full rounded transition-all duration-200"
                     :class="descCountColor.replace('text-','bg-')"
                     :style="'width:' + Math.min((descLen/160)*100,100) + '%'"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Focus Keyword --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Focus Keyword</label>
                <input type="text" name="focus_keyword" value="{{ $focusKw }}"
                    placeholder="primary target keyword"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" />
            </div>

            {{-- Meta Keywords --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                <input type="text" name="meta_keywords" value="{{ $metaKw }}"
                    placeholder="kw1, kw2, kw3"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" />
            </div>

            {{-- Canonical URL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                <input type="text" name="canonical_url" value="{{ $canonical }}"
                    placeholder="Leave blank to use default"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" />
            </div>

            {{-- Robots --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Robots</label>
                <select name="robots"
                    class="block w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                    @foreach(['index,follow','noindex,nofollow','noindex,follow','index,nofollow'] as $opt)
                        <option value="{{ $opt }}" {{ $robots === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Suggestions --}}
        <template x-if="suggestions.length > 0">
            <div class="p-3 rounded-md bg-blue-50 border border-blue-100">
                <p class="text-xs font-semibold text-blue-700 mb-1">Suggestions</p>
                <ul class="space-y-0.5">
                    <template x-for="s in suggestions" :key="s">
                        <li class="text-xs text-blue-600 flex items-start space-x-1">
                            <span class="mt-0.5">·</span><span x-text="s"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

    </div>
</x-admin.card>

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

        get titleCountColor() {
            if (this.titleLen === 0)                           return 'text-gray-400';
            if (this.titleLen >= 50 && this.titleLen <= 60)    return 'text-green-600';
            if (this.titleLen < 70)                            return 'text-yellow-600';
            return 'text-red-600';
        },
        get descCountColor() {
            if (this.descLen === 0)                            return 'text-gray-400';
            if (this.descLen >= 150 && this.descLen <= 160)    return 'text-green-600';
            if (this.descLen < 320)                            return 'text-yellow-600';
            return 'text-red-600';
        },
        get scoreColor() {
            if (this.score >= 90) return 'text-green-500';
            if (this.score >= 70) return 'text-blue-500';
            if (this.score >= 50) return 'text-yellow-500';
            if (this.score >= 30) return 'text-orange-500';
            return 'text-red-500';
        },

        init() {
            this.recalculate();
        },

        recalculate() {
            let s = 0; const checks = {}; const sugg = [];

            // Title
            const tl = this.titleLen;
            if (tl >= 50 && tl <= 60)      { s += 25; checks.title = {status:'good',    message:'Title length is optimal (50-60)'}; }
            else if (tl > 0 && tl < 70)    { s += 12; checks.title = {status:'warning', message:'Title length could be better'}; sugg.push(tl < 50 ? 'Lengthen title to 50-60 chars' : 'Shorten title to 50-60 chars'); }
            else                            {          checks.title = {status:'error',   message:'Title missing or too long'}; sugg.push('Add a meta title (50-60 chars optimal)'); }

            // Description
            const dl = this.descLen;
            if (dl >= 150 && dl <= 160)    { s += 25; checks.desc = {status:'good',    message:'Description length is optimal (150-160)'}; }
            else if (dl > 0 && dl < 320)   { s += 12; checks.desc = {status:'warning', message:'Description could be better'}; sugg.push(dl < 150 ? 'Lengthen description to 150-160 chars' : 'Shorten description to 160 chars'); }
            else                           {          checks.desc = {status:'error',   message:'Description missing or too long'}; sugg.push('Add a meta description (150-160 chars optimal)'); }

            // Focus keyword in title
            const fk = document.querySelector('[name="focus_keyword"]')?.value?.trim() ?? '';
            if (fk && this.metaTitle.toLowerCase().includes(fk.toLowerCase())) {
                s += 20; checks.kw_title = {status:'good', message:'Focus keyword in meta title'};
            } else if (fk) {
                checks.kw_title = {status:'warning', message:'Focus keyword not in meta title'}; sugg.push('Include your focus keyword in the meta title');
            }

            // Focus keyword in description
            if (fk && this.metaDesc.toLowerCase().includes(fk.toLowerCase())) {
                s += 15; checks.kw_desc = {status:'good', message:'Focus keyword in description'};
            } else if (fk) {
                checks.kw_desc = {status:'warning', message:'Focus keyword not in description'}; sugg.push('Include your focus keyword in the meta description');
            }

            // Keyword count
            const kwField = document.querySelector('[name="meta_keywords"]')?.value?.trim() ?? '';
            if (kwField) {
                const kwArr = kwField.split(',').map(k => k.trim()).filter(Boolean);
                if (kwArr.length >= 1 && kwArr.length <= 5) { s += 15; checks.kw_count = {status:'good', message:`Keyword count (${kwArr.length}) is good`}; }
                else                                        { s += 5;  checks.kw_count = {status:'warning', message:'Aim for 3–5 keywords'}; sugg.push('Focus on 3–5 meta keywords'); }
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
