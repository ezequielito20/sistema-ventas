#!/bin/bash

# 🚀 Script de Deployment - Roles Específicos por Empresa
# Ejecutar en el servidor de producción después de hacer el deploy del código

set -e  # Salir si hay algún error

echo "🚀 Iniciando deployment de roles específicos por empresa..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes con colores
print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto Laravel."
    exit 1
fi

print_info "Verificando entorno..."

# Verificar que estamos en producción
if [ "${APP_ENV}" != "production" ] && [ "${APP_ENV}" != "staging" ]; then
    print_warning "No estás en entorno de producción. ¿Continuar de todas formas? (y/N)"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        print_info "Deployment cancelado."
        exit 0
    fi
fi

print_success "Entorno verificado"

# Paso 1: Backup de base de datos (opcional pero recomendado)
print_info "¿Deseas crear un backup de la base de datos antes de continuar? (Y/n)"
read -r backup_response
if [[ "$backup_response" =~ ^[Yy]$ ]] || [[ -z "$backup_response" ]]; then
    print_info "Creando backup de base de datos..."
    
    # Obtener configuración de base de datos desde .env
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
    DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
    
    if [ -n "$DB_DATABASE" ]; then
        BACKUP_FILE="backup_roles_migration_$(date +%Y%m%d_%H%M%S).sql"
        print_info "Creando backup en: $BACKUP_FILE"
        
        if command -v pg_dump &> /dev/null; then
            PGPASSWORD="${DB_PASSWORD}" pg_dump -h "${DB_HOST}" -U "${DB_USERNAME}" "${DB_DATABASE}" > "$BACKUP_FILE"
            print_success "Backup creado: $BACKUP_FILE"
        else
            print_warning "pg_dump no encontrado. Saltando backup automático."
            print_warning "Asegúrate de hacer backup manual antes de continuar."
        fi
    else
        print_warning "No se pudo obtener configuración de DB. Saltando backup automático."
    fi
fi

# Paso 2: Ejecutar migraciones
print_info "Ejecutando migraciones de base de datos..."
php artisan migrate --force
print_success "Migraciones ejecutadas"

# Paso 3: Migrar roles a empresas
print_info "Ejecutando migración de roles a empresas..."
php artisan roles:migrate-to-companies --force
print_success "Migración de roles completada"

# Paso 4: Optimizaciones para producción
print_info "Aplicando optimizaciones para producción..."

# Limpiar y optimizar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

print_success "Optimizaciones aplicadas"

# Paso 5: Verificación final
print_info "Ejecutando verificaciones finales..."

# Verificar que los roles se crearon correctamente
ROLES_COUNT=$(php artisan tinker --execute="echo App\Models\Role::whereNotNull('company_id')->count();" 2>/dev/null | tail -1)
COMPANIES_COUNT=$(php artisan tinker --execute="echo App\Models\Company::count();" 2>/dev/null | tail -1)

if [ "$ROLES_COUNT" -gt 0 ] && [ "$COMPANIES_COUNT" -gt 0 ]; then
    print_success "Verificación exitosa: $ROLES_COUNT roles creados para $COMPANIES_COUNT empresas"
else
    print_error "Verificación falló. Revisar logs."
    exit 1
fi

# Paso 6: Mostrar estado final
print_info "Estado final del sistema:"
php artisan tinker --execute="
use App\Models\Role;
use App\Models\Company;
echo 'Empresas: ' . Company::count() . PHP_EOL;
echo 'Roles por empresa: ' . Role::whereNotNull('company_id')->count() . PHP_EOL;
Role::with('company')->whereNotNull('company_id')->get()->each(function(\$r) {
    echo '  - Rol: ' . \$r->name . ' | Empresa: ' . \$r->company->name . ' | Usuarios: ' . \$r->users->count() . PHP_EOL;
});
"

print_success "🎉 Deployment completado exitosamente!"
print_info "Cada empresa ahora tiene sus propios roles independientes."
print_info "Los usuarios pueden crear nuevos roles específicos para su empresa."

# Recordatorios finales
echo ""
print_warning "Recordatorios post-deployment:"
echo "  1. Verificar que la interfaz web muestra los roles correctamente"
echo "  2. Probar creación de nuevos roles"
echo "  3. Verificar asignación de roles a usuarios"
echo "  4. Monitorear logs por posibles errores"
echo ""

print_success "Deployment finalizado. ¡El sistema está listo!" 