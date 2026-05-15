@extends('layouts.app')

@section('title', 'Métodos de pago del catálogo')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @livewire('catalog-payment-methods-index')
    </div>
@endsection
