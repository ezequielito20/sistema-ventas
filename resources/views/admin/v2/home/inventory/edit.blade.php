@extends('layouts.app')

@section('title', 'Editar producto')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    @livewire('home.home-product-form', ['productId' => $productId])
</div>
@endsection
