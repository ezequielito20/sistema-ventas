@extends('layouts.app')

@section('title', 'Editar categoría')

@section('content')
    <livewire:category-form :category-id="$category->id" />
@endsection
