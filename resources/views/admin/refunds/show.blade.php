@extends('admin.layouts.admin')
@section('title', 'Refund #' . $refund->id)
@section('content')
@php
$statusColors = ['approved'=>'success','processed'=>'info','rejected'=>'danger','pending'=>'warning'];
@endphp

<div class="page-head">
    <div class="row" style="gap:12px;align-items:center">
        <h2 class="display">Refund #{{ $refund->id }}</h2>
        <span class="pill sm {{ $statusColors[$refund->status] ?? 'warning' }}">
            <span class="dot"></span>{{ ucfirst($refund->status) }}
        </span>
    </div>
    <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start">

    <div class="col-gap" style="--gap:18px">
        <div class="card pad">
            <div class="card-head" style="margin-bottom:18px">
                <span class="tile sm t-info"><span class="ico" data-ico="user" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Request Details</h3></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px 24px">
                <div>
                    <div class="faint" style="font-size:12px;margin-bottom:2px">Customer</div>
                    <div style="font-weight:600;font-size:13.5px">{{ $refund->user->name }}</div>
                </div>
                <div>
                    <div class="faint" style="font-size:12px;margin-bottom:2px">Email</div>
                    <div style="font-size:13.5px">{{ $refund->user->email }}</div>
                </div>
                <div>
                    <div class="faint" style="font-size:12px;margin-bottom:2px">Order</div>
                    <a href="{{ route('admin.orders.show', $refund->order) }}"
                       class="mono accent" style="font-weight:700;text-decoration:none;color:var(--accent)">
                        #{{ $refund->order->order_number }}
                    </a>
                </div>
                <div>
                    <div class="faint" style="font-size:12px;margin-bottom:2px">Refund Amount</div>
                    <div class="tnum" style="font-weight:700;font-size:15px">{{ money($refund->amount ?? $refund->order->total, 0) }}</div>
                </div>
                <div>
                    <div class="faint" style="font-size:12px;margin-bottom:2px">Reason</div>
                    <div style="font-size:13.5px">{{ \App\Models\Refund::REASONS[$refund->reason] ?? $refund->reason }}</div>
                </div>
                <div>
                    <div class="faint" style="font-size:12px;margin-bottom:2px">Requested</div>
                    <div style="font-size:13px">{{ $refund->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>

            @if($refund->details)
            <div style="margin-top:14px;padding:10px 14px;border-radius:10px;background:var(--surface-2);border:1px solid var(--border);font-size:13px">
                <span style="font-weight:600">Customer note:</span> {{ $refund->details }}
            </div>
            @endif

            @if($refund->admin_note)
            <div style="margin-top:10px;padding:10px 14px;border-radius:10px;background:color-mix(in srgb,var(--info) 10%,transparent);border:1px solid color-mix(in srgb,var(--info) 30%,transparent);font-size:13px">
                <span style="font-weight:600;color:var(--info)">Admin note:</span> {{ $refund->admin_note }}
            </div>
            @endif
        </div>

        <div class="card flush">
            <div style="padding:14px 18px;border-bottom:1px solid var(--border)">
                <h3 style="font-weight:700;font-size:14px">Order Items</h3>
            </div>
            <div style="padding:14px 18px" class="col-gap" style="--gap:12px">
                @foreach($refund->order->items as $item)
                <div class="row" style="gap:12px;padding:10px 0;border-bottom:1px solid var(--border)">
                    <div style="width:48px;height:48px;border-radius:10px;overflow:hidden;background:var(--surface-2);flex-shrink:0">
                        @if($item->product?->images->first())
                            <img src="{{ $item->product->images->first()->getUrl('thumb') }}" alt="{{ $item->product_name }}" style="width:100%;height:100%;object-fit:cover">
                        @endif
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:600;font-size:13.5px;truncate:ellipsis;overflow:hidden;white-space:nowrap">{{ $item->product_name }}</div>
                        @if($item->variant_label)
                        <div class="faint" style="font-size:12px">{{ $item->variant_label }}</div>
                        @endif
                        <div class="faint" style="font-size:12px">{{ money($item->unit_price, 0) }} × {{ $item->quantity }}</div>
                    </div>
                    <div style="font-weight:700;font-size:14px;flex-shrink:0">{{ money($item->total, 0) }}</div>
                </div>
                @endforeach
            </div>
            <div style="padding:12px 18px;border-top:1px solid var(--border);display:flex;justify-content:space-between;font-weight:700;font-size:14px">
                <span>Order Total</span>
                <span>{{ money($refund->order->total, 0) }}</span>
            </div>
        </div>
    </div>

    <div class="col-gap" style="--gap:18px">
        @if(in_array($refund->status, ['pending']))
        <div class="card pad">
            <div class="card-head" style="margin-bottom:16px">
                <span class="tile sm t-warning"><span class="ico" data-ico="check" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Take Action</h3></div>
            </div>
            <form method="POST" action="{{ route('admin.refunds.update', $refund) }}" class="col-gap" style="--gap:14px">
                @csrf @method('PATCH')
                <div class="field">
                    <span class="lbl">Decision</span>
                    <select class="input" name="status" required>
                        <option value="">Select…</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                        <option value="processed">Mark as Processed</option>
                    </select>
                </div>
                <div class="field">
                    <span class="lbl">Note to customer (optional)</span>
                    <textarea class="input" name="admin_note" rows="3" style="height:auto;resize:none" placeholder="Explain your decision…"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Decision</button>
            </form>
        </div>
        @elseif($refund->status === 'approved')
        <div class="card pad">
            <h3 style="font-weight:700;font-size:14px;margin-bottom:16px">Mark as Processed</h3>
            <form method="POST" action="{{ route('admin.refunds.update', $refund) }}" class="col-gap" style="--gap:14px">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="processed">
                <div class="field">
                    <span class="lbl">Processing note (optional)</span>
                    <textarea class="input" name="admin_note" rows="2" style="height:auto;resize:none" placeholder="e.g. Refunded via bKash…"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Mark Processed</button>
            </form>
        </div>
        @else
        <div class="card pad" style="text-align:center">
            <p style="font-size:13.5px;color:var(--text-muted)">
                This refund has been <span style="font-weight:700">{{ ucfirst($refund->status) }}</span>.
            </p>
            @if($refund->resolved_at)
            <p class="faint" style="font-size:12px;margin-top:6px">{{ $refund->resolved_at->format('M d, Y H:i') }}</p>
            @endif
        </div>
        @endif

        <div class="card pad">
            <div style="font-weight:700;font-size:13.5px;margin-bottom:14px">Order Info</div>
            <div class="col-gap" style="--gap:8px">
                @foreach(['Status' => ucfirst($refund->order->status), 'Payment' => ucwords(str_replace('_',' ',$refund->order->payment_status)), 'Method' => ucwords(str_replace('_',' ',$refund->order->payment_method ?? 'N/A'))] as $label => $val)
                <div class="between" style="font-size:13px">
                    <span class="faint">{{ $label }}</span>
                    <span style="font-weight:600">{{ $val }}</span>
                </div>
                @endforeach
                <div class="between" style="padding-top:8px;border-top:1px solid var(--border);font-weight:700">
                    <span>Total</span>
                    <span>{{ money($refund->order->total, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
