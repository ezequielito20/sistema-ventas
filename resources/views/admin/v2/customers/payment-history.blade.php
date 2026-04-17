@extends('layouts.app')

@section('title', 'Historial de Pagos')

@push('js')
    <script>
        window.paymentHistoryData = {
            payments: @json($payments->items()),
            currency: { symbol: '{{ $currency->symbol }}' },
            totalRemainingDebt: {{ $totalRemainingDebt }},
            charts: {
                weekdayLabels: @json($weekdayLabels),
                weekdayData: @json($weekdayData),
                monthlyLabels: @json($monthlyLabels),
                monthlyData: @json($monthlyData),
            },
        };
    </script>
    <script src="{{ asset('js/admin/customers/payment-history.js') }}" defer></script>
@endpush

@section('content')
    <div x-data="paymentHistory({ searchTerm: '{{ request('customer_search') }}' })" class="space-y-6">
        @include('admin.v2.customers.payment-history.partials.header')
        @include('admin.v2.customers.payment-history.partials.stats')
        @include('admin.v2.customers.payment-history.partials.filters')
        @include('admin.v2.customers.payment-history.partials.list', ['payments' => $payments, 'currency' => $currency])
        @include('admin.v2.customers.payment-history.partials.charts')
    </div>
@endsection
