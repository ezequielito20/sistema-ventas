@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <livewire:user-form :user-id="$user->id" />
@endsection
