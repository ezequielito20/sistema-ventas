@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
    <livewire:customer-form :customer-id="$customer->id" />
@endsection
