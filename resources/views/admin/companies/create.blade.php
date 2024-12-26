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
                <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }}"
                    style="box-shadow: 5px 5px 5px 5px #cccccc">


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
                                        <select id="country" name="country"
                                            class="form-control @error('country') is-invalid @enderror" required>
                                            <option value="">Seleccione un país</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                    {{ old('country') == $country->name ? 'selected' : '' }}>
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
                                        <input type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
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
                                        <input type="text" name="business_type"
                                            class="form-control @error('business_type') is-invalid @enderror"
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
                                        <input type="text" name="nit"
                                            class="form-control @error('nit') is-invalid @enderror"
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
                                        <input type="text" name="phone"
                                            class="form-control @error('phone') is-invalid @enderror"
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
                                        <input type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
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
                                    {{-- Tax amount field --}}
                                    <div class="input-group mb-3">
                                        <input type="number" name="tax_amount"
                                            class="form-control @error('tax_amount') is-invalid @enderror"
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
                                        <input type="text" name="tax_name"
                                            class="form-control @error('tax_name') is-invalid @enderror"
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
                                </div>

                                <div class="col-md-6">
                                    {{-- City, State and Postal Code fields --}}
                                    <div class="row">
                                        {{-- State --}}
                                        <div class="col-md-4">
                                            <div class="input-group mb-3">
                                                
                                                    {{-- <option value="">Estado</option> --}}
                                                    <div id="country_response"></div>
                                                    


                                                
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
                                                <select name="city"
                                                    class="form-control @error('city') is-invalid @enderror" required>
                                                    <option value="">Ciudad</option>
                                                    {{-- @foreach ($cities as $city)
                                                        <option value="{{ $city->id }}" {{ old('city') == $city->id ? 'selected' : '' }}>
                                                            {{ $city->name }}
                                                        </option>
                                                @endforeach --}}
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
                                                <select name="postal_code"
                                                    class="form-control @error('postal_code') is-invalid @enderror"
                                                    required>
                                                    <option value="">Código postal</option>
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country->phone_code }}"
                                                            {{ old('postal_code') == $country->phone_code ? 'selected' : '' }}>
                                                            {{ $country->phone_code }}
                                                        </option>
                                                    @endforeach
                                                </select>
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
                                        <select name="currency"
                                            class="form-control @error('currency') is-invalid @enderror" required>
                                            <option value="">Seleccione una moneda</option>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->code }}"
                                                    {{ old('currency') == $currency->code ? 'selected' : '' }}>
                                                    {{ $currency->code }} - {{ $currency->name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" placeholder="Dirección"
                                            required>{{ old('address') }}</textarea>
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
                                                        <input type="file" class="custom-file-input" id="file"
                                                            name="logo" accept="image/*">
                                                        <label class="custom-file-label" for="logo">Seleccionar
                                                            archivo</label>
                                                    </div>
                                                </div>
                                                @error('logo')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-center">
                                                <output id="list"></output>
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
    <script>
        $(document).ready(function() {
            $('#country').change(function() {
                var id_country = $(this).val();

                if (id_country) {
                    $.ajax({
                        url: "{{ route('admin.company.search_country', '') }}/" + id_country,
                        type: 'GET',
                        data: {
                            id_country: id_country
                        },
                        success: function(data) {
                            // if(data.states) {
                            //     var stateSelect = $('select[name="state"]');
                            //     stateSelect.empty();
                            //     stateSelect.append('<option value="">Seleccione un estado</option>');

                            //     $.each(data.states, function(key, value) {
                            //         stateSelect.append('<option value="'+ value.name +'">'+ value.name +'</option>');
                            //     });
                            // }

                            // if(data.postal_code) {
                            //     $('input[name="postal_code"]').val(data.postal_code);
                            // }
                            $('#country_response').html(data);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al obtener información del país:', error);
                        }
                    });
                } else {
                    $('select[name="state"]').empty().append(
                        '<option value="">Seleccione un estado</option>');
                    $('input[name="postal_code"]').val('');
                }
            });
        });
    </script>
@stop
