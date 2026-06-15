@extends('admin.layouts.admin')
@section('title', 'Failed Jobs')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Failed Jobs</h2>
        <div class="sub">{{ $jobs->total() }} failed job(s)</div>
    </div>
    @if($jobs->total() > 0)
    <div class="row" style="gap:10px">
        <form method="POST" action="{{ route('admin.failed-jobs.retry-all') }}"
              onsubmit="return confirm('Retry all {{ $jobs->total() }} failed jobs?')">
            @csrf
            <button type="submit" class="btn btn-outline">Retry All</button>
        </form>
        <form method="POST" action="{{ route('admin.failed-jobs.destroy-all') }}"
              onsubmit="return confirm('Permanently delete all failed jobs?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger)">Clear All</button>
        </form>
    </div>
    @endif
</div>

<div class="card flush">
    <form method="GET" style="padding:14px 18px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;border-bottom:1px solid var(--border)">
        <div style="flex:1;min-width:200px">
            <input class="input" type="text" name="search" value="{{ $search }}" placeholder="Search class name, message…">
        </div>
        <div style="min-width:160px">
            <select class="input" name="queue">
                <option value="">All queues</option>
                @foreach($queues as $q)
                <option value="{{ $q }}" {{ $queue === $q ? 'selected' : '' }}>{{ $q }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-outline">Filter</button>
        @if($search || $queue)
        <a href="{{ route('admin.failed-jobs.index') }}" class="btn btn-sm">Clear</a>
        @endif
    </form>

    @if($jobs->isEmpty())
    <div style="text-align:center;padding:60px 20px">
        <span class="tile t-success" style="width:52px;height:52px;border-radius:16px;margin:0 auto 14px">
            <span class="ico" data-ico="check" style="width:24px;height:24px"></span>
        </span>
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">No failed jobs</div>
        <div class="faint" style="font-size:13px">Everything is running smoothly.</div>
    </div>
    @else
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Job Class</th>
                    <th>Queue</th>
                    <th>Error</th>
                    <th>Failed At</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                @php
                    $payload   = json_decode($job->payload, true);
                    $jobClass  = class_basename($payload['displayName'] ?? $payload['job'] ?? 'Unknown');
                    $firstLine = Str::limit(explode("\n", $job->exception)[0], 100);
                @endphp
                <tr class="hoverable">
                    <td style="font-weight:700;font-size:13.5px">{{ $jobClass }}</td>
                    <td><span class="pill sm">{{ $job->queue }}</span></td>
                    <td class="mono faint" style="font-size:12px;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $firstLine }}">
                        {{ $firstLine }}
                    </td>
                    <td class="faint" style="font-size:13px;white-space:nowrap">
                        {{ \Carbon\Carbon::parse($job->failed_at)->format('d M Y, H:i') }}
                    </td>
                    <td style="text-align:right">
                        <div class="row" style="gap:6px;justify-content:flex-end">
                            <a href="{{ route('admin.failed-jobs.show', $job->uuid) }}" class="btn btn-sm btn-outline">View</a>
                            <form method="POST" action="{{ route('admin.failed-jobs.retry', $job->uuid) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="color:var(--success);border:1px solid var(--success);background:var(--success-soft)">Retry</button>
                            </form>
                            <form method="POST" action="{{ route('admin.failed-jobs.destroy', $job->uuid) }}"
                                  onsubmit="return confirm('Discard this job?')" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn">
                                    <span class="ico" data-ico="trash" style="width:15px;height:15px"></span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($jobs->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $jobs->links() }}</div>
    @endif
    @endif
</div>

@endsection
