@extends('layouts.app')

@section('title', 'Servicios del Hogar')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    @livewire('home.home-services-index')
</div>
@endsection
