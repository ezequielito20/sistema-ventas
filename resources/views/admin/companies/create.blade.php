@extends('adminlte::master')

@php($dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home'))

@if (config('adminlte.use_route_url', false))
    @php($dashboard_url = $dashboard_url ? route($dashboard_url) : '')
@else
    @php($dashboard_url = $dashboard_url ? url($dashboard_url) : '')
@endif

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body'){{ ($auth_type ?? 'login') . '-page' }}@stop

@section('body')
    <div class="container">



        <div class="{{ $auth_type ?? 'login' }}-logo">
            <a href="{{ $dashboard_url }}">
                <img src="{{ asset('assets/img/logotipo.jpg') }}" alt="Logo" {{-- height="50" --}} width="250px">
               
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                {{-- Card Box --}}
                <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }}" style="box-shadow: 5px 5px 5px 5px #cccccc">

                    
                        <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                            <h3 class="card-title float-none text-center">
                                 <b>Crear empresa</b>
                            </h3>
                        </div>

                    {{-- Card Body --}}
                    <div
                        class="card-body {{ $auth_type ?? 'login' }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                    <form action="{{ route('admin.company.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Country field --}}
                                <div class="input-group mb-3">
                                    <select name="country" class="form-control @error('country') is-invalid @enderror" required>
                                        <option value="">Seleccione un país</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country['iso2'] }}" {{ old('country') == $country['iso2'] ? 'selected' : '' }}>
                                                {{ $country['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-globe"></span>
                                        </div>
                                    </div>
                                    @error('country')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Company name field --}}
                                <div class="input-group mb-3">
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="Nombre de la empresa" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-building"></span>
                                        </div>
                                    </div>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Business type field --}}
                                <div class="input-group mb-3">
                                    <input type="text" name="business_type" class="form-control @error('business_type') is-invalid @enderror"
                                        value="{{ old('business_type') }}" placeholder="Tipo de negocio" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-store"></span>
                                        </div>
                                    </div>
                                    @error('business_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- NIT field --}}
                                <div class="input-group mb-3">
                                    <input type="text" name="nit" class="form-control @error('nit') is-invalid @enderror"
                                        value="{{ old('nit') }}" placeholder="NIT" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-id-card"></span>
                                        </div>
                                    </div>
                                    @error('nit')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Phone field --}}
                                <div class="input-group mb-3">
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone') }}" placeholder="Teléfono" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-phone"></span>
                                        </div>
                                    </div>
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Email field --}}
                                <div class="input-group mb-3">
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="Email" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-envelope"></span>
                                        </div>
                                    </div>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- Tax amount field --}}
                                <div class="input-group mb-3">
                                    <input type="number" name="tax_amount" class="form-control @error('tax_amount') is-invalid @enderror"
                                        value="{{ old('tax_amount') }}" placeholder="Porcentaje de impuesto" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-percent"></span>
                                        </div>
                                    </div>
                                    @error('tax_amount')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Tax name field --}}
                                <div class="input-group mb-3">
                                    <input type="text" name="tax_name" class="form-control @error('tax_name') is-invalid @enderror"
                                        value="{{ old('tax_name') }}" placeholder="Nombre del impuesto" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-file-invoice-dollar"></span>
                                        </div>
                                    </div>
                                    @error('tax_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Currency field --}}
                                <div class="input-group mb-3">
                                    <input type="text" name="currency" class="form-control @error('currency') is-invalid @enderror"
                                        value="{{ old('currency') }}" placeholder="Moneda (ej: USD)" maxlength="3" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-dollar-sign"></span>
                                        </div>
                                    </div>
                                    @error('currency')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Address field --}}
                                <div class="input-group mb-3">
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                        placeholder="Dirección" required>{{ old('address') }}</textarea>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-map-marker-alt"></span>
                                        </div>
                                    </div>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- City, State and Postal Code fields --}}
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                                value="{{ old('city') }}" placeholder="Ciudad" required>
                                            @error('city')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                                                value="{{ old('state') }}" placeholder="Estado/Provincia" required>
                                            @error('state')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group mb-3">
                                            <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                                                value="{{ old('postal_code') }}" placeholder="Código Postal" required>
                                            @error('postal_code')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Logo field --}}
                                <div class="input-group mb-3">
                                    <div class="custom-file">
                                        <input type="file" name="logo" class="custom-file-input @error('logo') is-invalid @enderror" id="logo" accept="image/*">
                                        <label class="custom-file-label" for="logo">Elegir logo</label>
                                    </div>
                                    @error('logo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary btn-block">
                                    Crear empresa
                                </button>
                            </div>
                        </div>
                    </form>
                    </div>

                    {{-- Card Footer --}}
                    @hasSection('auth_footer')
                        <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                            @yield('auth_footer')
                        </div>
                    @endif

                </div>
            </div>
        </div>


    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
