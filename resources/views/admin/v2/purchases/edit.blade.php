@extends('layouts.app')

@section('title', 'Editar compra')

@section('content')
    <livewire:purchase-form :purchase-id="$purchaseId" />
@endsection
