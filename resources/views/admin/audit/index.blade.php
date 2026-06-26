@extends('admin.layouts.admin')
@section('title', 'Audit Log')
@section('content')
@php
$eventColors = ['created'=>'success','updated'=>'warning','deleted'=>'danger'];
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Audit Log</h2>
        <div class="sub">All create / update / delete events across audited models</div>
    </div>
</div>

<div class="card flush" style="margin-bottom:14px">
    <form method="GET" action="{{ route('admin.audit.index') }}"
          style="padding:14px 18px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;border-bottom:1px solid var(--border)">
        <div class="field" style="margin:0;min-width:140px">
            <span class="lbl">Event</span>
            <select class="input" name="event" style="height:36px;font-size:13px">
                <option value="">All Events</option>
                <option value="created"  {{ request('event') === 'created'  ? 'selected' : '' }}>Created</option>
                <option value="updated"  {{ request('event') === 'updated'  ? 'selected' : '' }}>Updated</option>
                <option value="deleted"  {{ request('event') === 'deleted'  ? 'selected' : '' }}>Deleted</option>
            </select>
        </div>
        <div class="field" style="margin:0;min-width:160px">
            <span class="lbl">Model</span>
            <select class="input" name="model" style="height:36px;font-size:13px">
                <option value="">All Models</option>
                @foreach($modelTypes as $type)
                <option value="{{ $type }}" {{ request('model') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="margin:0">
            <span class="lbl">From</span>
            <input class="input" type="date" name="from" value="{{ request('from') }}" style="height:36px;font-size:13px">
        </div>
        <div class="field" style="margin:0">
            <span class="lbl">To</span>
            <input class="input" type="date" name="to" value="{{ request('to') }}" style="height:36px;font-size:13px">
        </div>
        <div class="row" style="gap:8px;padding-top:18px">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline">Clear</a>
        </div>
    </form>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Event</th>
                    <th>Model</th>
                    <th>By</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="hoverable" x-data="{ open: false }">
                    <td class="faint" style="font-size:12.5px;white-space:nowrap">
                        {{ $log->created_at->format('d M Y H:i') }}
                    </td>
                    <td>
                        <span class="pill sm {{ $eventColors[$log->event] ?? '' }}">
                            {{ ucfirst($log->event) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-weight:600;font-size:13.5px">{{ class_basename($log->auditable_type) }}</span>
                        <span class="faint" style="font-size:12px"> #{{ $log->auditable_id }}</span>
                    </td>
                    <td class="muted" style="font-size:13px">{{ $log->user?->name ?? 'System' }}</td>
                    <td>
                        @if($log->event === 'updated' && $log->old_values)
                        <button @click="open=!open" class="link-btn" style="font-size:12.5px">
                            <span x-text="open ? 'Hide diff' : 'Show diff'">Show diff</span>
                        </button>
                        <div x-show="open" style="margin-top:8px;display:none" x-cloak>
                            @foreach(array_keys($log->new_values ?? []) as $field)
                                @if(isset($log->old_values[$field]) && $log->old_values[$field] !== ($log->new_values[$field] ?? null))
                                <div style="font-size:12px;margin-bottom:3px">
                                    <span style="font-weight:600;color:var(--text)">{{ $field }}:</span>
                                    <span style="text-decoration:line-through;color:var(--danger);margin-left:6px">{{ Str::limit(is_array($log->old_values[$field]) ? json_encode($log->old_values[$field]) : (string) $log->old_values[$field], 40) }}</span>
                                    <span style="color:var(--success);margin-left:4px">→ {{ Str::limit(is_array($log->new_values[$field] ?? '') ? json_encode($log->new_values[$field]) : (string) ($log->new_values[$field] ?? ''), 40) }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        @elseif($log->event === 'created')
                        <span class="faint" style="font-size:12.5px">New record</span>
                        @else
                        <span class="faint" style="font-size:12.5px">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:48px 20px">
                        <div class="faint" style="font-size:13.5px">No audit events found.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $logs->links() }}</div>
</div>

@endsection
