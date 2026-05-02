@extends('layouts.app')

@section('title', 'Editar Caja')

@section('content')
    <livewire:cash-count-form :cashCountId="$cashCountId" />
@endsection
