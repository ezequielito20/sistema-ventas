<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar revisión de claves foráneas
        Schema::disableForeignKeyConstraints();

        // Limpiar la tabla de permisos
        Permission::query()->delete();

        $permissions = [
            // Usuarios
            'users.index' => 'Ver listado de usuarios',
            'users.create' => 'Crear usuarios',
            'users.edit' => 'Editar usuarios',
            'users.destroy' => 'Eliminar usuarios',
            'users.report' => 'Generar reportes de usuarios',
            'users.show' => 'Ver detalles de usuarios',

            // Roles
            'roles.index' => 'Ver listado de roles',
            'roles.create' => 'Crear roles',
            'roles.edit' => 'Editar roles',
            'roles.destroy' => 'Eliminar roles',
            'roles.report' => 'Generar reportes de roles',
            'roles.show' => 'Ver detalles de roles',
            'roles.permissions' => 'Asignar permisos a roles',
            'roles.assign.permissions' => 'Asignar permisos a roles',
            // Permisos
            'permissions.index' => 'Ver listado de permisos',
            'permissions.create' => 'Crear permisos',
            'permissions.edit' => 'Editar permisos',
            'permissions.destroy' => 'Eliminar permisos',
            'permissions.report' => 'Generar reportes de permisos',
            'permissions.show' => 'Ver detalles de permisos',

            // Clientes
            'customers.index' => 'Ver listado de clientes',
            'customers.create' => 'Crear clientes',
            'customers.edit' => 'Editar clientes',
            'customers.destroy' => 'Eliminar clientes',
            'customers.report' => 'Generar reportes de clientes',
            'customers.show' => 'Ver detalles de clientes',

            // Proveedores
            'suppliers.index' => 'Ver listado de proveedores',
            'suppliers.create' => 'Crear proveedores',
            'suppliers.edit' => 'Editar proveedores',
            'suppliers.destroy' => 'Eliminar proveedores',
            'suppliers.report' => 'Generar reportes de proveedores',
            'suppliers.show' => 'Ver detalles de proveedores',

            // Productos
            'products.index' => 'Ver listado de productos',
            'products.create' => 'Crear productos',
            'products.edit' => 'Editar productos',
            'products.destroy' => 'Eliminar productos',
            'products.report' => 'Generar reportes de productos',
            'products.show' => 'Ver detalles de productos',

            // Categorías
            'categories.index' => 'Ver listado de categorías',
            'categories.create' => 'Crear categorías',
            'categories.edit' => 'Editar categorías',
            'categories.destroy' => 'Eliminar categorías',
            'categories.report' => 'Generar reportes de categorías',
            'categories.show' => 'Ver detalles de categorías',

            // Ventas
            'sales.index' => 'Ver listado de ventas',
            'sales.create' => 'Crear ventas',
            'sales.edit' => 'Editar ventas',
            'sales.update' => 'Actualizar ventas',
            'sales.show' => 'Ver detalles de ventas',
            'sales.destroy' => 'Eliminar ventas',
            'sales.report' => 'Generar reportes de ventas',
            'sales.print' => 'Imprimir ventas',
            'sales.details' => 'Ver detalles de productos en ventas',
            'sales.product-details' => 'Ver detalles de producto específico en ventas',
            'sales.product-by-code' => 'Buscar producto por código en ventas',

            // Compras
            'purchases.index' => 'Ver listado de compras',
            'purchases.create' => 'Crear compras',
            'purchases.show' => 'Ver detalles de compras',
            'purchases.destroy' => 'Eliminar compras',
            'purchases.report' => 'Generar reportes de compras',
            'purchases.details' => 'Ver detalles de productos en compras',
            'purchases.product-details' => 'Ver detalles de producto específico en compras',
            'purchases.product-by-code' => 'Buscar producto por código en compras',
            'purchases.edit' => 'Editar compras',
            // Arqueos
            'cash-counts.index' => 'Ver listado de arqueos',
            'cash-counts.create' => 'Crear arqueos',
            'cash-counts.edit' => 'Editar arqueos',
            'cash-counts.destroy' => 'Eliminar arqueos',
            'cash-counts.report' => 'Generar reportes de arqueos',
            'cash-counts.show' => 'Ver detalles de arqueos',
            'cash-counts.store-movement' => 'Registrar movimientos de caja',
            'cash-counts.close' => 'Cerrar arqueo de caja',

            'orders.index' => 'Ver pedidos desde catálogo',
            'orders.update' => 'Actualizar pedidos (pago, entrega, resumen)',
            'orders.cancel' => 'Cancelar pedidos pendientes',
            'orders.settings' => 'Configurar métodos de pago y entrega del catálogo (legado)',

            'catalog-payments.index' => 'Ver métodos de pago del catálogo',
            'catalog-payments.create' => 'Crear métodos de pago del catálogo',
            'catalog-payments.edit' => 'Editar métodos de pago del catálogo',
            'catalog-payments.destroy' => 'Eliminar métodos de pago del catálogo',
            'catalog-payments.report' => 'Generar informes PDF de métodos de pago del catálogo',
            'catalog-payments.show' => 'Ver detalle de método de pago del catálogo',

            'catalog-deliveries.index' => 'Ver métodos de entrega del catálogo',
            'catalog-deliveries.create' => 'Crear métodos de entrega del catálogo',
            'catalog-deliveries.edit' => 'Editar métodos de entrega del catálogo',
            'catalog-deliveries.destroy' => 'Eliminar métodos de entrega del catálogo',
            'catalog-deliveries.report' => 'Generar informes PDF de métodos de entrega del catálogo',
            'catalog-deliveries.show' => 'Ver detalle de método de entrega del catálogo',

            // Configuración
            'companies.edit' => 'Editar configuración',
            'companies.create' => 'Crear configuración inicial',
            'companies.store' => 'Guardar configuración inicial',
            'companies.update' => 'Actualizar configuración',

            // Mi plan (suscripción tenant)
            'my-plan.view' => 'Ver resumen del plan y límites de la suscripción',

            // Sistema / Super Admin
            'system.owner' => 'Acceso al panel de super administrador',
            'plans.index' => 'Ver listado de planes',
            'plans.create' => 'Crear planes',
            'plans.edit' => 'Editar planes',
            'plans.destroy' => 'Eliminar planes',
            'subscriptions.index' => 'Ver listado de suscripciones',
            'subscriptions.edit' => 'Editar suscripciones',
            'subscriptions.payments' => 'Gestionar pagos de suscripciones',
            'super-admin.access' => 'Acceso al panel de administración del sistema',
        ];

        // Crear los permisos
        foreach ($permissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $this->adjustAutoIncrement('permissions');

        // Reactivar revisión de claves foráneas
        Schema::enableForeignKeyConstraints();

        $this->command->info('Se han creado '.count($permissions).' permisos.');
    }

    protected function adjustAutoIncrement(string $table)
    {
        $maxId = DB::table($table)->max('id') + 1;

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $sequenceName = $table.'_id_seq';
            DB::statement("SELECT setval('$sequenceName', $maxId)");
        } elseif ($driver === 'mysql' or $driver === 'mariadb') {
            DB::statement("ALTER TABLE $table AUTO_INCREMENT = $maxId");
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE sqlite_sequence SET seq = $maxId WHERE name = '$table'");
        } else {
            throw new \Exception('Unsupported database driver: '.$driver);
        }
    }
}
