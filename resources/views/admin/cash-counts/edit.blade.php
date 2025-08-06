@extends('adminlte::page')

@section('title', 'Editar Caja')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Editar Caja #{{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}</h1>
        <a href="{{ route('admin.cash-counts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.cash-counts.update', $cashCount->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Información General --}}
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Información General</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="opening_date">Fecha de Apertura</label>
                                    <input type="datetime-local"
                                        class="form-control @error('opening_date') is-invalid @enderror" id="opening_date"
                                        name="opening_date"
                                        value="{{ old('opening_date', \Carbon\Carbon::parse($cashCount->opening_date)->format('Y-m-d\TH:i')) }}">
                                    @error('opening_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="initial_amount">Monto Inicial ({{ $currency->symbol }})</label>
                                    <input type="number" step="0.01"
                                        class="form-control @error('initial_amount') is-invalid @enderror"
                                        id="initial_amount" name="initial_amount"
                                        value="{{ old('initial_amount', $cashCount->initial_amount) }}">
                                    @error('initial_amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="observations">Observaciones</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations"
                                        rows="3">{{ old('observations', $cashCount->observations) }}</textarea>
                                    @error('observations')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Resumen de Movimientos --}}
                    <div class="col-md-6">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">Resumen de Movimientos</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Ingresos</span>
                                        <span class="info-box-number">
                                            {{ $currency->symbol }}
                                            {{ number_format($cashCount->movements()->where('type', 'income')->sum('amount'), 2) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="info-box bg-danger">
                                    <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Egresos</span>
                                        <span class="info-box-number">
                                            {{ $currency->symbol }}
                                            {{ number_format($cashCount->movements()->where('type', 'expense')->sum('amount'), 2) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Movimientos</span>
                                        <span class="info-box-number">
                                            {{ $cashCount->movements()->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box-number {
            font-size: 1.5rem;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar SweetAlert2
            loadSweetAlert2(function() {
                // Validación de formulario
                $('form').submit(function(e) {
                    const initialAmount = parseFloat($('#initial_amount').val());

                    if (initialAmount < 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'El monto inicial no puede ser negativo'
                        });
                    }
                });
                
            });
        });
    </script>
@stop
