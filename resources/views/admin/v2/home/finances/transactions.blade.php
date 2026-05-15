@extends('layouts.app')

@section('title', 'Transacciones')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    @livewire('home.home-transactions-index')
</div>
@endsection
