@extends('admin.layouts.admin')

@section('title', 'Order #' . $order->order_number)

@section('content')
@php
$statusPill  = $order->statusPillClass();
$paymentPill = $order->paymentPillClass();
$sourcePill  = $order->sourcePillClass();
$avatarColors = ['var(--info)','var(--violet)','var(--pop)','var(--teal)','var(--warning)','var(--accent)'];
$avatarColor  = $avatarColors[abs(crc32($order->customer_name)) % count($avatarColors)];
$initials     = collect(explode(' ', $order->customer_name))->map(fn($w) => strtoupper(mb_substr($w,0,1)))->take(2)->implode('');
@endphp

<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.orders.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <div class="grow" style="min-width:200px">
        <div class="breadcrumb"><a href="{{ route('admin.orders.index') }}">Orders</a> / #{{ $order->order_number }}</div>
        <div class="row" style="gap:10px;flex-wrap:wrap">
            <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">#{{ $order->order_number }}</h2>
            <span class="pill {{ $statusPill }}"><span class="dot"></span>{{ ucfirst($order->status) }}</span>
            <span class="pill {{ $paymentPill }}">{{ ucwords(str_replace('_',' ',$order->payment_status)) }}</span>
        </div>
    </div>
    <div class="row" style="gap:10px">
        <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="btn btn-outline">
            <span class="ico" data-ico="download" style="width:18px;height:18px"></span>Print slip
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px;align-items:start" class="grid-2">

<div class="col-gap">

    {{-- Items --}}
    <div class="card flush">
        <div class="card-head" style="padding:20px 22px 14px;margin:0">
            <span class="tile sm t-accent"><span class="ico" data-ico="package" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Items</h3><div class="sub">{{ $order->items->count() }} {{ Str::plural('product', $order->items->count()) }}</div></div>
        </div>
        <div class="table-scroll"><table class="table">
            <thead><tr>
                <th>Product</th>
                <th style="text-align:right">Price</th>
                <th style="text-align:right">Qty</th>
                <th style="text-align:right">Total</th>
            </tr></thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="row" style="gap:11px">
                            @if($item->product?->images->first())
                            <img class="thumb" style="width:40px;height:40px" src="{{ $item->product->images->first()->getUrl('thumb') }}" alt="">
                            @else
                            <span class="tile sm" style="width:40px;height:40px;border-radius:8px;background:var(--surface-2)">
                                <span class="ico" data-ico="image" style="width:18px;height:18px;color:var(--text-faint)"></span>
                            </span>
                            @endif
                            <div>
                                <div style="font-weight:700;font-size:13.5px">{{ $item->product_name }}</div>
                                @if($item->sku)
                                <div class="mono faint" style="font-size:11.5px">{{ $item->sku }}</div>
                                @endif
                                @if($item->variant_label)
                                <div class="faint" style="font-size:11.5px">{{ $item->variant_label }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="text-align:right" class="tnum">{{ money($item->unit_price, 0) }}</td>
                    <td style="text-align:right" class="tnum">{{ $item->quantity }}</td>
                    <td style="text-align:right" class="tnum"><b>{{ money($item->total, 0) }}</b></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        <div style="padding:16px 22px;border-top:1px solid var(--border)">
            <div class="between" style="padding:5px 0"><span class="muted" style="font-size:13.5px">Subtotal</span><span class="tnum">{{ money($order->subtotal, 0) }}</span></div>
            @if($order->discount_amount > 0)
            <div class="between" style="padding:5px 0"><span class="muted" style="font-size:13.5px">Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</span><span class="tnum" style="color:var(--success)">−{{ money($order->discount_amount, 0) }}</span></div>
            @endif
            <div class="between" style="padding:5px 0"><span class="muted" style="font-size:13.5px">Shipping</span><span class="tnum">{{ $order->shipping_cost > 0 ? money($order->shipping_cost, 0) : 'Free' }}</span></div>
            @if($order->tax > 0)
            <div class="between" style="padding:5px 0"><span class="muted" style="font-size:13.5px">Tax</span><span class="tnum">{{ money($order->tax, 0) }}</span></div>
            @endif
            <div class="between" style="padding:10px 0 0;margin-top:6px;border-top:1px solid var(--border)">
                <span style="font-weight:700">Total</span>
                <span class="display tnum" style="font-size:20px;font-weight:700">{{ money($order->total, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Addresses --}}
    @foreach(['shipping_address' => 'Shipping address', 'billing_address' => 'Billing address'] as $field => $label)
    @php $addr = $order->$field ?? []; @endphp
    <div class="card pad" x-data="{ editing: false }">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="mapPin" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>{{ $label }}</h3></div>
            <button type="button" @click="editing = !editing" class="link-btn head-action" x-text="editing ? 'Cancel' : 'Edit'"></button>
        </div>

        <div x-show="!editing">
            @if($addr)
            <address style="not-italic;font-size:13.5px;line-height:1.7;color:var(--text-muted);font-style:normal">
                <b style="color:var(--text)">{{ $addr['name'] ?? $order->customer_name }}</b><br>
                @if(!empty($addr['phone'])){{ $addr['phone'] }}<br>@endif
                @if(!empty($addr['address'])){{ $addr['address'] }}<br>@endif
                @if(!empty($addr['city'])){{ $addr['city'] }}{{ !empty($addr['state']) ? ', '.$addr['state'] : '' }} {{ $addr['postcode'] ?? '' }}<br>@endif
                @if(!empty($addr['country'])){{ $addr['country'] }}@endif
            </address>
            @else
            <span class="faint" style="font-size:13px">No address on file.</span>
            @endif
        </div>

        <form x-show="editing" method="POST" action="{{ route('admin.orders.update-address', $order) }}" x-cloak>
            @csrf @method('PATCH')
            <input type="hidden" name="type" value="{{ $field }}">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
                <div class="field"><span class="lbl" style="font-size:12px">Name</span><input class="input" name="name" value="{{ $addr['name'] ?? $order->customer_name }}" required></div>
                <div class="field"><span class="lbl" style="font-size:12px">Phone</span><input class="input" name="phone" value="{{ $addr['phone'] ?? '' }}"></div>
            </div>
            <div class="field" style="margin-bottom:10px"><span class="lbl" style="font-size:12px">Address</span><input class="input" name="address" value="{{ $addr['address'] ?? '' }}" required></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
                <div class="field"><span class="lbl" style="font-size:12px">City</span><input class="input" name="city" value="{{ $addr['city'] ?? '' }}" required></div>
                <div class="field"><span class="lbl" style="font-size:12px">State</span><input class="input" name="state" value="{{ $addr['state'] ?? '' }}"></div>
                <div class="field"><span class="lbl" style="font-size:12px">Postcode</span><input class="input" name="postcode" value="{{ $addr['postcode'] ?? '' }}"></div>
                <div class="field"><span class="lbl" style="font-size:12px">Country</span><input class="input" name="country" value="{{ $addr['country'] ?? \App\Models\SiteSetting::get('general.store_country', 'BD') }}"></div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Save {{ $label }}</button>
        </form>
    </div>
    @endforeach

    {{-- Notes --}}
    @if($order->notes || $order->admin_note)
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-teal"><span class="ico" data-ico="fileText" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Notes</h3></div>
        </div>
        @if($order->notes)
        <div style="margin-bottom:12px">
            <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--text-faint);text-transform:uppercase;margin-bottom:5px">Customer note</div>
            <p style="font-size:13.5px;color:var(--text-muted)">{{ $order->notes }}</p>
        </div>
        @endif
        @if($order->admin_note)
        <div style="{{ $order->notes ? 'padding-top:12px;border-top:1px solid var(--border)' : '' }}">
            <div style="font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--text-faint);text-transform:uppercase;margin-bottom:5px">Admin note</div>
            <p style="font-size:13.5px;color:var(--text-muted);background:var(--warning-soft);border:1px solid color-mix(in srgb,var(--warning) 20%,transparent);border-radius:8px;padding:10px 14px">{{ $order->admin_note }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Activity Timeline --}}
    @if($order->activities->count())
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="clock" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Timeline</h3></div>
        </div>
        <div class="stack" style="gap:0">
            @foreach($order->activities->sortByDesc('created_at') as $i => $activity)
            @php
            $ico = match($activity->type) {
                'created'         => 'check',
                'status_changed'  => 'refresh',
                'payment_updated' => 'card',
                'tracking_added'  => 'truck',
                'courier_dispatched' => 'truck',
                default           => 'message',
            };
            $tc = match($activity->type) {
                'created'         => 't-success',
                'status_changed'  => 't-info',
                'payment_updated' => 't-teal',
                'tracking_added'  => 't-violet',
                'courier_dispatched' => 't-violet',
                default           => 't-accent',
            };
            @endphp
            <div class="row top" style="gap:12px;padding-bottom:{{ $loop->last ? '0' : '16px' }}">
                <span class="tile sm {{ $tc }}" style="width:28px;height:28px;flex-shrink:0">
                    <span class="ico" data-ico="{{ $ico }}" style="width:15px;height:15px"></span>
                </span>
                <div>
                    <div style="font-weight:700;font-size:13.5px">{{ $activity->title }}</div>
                    @if($activity->description)
                    <p class="faint" style="font-size:12px">{{ $activity->description }}</p>
                    @endif
                    <div class="row" style="gap:8px;margin-top:3px">
                        <time class="faint" style="font-size:12px">{{ $activity->created_at->format('M d, Y · g:i A') }}</time>
                        @if($activity->admin)
                        <span class="faint" style="font-size:12px">· {{ $activity->admin->name }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- /left col --}}

<div class="col-gap">

    {{-- Customer --}}
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Customer</h3></div></div>
        <div class="row" style="gap:12px;margin-bottom:14px">
            <span class="avatar" style="width:46px;height:46px;font-size:17px;background:{{ $avatarColor }}">{{ $initials }}</span>
            <div>
                <div style="font-weight:700;font-size:14.5px">{{ $order->customer_name }}</div>
                @if($order->customer_email)
                <div class="faint" style="font-size:12.5px">{{ $order->customer_email }}</div>
                @endif
                @if($order->customer_phone)
                <div class="faint" style="font-size:12.5px">{{ $order->customer_phone }}</div>
                @endif
            </div>
        </div>
        @if($order->user)
        <div class="row" style="gap:8px;flex-wrap:wrap">
            <span class="pill sm teal">Registered</span>
            <span class="faint" style="font-size:12.5px">{{ $order->user->orders()->count() }} orders</span>
            <a href="{{ route('admin.users.show', $order->user) }}" class="link-btn" style="font-size:12.5px;margin-left:auto">View account</a>
        </div>
        @else
        <span class="pill sm" style="font-size:11px">Guest</span>
        @endif
    </div>

    {{-- Fraud check --}}
    @if($order->customer_phone)
    @php
    $fraudSeed = $fraudCache ? [
        'risk_level' => $fraudCache->risk_level,
        'risk_score' => $fraudCache->risk_score,
        'couriers'   => $fraudCache->couriers ?? [],
        'summary'    => $fraudCache->summary  ?? null,
        'reports'    => $fraudCache->reports  ?? [],
        'provider'   => $fraudCache->provider,
        'from_cache' => true,
        'cached_at'  => $fraudCache->updated_at->toIso8601String(),
        'expires_at' => $fraudCache->expires_at->toIso8601String(),
    ] : null;
    @endphp
    {{-- Seed in <script> so JSON double-quotes don't break the HTML attribute --}}
    <script>window.__fraudSeed = @json($fraudSeed);</script>
    <div class="card pad" x-data="fraudCheck('{{ $order->customer_phone }}', window.__fraudSeed)">
        {{-- Header --}}
        <div class="card-head" style="margin-bottom:0">
            <span class="tile sm t-warning"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
            <div class="ct">
                <h3>Fraud Check</h3>
                <div class="sub">{{ $order->customer_phone }}</div>
            </div>
            <div class="row" style="gap:8px;align-items:center">
                <template x-if="checked && from_cache">
                    <span class="pill sm muted" style="font-size:10px">Cached</span>
                </template>
                <button @click="run(false)" :disabled="loading" class="link-btn head-action" x-show="!checked && !loading">Check</button>
                <button @click="run(true)"  :disabled="loading" class="link-btn head-action" x-show="checked">Re-check</button>
            </div>
        </div>

        <template x-if="loading">
            <p class="faint" style="font-size:12.5px;margin-top:10px">Fetching courier data…</p>
        </template>

        <template x-if="!checked && !loading && !error">
            <p class="faint" style="font-size:12.5px;margin-top:10px">Check courier delivery history for this number.</p>
        </template>

        <template x-if="error">
            <div style="background:var(--danger-soft);border:1px solid color-mix(in srgb,var(--danger) 30%,transparent);border-radius:8px;padding:10px 14px;margin-top:10px">
                <span style="font-size:12.5px;color:var(--danger)" x-text="error"></span>
            </div>
        </template>

        <template x-if="sandbox && checked">
            <div style="background:var(--warning-soft);border:1px solid color-mix(in srgb,var(--warning) 30%,transparent);border-radius:8px;padding:10px 14px;margin-top:10px">
                <span style="font-size:12px;color:var(--warning-fg)">Sandbox mode — dummy data. Add live key in <a href="{{ route('admin.integrations.index') }}" class="link-btn" style="font-size:12px">Integrations</a>.</span>
            </div>
        </template>

        {{-- Risk Score Banner --}}
        <template x-if="checked && risk_level">
            <div style="margin-top:12px">
                {{-- Score card --}}
                <div :class="riskBg(risk_level)" style="border-radius:10px;padding:14px 16px;margin-bottom:12px;border:1px solid">
                    <div class="between" style="margin-bottom:8px">
                        <div>
                            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;opacity:.7" x-text="riskLabel(risk_level) + ' Customer'"></div>
                            <div style="font-size:22px;font-weight:800;line-height:1.1;margin-top:2px" x-text="riskLabel(risk_level)"></div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:11px;font-weight:600;opacity:.7">Score</div>
                            <div style="font-size:28px;font-weight:800;line-height:1" x-text="risk_score"></div>
                            <div style="font-size:10px;opacity:.6">/ 100</div>
                        </div>
                    </div>
                    {{-- Score bar --}}
                    <div style="background:rgba(0,0,0,.15);border-radius:100px;height:6px;overflow:hidden">
                        <div :style="'width:'+risk_score+'%;height:100%;background:currentColor;border-radius:100px;opacity:.8;transition:width .4s'"></div>
                    </div>
                    <template x-if="from_cache && cached_at">
                        <div style="font-size:10px;opacity:.6;margin-top:6px" x-text="'Cached ' + fmtDate(cached_at)"></div>
                    </template>
                </div>

                {{-- Fraud reports alert --}}
                <template x-if="reports.length > 0">
                    <div style="background:var(--danger-soft);border:1px solid color-mix(in srgb,var(--danger) 30%,transparent);border-radius:8px;padding:10px 14px;margin-bottom:10px">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--danger);margin-bottom:6px">
                            <span x-text="reports.length"></span> Fraud Report<span x-show="reports.length > 1">s</span> on file
                        </div>
                        <div class="stack" style="gap:6px">
                            <template x-for="r in reports" :key="r.id ?? r.name">
                                <div style="border-top:1px solid color-mix(in srgb,var(--danger) 20%,transparent);padding-top:6px">
                                    <div class="between">
                                        <span style="font-weight:700;font-size:12.5px;color:var(--danger-fg)" x-text="r.name"></span>
                                        <template x-if="r.courier">
                                            <span class="pill sm muted" style="font-size:10px" x-text="r.courier"></span>
                                        </template>
                                    </div>
                                    <div style="font-size:12px;color:var(--danger-fg);opacity:.85;margin-top:2px" x-text="r.details"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Summary stats (BDCourier) --}}
                <template x-if="summary && summary.total_parcel > 0">
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:10px">
                        <div style="text-align:center;background:var(--surface-2);border-radius:8px;padding:8px 4px">
                            <div class="faint" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px">Total</div>
                            <div style="font-weight:700;font-size:16px" x-text="summary.total_parcel"></div>
                        </div>
                        <div style="text-align:center;background:var(--surface-2);border-radius:8px;padding:8px 4px">
                            <div class="faint" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px">Success</div>
                            <div style="font-weight:700;font-size:16px;color:var(--success)" x-text="summary.success_parcel"></div>
                        </div>
                        <div style="text-align:center;background:var(--surface-2);border-radius:8px;padding:8px 4px">
                            <div class="faint" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px">Cancel</div>
                            <div style="font-weight:700;font-size:16px;color:var(--danger)" x-text="summary.cancelled_parcel"></div>
                        </div>
                        <div style="text-align:center;background:var(--surface-2);border-radius:8px;padding:8px 4px">
                            <div class="faint" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px">Rate</div>
                            <div style="font-weight:700;font-size:16px" x-text="summary.success_ratio + '%'"></div>
                        </div>
                    </div>
                </template>

                {{-- Per-courier rows (BDCourier) --}}
                <template x-if="couriers.length > 0 && provider === 'bdcourier'">
                    <div class="stack" style="gap:5px">
                        <template x-for="c in couriers" :key="c.name">
                            <div class="card pad" style="padding:8px 12px">
                                <div class="between" style="margin-bottom:4px">
                                    <div class="row" style="gap:7px;align-items:center">
                                        <template x-if="c.logo">
                                            <img :src="c.logo" :alt="c.name" style="height:16px;width:auto;object-fit:contain">
                                        </template>
                                        <span style="font-weight:700;font-size:12.5px" x-text="c.name"></span>
                                    </div>
                                    <span class="pill sm" :class="c.ratio_color" x-text="c.success_ratio + '%'"></span>
                                </div>
                                <div class="row" style="gap:14px;font-size:11.5px;color:var(--text-muted)">
                                    <span><span class="faint">Total </span><span x-text="c.total_parcel"></span></span>
                                    <span><span class="faint">✓ </span><span style="color:var(--success)" x-text="c.success_parcel"></span></span>
                                    <span><span class="faint">✗ </span><span style="color:var(--danger)" x-text="c.cancelled_parcel"></span></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Per-courier rows (FraudBD) --}}
                <template x-if="couriers.length > 0 && provider !== 'bdcourier'">
                    <div class="stack" style="gap:5px">
                        <template x-for="c in couriers" :key="c.name">
                            <div class="card pad" style="padding:8px 12px">
                                <div class="between">
                                    <span style="font-weight:700;font-size:12.5px;text-transform:capitalize" x-text="c.name"></span>
                                    <template x-if="c.risk_level">
                                        <span class="pill sm" x-text="c.risk_level.replace('_',' ')"
                                            :class="{'danger':c.risk_color==='red','warning':c.risk_color==='orange'||c.risk_color==='yellow','success':c.risk_color==='green'}"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="couriers.length === 0 && (!summary || summary.total_parcel === 0)">
                    <p class="faint" style="font-size:12px">No courier history found for this number.</p>
                </template>
            </div>
        </template>
    </div>
    @endif

    {{-- Update order --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-info"><span class="ico" data-ico="edit" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Update order</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
            @csrf @method('PATCH')
            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Status</span>
                <select class="input" name="status">
                    @foreach(\App\Models\Order::STATUSES as $s)
                    <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Payment status</span>
                <select class="input" name="payment_status">
                    @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                    <option value="{{ $s }}" {{ $order->payment_status === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Payment method</span>
                <input class="input" type="text" name="payment_method" value="{{ $order->payment_method }}" placeholder="bkash, nagad, cod…">
            </div>
            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Tracking number</span>
                <input class="input" type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="e.g. 1Z999AA1…">
            </div>
            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Customer note</span>
                <textarea class="input" name="notes" rows="2" placeholder="Visible to customer…" style="height:auto;resize:vertical">{{ $order->notes }}</textarea>
            </div>
            <div class="field" style="margin-bottom:16px">
                <span class="lbl">Admin note</span>
                <textarea class="input" name="admin_note" rows="2" placeholder="Internal only…" style="height:auto;resize:vertical;background:var(--warning-soft)">{{ $order->admin_note }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Save changes</button>
        </form>
    </div>

    {{-- Courier dispatch --}}
    @php $hasCourier = app(\App\Services\CourierService::class)->hasDefault(); @endphp
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="truck" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Courier dispatch</h3></div>
        </div>

        @if($order->consignment_id)
        <div class="card pad" style="background:var(--success-soft);border-color:color-mix(in srgb,var(--success) 30%,transparent);padding:12px 14px;margin-bottom:12px">
            <div class="between" style="margin-bottom:4px">
                <span style="font-weight:700;font-size:13px;color:var(--success-fg)">Dispatched via {{ $order->courier_provider }}</span>
                <form method="POST" action="{{ route('admin.orders.refresh-courier-status', $order) }}">
                    @csrf
                    <button type="submit" class="link-btn" style="font-size:12px">Refresh</button>
                </form>
            </div>
            <div class="mono faint" style="font-size:12px">{{ $order->consignment_id }}</div>
            @if($order->courier_status)
            <div class="faint" style="font-size:12px;margin-top:4px">Status: <b>{{ $order->courier_status }}</b></div>
            @endif
        </div>
        @endif

        @if(!$hasCourier)
        <p class="faint" style="font-size:13px">No default courier configured. <a href="{{ route('admin.integrations.index') }}" class="link-btn" style="font-size:13px">Set one up</a></p>
        @elseif(!$order->consignment_id)
        <form method="POST" action="{{ route('admin.orders.dispatch-courier', $order) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
                <div class="field"><span class="lbl" style="font-size:12px">Weight (kg)</span><input class="input" type="number" name="item_weight" step="0.1" min="0.1" value="0.5"></div>
                <div class="field"><span class="lbl" style="font-size:12px">COD amount ({{ currency_symbol() }})</span><input class="input" type="number" name="cod_amount" step="0.01" value="{{ $order->total }}"></div>
            </div>
            <div class="field" style="margin-bottom:14px">
                <span class="lbl" style="font-size:12px">Delivery area</span>
                <input class="input" type="text" name="area" value="{{ $order->shipping_address['city'] ?? '' }}" placeholder="e.g. Dhaka, Mirpur">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Push to courier</button>
        </form>
        @endif
    </div>

    {{-- Payment info --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-teal"><span class="ico" data-ico="card" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Payment</h3></div>
        </div>
        <div class="row" style="gap:11px;margin-bottom:12px">
            <span class="tile sm muted"><span class="ico" data-ico="card" style="width:17px;height:17px"></span></span>
            <div>
                <div style="font-weight:700;font-size:13.5px">{{ $order->payment_method ?? 'Not specified' }}</div>
                <div class="faint" style="font-size:12px">{{ $order->paid_at ? 'Paid ' . $order->paid_at->format('M d, Y') : 'Not paid yet' }}</div>
            </div>
        </div>
        @if($order->coupon_code)
        <div class="between" style="font-size:13px">
            <span class="faint">Coupon</span>
            <span class="pill sm success mono">{{ $order->coupon_code }}</span>
        </div>
        @endif
        <div class="between" style="font-size:13px;margin-top:8px">
            <span class="faint">Source</span>
            <span class="pill sm {{ $sourcePill }}">{{ \App\Models\Order::SOURCES[$order->source] ?? ucfirst($order->source) }}</span>
        </div>
        <div class="between" style="font-size:13px;margin-top:8px">
            <span class="faint">Placed</span>
            <span>{{ $order->created_at->format('M d, Y g:i A') }}</span>
        </div>
    </div>

</div>{{-- /right col --}}
</div>

@push('styles')
<style>
.risk-safe    { background:var(--success-soft); border-color:color-mix(in srgb,var(--success) 35%,transparent); color:var(--success-fg); }
.risk-low     { background:color-mix(in srgb,var(--info) 12%,transparent); border-color:color-mix(in srgb,var(--info) 35%,transparent); color:var(--info,#0ea5e9); }
.risk-mid     { background:var(--warning-soft); border-color:color-mix(in srgb,var(--warning) 35%,transparent); color:var(--warning-fg); }
.risk-high    { background:var(--danger-soft);  border-color:color-mix(in srgb,var(--danger)  35%,transparent); color:var(--danger-fg); }
.risk-unknown { background:var(--surface-2);    border-color:var(--border); color:var(--text-muted); }
</style>
@endpush

@push('scripts')
<script>
function fraudCheck(phone, seed = null) {
    return {
        phone,
        loading:    false,
        checked:    seed !== null,
        error:      null,
        sandbox:    false,
        provider:   seed?.provider   ?? null,
        risk_level: seed?.risk_level ?? null,
        risk_score: seed?.risk_score ?? 0,
        couriers:   seed?.couriers   ?? [],
        summary:    seed?.summary    ?? null,
        reports:    seed?.reports    ?? [],
        from_cache: seed?.from_cache ?? false,
        cached_at:  seed?.cached_at  ?? null,
        expires_at: seed?.expires_at ?? null,

        async run(force = false) {
            this.loading  = true;
            this.error    = null;

            if (force) {
                this.checked    = false;
                this.risk_level = null;
                this.couriers   = [];
                this.summary    = null;
                this.reports    = [];
            }

            try {
                const res = await fetch('{{ route('admin.fraud.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone: this.phone, force: force ? 1 : 0 }),
                });

                const json = await res.json();

                if (!json.success) {
                    this.error = json.message || 'Unexpected error.';
                } else {
                    const d = json.data;
                    this.provider   = d.provider   ?? null;
                    this.risk_level = d.risk_level  ?? null;
                    this.risk_score = d.risk_score  ?? 0;
                    this.couriers   = d.couriers    ?? [];
                    this.summary    = d.summary     ?? null;
                    this.reports    = d.reports     ?? [];
                    this.sandbox    = d.sandbox     ?? false;
                    this.from_cache = d.from_cache  ?? false;
                    this.cached_at  = d.cached_at   ?? null;
                    this.expires_at = d.expires_at  ?? null;
                    this.checked    = true;
                }
            } catch (e) {
                this.error = 'Network error — could not reach fraud check service.';
            } finally {
                this.loading = false;
            }
        },

        riskLabel(level) {
            return { safe: 'Safe', low_risk: 'Low Risk', mid_risk: 'Mid Risk', high_risk: 'High Risk', unknown: 'Unknown' }[level] ?? 'Unknown';
        },

        riskBg(level) {
            return {
                safe:      'risk-safe',
                low_risk:  'risk-low',
                mid_risk:  'risk-mid',
                high_risk: 'risk-high',
                unknown:   'risk-unknown',
            }[level] ?? 'risk-unknown';
        },

        fmtDate(iso) {
            if (!iso) return '';
            const d = new Date(iso);
            return d.toLocaleDateString('en-GB', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' });
        },
    };
}
</script>
@endpush

@endsection
