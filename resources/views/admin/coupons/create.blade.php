@extends('admin.layouts.admin')
@section('title', 'Add Coupon')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Add Coupon</h2>
        <div class="sub">Create a new discount code</div>
    </div>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back to Coupons
    </a>
</div>

<div style="max-width:680px">
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="col-gap" style="--gap:18px">
        @csrf
        @include('admin.coupons._form')
        <div class="row" style="gap:12px">
            <button type="submit" class="btn btn-primary">Create Coupon</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
