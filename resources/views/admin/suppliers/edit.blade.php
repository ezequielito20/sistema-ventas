@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('content')
<!-- Background Pattern -->
<div class="page-background"></div>

<!-- Main Container -->
<div class="main-container" x-data="supplierEditForm()">
    <!-- Floating Header -->
    <div class="floating-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon-wrapper">
                    <div class="header-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Editar Proveedor</h1>
                    <p class="header-subtitle">Modifica la información del proveedor seleccionado</p>
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
            <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST" @submit.prevent="submitForm" x-ref="form">
                @csrf
                @method('PUT')
                
                <!-- Progress Steps -->
                <div class="progress-steps">
                    <div class="step" :class="{ 'active': currentStep >= 1, 'completed': currentStep > 1 }">
                        <div class="step-number">1</div>
                        <div class="step-label">Empresa</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" :class="{ 'active': currentStep >= 2, 'completed': currentStep > 2 }">
                        <div class="step-number">2</div>
                        <div class="step-label">Contacto</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" :class="{ 'active': currentStep >= 3, 'completed': currentStep > 3 }">
                        <div class="step-number">3</div>
                        <div class="step-label">Revisar</div>
                    </div>
                </div>

                <!-- Step 1: Company Information -->
                <div class="form-step" x-show="currentStep === 1" x-transition>
                    <div class="step-header">
                        <div class="step-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="step-content">
                            <h3>Información de la Empresa</h3>
                            <p>Actualiza los datos básicos de la empresa proveedora</p>
                        </div>
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

                    <div class="step-actions">
                        <button type="button" @click="nextStep" class="btn-primary" :disabled="!canProceedToStep2">
                            <i class="fas fa-arrow-right"></i>
                            <span>Siguiente</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Contact Information -->
                <div class="form-step" x-show="currentStep === 2" x-transition>
                    <div class="step-header">
                        <div class="step-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="step-content">
                            <h3>Información del Contacto</h3>
                            <p>Actualiza los datos de la persona de contacto principal</p>
                        </div>
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

                    <div class="step-actions">
                        <button type="button" @click="prevStep" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            <span>Anterior</span>
                        </button>
                        <button type="button" @click="nextStep" class="btn-primary" :disabled="!canProceedToStep3">
                            <i class="fas fa-arrow-right"></i>
                            <span>Siguiente</span>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Review -->
                <div class="form-step" x-show="currentStep === 3" x-transition>
                    <div class="step-header">
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="step-content">
                            <h3>Revisar Información</h3>
                            <p>Confirma que todos los datos sean correctos</p>
                        </div>
                    </div>

                    <div class="review-container">
                        <div class="review-section">
                            <h4><i class="fas fa-building"></i> Datos de la Empresa</h4>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Empresa:</span>
                                    <span class="review-value" x-text="formData.company_name"></span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Email:</span>
                                    <span class="review-value" x-text="formData.company_email"></span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Teléfono:</span>
                                    <span class="review-value" x-text="formData.company_phone"></span>
                                </div>
                                <div class="review-item full-width">
                                    <span class="review-label">Dirección:</span>
                                    <span class="review-value" x-text="formData.company_address"></span>
                                </div>
                            </div>
                        </div>

                        <div class="review-section">
                            <h4><i class="fas fa-user"></i> Datos del Contacto</h4>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Contacto:</span>
                                    <span class="review-value" x-text="formData.supplier_name"></span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Teléfono:</span>
                                    <span class="review-value" x-text="formData.supplier_phone"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Información del proveedor -->
                        <div class="supplier-info-section">
                            <div class="info-card">
                                <div class="info-header">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Información del Proveedor</span>
                                </div>
                                <div class="info-content">
                                    <div class="info-item">
                                        <span class="info-label">ID del Proveedor:</span>
                                        <span class="info-value">{{ $supplier->id }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Fecha de Creación:</span>
                                        <span class="info-value">{{ $supplier->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Última Actualización:</span>
                                        <span class="info-value">{{ $supplier->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="button" @click="prevStep" class="btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            <span>Anterior</span>
                        </button>
                        <button type="submit" class="btn-success" :disabled="isSubmitting">
                            <i class="fas fa-save" x-show="!isSubmitting"></i>
                            <i class="fas fa-spinner fa-spin" x-show="isSubmitting"></i>
                            <span x-text="isSubmitting ? 'Actualizando...' : 'Actualizar Proveedor'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    /* ===== VARIABLES CSS ===== */
    :root {
        --primary-50: #f0f9ff;
        --primary-100: #e0f2fe;
        --primary-200: #bae6fd;
        --primary-300: #7dd3fc;
        --primary-400: #38bdf8;
        --primary-500: #0ea5e9;
        --primary-600: #0284c7;
        --primary-700: #0369a1;
        --primary-800: #075985;
        --primary-900: #0c4a6e;
        
        --secondary-50: #f8fafc;
        --secondary-100: #f1f5f9;
        --secondary-200: #e2e8f0;
        --secondary-300: #cbd5e1;
        --secondary-400: #94a3b8;
        --secondary-500: #64748b;
        --secondary-600: #475569;
        --secondary-700: #334155;
        --secondary-800: #1e293b;
        --secondary-900: #0f172a;
        
        --success-50: #f0fdf4;
        --success-100: #dcfce7;
        --success-500: #22c55e;
        --success-600: #16a34a;
        --success-700: #15803d;
        
        --warning-50: #fffbeb;
        --warning-100: #fef3c7;
        --warning-500: #f59e0b;
        --warning-600: #d97706;
        --warning-700: #b45309;
        
        --danger-50: #fef2f2;
        --danger-100: #fee2e2;
        --danger-500: #ef4444;
        --danger-600: #dc2626;
        --danger-700: #b91c1c;
        
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-success: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        --gradient-secondary: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        
        --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        
        --border-radius-sm: 0.375rem;
        --border-radius-md: 0.5rem;
        --border-radius-lg: 0.75rem;
        --border-radius-xl: 1rem;
        --border-radius-2xl: 1.5rem;
        
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
        
        --spacing-xs: 0.25rem;
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 2rem;
        --spacing-2xl: 3rem;
    }

    /* ===== RESET Y BASE ===== */
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--secondary-50);
        color: var(--secondary-900);
        line-height: 1.6;
        margin: 0;
        padding: 0;
    }

    /* ===== FONDO Y CONTENEDOR PRINCIPAL ===== */
    .page-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 30%, rgba(102, 126, 234, 0.08) 0%, transparent 50%),
            radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.06) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(34, 197, 94, 0.05) 0%, transparent 50%),
            linear-gradient(135deg, var(--secondary-50) 0%, var(--secondary-100) 50%, var(--secondary-200) 100%);
        z-index: -1;
    }

    .main-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: var(--spacing-lg);
        position: relative;
        z-index: 1;
    }

    /* ===== HEADER FLOTANTE ===== */
    .floating-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-xl);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
        position: sticky;
        top: var(--spacing-md);
        z-index: 100;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
    }

    .header-icon-wrapper {
        position: relative;
        width: 64px;
        height: 64px;
    }

    .header-icon {
        width: 100%;
        height: 100%;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.75rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        z-index: 2;
    }

    .icon-glow {
        position: absolute;
        inset: -8px;
        background: var(--gradient-primary);
        border-radius: 50%;
        opacity: 0.3;
        filter: blur(12px);
        z-index: 1;
    }

    .header-text {
        flex: 1;
    }

    .header-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--secondary-900);
        margin: 0;
        line-height: 1.2;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .header-subtitle {
        font-size: 1rem;
        color: var(--secondary-600);
        margin-top: var(--spacing-xs);
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: var(--spacing-md);
    }

    /* ===== BOTONES ===== */
    .btn-glass {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-md) var(--spacing-lg);
        border: 1px solid transparent;
        border-radius: var(--border-radius-lg);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--transition-normal);
        overflow: hidden;
    }

    .btn-glass:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-secondary-glass {
        background: rgba(255, 255, 255, 0.9);
        border-color: var(--secondary-200);
        color: var(--secondary-700);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary-glass:hover {
        background: rgba(255, 255, 255, 1);
        border-color: var(--secondary-300);
        color: var(--secondary-800);
    }

    /* ===== CONTENEDOR DEL FORMULARIO ===== */
    .form-container {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--border-radius-2xl);
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    .form-card {
        padding: var(--spacing-2xl);
    }

    /* ===== PROGRESS STEPS ===== */
    .progress-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-2xl);
        padding: var(--spacing-lg);
        background: var(--secondary-50);
        border-radius: var(--border-radius-xl);
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--spacing-sm);
        position: relative;
        z-index: 2;
    }

    .step-number {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--secondary-200);
        color: var(--secondary-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
        transition: all var(--transition-normal);
    }

    .step.active .step-number {
        background: var(--gradient-primary);
        color: white;
        box-shadow: var(--shadow-md);
    }

    .step.completed .step-number {
        background: var(--gradient-success);
        color: white;
        box-shadow: var(--shadow-md);
    }

    .step-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--secondary-600);
        text-align: center;
    }

    .step.active .step-label {
        color: var(--primary-700);
    }

    .step.completed .step-label {
        color: var(--success-700);
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: var(--secondary-200);
        margin: 0 var(--spacing-md);
        position: relative;
        z-index: 1;
    }

    .step.completed + .step-line {
        background: var(--success-500);
    }

    /* ===== PASOS DEL FORMULARIO ===== */
    .form-step {
        min-height: 400px;
    }

    .step-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
        padding: var(--spacing-lg);
        background: var(--secondary-50);
        border-radius: var(--border-radius-xl);
    }

    .step-icon {
        width: 56px;
        height: 56px;
        background: var(--gradient-primary);
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .step-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--secondary-900);
        margin: 0;
    }

    .step-content p {
        color: var(--secondary-600);
        margin: var(--spacing-xs) 0 0 0;
        font-size: 0.875rem;
    }

    /* ===== GRID DEL FORMULARIO ===== */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--secondary-700);
    }

    .form-label i {
        color: var(--primary-500);
        width: 16px;
    }

    .required {
        color: var(--danger-500);
        font-weight: 700;
    }

    /* ===== INPUTS ===== */
    .input-wrapper {
        position: relative;
    }

    .input-wrapper input,
    .input-wrapper textarea {
        width: 100%;
        padding: var(--spacing-md) var(--spacing-lg);
        padding-left: 3rem;
        border: 2px solid var(--secondary-200);
        border-radius: var(--border-radius-lg);
        font-size: 0.875rem;
        background: white;
        transition: all var(--transition-fast);
        font-family: inherit;
    }

    .input-wrapper textarea {
        padding-left: var(--spacing-lg);
        resize: vertical;
        min-height: 80px;
    }

    .input-wrapper input:focus,
    .input-wrapper textarea:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .input-wrapper input.error,
    .input-wrapper textarea.error {
        border-color: var(--danger-500);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .input-icon {
        position: absolute;
        left: var(--spacing-md);
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary-400);
        font-size: 1rem;
        pointer-events: none;
    }

    .input-wrapper textarea + .input-icon {
        top: var(--spacing-md);
        transform: none;
    }

    .input-border {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--gradient-primary);
        transition: width var(--transition-normal);
    }

    .input-wrapper input:focus + .input-icon + .input-border,
    .input-wrapper textarea:focus + .input-icon + .input-border {
        width: 100%;
    }

    .error-message {
        color: var(--danger-600);
        font-size: 0.75rem;
        font-weight: 500;
        margin-top: var(--spacing-xs);
    }

    /* ===== ACCIONES DE PASOS ===== */
    .step-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: var(--spacing-xl);
        border-top: 1px solid var(--secondary-200);
    }

    .btn-primary,
    .btn-secondary,
    .btn-success {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-md) var(--spacing-xl);
        border: none;
        border-radius: var(--border-radius-lg);
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-normal);
        text-decoration: none;
    }

    .btn-primary {
        background: var(--gradient-primary);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .btn-primary:hover:not(:disabled) {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--secondary-100);
        color: var(--secondary-700);
        border: 1px solid var(--secondary-200);
    }

    .btn-secondary:hover:not(:disabled) {
        background: var(--secondary-200);
        color: var(--secondary-800);
    }

    .btn-success {
        background: var(--gradient-success);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .btn-success:hover:not(:disabled) {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .btn-primary:disabled,
    .btn-secondary:disabled,
    .btn-success:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    /* ===== REVISIÓN ===== */
    .review-container {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xl);
    }

    .review-section {
        background: var(--secondary-50);
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-lg);
    }

    .review-section h4 {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--secondary-900);
        margin: 0 0 var(--spacing-lg) 0;
    }

    .review-section h4 i {
        color: var(--primary-500);
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-md);
    }

    .review-item.full-width {
        grid-column: 1 / -1;
    }

    .review-item {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .review-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--secondary-600);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .review-value {
        font-size: 0.875rem;
        color: var(--secondary-900);
        font-weight: 500;
        word-break: break-word;
    }

    /* ===== INFORMACIÓN DEL PROVEEDOR ===== */
    .supplier-info-section {
        margin-top: var(--spacing-lg);
    }

    .info-card {
        background: var(--primary-50);
        border: 1px solid var(--primary-200);
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-lg);
    }

    .info-header {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1rem;
        font-weight: 700;
        color: var(--primary-700);
        margin-bottom: var(--spacing-md);
    }

    .info-header i {
        color: var(--primary-500);
    }

    .info-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--primary-600);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-value {
        font-size: 0.875rem;
        color: var(--primary-900);
        font-weight: 500;
    }

    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 768px) {
        .main-container {
            padding: var(--spacing-md);
        }

        .floating-header {
            padding: var(--spacing-lg);
        }

        .header-content {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-lg);
        }

        .header-left {
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .form-card {
            padding: var(--spacing-lg);
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .step-header {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-md);
        }

        .step-actions {
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .btn-primary,
        .btn-secondary,
        .btn-success {
            width: 100%;
        }

        .review-grid {
            grid-template-columns: 1fr;
        }

        .info-content {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .header-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .header-title {
            font-size: 1.5rem;
        }

        .progress-steps {
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .step-line {
            width: 2px;
            height: 20px;
            margin: 0;
        }
    }
</style>
@endpush

@push('js')
<script>
function supplierEditForm() {
    return {
        currentStep: 1,
        isSubmitting: false,
        formData: {
            company_name: '{{ old("company_name", $supplier->company_name) }}',
            company_email: '{{ old("company_email", $supplier->company_email) }}',
            company_phone: '{{ old("company_phone", $supplier->company_phone) }}',
            company_address: '{{ old("company_address", $supplier->company_address) }}',
            supplier_name: '{{ old("supplier_name", $supplier->supplier_name) }}',
            supplier_phone: '{{ old("supplier_phone", $supplier->supplier_phone) }}'
        },
        errors: {},

        get canProceedToStep2() {
            return this.formData.company_name.trim() && 
                   this.formData.company_email.trim() && 
                   this.formData.company_phone.trim() && 
                   this.formData.company_address.trim() &&
                   !this.errors.company_name && 
                   !this.errors.company_email && 
                   !this.errors.company_phone && 
                   !this.errors.company_address;
        },

        get canProceedToStep3() {
            return this.formData.supplier_name.trim() && 
                   this.formData.supplier_phone.trim() &&
                   !this.errors.supplier_name && 
                   !this.errors.supplier_phone;
        },

        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        validateField(fieldName) {
            this.errors[fieldName] = '';
            
            const value = this.formData[fieldName].trim();
            
            switch (fieldName) {
                case 'company_name':
                case 'supplier_name':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (value.length < 2) {
                        this.errors[fieldName] = 'Debe tener al menos 2 caracteres';
                    }
                    break;
                    
                case 'company_email':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (!this.isValidEmail(value)) {
                        this.errors[fieldName] = 'Ingrese un email válido';
                    }
                    break;
                    
                case 'company_phone':
                case 'supplier_phone':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (!this.isValidPhone(value)) {
                        this.errors[fieldName] = 'Formato: (123) 456-7890';
                    }
                    break;
                    
                case 'company_address':
                    if (!value) {
                        this.errors[fieldName] = 'Este campo es requerido';
                    } else if (value.length < 10) {
                        this.errors[fieldName] = 'Debe tener al menos 10 caracteres';
                    }
                    break;
            }
        },

        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        isValidPhone(phone) {
            const phoneRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;
            return phoneRegex.test(phone);
        },

        formatPhone(input, fieldName) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length >= 10) {
                value = value.substring(0, 10);
                const formatted = `(${value.substring(0, 3)}) ${value.substring(3, 6)}-${value.substring(6)}`;
                this.formData[fieldName] = formatted;
            } else {
                this.formData[fieldName] = value;
            }
            
            this.validateField(fieldName);
        },

        async submitForm() {
            // Validar todos los campos
            Object.keys(this.formData).forEach(field => {
                this.validateField(field);
            });

            // Verificar si hay errores
            if (Object.values(this.errors).some(error => error)) {
                this.showNotification('Por favor, corrija los errores en el formulario', 'error');
                return;
            }

            // Confirmar actualización
            const confirmed = await this.showConfirmDialog(
                '¿Confirmar actualización?',
                '¿Está seguro de que desea actualizar la información del proveedor?'
            );

            if (!confirmed) {
                return;
            }

            this.isSubmitting = true;

            try {
                const formData = new FormData(this.$refs.form);
                const response = await fetch(this.$refs.form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.showNotification('Proveedor actualizado exitosamente', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.suppliers.index") }}';
                    }, 1500);
                } else {
                    // Manejar errores de validación del servidor
                    if (result.errors) {
                        // Mostrar errores específicos del servidor
                        Object.keys(result.errors).forEach(field => {
                            this.errors[field] = result.errors[field][0];
                        });
                        this.showNotification('Por favor, corrija los errores en el formulario', 'error');
                    } else {
                        this.showNotification(result.message || 'Error al actualizar el proveedor', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('Error de conexión. Por favor, inténtelo de nuevo.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        async showConfirmDialog(title, message) {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, actualizar',
                    cancelButtonText: 'Cancelar'
                });
                return result.isConfirmed;
            } else {
                return confirm(`${title}\n${message}`);
            }
        },

        showNotification(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? '¡Éxito!' : 'Error',
                    text: message,
                    icon: type,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: type === 'success' ? '#22c55e' : '#ef4444'
                });
            } else {
                alert(message);
            }
        }
    }
}
</script>
@endpush
