@extends('admin.layouts.admin')

@section('title', 'Attributes')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Attributes</h2>
        <div class="sub">Define options like size, colour and material for product variants</div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.attributes.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Add attribute
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="sliders"></span></span>
        <div><div class="num">{{ $stats['total'] }}</div><div class="lbl">Attributes</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="layers"></span></span>
        <div><div class="num">{{ $stats['values'] }}</div><div class="lbl">Total values</div></div>
    </div>
</div>

@php
$tileColors = ['t-info','t-violet','t-pop','t-teal','t-warning','t-accent'];
$tileIcons  = ['sliders','layers','tag','package','list','gear'];
@endphp

<div class="card" style="padding:8px">
    @forelse($attributes as $i => $attr)
    @php $tc = $tileColors[$i % count($tileColors)]; $ti = $tileIcons[$i % count($tileIcons)]; @endphp
    <div style="padding:16px 14px;@if(!$loop->last)border-bottom:1px solid var(--border);@endif">
        <div class="row" style="gap:12px;margin-bottom:12px">
            <span class="tile {{ $tc }}">
                <span class="ico" data-ico="{{ $ti }}" style="width:20px;height:20px"></span>
            </span>
            <div class="grow">
                <div style="font-weight:700;font-size:15.5px">{{ $attr->name }}</div>
                <div class="faint" style="font-size:12px;font-weight:600;margin-top:2px">
                    {{ $attr->values_count }} values · <span class="mono">/{{ $attr->slug }}</span>
                </div>
            </div>
            <div class="row" style="gap:4px">
                <a class="icon-btn" href="{{ route('admin.attributes.edit', $attr) }}" title="Edit">
                    <span class="ico" data-ico="edit" style="width:16px;height:16px"></span>
                </a>
                <form method="POST" action="{{ route('admin.attributes.destroy', $attr) }}"
                      onsubmit="return confirm('Delete {{ addslashes($attr->name) }} and all its values?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="icon-btn danger" title="Delete">
                        <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                    </button>
                </form>
            </div>
        </div>
        @if($attr->values->isNotEmpty())
        <div class="row wrap" style="gap:7px;padding-top:12px;border-top:1px solid var(--border)">
            @foreach($attr->values as $val)
            <span class="pill" style="background:var(--surface-2);border:1px solid var(--border)">{{ $val->value }}</span>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div style="padding:48px;text-align:center;color:var(--text-faint)">No attributes yet.</div>
    @endforelse
</div>

@endsection
