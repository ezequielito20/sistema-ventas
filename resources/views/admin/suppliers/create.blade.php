@extends('layouts.app')

@section('title', 'Crear Proveedor')

@section('content')
<!-- Background Pattern -->
<div class="page-background"></div>

<!-- Main Container -->
<div class="main-container" x-data="supplierForm()">
    <!-- Floating Header -->
    <div class="floating-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon-wrapper">
                    <div class="header-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Crear Nuevo Proveedor</h1>
                    <p class="header-subtitle">Agrega un nuevo proveedor al sistema</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.suppliers.index') }}" class="btn-glass btn-secondary-glass">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                    <div class="btn-ripple"></div>
                </a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="form-card">
            <form action="{{ route('admin.suppliers.store') }}" method="POST" @submit.prevent="submitForm" x-ref="form">
                @csrf
                
                <!-- Form Header -->
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="form-title">
                        <h3>Información del Proveedor</h3>
                        <p>Completa todos los campos requeridos</p>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="form-content">
                    <!-- Company Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-building"></i>
                            <h4>Datos de la Empresa</h4>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="company_name" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Nombre de la Empresa
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" 
                                           id="company_name"
                                           name="company_name"
                                           x-model="formData.company_name"
                                           @input="validateField('company_name')"
                                           :class="{ 'error': errors.company_name }"
                                           placeholder="Ingrese el nombre de la empresa"
                                           required>
                                    <div class="input-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="input-border"></div>
                                </div>
                                <div class="error-message" x-show="errors.company_name" x-text="errors.company_name"></div>
                            </div>

                            <div class="form-group">
                                <label for="company_email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Correo Electrónico
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="email" 
                                           id="company_email"
                                           name="company_email"
                                           x-model="formData.company_email"
                                           @input="validateField('company_email')"
                                           :class="{ 'error': errors.company_email }"
                                           placeholder="ejemplo@empresa.com"
                                           required>
                                    <div class="input-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="input-border"></div>
                                </div>
                                <div class="error-message" x-show="errors.company_email" x-text="errors.company_email"></div>
                            </div>

                            <div class="form-group">
                                <label for="company_phone" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Teléfono de la Empresa
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="tel" 
                                           id="company_phone"
                                           name="company_phone"
                                           x-model="formData.company_phone"
                                           @input="formatPhone($event.target, 'company_phone')"
                                           :class="{ 'error': errors.company_phone }"
                                           placeholder="(123) 456-7890"
                                           maxlength="14"
                                           required>
                                    <div class="input-icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="input-border"></div>
                                </div>
                                <div class="error-message" x-show="errors.company_phone" x-text="errors.company_phone"></div>
                            </div>

                            <div class="form-group full-width">
                                <label for="company_address" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Dirección de la Empresa
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <textarea id="company_address"
                                              name="company_address"
                                              x-model="formData.company_address"
                                              @input="validateField('company_address')"
                                              :class="{ 'error': errors.company_address }"
                                              placeholder="Ingrese la dirección completa de la empresa"
                                              rows="3"
                                              required></textarea>
                                    <div class="input-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="input-border"></div>
                                </div>
                                <div class="error-message" x-show="errors.company_address" x-text="errors.company_address"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-user"></i>
                            <h4>Datos del Contacto</h4>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="supplier_name" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Nombre del Contacto
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="text" 
                                           id="supplier_name"
                                           name="supplier_name"
                                           x-model="formData.supplier_name"
                                           @input="validateField('supplier_name')"
                                           :class="{ 'error': errors.supplier_name }"
                                           placeholder="Nombre completo del contacto"
                                           required>
                                    <div class="input-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="input-border"></div>
                                </div>
                                <div class="error-message" x-show="errors.supplier_name" x-text="errors.supplier_name"></div>
                            </div>

                            <div class="form-group">
                                <label for="supplier_phone" class="form-label">
                                    <i class="fas fa-mobile-alt"></i>
                                    Teléfono del Contacto
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="tel" 
                                           id="supplier_phone"
                                           name="supplier_phone"
                                           x-model="formData.supplier_phone"
                                           @input="formatPhone($event.target, 'supplier_phone')"
                                           :class="{ 'error': errors.supplier_phone }"
                                           placeholder="(123) 456-7890"
                                           maxlength="14"
                                           required>
                                    <div class="input-icon">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="input-border"></div>
                                </div>
                                <div class="error-message" x-show="errors.supplier_phone" x-text="errors.supplier_phone"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" @click="resetForm" class="btn-secondary">
                        <i class="fas fa-undo"></i>
                        <span>Limpiar</span>
                    </button>
                    <button type="submit" class="btn-primary" :disabled="isSubmitting || !isFormValid">
                        <i class="fas fa-save" x-show="!isSubmitting"></i>
                        <i class="fas fa-spinner fa-spin" x-show="isSubmitting"></i>
                        <span x-text="isSubmitting ? 'Guardando...' : 'Guardar Proveedor'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/suppliers/create.css') }}">
@endpush

@push('js')
<script src="{{ asset('js/admin/suppliers/create.js') }}"></script>
@endpush
