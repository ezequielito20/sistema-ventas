#!/bin/bash

# Script de deployment para arreglar problemas de imágenes en producción
# Laravel Cloud con Cloudflare R2

set -e

echo "🚀 Iniciando deployment de corrección de imágenes..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes con colores
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar que estamos en producción
if [ "$APP_ENV" != "production" ]; then
    print_error "Este script solo debe ejecutarse en producción"
    exit 1
fi

print_status "Verificando entorno de producción..."

# 1. Verificar dependencias
print_status "Verificando dependencias de AWS S3..."
if ! composer show | grep -q "league/flysystem-aws-s3-v3"; then
    print_warning "Instalando dependencia de AWS S3..."
    composer require league/flysystem-aws-s3-v3 --no-dev --optimize-autoloader
    print_success "Dependencia AWS S3 instalada"
else
    print_success "Dependencia AWS S3 ya está instalada"
fi

# 2. Verificar configuración de R2
print_status "Verificando configuración de Cloudflare R2..."
php artisan storage:diagnose

# 3. Limpiar caché de configuración
print_status "Limpiando caché de configuración..."
php artisan config:clear
php artisan config:cache

# 4. Optimizar autoloader
print_status "Optimizando autoloader..."
composer dump-autoload --optimize --no-dev

# 5. Probar operaciones de storage
print_status "Probando operaciones de storage..."
if php artisan storage:diagnose --test; then
    print_success "Operaciones de storage funcionando correctamente"
else
    print_error "Problemas detectados en operaciones de storage"
    print_status "Ejecutando diagnóstico detallado..."
    php artisan storage:diagnose
    exit 1
fi

# 6. Limpiar caché de aplicación
print_status "Limpiando caché de aplicación..."
php artisan cache:clear

# 7. Optimizar para producción
print_status "Optimizando para producción..."
php artisan route:cache
php artisan view:cache

print_success "✅ Deployment de corrección de imágenes completado exitosamente"

echo ""
echo "📋 Resumen del deployment:"
echo "   - Dependencias AWS S3: ✅ Verificadas"
echo "   - Configuración R2: ✅ Verificada"
echo "   - Operaciones storage: ✅ Probadas"
echo "   - Caché: ✅ Limpiado y optimizado"
echo ""
echo "🔧 Si persisten problemas, verificar:"
echo "   - Variables de entorno AWS_* en .env"
echo "   - Permisos del bucket en Cloudflare R2"
echo "   - Configuración de CORS en R2"
echo ""
echo "📊 Para monitorear el sistema:"
echo "   php artisan storage:diagnose --test" 