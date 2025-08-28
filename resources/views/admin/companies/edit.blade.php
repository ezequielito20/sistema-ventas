@extends('layouts.app')

@section('title', 'Editar Empresa')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/companies/edit.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/companies/edit.js') }}" defer></script>
@endpush

@section('content')
<!-- Floating Background Elements -->
<div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
</div>

<div class="main-container">
    <!-- Header Section -->
    <div class="header-section">
        <h1 class="page-title">Editar Empresa</h1>
        <p class="page-subtitle">Actualiza la información de tu empresa</p>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <!-- Form Header -->
        <div class="form-header">
            <div class="form-header-content">
                <h2 class="form-title">
                    <i class="fas fa-edit mr-3"></i>
                    {{ $company->name }}
                </h2>
                <p class="form-subtitle">Modifica los datos de tu empresa</p>
            </div>
        </div>

        <!-- Form Body -->
        <div class="form-body">
            <form action="{{ url('settings/' . $company->id) }}" method="POST" enctype="multipart/form-data" id="companyForm">
                @csrf
                @method('PUT')

                <!-- Basic Information Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Información Básica
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Company name field -->
                            <div class="form-group">
                                <label for="name" class="form-label">Nombre de la Empresa</label>
                                <div class="input-group">
                                    <input type="text" id="name" name="name" class="form-input @error('name') is-invalid @enderror"
                                           value="{{ $company->name }}" placeholder="Ingresa el nombre de tu empresa" required>
                                    <i class="fas fa-building input-icon"></i>
                                </div>
                                @error('name')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Business type field -->
                            <div class="form-group">
                                <label for="business_type" class="form-label">Tipo de Negocio</label>
                                <div class="input-group">
                                    <input type="text" id="business_type" name="business_type" class="form-input @error('business_type') is-invalid @enderror"
                                           value="{{ $company->business_type }}" placeholder="Ej: Comercio, Servicios, etc." required>
                                    <i class="fas fa-store input-icon"></i>
                                </div>
                                @error('business_type')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- NIT field -->
                            <div class="form-group">
                                <label for="nit" class="form-label">Cédula / NIT</label>
                                <div class="input-group">
                                    <input type="text" id="nit" name="nit" class="form-input @error('nit') is-invalid @enderror"
                                           value="{{ $company->nit }}" placeholder="Número de identificación tributaria" required>
                                    <i class="fas fa-id-card input-icon"></i>
                                </div>
                                @error('nit')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Phone field -->
                            <div class="form-group">
                                <label for="phone" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <input type="text" id="phone" name="phone" class="form-input @error('phone') is-invalid @enderror"
                                           value="{{ $company->phone }}" placeholder="Número de teléfono" required>
                                    <i class="fas fa-phone input-icon"></i>
                                </div>
                                @error('phone')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Email field -->
                            <div class="form-group">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <input type="email" id="email" name="email" class="form-input @error('email') is-invalid @enderror"
                                           value="{{ $company->email }}" placeholder="correo@empresa.com" required>
                                    <i class="fas fa-envelope input-icon"></i>
                                </div>
                                @error('email')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Tax amount field -->
                            <div class="form-group">
                                <label for="tax_amount" class="form-label">Porcentaje de Impuesto</label>
                                <div class="input-group">
                                    <input type="number" id="tax_amount" name="tax_amount" class="form-input @error('tax_amount') is-invalid @enderror"
                                           value="{{ intval($company->tax_amount) }}" placeholder="Ej: 19" step="1" required>
                                    <i class="fas fa-percent input-icon"></i>
                                </div>
                                @error('tax_amount')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Tax name field -->
                            <div class="form-group">
                                <label for="tax_name" class="form-label">Nombre del Impuesto</label>
                                <div class="input-group">
                                    <input type="text" id="tax_name" name="tax_name" class="form-input @error('tax_name') is-invalid @enderror"
                                           value="{{ $company->tax_name }}" placeholder="Ej: IVA, GST, etc." required>
                                    <i class="fas fa-file-invoice-dollar input-icon"></i>
                                </div>
                                @error('tax_name')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Instagram field -->
                            <div class="form-group">
                                <label for="ig" class="form-label">Usuario de Instagram</label>
                                <div class="input-group">
                                    <input type="text" id="ig" name="ig" class="form-input @error('ig') is-invalid @enderror"
                                           value="{{ $company->ig }}" placeholder="@usuario_instagram">
                                    <i class="fab fa-instagram input-icon"></i>
                                </div>
                                @error('ig')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Ubicación
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <!-- Country field -->
                            <div class="form-group">
                                <label for="country" class="form-label">País</label>
                                <div class="input-group">
                                    <select id="country" name="country" class="form-select country-select @error('country') is-invalid @enderror" required>
                                        <option value="">Seleccione un país</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}" 
                                                {{ $company->country == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-globe input-icon"></i>
                                </div>
                                @error('country')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <!-- State -->
                            <div class="form-group">
                                <label for="state" class="form-label">Estado / Provincia</label>
                                <div class="input-group">
                                    <select name="state" id="state" class="form-select @error('state') is-invalid @enderror" required>
                                        <option value="">Seleccione estado</option>
                                    </select>
                                    <i class="fas fa-map input-icon"></i>
                                </div>
                                @error('state')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <!-- City -->
                            <div class="form-group">
                                <label for="city" class="form-label">Ciudad</label>
                                <div class="input-group">
                                    <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" required>
                                        <option value="">Seleccione ciudad</option>
                                    </select>
                                    <i class="fas fa-city input-icon"></i>
                                </div>
                                @error('city')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <!-- Postal Code -->
                            <div class="form-group">
                                <label for="postal_code" class="form-label">Código Postal</label>
                                <div class="input-group">
                                    <input type="text" id="postal_code" name="postal_code" class="form-input @error('postal_code') is-invalid @enderror"
                                           value="{{ $company->postal_code }}" placeholder="Código postal" required>
                                    <i class="fas fa-mail-bulk input-icon"></i>
                                </div>
                                @error('postal_code')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="form-group">
                        <label for="address" class="form-label">Dirección Completa</label>
                        <div class="input-group">
                            <textarea id="address" name="address" class="form-textarea @error('address') is-invalid @enderror" 
                                      placeholder="Ingresa la dirección completa de tu empresa" required>{{ $company->address }}</textarea>
                            <i class="fas fa-map-marker-alt input-icon"></i>
                        </div>
                        @error('address')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="form-group">
                        <label for="currency" class="form-label">Moneda de la Empresa</label>
                        <div class="input-group">
                            <select id="currency" name="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                <option value="">Seleccione una moneda</option>
                                @foreach ($currencies as $currency_option)
                                    <option value="{{ $currency_option->code }}" 
                                        {{ $company->currency == $currency_option->code ? 'selected' : '' }}>
                                        {{ $currency_option->code }} - {{ $currency_option->symbol }} ({{ $currency_option->name }})
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-coins input-icon"></i>
                        </div>
                        @error('currency')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Logo Section -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-image mr-2"></i>
                        Logo de la Empresa
                    </h3>
                    
                    <div class="file-upload-container" id="fileUploadContainer">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-text">
                            Arrastra y suelta tu nuevo logo aquí o haz clic para seleccionar
                        </div>
                        <input type="file" id="file" name="logo" class="file-input" accept="image/*">
                        <label for="file" class="file-label">
                            <i class="fas fa-upload mr-2"></i>
                            Seleccionar Archivo
                        </label>
                        <div class="preview-container" id="previewContainer">
                            @if($company->logo)
                                <img src="{{ $company->logo_url }}" class="preview-image" alt="Logo actual">
                            @endif
                        </div>
                    </div>
                    <small class="text-muted d-block text-center mt-2">Dejar vacío para mantener el logo actual</small>
                    @error('logo')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitButton">
                        <span class="button-text">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </span>
                        <div class="loading">
                            <div class="spinner"></div>
                        </div>
                        <div class="success-checkmark">
                            <i class="fas fa-check"></i>
                        </div>
                    </button>
                    
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Meta tags para datos iniciales -->
<meta name="initial-country-id" content="{{ $company->country }}">
<meta name="initial-state-id" content="{{ $company->state }}">
<meta name="initial-city-id" content="{{ $company->city }}">

<!-- Meta tags para notificaciones de sesión -->
@if(session('error'))
    <meta name="error-message" content="{{ session('error') }}">
@endif

@if(session('message'))
    <meta name="success-message" content="{{ session('message') }}">
@endif
@endsection
