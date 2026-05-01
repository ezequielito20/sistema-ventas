@extends('layouts.app')

@section('title', 'Editar venta')

@section('content')
    <livewire:sale-form :sale-id="$saleId" />
@endsection