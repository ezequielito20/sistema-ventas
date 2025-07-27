@extends('adminlte::page')

@section('title', 'Abrir Caja')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Abrir Nueva Caja</h1>
        <a href="{{ route('admin.cash-counts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <form action="{{ route('admin.cash-counts.store') }}" method="POST" id="cashCountForm">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ request()->headers->get('referer') }}">

                        <div class="row">
                            {{-- Fecha de Apertura --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="opening_date">Fecha de Apertura <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('opening_date') is-invalid @enderror"
                                        id="opening_date" name="opening_date"
                                        value="{{ old('opening_date', date('Y-m-d')) }}" required>
                                    @error('opening_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Monto Inicial --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="initial_amount">Monto Inicial <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ $currency->symbol }}</span>
                                        </div>
                                        <input type="number" step="0.01"
                                            class="form-control @error('initial_amount') is-invalid @enderror"
                                            id="initial_amount" name="initial_amount"
                                            value="{{ old('initial_amount', '0.00') }}" required>
                                        @error('initial_amount')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="observations">Observaciones</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations"
                                        rows="3">{{ old('observations') }}</textarea>
                                    @error('observations')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Abrir Caja
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Validación del formulario
            $('#cashCountForm').submit(function(e) {
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

            // Formateo automático del monto
            $('#initial_amount').on('blur', function() {
                this.value = parseFloat(this.value).toFixed(2);
            });
        });
    </script>
@stop
