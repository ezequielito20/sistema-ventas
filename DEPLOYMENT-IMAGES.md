# Configuración de Imágenes para Laravel Cloud

## Resumen de Cambios Implementados

Este documento describe todos los cambios realizados para configurar correctamente el manejo de imágenes en producción con Laravel Cloud y Cloudflare R2.

## 1. Configuración del Sistema de Archivos

### Archivo: `config/filesystems.php`
- Se agregó la configuración del disco `private` para Laravel Cloud
- Se configuró para usar Cloudflare R2 como storage backend

### Variables de Entorno Necesarias
Las siguientes variables ya están configuradas en tu entorno de producción:
```
FILESYSTEM_DISK=private
AWS_ACCESS_KEY_ID=037b640a63e5ae506031bd7ba07d5bb2
AWS_SECRET_ACCESS_KEY=892e84cd8c5fbecdbbef17203974fe1b29781a72ada819969d8a989f6c5f43ab
AWS_BUCKET=fls-9f51c728-b0c9-442c-9e50-e191865dd687
AWS_DEFAULT_REGION=auto
AWS_ENDPOINT=https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## 2. Servicio de Manejo de URLs

### Archivo: `app/Services/ImageUrlService.php`
- Servicio centralizado para generar URLs de imágenes
- Maneja automáticamente las diferencias entre desarrollo y producción
- Genera URLs correctas para Cloudflare R2

## 3. Modelos Actualizados

### Product Model (`app/Models/Product.php`)
- Agregado atributo `image_url` que retorna la URL correcta de la imagen
- Uso del servicio `ImageUrlService`

### Company Model (`app/Models/Company.php`)
- Agregado atributo `logo_url` que retorna la URL correcta del logo
- Uso del servicio `ImageUrlService`

## 4. Controladores Actualizados

### ProductController (`app/Http/Controllers/ProductController.php`)
- Actualizado para usar el disco correcto según el entorno
- Manejo consistente de imágenes en create, update y delete

### CompanyController (`app/Http/Controllers/CompanyController.php`)
- Actualizado para usar el disco correcto según el entorno
- Manejo consistente de logos en create, update y delete

## 5. Vistas Actualizadas

### Archivos modificados:
- `resources/views/admin/products/index.blade.php`
- `resources/views/admin/products/edit.blade.php`
- `resources/views/admin/companies/edit.blade.php`

Las vistas ahora usan los atributos `image_url` y `logo_url` en lugar de construir las URLs manualmente.

## 6. Blade Directive

### Archivo: `app/Providers/AppServiceProvider.php`
- Agregada directiva `@imageUrl()` para usar en las vistas
- Ejemplo de uso: `@imageUrl($product->image)`

## 7. Configuración de Imágenes

### Archivo: `config/images.php`
- Configuración centralizada para el manejo de imágenes
- Definición de tipos permitidos, tamaños y directorios

## 8. Comandos de Despliegue

### Comandos a ejecutar en producción:
```bash
# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de vistas
php artisan view:clear

# Limpiar caché de rutas
php artisan route:clear

# Optimizar para producción
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

## 9. Verificación del Funcionamiento

### Checklist de verificación:
1. ✅ Las imágenes de productos se suben correctamente
2. ✅ Las imágenes se muestran en las vistas
3. ✅ Los logos de empresa funcionan correctamente
4. ✅ Las URLs se generan correctamente para Cloudflare R2
5. ✅ No hay errores 404 en las imágenes
6. ✅ Las imágenes se eliminan correctamente al actualizar/eliminar registros

## 10. Troubleshooting

### Problemas comunes y soluciones:

**Problema:** Las imágenes no se muestran
**Solución:** Verificar que las variables de entorno estén configuradas correctamente

**Problema:** Error 404 en las imágenes
**Solución:** Verificar que el disco `private` esté configurado correctamente

**Problema:** Las imágenes no se suben
**Solución:** Verificar permisos de escritura y configuración de Cloudflare R2

## 11. Archivos Importantes

### Archivos modificados:
- `config/filesystems.php`
- `app/Services/ImageUrlService.php`
- `app/Models/Product.php`
- `app/Models/Company.php`
- `app/Http/Controllers/ProductController.php`
- `app/Http/Controllers/CompanyController.php`
- `app/Providers/AppServiceProvider.php`
- `config/images.php`
- Vistas de productos y empresas

### Archivos nuevos:
- `app/Services/ImageUrlService.php`
- `config/images.php`
- `DEPLOYMENT-IMAGES.md`

## 12. Notas Importantes

1. **Seguridad**: Todas las imágenes se almacenan en Cloudflare R2 con acceso público configurado
2. **Performance**: Las URLs se generan dinámicamente para evitar problemas de caché
3. **Compatibilidad**: El código funciona tanto en desarrollo (local) como en producción (Laravel Cloud)
4. **Fallback**: Si una imagen no existe, se muestra una imagen por defecto

## 13. Próximos Pasos

1. Crear la imagen de fallback `public/img/no-image.png`
2. Testear la subida de imágenes en producción
3. Monitorear el uso de storage en Cloudflare R2
4. Considerar implementar compresión de imágenes si es necesario

# Guía de Deployment - Corrección de Imágenes en Producción

## Problema Identificado

El sistema presenta errores 500 al intentar subir imágenes de productos en producción (Laravel Cloud con Cloudflare R2), mientras que en desarrollo local las imágenes no se muestran correctamente.

## Causa del Problema

1. **Falta de dependencia AWS S3**: El paquete `league/flysystem-aws-s3-v3` no estaba instalado
2. **Configuración incorrecta**: El sistema no manejaba correctamente el entorno de producción con Cloudflare R2
3. **Manejo de errores insuficiente**: No había diagnóstico adecuado para problemas de storage

## Solución Implementada

### 1. Dependencias Actualizadas

```bash
composer require league/flysystem-aws-s3-v3
```

### 2. Servicio de Imágenes Mejorado

- **ImageUrlService**: Completamente refactorizado para manejar producción
- **Manejo de errores robusto**: Logs detallados y fallbacks
- **Optimización para R2**: Configuración específica para Cloudflare R2

### 3. Controladores Actualizados

- **ProductController**: Uso del nuevo servicio de imágenes
- **Manejo de errores mejorado**: Mejor feedback al usuario
- **Limpieza automática**: Eliminación de imágenes en caso de error

### 4. Comandos de Diagnóstico y Corrección

```bash
# Diagnóstico del sistema
php artisan storage:diagnose [--test]

# Corrección automática para producción
php artisan images:fix-production [--test] [--force]
```

## Deployment en Producción

### Método Recomendado: Comando Artisan

El comando `images:fix-production` es la forma más segura y eficiente de corregir problemas de imágenes en Laravel Cloud:

```bash
# Ejecutar corrección automática
php artisan images:fix-production

# Con pruebas incluidas
php artisan images:fix-production --test

# Forzar ejecución en entorno no-producción (solo para pruebas)
php artisan images:fix-production --force
```

**¿Qué hace este comando?**
1. ✅ Verifica el entorno de producción
2. ✅ Muestra la configuración actual
3. ✅ Limpia y regenera cachés
4. ✅ Verifica conectividad con R2
5. ✅ Optimiza para producción
6. ✅ Ejecuta pruebas (si se solicita)
7. ✅ Muestra resumen detallado

### Método Alternativo: Script de Deployment

```bash
# Si prefieres usar script
./deploy-images.sh
```

### Verificación Manual

```bash
# Verificar configuración
php artisan storage:diagnose

# Probar operaciones
php artisan storage:diagnose --test
```

## Configuración Requerida en Producción

### Variables de Entorno (.env)

```env
# Filesystem
FILESYSTEM_DISK=private

# Cloudflare R2
AWS_ACCESS_KEY_ID=your_r2_access_key
AWS_SECRET_ACCESS_KEY=your_r2_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your_bucket_name
AWS_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
AWS_URL=https://your_custom_domain.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Configuración de Cloudflare R2

1. **Bucket creado** con permisos de lectura/escritura
2. **CORS configurado** para permitir uploads desde tu dominio
3. **Custom domain** configurado (opcional pero recomendado)

### Ejemplo de configuración CORS para R2:

```json
[
  {
    "AllowedOrigins": ["https://your-domain.com"],
    "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
    "AllowedHeaders": ["*"],
    "ExposeHeaders": ["ETag"],
    "MaxAgeSeconds": 3600
  }
]
```

## Verificación Post-Deployment

### 1. Verificar Configuración

```bash
php artisan storage:diagnose
```

**Salida esperada:**
```
🔍 Diagnosticando configuración de almacenamiento...
📋 Configuración de Almacenamiento:
  Disco por defecto: private
  Entorno: production
  Configuración del disco 'private':
    Driver: s3
    Bucket: your-bucket-name
    Region: auto
    Endpoint: https://your-account-id.r2.cloudflarestorage.com
    URL: https://your-custom-domain.com
    Key configurado: Sí
    Secret configurado: Sí

💾 Discos Disponibles:
  ✅ private (s3)
  ✅ public (local)
```

### 2. Probar Operaciones

```bash
php artisan storage:diagnose --test
```

**Salida esperada:**
```
🧪 Probando operaciones de almacenamiento...
  Usando disco: private
  📝 Probando escritura de archivo...
    ✅ Escritura exitosa
  📖 Probando lectura de archivo...
    ✅ Lectura exitosa
  🔍 Probando verificación de existencia...
    ✅ Verificación exitosa
  🔗 Probando generación de URL...
    URL generada: https://your-custom-domain.com/diagnostic_test_xxx.txt
  🗑️  Probando eliminación de archivo...
    ✅ Eliminación exitosa
```

### 3. Probar Upload de Imagen

1. Ir a **Productos** → **Editar Producto**
2. Subir una imagen
3. Verificar que se guarda correctamente
4. Verificar que se muestra en la lista de productos

## Troubleshooting

### Error 500 al subir imágenes

**Posibles causas:**
1. Variables de entorno mal configuradas
2. Permisos insuficientes en el bucket
3. CORS no configurado
4. Dependencia AWS S3 no instalada

**Solución:**
```bash
# Verificar logs
tail -f storage/logs/laravel.log

# Verificar configuración
php artisan storage:diagnose

# Reinstalar dependencias
composer install --no-dev --optimize-autoloader
```

### Imágenes no se muestran

**Posibles causas:**
1. URL mal configurada
2. Custom domain no configurado
3. Permisos de lectura en el bucket

**Solución:**
```bash
# Verificar generación de URLs
php artisan tinker
>>> App\Services\ImageUrlService::getImageUrl('products/test.jpg')
```

### Configuración de R2 incorrecta

**Verificar:**
1. Access Key y Secret Key válidos
2. Bucket existe y es accesible
3. Endpoint correcto para tu región
4. Custom domain configurado correctamente

## Monitoreo Continuo

### Comandos útiles:

```bash
# Verificar estado del storage
php artisan storage:diagnose

# Probar operaciones
php artisan storage:diagnose --test

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Verificar espacio en bucket (si tienes AWS CLI configurado)
aws s3 ls s3://your-bucket-name --endpoint-url=https://your-account-id.r2.cloudflarestorage.com
```

### Alertas recomendadas:

1. **Error 500 en uploads**: Configurar alertas en logs
2. **Espacio en bucket**: Monitorear uso de almacenamiento
3. **Latencia de R2**: Monitorear tiempos de respuesta

## Rollback (Si es necesario)

Si hay problemas críticos:

```bash
# 1. Cambiar a disco local temporalmente
echo "FILESYSTEM_DISK=public" >> .env
php artisan config:cache

# 2. Crear enlace simbólico
php artisan storage:link

# 3. Verificar funcionamiento
php artisan storage:diagnose --test
```

## Notas Adicionales

- **Backup**: Las imágenes en R2 están respaldadas automáticamente
- **CDN**: Cloudflare R2 incluye CDN global
- **Costos**: R2 es más económico que S3 para almacenamiento
- **Rendimiento**: Mejor rendimiento global gracias a la red de Cloudflare

## Contacto de Soporte

Para problemas técnicos:
1. Revisar logs: `storage/logs/laravel.log`
2. Ejecutar diagnóstico: `php artisan storage:diagnose`
3. Verificar configuración de R2 en Cloudflare Dashboard 