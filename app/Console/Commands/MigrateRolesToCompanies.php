<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class MigrateRolesToCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:migrate-to-companies {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing roles to be company-specific and create administrator roles for each company';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando migración de roles a empresas específicas...');
        
        // Verificar si ya se ejecutó antes
        if ($this->hasCompanySpecificRoles()) {
            $this->warn('⚠️  Ya existen roles específicos por empresa. La migración ya fue ejecutada.');
            
            if (!$this->option('force') && !$this->confirm('¿Desea continuar de todas formas?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        // Mostrar información actual
        $this->showCurrentState();

        // Confirmar antes de proceder (excepto si se usa --force)
        if (!$this->option('force') && !$this->confirm('¿Desea proceder con la migración?')) {
            $this->info('Operación cancelada.');
            return 0;
        }

        try {
            DB::beginTransaction();

            // Paso 1: Crear roles específicos por empresa
            $this->createCompanySpecificRoles();

            // Paso 2: Asignar usuarios a sus roles específicos
            $this->assignUsersToCompanyRoles();

            // Paso 3: Limpiar roles antiguos sin empresa
            $this->cleanupOldRoles();

            // Paso 4: Limpiar caché
            $this->clearCaches();

            DB::commit();

            $this->info('✅ Migración completada exitosamente!');
            $this->showFinalState();

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error durante la migración: ' . $e->getMessage());
            $this->error('Los cambios han sido revertidos.');
            return 1;
        }
    }

    /**
     * Verificar si ya existen roles específicos por empresa
     */
    private function hasCompanySpecificRoles(): bool
    {
        return Role::whereNotNull('company_id')->exists();
    }

    /**
     * Mostrar el estado actual del sistema
     */
    private function showCurrentState(): void
    {
        $this->info('📊 Estado actual del sistema:');
        
        $companies = Company::all();
        $this->table(['ID', 'Empresa'], $companies->map(function ($company) {
            return [$company->id, $company->name];
        })->toArray());

        $roles = Role::all();
        if ($roles->count() > 0) {
            $this->table(['ID', 'Nombre', 'Company ID', 'Usuarios'], $roles->map(function ($role) {
                return [
                    $role->id,
                    $role->name,
                    $role->company_id ?? 'NULL',
                    $role->users->count()
                ];
            })->toArray());
        } else {
            $this->info('No hay roles en el sistema.');
        }
    }

    /**
     * Crear roles específicos por empresa
     */
    private function createCompanySpecificRoles(): void
    {
        $this->info('🏗️  Creando roles específicos por empresa...');
        
        $companies = Company::all();
        $allPermissions = Permission::all();

        foreach ($companies as $company) {
            $this->line("   Procesando empresa: {$company->name} (ID: {$company->id})");
            
            // Crear rol administrador para esta empresa
            $adminRole = Role::firstOrCreate([
                'name' => 'administrador',
                'guard_name' => 'web',
                'company_id' => $company->id
            ]);
            
            $this->line("   ✓ Rol administrador creado/encontrado: ID {$adminRole->id}");
            
            // Asignar todos los permisos al rol administrador
            $adminRole->syncPermissions($allPermissions);
            $this->line("   ✓ Permisos asignados: {$allPermissions->count()}");
        }
    }

    /**
     * Asignar usuarios a sus roles específicos por empresa
     */
    private function assignUsersToCompanyRoles(): void
    {
        $this->info('👥 Asignando usuarios a roles específicos por empresa...');
        
        $companies = Company::all();

        foreach ($companies as $company) {
            $adminRole = Role::where('name', 'administrador')
                            ->where('company_id', $company->id)
                            ->first();
            
            if (!$adminRole) {
                $this->warn("   ⚠️  No se encontró rol administrador para empresa {$company->name}");
                continue;
            }

            $users = User::where('company_id', $company->id)->get();
            
            foreach ($users as $user) {
                // Remover roles anteriores y asignar el nuevo rol específico de la empresa
                $user->syncRoles([$adminRole]);
                $this->line("   ✓ Usuario {$user->name} asignado al rol administrador de {$company->name}");
            }
        }
    }

    /**
     * Limpiar roles antiguos sin empresa asignada
     */
    private function cleanupOldRoles(): void
    {
        $this->info('🧹 Limpiando roles antiguos sin empresa asignada...');
        
        $oldRoles = Role::whereNull('company_id')->get();
        
        foreach ($oldRoles as $role) {
            if ($role->users->count() == 0) {
                $this->line("   🗑️  Eliminando rol: {$role->name} (ID: {$role->id})");
                $role->delete();
            } else {
                $this->warn("   ⚠️  Manteniendo rol {$role->name} porque tiene usuarios asignados");
            }
        }
    }

    /**
     * Limpiar cachés del sistema
     */
    private function clearCaches(): void
    {
        $this->info('🔄 Limpiando cachés...');
        
        // Limpiar caché de configuración
        $this->call('config:clear');
        
        // Limpiar caché de aplicación
        $this->call('cache:clear');
        
        // Limpiar caché de permisos
        $this->call('permission:cache-reset');
        
        $this->line('   ✓ Cachés limpiados');
    }

    /**
     * Mostrar el estado final después de la migración
     */
    private function showFinalState(): void
    {
        $this->info('📊 Estado final del sistema:');
        
        $roles = Role::with('users', 'company')->get();
        
        if ($roles->count() > 0) {
            $this->table(['ID', 'Nombre', 'Empresa', 'Usuarios'], $roles->map(function ($role) {
                return [
                    $role->id,
                    $role->name,
                    $role->company ? $role->company->name : 'Sin empresa',
                    $role->users->count()
                ];
            })->toArray());
        }

        $this->info('🎉 Cada empresa ahora tiene sus propios roles independientes.');
        $this->info('💡 Los usuarios pueden crear nuevos roles específicos para su empresa.');
    }
}
