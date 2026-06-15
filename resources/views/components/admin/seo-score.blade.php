@props(['score' => 0, 'grade' => 'poor', 'checks' => [], 'suggestions' => []])

@php
$gradeColor = match($grade) {
    'excellent' => 'var(--success)',
    'good'      => 'var(--info)',
    'average'   => 'var(--warning)',
    'poor'      => 'var(--pop)',
    'critical'  => 'var(--danger)',
    default     => 'var(--text-faint)',
};
$gradeSoft = match($grade) {
    'excellent' => 'var(--success-soft)',
    'good'      => 'var(--info-soft)',
    'average'   => 'var(--warning-soft)',
    'poor'      => 'var(--pop-soft)',
    'critical'  => 'var(--danger-soft)',
    default     => 'var(--surface-3)',
};
@endphp

<div class="card pad">
    <div class="between" style="margin-bottom:16px">
        <h3>SEO Score</h3>
        <div class="row" style="gap:12px">
            <div style="text-align:right">
                <div class="num" style="font-size:24px;font-weight:700">{{ $score }}<span style="font-size:14px;font-weight:500;color:var(--text-muted)">/100</span></div>
                <span class="pill sm" style="background:{{ $gradeSoft }};color:{{ $gradeColor }};border-color:{{ $gradeColor }}20">{{ ucfirst($grade) }}</span>
            </div>
            <div style="position:relative;width:56px;height:56px">
                <svg width="56" height="56" viewBox="0 0 36 36" style="transform:rotate(-90deg)">
                    <path fill="none" stroke="var(--surface-3)" stroke-width="3"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path fill="none" stroke="{{ $gradeColor }}" stroke-width="3"
                          stroke-dasharray="{{ $score }}, 100"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700">{{ $score }}</div>
            </div>
        </div>
    </div>

    @if(count($checks) > 0)
        <div class="stack" style="gap:8px;margin-bottom:14px">
            @foreach($checks as $check => $data)
                @php
                $ico = match($data['status'] ?? '') {
                    'good'    => 'check',
                    'warning' => 'bell',
                    default   => 'x',
                };
                $col = match($data['status'] ?? '') {
                    'good'    => 'var(--success)',
                    'warning' => 'var(--warning)',
                    default   => 'var(--danger)',
                };
                @endphp
                <div class="row" style="gap:8px;font-size:13px">
                    <span class="ico" data-ico="{{ $ico }}" style="width:15px;height:15px;color:{{ $col }};flex-shrink:0"></span>
                    <span>{{ $data['message'] ?? 'Status check' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if(count($suggestions) > 0)
        <div style="border-top:1px solid var(--border);padding-top:14px">
            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-muted);margin-bottom:8px">Suggestions</div>
            <div class="stack" style="gap:6px">
                @foreach($suggestions as $suggestion)
                    <div class="row" style="gap:8px;font-size:13px;align-items:flex-start">
                        <span style="color:var(--accent);font-weight:700;flex-shrink:0;margin-top:1px">•</span>
                        <span>{{ $suggestion }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
