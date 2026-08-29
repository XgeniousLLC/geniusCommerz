<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Slip — #{{ $order->order_number }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 13px;
        color: #111;
        background: #f5f5f5;
        display: flex;
        justify-content: center;
        padding: 24px 16px;
    }

    .slip {
        background: #fff;
        width: 100%;
        max-width: 420px;
        padding: 28px 24px;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,.1);
    }

    /* ── Header ── */
    .brand { text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px dashed #e0e0e0; }
    .brand-name { font-size: 22px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; }
    .brand-tagline { font-size: 11px; color: #888; margin-top: 2px; }
    .brand-contact { font-size: 11px; color: #555; margin-top: 8px; line-height: 1.8; }
    .order-meta { font-size: 11px; color: #666; margin-top: 8px; line-height: 1.7; }
    .order-meta strong { color: #111; }

    /* ── Sections ── */
    .section { margin: 16px 0; padding-bottom: 14px; border-bottom: 1px dashed #e0e0e0; }
    .section:last-child { border-bottom: none; }
    .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #888; margin-bottom: 8px; }

    /* ── Customer ── */
    .customer-name { font-weight: 700; font-size: 14px; margin-bottom: 3px; }
    .customer-info { color: #444; line-height: 1.8; font-size: 12px; }

    /* ── Items ── */
    table { width: 100%; border-collapse: collapse; }
    thead th {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .6px; color: #888; padding: 4px 0;
        border-bottom: 1px solid #ddd;
    }
    thead th:last-child, tbody td:last-child { text-align: right; }
    tbody td { padding: 7px 0; border-bottom: 1px solid #f0f0f0; vertical-align: top; font-size: 12px; }
    tbody tr:last-child td { border-bottom: none; }
    .item-name { font-weight: 600; }
    .item-sku { font-size: 11px; color: #888; }
    .qty-col { text-align: center; width: 36px; color: #555; }
    .price-col { text-align: right; white-space: nowrap; }

    /* ── Totals ── */
    .totals { width: 100%; }
    .totals td { padding: 3px 0; font-size: 12px; }
    .totals td:last-child { text-align: right; }
    .totals .discount { color: #16a34a; }
    .totals .grand-total td { font-size: 15px; font-weight: 800; padding-top: 8px; border-top: 2px solid #111; }

    /* ── Notes ── */
    .note-box {
        background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px;
        padding: 8px 10px; font-size: 12px; color: #444; line-height: 1.5;
        white-space: pre-wrap;
    }

    /* ── Status badges ── */
    .badges { display: flex; flex-wrap: wrap; gap: 6px; }
    .badge {
        display: inline-block; padding: 2px 8px; border-radius: 4px;
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    }
    .badge-gray   { background: #f0f0f0; color: #555; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }

    /* ── Footer ── */
    .footer { text-align: center; font-size: 11px; color: #aaa; margin-top: 20px; padding-top: 14px; border-top: 1px dashed #e0e0e0; }

    /* ── Print actions (screen only) ── */
    .print-actions { text-align: center; margin-top: 20px; display: flex; gap: 10px; justify-content: center; }
    .btn { padding: 9px 22px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-block; }
    .btn-primary { background: #2563eb; color: #fff; }
    .btn-primary:hover { background: #1d4ed8; }
    .btn-ghost { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
    .btn-ghost:hover { background: #e5e7eb; }

    @media print {
        body { background: #fff; padding: 0; }
        .slip { box-shadow: none; border-radius: 0; max-width: 100%; padding: 16px; }
        .print-actions { display: none !important; }
    }
</style>
</head>
<body>
<div class="slip">

    {{-- ── Brand header ── --}}
    <div class="brand">
        <div class="brand-name">{{ $site['name'] }}</div>
        @if($site['tagline'])
            <div class="brand-tagline">{{ $site['tagline'] }}</div>
        @endif
        @if($site['phone'] || $site['email'] || $site['address'] || $site['tax_number'])
        <div class="brand-contact">
            @if($site['phone'])<div>Tel: {{ $site['phone'] }}</div>@endif
            @if($site['email'])<div>{{ $site['email'] }}</div>@endif
            @if($site['address'])<div>{{ $site['address'] }}</div>@endif
            @if($site['tax_number'])<div>VAT/Tax: {{ $site['tax_number'] }}</div>@endif
        </div>
        @endif
        <div class="order-meta">
            <strong>{{ $site['invoice_prefix'] ? 'Invoice #' . $site['invoice_prefix'] . $order->order_number : 'Order #' . $order->order_number }}</strong><br>
            {{ $order->created_at->format('d M Y, g:i A') }}
        </div>
    </div>

    {{-- ── Status ── --}}
    <div class="section">
        <div class="section-title">Status</div>
        <div class="badges">
            <span class="badge badge-gray">{{ ucfirst($order->status) }}</span>
            <span class="badge {{ $order->payment_status === 'paid' ? 'badge-green' : 'badge-yellow' }}">
                {{ ucwords(str_replace('_', ' ', $order->payment_status)) }}
            </span>
            @if($order->payment_method)
            <span class="badge badge-blue">{{ $order->payment_method }}</span>
            @endif
            @if($order->tracking_number)
            <span class="badge badge-gray">Tracking: {{ $order->tracking_number }}</span>
            @endif
        </div>
    </div>

    {{-- ── Customer ── --}}
    <div class="section">
        <div class="section-title">Customer</div>
        <div class="customer-name">{{ $order->customer_name }}</div>
        <div class="customer-info">
            @if($order->customer_phone)<div>Tel: {{ $order->customer_phone }}</div>@endif
            @if($order->customer_email)<div>{{ $order->customer_email }}</div>@endif
        </div>
    </div>

    {{-- ── Items ── --}}
    <div class="section">
        <div class="section-title">Items</div>
        <table>
            <thead>
                <tr>
                    <th style="text-align:left">Product</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        @if($item->sku)<div class="item-sku">SKU: {{ $item->sku }}</div>@endif
                        @if($item->variant_label)<div class="item-sku">{{ $item->variant_label }}</div>@endif
                    </td>
                    <td class="qty-col">{{ $item->quantity }}</td>
                    <td class="price-col">{{ $site['currency_symbol'] }}{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Totals ── --}}
    <div class="section">
        <table class="totals">
            <tr>
                <td style="color:#555">Subtotal</td>
                <td>{{ $site['currency_symbol'] }}{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->shipping_cost > 0)
            <tr>
                <td style="color:#555">Shipping</td>
                <td>{{ $site['currency_symbol'] }}{{ number_format($order->shipping_cost, 2) }}</td>
            </tr>
            @endif
            @if($order->discount_amount > 0)
            <tr class="discount">
                <td>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</td>
                <td>-{{ $site['currency_symbol'] }}{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            @endif
            {{-- Read from the order, never recomputed: a recomputed figure drifts from
                 what was actually charged as soon as a rate changes. --}}
            @forelse($order->tax_breakdown ?? [] as $line)
            <tr>
                <td style="color:#555">
                    {{ $line['name'] }} ({{ rtrim(rtrim(number_format($line['rate'], 2), '0'), '.') }}%{{ $order->prices_include_tax ? ' incl.' : '' }})
                </td>
                <td>{{ $site['currency_symbol'] }}{{ number_format($line['amount'], 2) }}</td>
            </tr>
            @empty
                @if((float) $order->tax > 0)
                <tr>
                    <td style="color:#555">Tax{{ $order->prices_include_tax ? ' (incl.)' : '' }}</td>
                    <td>{{ $site['currency_symbol'] }}{{ number_format($order->tax, 2) }}</td>
                </tr>
                @endif
            @endforelse
            <tr class="grand-total">
                <td>Total</td>
                <td>{{ $site['currency_symbol'] }}{{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Order note ── --}}
    @if($order->notes)
    <div class="section">
        <div class="section-title">Order Note</div>
        <div class="note-box">{{ $order->notes }}</div>
    </div>
    @endif

    {{-- ── Invoice additional note ── --}}
    @if($site['invoice_note'])
    <div class="section">
        <div class="section-title">Note</div>
        <div class="note-box">{{ $site['invoice_note'] }}</div>
    </div>
    @endif

    {{-- ── Invoice footer (from Accounting settings) ── --}}
    @if($site['invoice_footer'])
    <div class="section">
        <div class="section-title">Terms</div>
        <div class="note-box">{{ $site['invoice_footer'] }}</div>
    </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="footer">
        Thank you for your order!<br>
        {{ $site['name'] }} · {{ now()->format('d M Y') }}
    </div>

    {{-- ── Print / Close buttons (screen only) ── --}}
    <div class="print-actions">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
        <button class="btn btn-ghost" onclick="window.close()">Close</button>
    </div>

</div>

<script>
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
</script>
</body>
</html>
