@extends('admin.layouts.admin')

@section('title', 'Products')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Products</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Products</h1>
            <p class="text-gray-600 mt-1">{{ $products->total() }} total</p>
        </div>
        <x-admin.button href="{{ route('admin.products.create') }}">Add Product</x-admin.button>
    </div>
@endsection

@section('content')
{{-- Filters --}}
<x-admin.card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <x-admin.input type="text" name="search" value="{{ request('search') }}" placeholder="Name or SKU…" />
        </div>
        <div class="w-36">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <x-admin.select name="status">
                <option value="">All statuses</option>
                @foreach(['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'] as $v => $l)
                    <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </x-admin.select>
        </div>
        <div class="w-44">
            <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
            <x-admin.select name="brand_id">
                <option value="">All brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </x-admin.select>
        </div>
        <div class="w-44">
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <x-admin.select name="category_id">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @foreach($cat->children as $child)
                        <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>
                            &nbsp;&nbsp;— {{ $child->name }}
                        </option>
                    @endforeach
                @endforeach
            </x-admin.select>
        </div>
        <x-admin.button type="submit">Filter</x-admin.button>
        @if(request()->anyFilled(['search','status','brand_id','category_id','featured']))
            <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Clear</a>
        @endif
    </form>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">SKU / Variants</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @php $thumb = $product->images->first(); @endphp
                            @if($thumb)
                                <img src="{{ $thumb->getUrl('thumb') }}" alt="{{ $thumb->alt ?: $product->name }}"
                                     class="w-10 h-10 rounded-lg object-cover border border-gray-200 shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $product->brand?->name }}
                                    @if($product->categories->isNotEmpty())
                                        · {{ $product->categories->pluck('name')->join(', ') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">
                        @if($product->has_variants)
                            <span class="text-purple-600">{{ $product->variants_count }} variant(s)</span>
                        @else
                            {{ $product->sku ?? '—' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $product->displayPrice() }}</td>
                    <td class="px-4 py-3">
                        @php
                            $badge = match($product->status) {
                                'active'   => 'bg-green-100 text-green-800',
                                'archived' => 'bg-red-100 text-red-700',
                                default    => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                            {{ ucfirst($product->status) }}
                        </span>
                        @if($product->is_featured)
                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Featured</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline"
                              onsubmit="return confirm('Delete {{ addslashes($product->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="mt-4 px-4">{{ $products->links() }}</div>
    @endif
</x-admin.card>
@endsection
