@extends('layouts.app')

@section('title', 'Nuevo método de pago')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <livewire:catalog-payment-method-form />
    </div>
@endsection
