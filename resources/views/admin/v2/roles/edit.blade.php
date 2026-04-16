@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
    <livewire:role-form :role-id="$role->id" />
@endsection
