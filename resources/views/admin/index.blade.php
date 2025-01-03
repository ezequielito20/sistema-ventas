@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Panel de <b>{{ $company->name }}</b></h1>
@stop

@section('content')
    <p> </p>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    @if (($message = Session::get('message')) && ($icons = Session::get('icons')))
            <script>
                Swal.fire({
                    position: "center",
                    icon: "{{ $icons }}",
                    title: "{{ $message }}",
                    showConfirmButton: false,
                    timer: 2500
                });
            </script>
        @endif
@stop