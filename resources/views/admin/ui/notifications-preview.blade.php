@extends('layouts.app')

@section('title', 'Preview UI Notifications')

@section('content')
    <div class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Preview de Alertas y Notificaciones</h1>
                <p class="card-subtitle">
                    Esta pagina permite aprobar el estilo futurista antes de migrar todos los modulos.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <button type="button" class="btn-success" data-toast-type="success">
                    <i class="fas fa-check-circle mr-2"></i>
                    Toast Exito
                </button>
                <button type="button" class="btn-danger" data-toast-type="error">
                    <i class="fas fa-times-circle mr-2"></i>
                    Toast Error
                </button>
                <button type="button" class="btn-secondary" data-toast-type="warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Toast Advertencia
                </button>
                <button type="button" class="btn-primary" data-toast-type="info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Toast Informacion
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Toasts Rich Data (futurista)</h2>
                <p class="card-subtitle">Notificaciones con informacion distribuida para escenarios reales.</p>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <button id="preview-toast-rich-success" type="button" class="btn-success">
                    <i class="fas fa-rocket mr-2"></i>
                    Deploy Exitoso (rich)
                </button>
                <button id="preview-toast-rich-critical" type="button" class="btn-danger">
                    <i class="fas fa-radiation mr-2"></i>
                    Incidencia Critica (rich)
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dialogs de Confirmacion / Alerta</h2>
                <p class="card-subtitle">Misma estructura para todos los modulos CRUD.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button id="preview-confirm-delete" type="button" class="btn-danger">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Confirmar Eliminacion
                </button>
                <button id="preview-confirm-action" type="button" class="btn-outline">
                    <i class="fas fa-bolt mr-2"></i>
                    Confirmar Accion
                </button>
                <button id="preview-alert-success" type="button" class="btn-success">
                    <i class="fas fa-badge-check mr-2"></i>
                    Alerta Exito
                </button>
                <button id="preview-alert-error" type="button" class="btn-danger">
                    <i class="fas fa-triangle-exclamation mr-2"></i>
                    Alerta Error
                </button>
                <button id="preview-alert-rich" type="button" class="btn-primary">
                    <i class="fas fa-wave-square mr-2"></i>
                    Alerta Rich Data
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Ruta de Preview</h2>
                <p class="card-subtitle">Comparte esta URL para revisiones internas.</p>
            </div>
            <code class="text-sm text-gray-700">{{ route('admin.ui.notifications.preview') }}</code>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toastMessageByType = {
                success: 'Cambios guardados correctamente.',
                error: 'No se pudo completar la operacion. Intenta de nuevo.',
                warning: 'Revisa los datos antes de continuar.',
                info: 'Hay nueva informacion disponible en el modulo.',
            };

            document.querySelectorAll('[data-toast-type]').forEach((button) => {
                button.addEventListener('click', () => {
                    const type = button.getAttribute('data-toast-type');
                    window.uiNotifications.showToast(toastMessageByType[type], {
                        type,
                    });
                });
            });

            document.getElementById('preview-confirm-delete')?.addEventListener('click', async () => {
                const confirmed = await window.uiNotifications.confirmDialog({
                    title: 'Eliminar registro',
                    text: 'Esta accion eliminara el registro seleccionado de forma permanente.',
                    type: 'warning',
                    confirmText: 'Eliminar',
                    cancelText: 'Cancelar',
                });

                window.uiNotifications.showToast(
                    confirmed ? 'Eliminacion confirmada.' : 'Operacion cancelada por el usuario.',
                    { type: confirmed ? 'success' : 'info' }
                );
            });

            document.getElementById('preview-confirm-action')?.addEventListener('click', async () => {
                const confirmed = await window.uiNotifications.confirmDialog({
                    title: 'Aplicar cambios masivos',
                    text: 'Se actualizaran los registros seleccionados.',
                    type: 'info',
                    confirmText: 'Aplicar cambios',
                    cancelText: 'Revisar primero',
                });

                window.uiNotifications.showToast(
                    confirmed ? 'Cambios aplicados en preview.' : 'No se realizaron cambios.',
                    { type: confirmed ? 'success' : 'warning' }
                );
            });

            document.getElementById('preview-alert-success')?.addEventListener('click', async () => {
                await window.uiNotifications.alertDialog({
                    title: 'Operacion completada',
                    text: 'El proceso finalizo correctamente.',
                    type: 'success',
                    confirmText: 'Perfecto',
                });
            });

            document.getElementById('preview-alert-error')?.addEventListener('click', async () => {
                await window.uiNotifications.alertDialog({
                    title: 'Error de sincronizacion',
                    text: 'No fue posible sincronizar los datos. Verifica la conexion.',
                    type: 'error',
                    subtitle: 'Se detecto timeout en el nodo principal.',
                    highlight: 'Sugencia: reintentar en 30 segundos o cambiar a nodo backup.',
                    metrics: [{
                            label: 'Nodos afectados',
                            value: '03'
                        },
                        {
                            label: 'Latencia promedio',
                            value: '2.7s'
                        }
                    ],
                    items: [
                        'El servicio de notificaciones queda degradado temporalmente.',
                        'No se pierden transacciones ya confirmadas.',
                        'Se activo monitoreo reforzado automaticamente.',
                    ],
                    confirmText: 'Entendido',
                });
            });

            document.getElementById('preview-toast-rich-success')?.addEventListener('click', () => {
                window.uiNotifications.showToast('Nueva version aplicada en produccion.', {
                    type: 'success',
                    title: 'Deploy completado',
                    subtitle: 'Sin downtime y con rollback disponible.',
                    metadata: ['v2.3.0', 'latencia -18%', '99.99% healthy'],
                    timeout: 5200,
                });
            });

            document.getElementById('preview-toast-rich-critical')?.addEventListener('click', () => {
                window.uiNotifications.showToast('Anomalia detectada en modulo de pagos.', {
                    type: 'critical',
                    title: 'Evento critico',
                    subtitle: 'Se aplico proteccion preventiva para evitar impactos.',
                    metadata: ['INC-2048', 'nodo us-east-1', 'escalado activo'],
                    timeout: 6000,
                });
            });

            document.getElementById('preview-alert-rich')?.addEventListener('click', async () => {
                await window.uiNotifications.alertDialog({
                    title: 'Resumen de corte diario',
                    text: 'El sistema consolido operaciones y detecto oportunidades de mejora.',
                    subtitle: 'Datos clave para toma de decisiones.',
                    type: 'info',
                    highlight: 'Margen neto diario por encima del objetivo en +6.4%.',
                    metrics: [{
                            label: 'Ventas cerradas',
                            value: '142'
                        },
                        {
                            label: 'Ticket promedio',
                            value: '$38.20'
                        },
                        {
                            label: 'Conversion',
                            value: '17.4%'
                        },
                        {
                            label: 'Satisfaccion',
                            value: '4.8/5'
                        }
                    ],
                    items: [
                        'Los productos de mayor rotacion aumentaron 12%.',
                        'Las devoluciones bajaron un 8% respecto al promedio semanal.',
                        'Se recomienda reforzar stock en categoria premium.',
                    ],
                    confirmText: 'Cerrar panel',
                });
            });
        });
    </script>
@endpush
