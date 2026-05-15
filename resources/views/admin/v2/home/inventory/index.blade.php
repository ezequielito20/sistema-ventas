@extends('layouts.app')

@section('title', 'Inventario del Hogar')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    @livewire('home.home-products-index')
</div>
@endsection
