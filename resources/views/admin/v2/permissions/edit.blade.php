@extends('layouts.app')

@section('title', 'Editar permiso')

@section('content')
    <livewire:permission-form :permission-id="$permission->id" />
@endsection
