@extends('layouts.app')

@section('title', 'Editar producto')

@section('content')
    <livewire:product-form :product-id="$product->id" />
@endsection
