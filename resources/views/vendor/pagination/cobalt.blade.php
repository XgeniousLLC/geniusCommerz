@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <span style="font-size:13px;color:var(--text-muted)">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </span>
    <div style="display:flex;align-items:center;gap:4px">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm" style="opacity:.4;cursor:default;display:inline-flex;align-items:center;gap:4px">
                <span class="ico" data-ico="chevLeft" style="width:14px;height:14px"></span>Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline btn-sm" style="display:inline-flex;align-items:center;gap:4px">
                <span class="ico" data-ico="chevLeft" style="width:14px;height:14px"></span>Prev
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:0 4px;color:var(--text-muted);font-size:13px">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-sm" style="min-width:34px;text-align:center">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-outline btn-sm" style="min-width:34px;text-align:center">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline btn-sm" style="display:inline-flex;align-items:center;gap:4px">
                Next<span class="ico" data-ico="chevRight" style="width:14px;height:14px"></span>
            </a>
        @else
            <span class="btn btn-outline btn-sm" style="opacity:.4;cursor:default;display:inline-flex;align-items:center;gap:4px">
                Next<span class="ico" data-ico="chevRight" style="width:14px;height:14px"></span>
            </span>
        @endif

    </div>
</nav>
@endif
