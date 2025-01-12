<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      $roles = Role::with('permissions')
         ->orderBy('created_at', 'desc')
         ->get();

      // Agrupar permisos por módulo con nombres amigables
      $permissions = Permission::all()->map(function($permission) {
         // Agregar descripción amigable basada en el nombre técnico
         $friendlyNames = [
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

            // Arqueos
            'cash-counts.index' => 'Ver listado de arqueos',
            'cash-counts.create' => 'Crear arqueos',
            'cash-counts.edit' => 'Editar arqueos',
            'cash-counts.destroy' => 'Eliminar arqueos',
            'cash-counts.report' => 'Generar reportes de arqueos',
            'cash-counts.show' => 'Ver detalles de arqueos',
            'cash-counts.store-movement' => 'Registrar movimientos de caja',
            'cash-counts.close' => 'Cerrar arqueo de caja',

            // Configuración
            'companies.edit' => 'Editar configuración',
            'companies.create' => 'Crear configuración inicial',
            'companies.store' => 'Guardar configuración inicial',
            'companies.update' => 'Actualizar configuración',
         ];

         $permission->friendly_name = $friendlyNames[$permission->name] ?? ucfirst(str_replace('.', ' ', $permission->name));
         return $permission;
      })->groupBy(function($permission) {
         return explode('.', $permission->name)[0];
      });

      return view('admin.roles.index', compact('roles', 'permissions'));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      return view('admin.roles.create');
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      // Validación básica del request
      $validated = $request->validate([
         'name' => [
            'required',
            'string',
            'max:255',
            'unique:roles,name',
            // Asegura que el nombre solo contenga letras, números, espacios y guiones
            'regex:/^[a-zA-Z0-9\s-]+$/',
         ]
      ], [
         'name.required' => 'El nombre del rol es obligatorio',
         'name.string' => 'El nombre debe ser una cadena de texto',
         'name.max' => 'El nombre no puede exceder los 255 caracteres',
         'name.unique' => 'Este nombre de rol ya existe',
         'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
      ]);

      try {
         DB::beginTransaction();

         // Limpieza y formateo del nombre
         $roleName = trim(strtolower($validated['name']));

         // Verificación adicional de roles del sistema
         $systemRoles = ['admin', 'superadmin', 'administrator', 'root'];
         if (in_array($roleName, $systemRoles)) {
            throw new \Exception('No se pueden crear roles del sistema');
         }

         // Crear el rol
         $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
         ]);

         DB::commit();

         // Registro exitoso
         return redirect()->route('admin.roles.index')
            ->with('message', 'Rol creado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();

         // Si es un error específico de roles del sistema
         if ($e->getMessage() === 'No se pueden crear roles del sistema') {
            return redirect()->back()
               ->with('message', 'No se pueden crear roles del sistema')
               ->with('icons', 'error')
               ->withInput();
         }

         // Para otros errores
         return redirect()->back()
            ->with('message', 'Error al crear el rol: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit($id)
   {
      $role = Role::findOrFail($id);
      return view('admin.roles.edit', compact('role'));
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      $role = Role::findOrFail($id);

      // Validación mejorada del request
      $validated = $request->validate([
         'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('roles')->ignore($role->id),
            // Asegura que el nombre solo contenga letras, números, espacios y guiones
            'regex:/^[a-zA-Z0-9\s-]+$/',
         ]
      ], [
         'name.required' => 'El nombre del rol es obligatorio',
         'name.string' => 'El nombre debe ser una cadena de texto',
         'name.max' => 'El nombre no puede exceder los 255 caracteres',
         'name.unique' => 'Este nombre de rol ya existe',
         'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
      ]);

      try {
         DB::beginTransaction();

         // Limpieza y formateo del nombre
         $roleName = trim(strtolower($validated['name']));

         // Verificación adicional de roles del sistema
         $systemRoles = ['admin', 'superadmin', 'administrator', 'root', 'user'];
         if (in_array($roleName, $systemRoles) && !in_array($role->name, $systemRoles)) {
            throw new \Exception('No se puede cambiar a un nombre de rol del sistema');
         }

         // Verificar si es un rol del sistema que intenta ser modificado
         if (in_array($role->name, $systemRoles) && $role->name !== $roleName) {
            throw new \Exception('No se pueden modificar roles del sistema');
         }

         // Actualizar el rol
         $role->update([
            'name' => $roleName,
            'updated_at' => now(),
         ]);

         DB::commit();

         return redirect()->route('admin.roles.index')
            ->with('message', 'Rol actualizado exitosamente')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();

         // Si es un error específico de roles del sistema
         if (str_contains($e->getMessage(), 'roles del sistema')) {
            return redirect()->back()
               ->with('message', $e->getMessage())
               ->with('icons', 'error')
               ->withInput();
         }

         // Para otros errores
         return redirect()->back()
            ->with('message', 'Error al actualizar el rol: ' . $e->getMessage())
            ->with('icons', 'error')
            ->withInput();
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         $role = Role::findOrFail($id);

         // Verificar si es un rol del sistema
         if (in_array($role->name, ['admin', 'user', 'superadmin'])) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se pueden eliminar roles del sistema'
            ], 403);
         }

         // Verificar si el rol tiene usuarios asignados
         if ($role->users()->count() > 0) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se puede eliminar un rol que tiene usuarios asignados'
            ], 403);
         }

         $role->delete();

         return response()->json([
            'status' => 'success',
            'message' => 'Rol eliminado exitosamente'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al eliminar el rol'
         ], 500);
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         $role = Role::with('permissions', 'users')->findOrFail($id);

         return response()->json([
            'status' => 'success',
            'role' => [
               'id' => $role->id,
               'name' => $role->name,
               'created_at' => $role->created_at->format('d/m/Y H:i'),
               'updated_at' => $role->updated_at->format('d/m/Y H:i'),
               'users_count' => $role->users->count(),
               'permissions_count' => $role->permissions->count()
            ]
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener los datos del rol'
         ], 500);
      }
   }

   public function report()
   {
      $company = Company::find(Auth::user()->company_id);
      $roles = Role::with('permissions')
      ->orderBy('name', 'asc')
      ->get();
      $pdf = PDF::loadView('admin.roles.report', compact('roles', 'company'));
      return $pdf->stream('reporte-roles.pdf');
   }

   /**
    * Obtener los permisos actuales del rol
    */
   public function permissions($id)
   {
      try {
         $role = Role::findOrFail($id);
         return response()->json([
            'status' => 'success',
            'permissions' => $role->permissions
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener los permisos del rol'
         ], 500);
      }
   }

   /**
    * Asignar permisos al rol
    */
   public function assignPermissions(Request $request, $id)
   {
      try {
         DB::beginTransaction();

         $role = Role::findOrFail($id);

         // Verificar si es un rol del sistema
         if (in_array($role->name, ['admin', 'superadmin', 'administrator', 'root'])) {
            throw new \Exception('No se pueden modificar los permisos de roles del sistema');
         }

         // Validar los permisos recibidos
         $permissions = $request->input('permissions', []);
         $validPermissions = Permission::whereIn('id', $permissions)->pluck('id')->toArray();

         // Sincronizar los permisos
         $role->syncPermissions($validPermissions);

         DB::commit();

         return response()->json([
            'status' => 'success',
            'message' => 'Permisos actualizados correctamente'
         ]);

      } catch (\Exception $e) {
         DB::rollBack();
         
         // Si es un error específico de roles del sistema
         if (str_contains($e->getMessage(), 'roles del sistema')) {
            return response()->json([
               'status' => 'error',
               'message' => $e->getMessage()
            ], 403);
         }

         return response()->json([
            'status' => 'error',
            'message' => 'Error al actualizar los permisos: ' . $e->getMessage()
         ], 500);
      }
   }
}
