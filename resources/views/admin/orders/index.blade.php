@extends('adminlte::page')

@section('title', 'Gestión de Pedidos')

@section('content')
    @livewire('orders-table')
@stop

@section('css')
    
    <style>
        /* Estilos personalizados para AdminLTE + Tailwind */
        .content-wrapper {
            background-color: #f9fafb !important;
        }
        
        .main-header {
            background-color: #1f2937 !important;
        }
        
        .main-sidebar {
            background-color: #111827 !important;
        }
        
        /* Asegurar que Tailwind no interfiera con AdminLTE */
        .content-header {
            background-color: transparent !important;
            padding: 0 !important;
        }
        
        .content-header h1 {
            display: none !important;
        }
        
        /* Ocultar breadcrumbs de AdminLTE */
        .breadcrumb {
            display: none !important;
        }
    </style>
@stop