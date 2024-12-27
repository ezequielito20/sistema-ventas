@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Configuraciones/Editar</h1>
@stop

@section('content')
    <div class="container">



        <div class="{{ $auth_type ?? 'login' }}-logo">
            <a href="">
                <img src="{{ asset('assets/img/logotipo.jpg') }}" alt="Logo" {{-- height="50" --}} width="250px">

            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                {{-- Card Box --}}
                <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }}"
                    style="box-shadow: 5px 5px 5px 5px #cccccc">


                    <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                        <h3 class="card-title float-none text-center">
                            <b>Editar empresa: {{ $company->name }}</b>
                        </h3>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body">
                        <form 
                        {{-- action="{{ route('admin.companies.update', $company->id) }}"  --}}
                        method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Country field --}}
                                    <div class="input-group mb-3">
                                        <select id="country" name="country" class="form-control @error('country') is-invalid @enderror" required>
                                            <option value="">Seleccione un país</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}" 
                                                    {{ $company->country == $country->id ? 'selected' : '' }}>
                                                    {{ $country->name }}
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
                                            value="{{ $company->name }}" placeholder="Nombre de la empresa" required>
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
                                        <input type="text" name="business_type"
                                            class="form-control @error('business_type') is-invalid @enderror"
                                            value="{{ $company->business_type }}" placeholder="Tipo de negocio" required>
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
                                        <input type="text" name="nit"
                                            class="form-control @error('nit') is-invalid @enderror"
                                            value="{{ $company->nit }}" placeholder="NIT" required>
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
                                        <input type="text" name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ $company->phone }}" placeholder="Teléfono" required>
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
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ $company->email }}" placeholder="Email" required>
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
                                    {{-- Tax amount field --}}
                                    <div class="input-group mb-3">
                                        <input type="number" name="tax_amount"
                                            class="form-control @error('tax_amount') is-invalid @enderror"
                                            value="{{ $company->tax_amount }}" placeholder="Porcentaje de impuesto" required>
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
                                        <input type="text" name="tax_name"
                                            class="form-control @error('tax_name') is-invalid @enderror"
                                            value="{{ $company->tax_name }}" placeholder="Nombre del impuesto" required>
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
                                </div>

                                <div class="col-md-6">
                                    {{-- City, State and Postal Code fields --}}
                                    <div class="row">
                                        {{-- State --}}
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <select name="state" id="state" class="form-control @error('state') is-invalid @enderror" required>
                                                    <option value="">Estado</option>
                                                    {{-- Los estados se cargarán vía AJAX --}}
                                                    <option value="{{ $company->state }}" selected>{{ $company->state_name }}</option>
                                                </select>
                                                @error('state')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- City --}}
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <select name="city" class="form-control @error('city') is-invalid @enderror" required>
                                                    <option value="">Ciudad</option>
                                                    {{-- Las ciudades se cargarán vía AJAX --}}
                                                    <option value="{{ $company->city }}" selected>{{ $company->city_name }}</option>
                                                </select>
                                                @error('city')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        {{-- Code Postal --}}
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                <input type="text" name="postal_code"
                                                    class="form-control @error('postal_code') is-invalid @enderror"
                                                    readonly
                                                    value="{{ $company->postal_code }}"
                                                    placeholder="Código postal">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">
                                                        <span class="fas fa-mail-bulk"></span>
                                                    </div>
                                                </div>
                                                @error('postal_code')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Currency field --}}
                                    <div class="input-group mb-3">
                                        <input type="text" name="currency" 
                                               placeholder="Moneda de la empresa" 
                                               readonly
                                               value="{{ $company->currency }}"
                                               class="form-control @error('currency') is-invalid @enderror" 
                                               required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-coins"></span>
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
                                        <textarea name="address" 
                                                  class="form-control @error('address') is-invalid @enderror" 
                                                  placeholder="Dirección"
                                                  required>{{ $company->address }}</textarea>
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

                                    {{-- Logo field --}}
                                    <div class="row mb-2" style="border: 1px solid #ccc; padding: 10px;">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="file">Logo de la empresa</label>
                                                <div class="input-group mb-3">
                                                    <div class="custom-file">
                                                        <input type="file" accept=".jpg,.jpeg,.png"
                                                            class="custom-file-input" id="file"
                                                            name="logo" accept="image/*">
                                                        <label class="custom-file-label" for="logo">
                                                            Cambiar logo
                                                        </label>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Dejar vacío para mantener el logo actual</small>
                                                @error('logo')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-center">
                                                <output id="list">
                                                    @if($company->logo)
                                                        <img src="{{ asset('storage/' . $company->logo) }}" 
                                                             class="thumb thumbnail img-fluid rounded" 
                                                             style="max-height: 208px;" 
                                                             alt="Logo actual">
                                                    @endif
                                                </output>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        function archivo(evt) {
                                            var files = evt.target.files; // Filelist object

                                            // Obtenemos la imagen del campo "file"
                                            for (var i = 0, f; f = files[i]; i++) {
                                                // Solo admitimos imágenes.
                                                if (!f.type.match('image.*')) {
                                                    continue;
                                                }

                                                var reader = new FileReader();
                                                reader.onload = (function(theFile) {
                                                    return function(e) {
                                                        // Insertamos la imagen
                                                        document.getElementById('list').innerHTML = [
                                                            '<img class="thumb thumbnail img-fluid rounded" src="', e
                                                            .target.result, '" style="max-height: 208px;" />'
                                                        ].join('');
                                                    };
                                                })(f);
                                                reader.readAsDataURL(f);
                                            }
                                        }

                                        document.getElementById('file').addEventListener('change', archivo, false);
                                    </script>
                                </div>
                            </div>

                            <div class="row justify-content-center mt-4">
                                <div class="col-md-3">
                                    <a href="
                                    {{-- {{ route('admin.companies.index') }} --}}
                                     " 
                                       class="btn btn-secondary btn-block">
                                        Cancelar
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        Guardar cambios
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

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
    <script>
        $(document).ready(function() {
            $('#country').change(function() {
                var id_country = $(this).val();

                if (id_country) {
                    $.ajax({
                        url: "{{ route('admin.company.search_country', '') }}/" + id_country,
                        type: 'GET',
                        success: function(response) {
                            // Actualizar select de estados
                            let stateSelect = $('#state');
                            stateSelect.empty().append('<option value="">Estado</option>');

                            if (response.states && response.states.length > 0) {
                                response.states.forEach(function(state) {
                                    stateSelect.append('<option value="' + state.id +
                                        '">' + state.name + '</option>');
                                });
                            }

                            // Actualizar código postal y moneda
                            $('input[name="postal_code"]').val(response.postal_code);
                            $('input[name="currency"]').val(response.currency_code);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al obtener estados:', error);
                            $('#state').empty().append(
                                '<option value="">Error al cargar estados</option>');
                        }
                    });
                } else {
                    $('#state').empty().append('<option value="">Estado</option>');
                    $('input[name="postal_code"]').val('');
                    $('input[name="currency"]').val('');
                }
            });

            // Manejar cambio de estado
            $('#state').change(function() {
                var id_state = $(this).val();

                if (id_state) {
                    $.ajax({
                        url: "{{ route('admin.company.search_state', '') }}/" + id_state,
                        type: 'GET',
                        success: function(response) {
                            // Actualizar ciudades
                            let citySelect = $('select[name="city"]');
                            citySelect.empty().append('<option value="">Ciudad </option>');

                            if (response.cities && response.cities.length > 0) {
                                response.cities.forEach(function(city) {
                                    citySelect.append('<option value="' + city.id +
                                        '">' + city.name + '</option>');
                                });
                            }


                        },
                        error: function(xhr, status, error) {
                            console.error('Error al obtener datos del estado:', error);
                            $('select[name="city"]').empty().append(
                                '<option value="">Error al cargar ciudades</option>');
                            $('input[name="postal_code"]').val('');
                        }
                    });
                } else {
                    $('select[name="city"]').empty().append('<option value="">Ciudad</option>');
                    $('input[name="postal_code"]').val('');
                }
            });
        });
    </script>
@stop
