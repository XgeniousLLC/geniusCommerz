@extends('admin.layouts.admin')

@section('title', 'Edit Coupon — ' . $coupon->code)

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.coupons.index') }}" class="hover:text-gray-700">Coupons</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">{{ $coupon->code }}</li>
</ol>
@endsection

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">Edit Coupon</h1>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-6">
        @csrf @method('PUT')
        @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif
        @include('admin.coupons._form')
        <div class="flex items-center gap-3">
            <x-admin.button type="submit">Save Changes</x-admin.button>
            <a href="{{ route('admin.coupons.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>
</div>
@endsection
