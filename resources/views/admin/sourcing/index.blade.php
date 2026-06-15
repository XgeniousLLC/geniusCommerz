@extends('admin.layouts.admin')

@section('title', 'Sourcing')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Sourcing</h2>
        <div class="sub">Track purchase costs &amp; let AI suggest your selling price</div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Source product
    </button>
</div>

<div class="stat-grid">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="package"></span></span>
        <div><div class="num">{{ $totalCount }}</div><div class="lbl">Items sourced</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $listedCount }}</div><div class="lbl">Listed</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="percent"></span></span>
        <div><div class="num">{{ $avgMargin }}%</div><div class="lbl">Avg. margin</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="wallet"></span></span>
        <div><div class="num">৳{{ number_format($capitalInvested, 0) }}</div><div class="lbl">Capital invested</div></div>
    </div>
</div>

<div class="card flush">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Supplier</th>
                    <th style="text-align:right">Cost</th>
                    <th style="text-align:right">Sell</th>
                    <th style="text-align:right">Margin</th>
                    <th>MOQ</th>
                    <th>Lead</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>
                        <div class="row" style="gap:11px">
                            @if($item->product?->firstImage())
                            <img class="thumb" style="width:40px;height:40px" src="{{ $item->product->firstImage()->getUrl('thumb') }}" alt="">
                            @else
                            <span class="tile muted" style="width:40px;height:40px;border-radius:10px;flex-shrink:0">
                                <span class="ico" data-ico="package" style="width:18px;height:18px"></span>
                            </span>
                            @endif
                            <span style="font-weight:700;font-size:13.5px">{{ $item->product_name }}</span>
                        </div>
                    </td>
                    <td class="muted" style="font-size:13px">{{ $item->supplier }}</td>
                    <td style="text-align:right" class="tnum">৳{{ number_format($item->cost_price, 2) }}</td>
                    <td style="text-align:right" class="tnum">
                        @if($item->sell_price)
                        <b>৳{{ number_format($item->sell_price, 2) }}</b>
                        @else
                        <form method="POST" action="{{ route('admin.ai.suggest-price') }}" style="display:inline"
                              x-data="{loading:false}" @submit.prevent="
                                loading=true;
                                fetch('{{ route('admin.ai.suggest-price') }}',{
                                    method:'POST',
                                    headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},
                                    body:JSON.stringify({product_name:'{{ addslashes($item->product_name) }}',cost_price:{{ $item->cost_price }},item_id:{{ $item->id }}})
                                }).then(r=>r.json()).then(d=>{
                                    if(d.sell_price) window.location.reload();
                                }).finally(()=>loading=false)">
                            <button type="submit" class="btn btn-sm"
                                    style="border:1px solid var(--violet);background:var(--violet-soft);color:var(--violet);font-size:12.5px;padding:6px 11px"
                                    :disabled="loading">
                                <span class="ico" data-ico="spark" style="width:14px;height:14px"></span>
                                <span x-text="loading ? 'Thinking...' : 'Suggest'">Suggest</span>
                            </button>
                        </form>
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if($item->margin() !== null)
                        <span class="pill sm {{ $item->margin() >= 40 ? 'success' : ($item->margin() >= 20 ? 'warning' : 'danger') }}">
                            {{ $item->margin() }}%
                        </span>
                        @else
                        <span class="faint">—</span>
                        @endif
                    </td>
                    <td class="tnum">{{ $item->moq }}</td>
                    <td class="faint" style="font-size:13px">{{ $item->lead_days }} days</td>
                    <td>
                        <span class="pill sm {{ $item->status === 'listed' ? 'success' : 'warning' }}">
                            <span class="dot"></span>{{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td style="text-align:right">
                        <form method="POST" action="{{ route('admin.sourcing.destroy', $item) }}"
                              onsubmit="return confirm('Delete this sourcing item?')" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn">
                                <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:48px 20px">
                        <span class="tile muted" style="width:48px;height:48px;border-radius:14px;margin:0 auto 12px">
                            <span class="ico" data-ico="package" style="width:22px;height:22px"></span>
                        </span>
                        <div class="faint" style="font-size:13.5px;margin-top:10px">No sourcing items yet. Click <strong>Source product</strong> to get started.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Modal --}}
<div id="addModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;padding:24px">
    <div class="card pad" style="width:100%;max-width:520px;position:relative">
        <div class="card-head" style="margin-bottom:20px">
            <span class="tile sm t-info"><span class="ico" data-ico="package" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Source a product</h3><div class="sub">Add a new item to track</div></div>
            <button class="icon-btn" style="margin-left:auto" onclick="document.getElementById('addModal').style.display='none'">
                <span class="ico" data-ico="x" style="width:18px;height:18px"></span>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.sourcing.store') }}" class="col-gap" style="--gap:14px">
            @csrf
            <div class="field">
                <span class="lbl">Product Name <span style="color:var(--danger)">*</span></span>
                <input class="input" type="text" name="product_name" value="{{ old('product_name') }}" required placeholder="e.g. Wireless Headphones">
            </div>
            <div class="field">
                <span class="lbl">Supplier <span style="color:var(--danger)">*</span></span>
                <input class="input" type="text" name="supplier" value="{{ old('supplier') }}" required placeholder="e.g. Shenzhen DirectTrade">
            </div>
            <div class="row" style="gap:12px">
                <div class="field grow">
                    <span class="lbl">Cost Price (৳) <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="number" name="cost_price" value="{{ old('cost_price') }}" min="0" step="0.01" required>
                </div>
                <div class="field grow">
                    <span class="lbl">Sell Price (৳)</span>
                    <input class="input" type="number" name="sell_price" value="{{ old('sell_price') }}" min="0" step="0.01" placeholder="Leave blank for AI suggest">
                </div>
            </div>
            <div class="row" style="gap:12px">
                <div class="field grow">
                    <span class="lbl">MOQ <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="number" name="moq" value="{{ old('moq', 1) }}" min="1" required>
                </div>
                <div class="field grow">
                    <span class="lbl">Lead Days <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="number" name="lead_days" value="{{ old('lead_days', 0) }}" min="0" required>
                </div>
            </div>
            <div class="field">
                <span class="lbl">Status</span>
                <select class="input" name="status">
                    <option value="sourced" {{ old('status','sourced') === 'sourced' ? 'selected' : '' }}>Sourced</option>
                    <option value="listed" {{ old('status') === 'listed' ? 'selected' : '' }}>Listed</option>
                </select>
            </div>
            <div class="row" style="gap:10px;padding-top:4px">
                <button type="submit" class="btn btn-primary">Save Item</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.getElementById('addModal').style.display='flex';</script>
@endif

@endsection
