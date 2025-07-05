# 🚀 Deployment: Roles Específicos por Empresa

## 📋 Resumen de Cambios

Este deployment implementa **roles específicos por empresa**, resolviendo el problema donde los roles eran globales y se mostraban en todas las empresas.

### ✨ Características Implementadas

- **🏢 Roles por Empresa**: Cada empresa tiene sus propios roles independientes
- **🔒 Aislamiento Total**: Los roles de una empresa no aparecen en otras
- **👑 Rol Administrador Automático**: Se crea automáticamente para cada empresa
- **🛡️ Seguridad Mejorada**: Validaciones específicas por empresa
- **📊 Estadísticas Precisas**: Conteos y reportes filtrados por empresa

## 🗃️ Archivos Modificados

### Migraciones
- `database/migrations/2025_07_05_140037_add_company_id_to_roles_table.php`

### Modelos
- `app/Models/Role.php` (nuevo modelo personalizado)

### Controladores
- `app/Http/Controllers/RoleController.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/CompanyController.php`

### Configuración
- `config/permission.php`

### Comandos
- `app/Console/Commands/MigrateRolesToCompanies.php`

## 🚀 Instrucciones de Deployment

### 1. **Backup de Base de Datos**
```bash
# IMPORTANTE: Hacer backup antes del deployment
pg_dump your_database > backup_before_roles_migration.sql
```

### 2. **Deploy del Código**
```bash
# Subir todos los archivos modificados al servidor
git push origin main
```

### 3. **Ejecutar Migraciones**
```bash
# En el servidor de producción
php artisan migrate
```

### 4. **Migrar Roles Existentes**
```bash
# Ejecutar el comando de migración de datos
php artisan roles:migrate-to-companies

# O si quiere ejecutar sin confirmaciones (para scripts automatizados)
php artisan roles:migrate-to-companies --force
```

### 5. **Verificar el Resultado**
```bash
# Verificar que los roles se crearon correctamente
php artisan tinker --execute="
use App\Models\Role;
use App\Models\Company;
echo 'Empresas: ' . Company::count() . PHP_EOL;
echo 'Roles por empresa: ' . Role::whereNotNull('company_id')->count() . PHP_EOL;
Role::with('company')->whereNotNull('company_id')->get()->each(function(\$r) {
    echo 'Rol: ' . \$r->name . ' - Empresa: ' . \$r->company->name . ' - Usuarios: ' . \$r->users->count() . PHP_EOL;
});
"
```

## 🔧 Comando de Migración

### Uso del Comando

```bash
# Migración interactiva (recomendado para primera vez)
php artisan roles:migrate-to-companies

# Migración automática (para scripts de deployment)
php artisan roles:migrate-to-companies --force
```

### ¿Qué hace el comando?

1. **✅ Verificación**: Comprueba si ya se ejecutó antes
2. **📊 Estado Actual**: Muestra empresas y roles existentes
3. **🏗️ Creación**: Crea rol "administrador" para cada empresa
4. **👥 Asignación**: Asigna usuarios a sus roles específicos
5. **🧹 Limpieza**: Elimina roles antiguos sin empresa
6. **🔄 Caché**: Limpia todas las cachés necesarias

### Salida Esperada

```
🚀 Iniciando migración de roles a empresas específicas...

📊 Estado actual del sistema:
+----+------------------+
| ID | Empresa          |
+----+------------------+
| 1  | Test Company     |
| 2  | Ikigai Boutique  |
+----+------------------+

🏗️ Creando roles específicos por empresa...
   Procesando empresa: Test Company (ID: 1)
   ✓ Rol administrador creado/encontrado: ID 3
   ✓ Permisos asignados: 76

👥 Asignando usuarios a roles específicos por empresa...
   ✓ Usuario superAdmin asignado al rol administrador de Test Company

🧹 Limpiando roles antiguos sin empresa asignada...
   🗑️ Eliminando rol: administrador (ID: 1)

🔄 Limpiando cachés...
   ✓ Cachés limpiados

✅ Migración completada exitosamente!

🎉 Cada empresa ahora tiene sus propios roles independientes.
```

## ⚠️ Consideraciones Importantes

### Seguridad
- ✅ **Transacciones**: Todo se ejecuta en una transacción DB
- ✅ **Rollback**: Si hay error, se revierten todos los cambios
- ✅ **Verificación**: Comprueba antes de ejecutar
- ✅ **Backup**: Siempre hacer backup antes del deployment

### Compatibilidad
- ✅ **Backwards Compatible**: No rompe funcionalidad existente
- ✅ **Usuarios Existentes**: Mantiene asignaciones de roles
- ✅ **Permisos**: Conserva todos los permisos asignados

### Performance
- ✅ **Optimizado**: Usa consultas eficientes
- ✅ **Caché**: Limpia automáticamente las cachés
- ✅ **Índices**: Mantiene índices de base de datos

## 🧪 Testing en Producción

### Verificar Funcionalidad

1. **Login**: Ingresar al sistema
2. **Roles**: Ir a "Gestión de Roles"
3. **Verificar**: Debe mostrar el rol "administrador"
4. **Crear**: Intentar crear un nuevo rol
5. **Usuarios**: Verificar asignación de roles en usuarios

### Rollback (si es necesario)

```bash
# Si algo sale mal, restaurar backup
psql your_database < backup_before_roles_migration.sql

# Revertir migración
php artisan migrate:rollback --step=1
```

## 📞 Soporte

Si encuentras algún problema durante el deployment:

1. **Verificar logs**: `storage/logs/laravel.log`
2. **Estado de DB**: Ejecutar queries de verificación
3. **Caché**: Limpiar todas las cachés
4. **Rollback**: Usar backup si es necesario

## ✅ Checklist de Deployment

- [ ] Backup de base de datos realizado
- [ ] Código subido al servidor
- [ ] `php artisan migrate` ejecutado
- [ ] `php artisan roles:migrate-to-companies` ejecutado
- [ ] Verificación de roles en interfaz web
- [ ] Testing de creación de nuevos roles
- [ ] Testing de asignación de usuarios
- [ ] Verificación de permisos funcionando

---

**🎉 ¡Deployment completado! Cada empresa ahora tiene sus roles independientes.** 