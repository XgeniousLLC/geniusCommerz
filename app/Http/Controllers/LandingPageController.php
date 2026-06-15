<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    private function confirmedOrder(): ?array
    {
        $orderNumber = session('lp_confirmed_order');
        if (! $orderNumber) return null;

        $order = \App\Models\Order::with('items')
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) return null;

        return [
            'order_number'   => $order->order_number,
            'customer_name'  => $order->customer_name,
            'total'          => (float) $order->total,
            'payment_method' => $order->payment_method,
            'items'          => $order->items->map(fn ($i) => [
                'product_name'  => $i->product_name,
                'variant_label' => $i->variant_label,
                'quantity'      => $i->quantity,
                'unit_price'    => (float) $i->unit_price,
                'total'         => (float) $i->total,
            ])->all(),
        ];
    }

    public function show(Product $product): Response
    {
        if ($product->status !== 'active') {
            abort(404);
        }

        $product->load([
            'images',
            'variants.variantValues.attributeValue.attribute',
            'variants.imageMedia',
        ]);

        $variants = $product->variants
            ->where('is_active', true)
            ->filter(fn ($v) => $v->stock_qty === null || $v->stock_qty > 0)
            ->map(fn ($v) => [
            'id'               => $v->id,
            'price'            => (float) $v->price,
            'compare_at_price' => $v->compare_at_price ? (float) $v->compare_at_price : null,
            'sku'              => $v->sku,
            'stock_qty'        => $v->stock_qty,
            'in_stock'         => $v->stock_qty === null || $v->stock_qty > 0,
            'label'            => $v->label(),
            'image'            => $v->imageMedia?->getUrl() ?? ($v->image ? Storage::url($v->image) : null),
        ])->values();

        // Payment methods
        $allMethods = [
            'cod'        => ['label' => 'Cash on Delivery', 'key' => 'payment.cod_enabled'],
            'bkash'      => ['label' => 'bKash',            'key' => 'payment.bkash_enabled'],
            'nagad'      => ['label' => 'Nagad',            'key' => 'payment.nagad_enabled'],
            'rocket'     => ['label' => 'Rocket',           'key' => 'payment.rocket_enabled'],
            'sslcommerz' => ['label' => 'Card / Net Banking','key' => 'payment.sslcommerz_enabled'],
        ];
        $paymentMethods = [];
        foreach ($allMethods as $value => $meta) {
            if (SiteSetting::get($meta['key'])) {
                $paymentMethods[$value] = $meta['label'];
            }
        }
        if (empty($paymentMethods)) {
            $paymentMethods['cod'] = 'Cash on Delivery';
        }

        // Abort if no orderable items exist
        $noVariantInStock = ! $product->has_variants && ($product->stock_qty !== null && $product->stock_qty <= 0);
        if ($noVariantInStock || ($product->has_variants && $variants->isEmpty())) {
            abort(404);
        }

        // Prefill from auth user
        $prefill = null;
        if (auth()->check()) {
            $user           = auth()->user();
            $defaultAddress = $user->addresses()->where('is_default', true)->first();
            $prefill = [
                'name'    => $user->name ?? '',
                'email'   => $user->email ?? '',
                'phone'   => $user->phone ?? '',
                'address' => $defaultAddress?->address ?? '',
                'city'    => $defaultAddress?->city ?? '',
            ];
        }

        return Inertia::render('LandingPage', [
            'product' => [
                'id'               => $product->id,
                'name'             => $product->name,
                'slug'             => $product->slug,
                'short_description'=> $product->short_description,
                'price'            => (float) $product->price,
                'compare_at_price' => $product->compare_at_price ? (float) $product->compare_at_price : null,
                'has_variants'     => $product->has_variants,
                'stock_qty'        => ! $product->has_variants ? $product->stock_qty : null,
                'in_stock'         => $product->has_variants
                    ? $product->variants->where('is_active', true)->contains(fn ($v) => $v->stock_qty === null || $v->stock_qty > 0)
                    : ($product->stock_qty === null || $product->stock_qty > 0),
                'shipping_included'=> (bool) $product->shipping_included,
                'images'           => $product->images->map(fn ($m) => [
                    'url'   => $m->getUrl(),
                    'thumb' => $m->getUrl('thumb'),
                ]),
                'variants'         => $variants,
            ],
            'paymentMethods'  => $paymentMethods,
            'prefill'         => $prefill,
            'confirmedOrder'  => $this->confirmedOrder(),
        ]);
    }
}
