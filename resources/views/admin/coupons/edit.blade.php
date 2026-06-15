@extends('admin.layouts.admin')
@section('title', 'Edit Coupon — ' . $coupon->code)
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Edit Coupon</h2>
        <div class="sub mono" style="font-size:14px;font-weight:700">{{ $coupon->code }}</div>
    </div>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back to Coupons
    </a>
</div>

<div style="max-width:680px">
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="col-gap" style="--gap:18px">
        @csrf @method('PUT')
        @include('admin.coupons._form')
        <div class="row" style="gap:12px">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
