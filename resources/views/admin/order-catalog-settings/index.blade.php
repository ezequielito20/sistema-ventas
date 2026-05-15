@extends('layouts.app')

@section('title', 'Checkout catálogo — pago y entrega')

@section('content')
    @php
        $tenantMayConfigureOrders = app(\App\Services\PlanEntitlementService::class)->tenantUserMayConfigureOrdersConsole(auth()->user());
    @endphp
    @if ($tenantMayConfigureOrders)
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-slate-100">Checkout del catálogo</h1>
            <p class="mt-1 text-sm text-slate-400">Métodos de pago, entrega, zonas y franjas que ve el cliente al cerrar el pedido.</p>
            <div class="mt-6">
                @livewire('order-catalog-settings')
            </div>
        </div>
    @else
        <div class="p-8 text-center text-slate-400">No tenés permiso para configurar el checkout del catálogo.</div>
    @endif
@endsection
