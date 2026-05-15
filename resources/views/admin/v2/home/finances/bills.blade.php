@extends('layouts.app')

@section('title', 'Facturas de Servicios')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    @livewire('home.home-service-bills-index')
</div>
@endsection
