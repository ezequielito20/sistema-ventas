@extends('layouts.app')

@section('title', 'Pedidos desde catálogo')

@section('content')
    @can('orders.index')
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-slate-100">Pedidos</h1>
            <p class="mt-1 text-sm text-slate-400">Pedidos realizados por clientes desde el catálogo público.</p>
            <div class="mt-6">
                @livewire('orders-table')
            </div>
        </div>
    @else
        <div class="p-8 text-center text-slate-400">No tenés permiso para ver pedidos.</div>
    @endcan
@endsection
