@extends('layouts.catalog')

@section('title', __('Pedido') . ' — ' . $company->name)

@section('content')
    <div class="pt-20 sm:pt-24">
        @livewire('catalog-checkout', ['companyId' => $company->id], key('catalog-checkout-' . $company->id))
    </div>
@endsection
