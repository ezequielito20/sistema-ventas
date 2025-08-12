@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="space-y-6">
        <!-- Header con gradiente -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">Gestión de Usuarios</h1>
                        <p class="page-subtitle">Administra los usuarios del sistema</p>
                    </div>
                </div>
                <div class="header-actions">
                    @can('users.report')
                        <a href="{{ route('admin.users.report') }}" class="btn-action btn-report" target="_blank">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Reporte
                        </a>
                    @endcan
                    @can('users.create')
                        <a href="{{ route('admin.users.create') }}" class="btn-action btn-create">
                            <i class="fas fa-user-plus mr-2"></i>
                            Nuevo Usuario
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        @push('css')
            <style>
                /* Contenedor principal */
                .space-y-6 {
                    max-width: 100%;
                    margin: 0 auto;
                    padding: 0;
                }

                .space-y-6>*+* {
                    margin-top: 2rem;
                }

                /* Header de la página */
                .page-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 2rem;
                    border-radius: 16px;
                    color: white;
                    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
                    margin-bottom: 2rem;
                    width: 100%;
                    position: relative;
                    z-index: 10;
                }

                .header-content {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .header-left {
                    display: flex;
                    align-items: center;
                    gap: 1.5rem;
                }

                .header-icon {
                    width: 60px;
                    height: 60px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.5rem;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                }

                .page-title {
                    font-size: 2rem;
                    font-weight: 800;
                    margin: 0;
                    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }

                .page-subtitle {
                    font-size: 1rem;
                    opacity: 0.9;
                    margin: 0.5rem 0 0 0;
                }

                .header-actions {
                    display: flex;
                    gap: 1rem;
                    align-items: center;
                }

                .btn-action {
                    background: rgba(255, 255, 255, 0.2);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    color: white;
                    padding: 0.75rem 1.5rem;
                    border-radius: 12px;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    backdrop-filter: blur(10px);
                    transition: all 0.3s ease;
                }

                .btn-action:hover {
                    background: rgba(255, 255, 255, 0.3);
                    transform: translateY(-2px);
                    color: white;
                }

                .btn-create {
                    background: rgba(16, 185, 129, 0.2);
                    border-color: rgba(16, 185, 129, 0.3);
                }

                .btn-report {
                    background: rgba(59, 130, 246, 0.2);
                    border-color: rgba(59, 130, 246, 0.3);
                }

                /* Estadísticas */
                .stats-container {
                    margin-bottom: 2rem;
                }

                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 1.5rem;
                }

                .stat-card {
                    background: white;
                    border-radius: 16px;
                    padding: 2rem;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                    border: 1px solid #e5e7eb;
                    display: flex;
                    align-items: center;
                    gap: 1.5rem;
                    transition: all 0.3s ease;
                }

                .stat-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                }

                .stat-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: 16px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 1.5rem;
                    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
                }

                .stat-success {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
                }

                .stat-warning {
                    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
                }

                .stat-info {
                    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
                }

                .stat-content {
                    flex: 1;
                }

                .stat-number {
                    font-size: 2rem;
                    font-weight: 800;
                    color: #1e293b;
                    margin: 0;
                    line-height: 1;
                }

                .stat-label {
                    color: #64748b;
                    font-size: 0.95rem;
                    margin: 0.5rem 0 0 0;
                    font-weight: 500;
                }

                /* Selector de modo de vista */
                .view-mode-selector {
                    background: white;
                    border-radius: 12px;
                    padding: 1rem;
                    margin-bottom: 1.5rem;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                    border: 1px solid #e5e7eb;
                    display: none;
                }

                @media (min-width: 992px) {
                    .view-mode-selector {
                        display: block;
                    }
                }

                .view-mode-buttons {
                    display: flex;
                    gap: 0.5rem;
                    align-items: center;
                }

                .view-mode-btn {
                    padding: 0.5rem 1rem;
                    border: 2px solid #e5e7eb;
                    background: white;
                    color: #64748b;
                    border-radius: 8px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .view-mode-btn.active {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-color: #667eea;
                }

                .view-mode-btn:hover:not(.active) {
                    border-color: #667eea;
                    color: #667eea;
                }

                /* Vista de Escritorio */
                .desktop-view {
                    display: none;
                }

                @media (min-width: 992px) {
                    .desktop-view {
                        display: block;
                    }

                    .mobile-view {
                        display: none;
                    }
                }

                /* Clases para controlar la visibilidad */
                .show-table {
                    display: block !important;
                }

                .show-cards {
                    display: block !important;
                }

                .hide-view {
                    display: none !important;
                }

                /* Asegurar que las vistas se muestren correctamente */
                .desktop-view.show-table {
                    display: block !important;
                }

                .desktop-view.show-cards {
                    display: block !important;
                }

                .desktop-view.hide-view {
                    display: none !important;
                }

                .table-container {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
                    border: 1px solid #e5e7eb;
                    overflow: hidden;
                }

                .table-header {
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    padding: 1.5rem 2rem;
                    border-bottom: 2px solid #e5e7eb;
                }

                .table-title {
                    font-size: 1.25rem;
                    font-weight: 700;
                    color: #1e293b;
                    margin: 0;
                    display: flex;
                    align-items: center;
                }

                .table-content {
                    overflow-x: auto;
                }

                .users-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .users-table thead th {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    font-size: 0.85rem;
                    padding: 1rem 1.5rem;
                    text-align: left;
                    border: none;
                }

                .users-table tbody tr {
                    transition: all 0.3s ease;
                    border-bottom: 1px solid #f1f5f9;
                }

                .users-table tbody tr:hover {
                    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
                    transform: scale(1.01);
                }

                .users-table tbody td {
                    padding: 1rem 1.5rem;
                    vertical-align: middle;
                }

                .user-info {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                }

                .user-avatar-small {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: bold;
                    font-size: 0.9rem;
                }

                .user-name {
                    font-weight: 600;
                    color: #1e293b;
                    margin: 0;
                }

                .last-login {
                    color: #64748b;
                    font-size: 0.9rem;
                }

                /* Badges */
                .badge {
                    padding: 0.4em 0.8em;
                    border-radius: 20px;
                    font-size: 0.75rem;
                    font-weight: 500;
                    letter-spacing: 0.5px;
                    text-transform: uppercase;
                }

                .badge-success {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: white;
                }

                .badge-warning {
                    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                    color: white;
                }

                .badge-role {
                    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                    color: white;
                    margin-right: 0.25rem;
                }

                /* Botones de acción */
                .action-buttons {
                    display: flex;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                }

                .action-btn {
                    width: 35px;
                    height: 35px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: none;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    text-decoration: none;
                }

                .action-btn:hover {
                    transform: scale(1.1);
                }

                .action-view {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: white;
                }

                .action-edit {
                    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                    color: white;
                }

                .action-delete {
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: white;
                }

                /* Vista Móvil */
                .mobile-view {
                    display: block;
                }

                @media (min-width: 992px) {
                    .mobile-view {
                        display: none;
                    }
                }

                .search-container {
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
                    padding: 1.5rem;
                    margin-bottom: 1.5rem;
                }

                .search-input-group {
                    display: flex;
                    gap: 0.5rem;
                }

                .search-input {
                    flex: 1;
                    border: 2px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 0.75rem 1rem;
                    font-size: 0.95rem;
                    transition: all 0.3s ease;
                }

                .search-input:focus {
                    outline: none;
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }

                .search-btn {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    color: white;
                    padding: 0.75rem 1rem;
                    border-radius: 12px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }

                .search-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
                }

                .users-grid {
                    display: grid;
                    gap: 1rem;
                }

                /* Grid responsivo para escritorio */
                @media (min-width: 992px) {
                    .desktop-view .users-grid {
                        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                        gap: 1.5rem;
                    }
                }

                .user-card {
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
                    border: 1px solid #e5e7eb;
                    overflow: hidden;
                    transition: all 0.3s ease;
                }

                .user-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
                }

                .user-card-header {
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    padding: 1.5rem;
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    border-bottom: 1px solid #e5e7eb;
                }

                .user-card-avatar {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: bold;
                    font-size: 1.1rem;
                }

                .user-card-info {
                    flex: 1;
                }

                .user-card-name {
                    font-weight: 600;
                    color: #1e293b;
                    margin: 0 0 0.25rem 0;
                    font-size: 1.1rem;
                }

                .user-card-email {
                    color: #64748b;
                    margin: 0;
                    font-size: 0.9rem;
                }

                .user-card-status {
                    text-align: right;
                }

                .user-card-body {
                    padding: 1.5rem;
                }

                .user-card-detail {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 0.75rem 0;
                    border-bottom: 1px solid #f1f5f9;
                }

                .user-card-detail:last-child {
                    border-bottom: none;
                }

                .detail-label {
                    font-weight: 600;
                    color: #374151;
                    font-size: 0.9rem;
                }

                .detail-value {
                    color: #64748b;
                    font-size: 0.9rem;
                    text-align: right;
                }

                .user-card-actions {
                    display: flex;
                    gap: 0.5rem;
                    justify-content: center;
                    margin-top: 1rem;
                    padding-top: 1rem;
                    border-top: 1px solid #f1f5f9;
                }

                /* Modal */
                .modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.6);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    backdrop-filter: blur(8px);
                    padding: 1rem;
                }
                
                .modal-overlay[x-cloak] {
                    display: none !important;
                }
                
                /* Asegurar que el modal se oculte cuando showModal es false */
                .modal-overlay:not([style*="display: flex"]) {
                    display: none !important;
                }

                .modal-container {
                    background: white;
                    border-radius: 24px;
                    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
                    max-width: 800px;
                    width: 100%;
                    max-height: 90vh;
                    overflow: hidden;
                    animation: modalSlideIn 0.3s ease-out;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                
                @keyframes modalSlideIn {
                    from {
                        opacity: 0;
                        transform: scale(0.9) translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1) translateY(0);
                    }
                }
                
                .modal-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 2rem 2.5rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    position: relative;
                    overflow: hidden;
                }
                
                .modal-header::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
                    opacity: 0.3;
                }
                
                .modal-title {
                    font-size: 1.5rem;
                    font-weight: 700;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    position: relative;
                    z-index: 1;
                }
                
                .modal-title i {
                    margin-right: 0.75rem;
                    font-size: 1.25rem;
                    opacity: 0.9;
                }
                
                .modal-close {
                    background: rgba(255, 255, 255, 0.15);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    color: white;
                    font-size: 1.25rem;
                    cursor: pointer;
                    padding: 0.75rem;
                    border-radius: 12px;
                    transition: all 0.3s ease;
                    position: relative;
                    z-index: 1;
                    backdrop-filter: blur(10px);
                }
                
                .modal-close:hover {
                    background: rgba(255, 255, 255, 0.25);
                    transform: scale(1.05);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }
                
                .modal-body {
                    padding: 2.5rem;
                    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                }
                
                .modal-content-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 2.5rem;
                }
                
                .modal-content-column {
                    display: flex;
                    flex-direction: column;
                    gap: 1.5rem;
                }
                
                .modal-detail-item {
                    background: white;
                    border-radius: 16px;
                    padding: 1.5rem;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                    border: 1px solid rgba(102, 126, 234, 0.1);
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                }
                
                .modal-detail-item::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 4px;
                    height: 100%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 0 2px 2px 0;
                }
                
                .modal-detail-item:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
                    border-color: rgba(102, 126, 234, 0.2);
                }
                
                .detail-label {
                    font-weight: 600;
                    color: #374151;
                    font-size: 0.9rem;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 0.5rem;
                    display: block;
                }
                
                .detail-value {
                    color: #1f2937;
                    font-size: 1.1rem;
                    font-weight: 500;
                    line-height: 1.4;
                }
                
                .modal-footer {
                    padding: 2rem 2.5rem;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: flex-end;
                    background: white;
                }
                
                /* Botones */
                .btn-secondary {
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    border: 2px solid #e5e7eb;
                    color: #64748b;
                    padding: 0.875rem 2rem;
                    border-radius: 16px;
                    font-weight: 600;
                    font-size: 1rem;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.75rem;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
                    transition: all 0.3s ease;
                    cursor: pointer;
                    position: relative;
                    overflow: hidden;
                }
                
                .btn-secondary::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
                    transition: left 0.5s;
                }
                
                .btn-secondary:hover::before {
                    left: 100%;
                }
                
                .btn-secondary:hover {
                    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
                    border-color: #cbd5e1;
                    color: #475569;
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                }
                
                /* Responsividad del modal */
                @media (max-width: 768px) {
                    .modal-container {
                        max-width: 95%;
                        border-radius: 20px;
                    }
                    
                    .modal-header {
                        padding: 1.5rem 2rem;
                    }
                    
                    .modal-title {
                        font-size: 1.25rem;
                    }
                    
                    .modal-body {
                        padding: 2rem;
                    }
                    
                    .modal-content-grid {
                        grid-template-columns: 1fr;
                        gap: 1.5rem;
                    }
                    
                    .modal-footer {
                        padding: 1.5rem 2rem;
                    }
                    
                    .btn-secondary {
                        padding: 0.75rem 1.5rem;
                        font-size: 0.95rem;
                    }
                    
                    .page-header {
                        padding: 1.5rem;
                        margin: 0 0 2rem 0;
                    }

                    .header-content {
                        flex-direction: column;
                        gap: 1rem;
                        text-align: center;
                    }

                    .header-left {
                        flex-direction: column;
                        gap: 1rem;
                    }

                    .page-title {
                        font-size: 1.5rem;
                    }

                    .header-icon {
                        width: 50px;
                        height: 50px;
                        font-size: 1.25rem;
                    }

                    .header-actions {
                        flex-direction: column;
                        width: 100%;
                    }

                    .btn-action {
                        width: 100%;
                        justify-content: center;
                    }

                    .stats-grid {
                        grid-template-columns: 1fr;
                        gap: 1rem;
                    }

                    .stat-card {
                        padding: 1.5rem;
                    }

                    .view-mode-selector {
                        display: none !important;
                    }

                    /* Asegurar que el contenido no se desborde */
                    .space-y-6 {
                        overflow-x: hidden;
                    }
                }

                @media (max-width: 480px) {
                    .modal-container {
                        max-width: 98%;
                        border-radius: 16px;
                    }
                    
                    .modal-header {
                        padding: 1.25rem 1.5rem;
                    }
                    
                    .modal-body {
                        padding: 1.5rem;
                    }
                    
                    .modal-footer {
                        padding: 1.25rem 1.5rem;
                    }
                    
                    .modal-detail-item {
                        padding: 1.25rem;
                    }
                    
                    .page-header {
                        padding: 1rem;
                    }

                    .page-title {
                        font-size: 1.25rem;
                    }

                    .stats-grid {
                        grid-template-columns: 1fr;
                    }

                    .stat-card {
                        padding: 1rem;
                    }

                    .table-container {
                        border-radius: 12px;
                    }

                    .table-header {
                        padding: 1rem;
                    }

                    /* Asegurar que todo el contenido sea visible */
                    .space-y-6 {
                        min-height: auto;
                        padding-bottom: 2rem;
                    }
                }

                /* Estilos adicionales para asegurar el layout correcto */
                .space-y-6 {
                    position: relative;
                    z-index: 1;
                }

                /* Asegurar que no haya elementos flotantes */
                .clearfix::after {
                    content: "";
                    clear: both;
                    display: table;
                }
                
                /* Pantallas grandes - hacer el modal más grande */
                @media (min-width: 1200px) {
                    .modal-container {
                        max-width: 900px;
                    }
                    
                    .modal-content-grid {
                        gap: 3rem;
                    }
                    
                    .modal-detail-item {
                        padding: 2rem;
                    }
                    
                    .detail-value {
                        font-size: 1.2rem;
                    }
                }
                
                @media (min-width: 1400px) {
                    .modal-container {
                        max-width: 1000px;
                    }
                }
            </style>
        @endpush

        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $users->count() }}</h3>
                        <p class="stat-label">Total Usuarios</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $users->where('email_verified_at', '!=', null)->count() }}</h3>
                        <p class="stat-label">Verificados</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-warning">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $users->where('email_verified_at', null)->count() }}</h3>
                        <p class="stat-label">Pendientes</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon stat-info">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">
                            {{ $users->filter(function ($user) {return $user->roles->count() > 0;})->count() }}</h3>
                        <p class="stat-label">Con Roles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de Modo de Vista (solo en pantallas grandes) -->
        <div class="view-mode-selector">
            <div class="view-mode-buttons">
                <button type="button" class="view-mode-btn active" onclick="changeViewMode('table')">
                    <i class="fas fa-table"></i>
                    Vista Tabla
                </button>
                <button type="button" class="view-mode-btn" onclick="changeViewMode('cards')">
                    <i class="fas fa-th-large"></i>
                    Vista Tarjetas
                </button>
            </div>
        </div>

        <!-- Vista de Escritorio -->
        <div class="desktop-view" id="desktopTableView">
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <i class="fas fa-table mr-2"></i>
                        Lista de Usuarios
                    </h3>
                </div>
                <div class="table-content">
                    <table id="usersTable" class="users-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Empresa</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar-small">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->company->name ?? 'N/A' }}</td>
                                    <td>
                                        @foreach ($user->roles as $role)
                                            <span class="badge badge-role">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if ($user->email_verified_at)
                                            <span class="badge badge-success">Verificado</span>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($user->last_login)
                                            <span class="last-login" title="{{ $user->last_login }}">
                                                {{ $user->last_login->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="text-muted">Nunca</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @can('users.show')
                                                <button type="button" class="action-btn action-view show-user"
                                                    data-id="{{ $user->id }}" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan
                                            @can('users.edit')
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @if ($user->id !== auth()->id())
                                                @can('users.destroy')
                                                    <button type="button" class="action-btn action-delete delete-user"
                                                        data-id="{{ $user->id }}" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vista de Tarjetas para Escritorio -->
        <div class="desktop-view" id="desktopCardsView" style="display: none;">
            <div class="search-container">
                <div class="search-input-group">
                    <input type="text" class="search-input" id="desktopSearch" placeholder="Buscar usuarios...">
                    <button class="search-btn" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div id="desktopUsersContainer" class="users-grid">
                @foreach ($users as $user)
                    <div class="user-card" data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '')) }}">
                        <div class="user-card-header">
                            <div class="user-card-avatar">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="user-card-info">
                                <h4 class="user-card-name">{{ $user->name }}</h4>
                                <p class="user-card-email">{{ $user->email }}</p>
                            </div>
                            <div class="user-card-status">
                                @if ($user->email_verified_at)
                                    <span class="badge badge-success">Verificado</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </div>
                        </div>
                        <div class="user-card-body">
                            <div class="user-card-detail">
                                <span class="detail-label">Empresa:</span>
                                <span class="detail-value">{{ $user->company->name ?? 'N/A' }}</span>
                            </div>
                            <div class="user-card-detail">
                                <span class="detail-label">Roles:</span>
                                <div class="detail-value">
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-role">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="user-card-detail">
                                <span class="detail-label">Último Acceso:</span>
                                <span class="detail-value">
                                    @if ($user->last_login)
                                        {{ $user->last_login->diffForHumans() }}
                                    @else
                                        Nunca
                                    @endif
                                </span>
                            </div>
                            <div class="user-card-actions">
                                @can('users.show')
                                    <button type="button" class="action-btn action-view show-user"
                                        data-id="{{ $user->id }}" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endcan
                                @can('users.edit')
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @if ($user->id !== auth()->id())
                                    @can('users.destroy')
                                        <button type="button" class="action-btn action-delete delete-user"
                                            data-id="{{ $user->id }}" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Vista Móvil -->
        <div class="mobile-view">
            <div class="search-container">
                <div class="search-input-group">
                    <input type="text" class="search-input" id="mobileSearch" placeholder="Buscar usuarios...">
                    <button class="search-btn" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div id="usersContainer" class="users-grid">
                @foreach ($users as $user)
                    <div class="user-card"
                        data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '')) }}">
                        <div class="user-card-header">
                            <div class="user-card-avatar">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="user-card-info">
                                <h4 class="user-card-name">{{ $user->name }}</h4>
                                <p class="user-card-email">{{ $user->email }}</p>
                            </div>
                            <div class="user-card-status">
                                @if ($user->email_verified_at)
                                    <span class="badge badge-success">Verificado</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </div>
                        </div>
                        <div class="user-card-body">
                            <div class="user-card-detail">
                                <span class="detail-label">Empresa:</span>
                                <span class="detail-value">{{ $user->company->name ?? 'N/A' }}</span>
                            </div>
                            <div class="user-card-detail">
                                <span class="detail-label">Roles:</span>
                                <div class="detail-value">
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-role">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="user-card-detail">
                                <span class="detail-label">Último Acceso:</span>
                                <span class="detail-value">
                                    @if ($user->last_login)
                                        {{ $user->last_login->diffForHumans() }}
                                    @else
                                        Nunca
                                    @endif
                                </span>
                            </div>
                            <div class="user-card-actions">
                                @can('users.show')
                                    <button type="button" class="action-btn action-view show-user"
                                        data-id="{{ $user->id }}" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endcan
                                @can('users.edit')
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @if ($user->id !== auth()->id())
                                    @can('users.destroy')
                                        <button type="button" class="action-btn action-delete delete-user"
                                            data-id="{{ $user->id }}" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal de Detalles -->
    <div class="modal-overlay" id="showUserModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-user-circle mr-2"></i>
              Detalles del Usuario
                </h3>
                <button type="button" class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-content-grid">
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <span class="detail-label">Nombre:</span>
                            <span class="detail-value" id="userName"></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value" id="userEmail"></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Empresa:</span>
                            <span class="detail-value" id="userCompany"></span>
                        </div>
                    </div>
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <span class="detail-label">Roles:</span>
                            <div class="detail-value" id="userRoles"></div>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Estado:</span>
                            <div class="detail-value" id="userVerification"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times mr-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
    </div>

    @push('js')
<script>
// Variables globales
let currentViewMode = 'table';

// Función principal para manejar la lógica de usuarios
function initializeUsersPage() {
    console.log('initializeUsersPage ejecutado');
    
    // Cargar modo de vista guardado
    const savedViewMode = localStorage.getItem('usersViewMode');
    if (savedViewMode && (savedViewMode === 'table' || savedViewMode === 'cards')) {
        currentViewMode = savedViewMode;
        changeViewMode(savedViewMode);
    } else {
        changeViewMode('table');
    }
    
    // Inicializar componentes
    initializeDataTable();
    initializeSearch();
    initializeEventListeners();
}

// Inicializar DataTable
function initializeDataTable() {
    if (typeof $.fn.DataTable !== 'undefined' && $('#usersTable').length) {
        // Destruir DataTable existente si existe
        if ($.fn.DataTable.isDataTable('#usersTable')) {
            $('#usersTable').DataTable().destroy();
        }
        
        $('#usersTable').DataTable({
            responsive: false,
            autoWidth: false,
            dom: 'Bfrtip',
            buttons: [{
                extend: 'collection',
                text: '<i class="fas fa-download mr-2"></i>Exportar',
                className: 'btn btn-primary',
                buttons: [{
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel mr-2"></i>Excel',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf mr-2"></i>PDF',
                        className: 'btn btn-danger',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print mr-2"></i>Imprimir',
                        className: 'btn btn-info',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    }
                ]
            }],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                emptyTable: 'No hay información disponible',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ usuarios',
                infoEmpty: 'Mostrando 0 a 0 de 0 usuarios',
                infoFiltered: '(Filtrado de _MAX_ total usuarios)',
                lengthMenu: 'Mostrar _MENU_ usuarios',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: 'Buscar:',
                zeroRecords: 'Sin resultados encontrados',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });
    }
}

// Inicializar búsqueda
function initializeSearch() {
    // Búsqueda móvil
    const searchInput = document.getElementById('mobileSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            performSearch(e.target.value, '.mobile-view .user-card');
        });
    }
    
    // Búsqueda desktop
    const desktopSearchInput = document.getElementById('desktopSearch');
    if (desktopSearchInput) {
        desktopSearchInput.addEventListener('input', (e) => {
            performSearch(e.target.value, '.desktop-view .user-card');
        });
    }
}

// Realizar búsqueda
function performSearch(searchTerm, selector) {
    const userCards = document.querySelectorAll(selector);
    const term = searchTerm.toLowerCase();
    
    userCards.forEach(card => {
        const cardData = card.dataset.search;
        if (cardData.includes(term)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Inicializar event listeners
function initializeEventListeners() {
    console.log('Inicializando event listeners...');
    
    // Botones de ver detalles
    const showButtons = document.querySelectorAll('.show-user');
    console.log('Botones de ver encontrados:', showButtons.length);
    showButtons.forEach(button => {
        button.removeEventListener('click', handleShowUser);
        button.addEventListener('click', handleShowUser);
        console.log('Event listener agregado para botón:', button.dataset.id);
    });
    
    // Botones de eliminar
    const deleteButtons = document.querySelectorAll('.delete-user');
    console.log('Botones de eliminar encontrados:', deleteButtons.length);
    deleteButtons.forEach(button => {
        button.removeEventListener('click', handleDeleteUser);
        button.addEventListener('click', handleDeleteUser);
        console.log('Event listener agregado para botón eliminar:', button.dataset.id);
    });
}

// Cambiar modo de vista
function changeViewMode(mode) {
    console.log('Cambiando modo de vista a:', mode);
    currentViewMode = mode;
    localStorage.setItem('usersViewMode', mode);
    
    // Actualizar clases de los botones
    document.querySelectorAll('.view-mode-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activar el botón correspondiente
    const activeButton = mode === 'table' ? 
        document.querySelector('.view-mode-btn:first-child') : 
        document.querySelector('.view-mode-btn:last-child');
    if (activeButton) {
        activeButton.classList.add('active');
    }
    
    // Mostrar/ocultar vistas
    const tableView = document.getElementById('desktopTableView');
    const cardsView = document.getElementById('desktopCardsView');
    
    if (mode === 'table') {
        tableView.style.display = 'block';
        cardsView.style.display = 'none';
        setTimeout(initializeDataTable, 100);
    } else {
        tableView.style.display = 'none';
        cardsView.style.display = 'block';
    }
}

// Manejar mostrar detalles de usuario
function handleShowUser() {
    console.log('handleShowUser ejecutado');
    const userId = this.dataset.id;
    console.log('User ID:', userId);
    const modalOverlay = document.getElementById('showUserModal');
    if (modalOverlay) {
        modalOverlay.style.display = 'flex';
        loadUserData(userId);
    } else {
        console.error('Modal no encontrado');
    }
}

// Manejar eliminar usuario
function handleDeleteUser() {
    console.log('handleDeleteUser ejecutado');
    const userId = this.dataset.id;
    console.log('User ID para eliminar:', userId);
    const userName = this.closest('tr, .user-card').querySelector('.user-name, .user-card-name').textContent;
    console.log('User name:', userName);
    deleteUser(userId, userName);
}

// Cargar datos del usuario
async function loadUserData(userId) {
    console.log('loadUserData ejecutado para user ID:', userId);
    try {
        const response = await fetch(`/users/${userId}`);
        console.log('Response:', response);
        const data = await response.json();
        console.log('Data:', data);
        
        if (data.status === 'success') {
            document.getElementById('userName').textContent = data.user.name;
            document.getElementById('userEmail').textContent = data.user.email;
            document.getElementById('userCompany').textContent = data.user.company_name || 'N/A';
            
            const rolesHtml = data.user.roles.map(role =>
                `<span class="badge badge-role">${role.display_name}</span>`
            ).join('');
            document.getElementById('userRoles').innerHTML = rolesHtml || '<span class="text-muted">Sin rol asignado</span>';
            
            document.getElementById('userVerification').innerHTML = data.user.verified ?
                '<span class="badge badge-success">Verificado</span>' :
                '<span class="badge badge-warning">Pendiente de verificación</span>';
        } else {
            console.error('Error en la respuesta:', data);
            showAlert('Error', 'No se pudieron obtener los datos del usuario', 'error');
        }
    } catch (error) {
        console.error('Error en loadUserData:', error);
        showAlert('Error', 'No se pudieron obtener los datos del usuario', 'error');
    }
}

// Eliminar usuario
function deleteUser(userId, userName) {
    showConfirmDialog(
        '¿Estás seguro?',
        `¿Deseas eliminar al usuario <strong>${userName}</strong>?<br><small class="text-muted">Esta acción no se puede revertir</small>`,
        'warning',
        () => performDelete(userId)
    );
}

// Realizar eliminación
async function performDelete(userId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await fetch(`/users/delete/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showAlert('¡Eliminado!', data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('Error', data.message, 'error');
        }
    } catch (error) {
        showAlert('Error', 'No se pudo eliminar el usuario', 'error');
    }
}

// Mostrar diálogo de confirmación
function showConfirmDialog(title, html, icon, onConfirm) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            html: html,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                onConfirm();
            }
        });
    } else {
        if (confirm(title)) {
            onConfirm();
        }
    }
}

// Mostrar alerta
function showAlert(title, text, icon) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: 'Entendido'
        });
    } else {
        alert(`${title}: ${text}`);
    }
}

// Cerrar modal
function closeModal() {
    const modalOverlay = document.getElementById('showUserModal');
    if (modalOverlay) {
        modalOverlay.style.display = 'none';
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando...');
    initializeUsersPage();
    
    // Observar cambios en el DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                setTimeout(initializeEventListeners, 100);
            }
        });
    });
    
    const mainContainer = document.querySelector('.space-y-6');
    if (mainContainer) {
        observer.observe(mainContainer, { childList: true, subtree: true });
    }
    
    // Cerrar modal al hacer clic fuera
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('showUserModal');
        if (modal && e.target === modal) {
            closeModal();
        }
    });
});

// Hacer funciones disponibles globalmente
window.closeModal = closeModal;
window.changeViewMode = changeViewMode;
</script>
@endpush
@endsection
