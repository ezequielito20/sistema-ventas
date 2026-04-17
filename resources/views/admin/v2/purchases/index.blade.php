@extends('layouts.app')

@section('title', 'Compras')

@push('scripts-before-app')
    <script>
        window.purchasesV2Config = {
            routes: {
                index: @js(route('admin.purchases.v2.index')),
                details: @js(url('/purchases')),
                destroy: @js(url('/purchases/delete')),
            },
            currencySymbol: @js($currency->symbol ?? '$'),
        };
        window.purchasesV2Data = {
            pagePurchaseIds: @js($purchases->pluck('id')->values()),
        };
    </script>
    <script src="{{ asset('js/admin/purchases/v2-index.js') }}?v={{ filemtime(public_path('js/admin/purchases/v2-index.js')) }}"></script>
@endpush

@section('content')
    <div
        class="space-y-6"
        x-data="purchasesV2({
            search: @js(request('search', '')),
            productId: @js(request('product_id', '')),
            paymentStatus: @js(request('payment_status', '')),
            dateFrom: @js(request('date_from', '')),
            dateTo: @js(request('date_to', '')),
            products: @js($products),
            canDestroy: @js($permissions['can_destroy'] ?? false),
        })"
    >
        @include('admin.v2.purchases.partials.header')
        @include('admin.v2.purchases.partials.stats')
        @include('admin.v2.purchases.partials.filters')
        @include('admin.v2.purchases.partials.list')
        @include('admin.v2.purchases.partials.details-modal')
    </div>
@endsection
