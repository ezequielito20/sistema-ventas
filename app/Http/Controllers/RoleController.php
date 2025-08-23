<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
   public $currencies;
   protected $company;

   public function __construct()
   {
      $this->middleware(function ($request, $next) {
         $this->company = Auth::user()->company;
         $this->currencies = DB::table('currencies')
            ->where('country_id', $this->company->country)
            ->first();

         return $next($request);
      });
   }
   public function index()
   {
      // Optimización de gates - verificar permisos una sola vez
      $permissions = [
         'can_report' => Gate::allows('roles.report'),
         'can_create' => Gate::allows('roles.create'),
         'can_edit' => Gate::allows('roles.edit'),
         'can_show' => Gate::allows('roles.show'),
         'can_destroy' => Gate::allows('roles.destroy'),
         'can_assign_permissions' => Gate::allows('roles.permissions'),
      ];

      // Optimización: Seleccionar solo campos necesarios y contar relaciones
      $roles = Role::select('id', 'name', 'created_at', 'updated_at', 'company_id')
         ->withCount(['users', 'permissions'])
         ->byCompany($this->company->id)
         ->orderBy('name', 'asc')
         ->get();

      $company = $this->company;

      // Optimización: Solo cargar permisos necesarios para el modal (sin eager loading de roles)
      $permissionsList = Permission::select('id', 'name')->get()->map(function($permission) {
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

      return view('admin.roles.index', compact('roles', 'permissions', 'permissionsList', 'company'));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      $company = $this->company;
      return view('admin.roles.create', compact('company'));
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
            Rule::unique('roles', 'name')->where(function ($query) {
               return $query->where('guard_name', 'web')
                           ->where('company_id', $this->company->id);
            }),
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
            'company_id' => $this->company->id,
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
      $company = $this->company;
      $role = Role::where('id', $id)
                  ->where('company_id', $company->id)
                  ->firstOrFail();
      return view('admin.roles.edit', compact('role', 'company'));
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      $role = Role::where('id', $id)
                  ->where('company_id', $this->company->id)
                  ->firstOrFail();

      // Validación mejorada del request
      $validated = $request->validate([
         'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('roles', 'name')->ignore($role->id)->where(function ($query) {
               return $query->where('guard_name', 'web')
                           ->where('company_id', $this->company->id);
            }),
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
         // Validar ID
         if (!is_numeric($id) || $id <= 0) {
            return response()->json([
               'status' => 'error',
               'message' => 'ID de rol inválido: ' . $id,
               'details' => ['error_type' => 'validation_error']
            ], 400);
         }

         $role = Role::where('id', $id)
                     ->where('company_id', $this->company->id)
                     ->first();

         if (!$role) {
            return response()->json([
               'status' => 'error',
               'message' => 'Rol no encontrado (ID: ' . $id . ', Empresa: ' . $this->company->id . ')',
               'details' => ['error_type' => 'not_found']
            ], 404);
         }

         // Verificar si es un rol del sistema
         $systemRoles = ['admin', 'user', 'superadmin'];
         if (in_array($role->name, $systemRoles)) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se pueden eliminar roles del sistema (' . $role->name . ')',
               'details' => ['error_type' => 'system_role', 'role_name' => $role->name]
            ], 403);
         }

         // Verificar si el rol tiene usuarios asignados
         $usersCount = $role->users()->count();
         if ($usersCount > 0) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se puede eliminar un rol que tiene ' . $usersCount . ' usuario(s) asignado(s)',
               'details' => ['error_type' => 'has_users', 'users_count' => $usersCount]
            ], 403);
         }

         $role->delete();

         return response()->json([
            'status' => 'success',
            'message' => 'Rol "' . $role->name . '" eliminado exitosamente'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error interno al eliminar el rol: ' . $e->getMessage(),
            'details' => [
               'error_type' => class_basename($e),
               'debug_info' => [
                  'role_id' => $id,
                  'company_id' => $this->company ? $this->company->id : 'N/A'
               ]
            ]
         ], 500);
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         // Validar ID
         if (!is_numeric($id) || $id <= 0) {
            return response()->json([
               'status' => 'error',
               'message' => 'ID de rol inválido: ' . $id,
               'details' => ['error_type' => 'validation_error']
            ], 400);
         }

         $role = Role::with('permissions', 'users')
                     ->where('id', $id)
                     ->where('company_id', $this->company->id)
                     ->first();

         if (!$role) {
            return response()->json([
               'status' => 'error',
               'message' => 'Rol no encontrado (ID: ' . $id . ', Empresa: ' . $this->company->id . ')',
               'details' => ['error_type' => 'not_found']
            ], 404);
         }

         return response()->json([
            'status' => 'success',
            'role' => [
               'id' => $role->id,
               'name' => $role->name,
               'created_at' => $role->created_at->format('d/m/Y H:i'),
               'updated_at' => $role->updated_at->format('d/m/Y H:i'),
               'users_count' => $role->users->count(),
               'permissions_count' => $role->permissions->count(),
               'is_system_role' => in_array($role->name, ['admin', 'user', 'superadmin'])
            ]
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error interno al obtener los datos del rol: ' . $e->getMessage(),
            'details' => [
               'error_type' => class_basename($e),
               'debug_info' => [
                  'role_id' => $id,
                  'company_id' => $this->company ? $this->company->id : 'N/A'
               ]
            ]
         ], 500);
      }
   }

   public function report()
   {
      $company = Company::find(Auth::user()->company_id);
      $roles = Role::with('permissions')
      ->byCompany($company->id)
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
         // Validar ID
         if (!is_numeric($id) || $id <= 0) {
            return response()->json([
               'status' => 'error',
               'message' => 'ID de rol inválido: ' . $id,
               'details' => ['error_type' => 'validation_error']
            ], 400);
         }

         // Verificar que la empresa existe
         if (!$this->company || !$this->company->id) {
            return response()->json([
               'status' => 'error',
               'message' => 'No se pudo determinar la empresa del usuario autenticado',
               'details' => ['error_type' => 'company_not_found']
            ], 500);
         }

         $role = Role::where('id', $id)
                     ->where('company_id', $this->company->id)
                     ->first();

         if (!$role) {
            return response()->json([
               'status' => 'error',
               'message' => 'Rol no encontrado (ID: ' . $id . ', Empresa: ' . $this->company->id . ')',
               'details' => ['error_type' => 'not_found']
            ], 404);
         }

         // Obtener permisos con información adicional
         $permissions = $role->permissions->map(function($permission) {
            return [
               'id' => $permission->id,
               'name' => $permission->name,
               'guard_name' => $permission->guard_name,
               'created_at' => $permission->created_at->format('d/m/Y H:i')
            ];
         });

         return response()->json([
            'status' => 'success',
            'permissions' => $permissions,
            'role_info' => [
               'id' => $role->id,
               'name' => $role->name,
               'is_system_role' => in_array($role->name, ['admin', 'superadmin', 'administrator', 'root'])
            ]
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Error interno al obtener los permisos del rol: ' . $e->getMessage(),
            'details' => [
               'error_type' => class_basename($e),
               'debug_info' => [
                  'role_id' => $id,
                  'company_id' => $this->company ? $this->company->id : 'N/A',
                  'user_authenticated' => Auth::check()
               ]
            ]
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

         // Validar que el ID sea válido
         if (!is_numeric($id) || $id <= 0) {
            throw new \Exception('ID de rol inválido: ' . $id);
         }

         // Verificar que la empresa existe
         if (!$this->company || !$this->company->id) {
            throw new \Exception('No se pudo determinar la empresa del usuario autenticado');
         }

         // Buscar el rol
         $role = Role::where('id', $id)
                     ->where('company_id', $this->company->id)
                     ->first();

         if (!$role) {
            throw new \Exception('Rol no encontrado (ID: ' . $id . ', Empresa: ' . $this->company->id . ')');
         }

         // Verificar si es un rol del sistema
         $systemRoles = ['admin', 'superadmin', 'administrator', 'root'];
         if (in_array($role->name, $systemRoles)) {
            throw new \Exception('No se pueden modificar los permisos de roles del sistema (' . $role->name . ')');
         }

         // Validar los permisos recibidos
         $permissions = $request->input('permissions', []);

         // Verificar que los permisos sean válidos
         if (!is_array($permissions)) {
            throw new \Exception('Los permisos deben ser un array. Tipo recibido: ' . gettype($permissions));
         }

         // Filtrar permisos válidos
         $validPermissions = [];
         if (!empty($permissions)) {
            $validPermissions = Permission::whereIn('id', $permissions)->pluck('id')->toArray();
         }

         // Verificar que el rol puede ser modificado
         if (!$role->exists) {
            throw new \Exception('El rol no existe en la base de datos');
         }

         // Intentar sincronizar los permisos
         try {
            $role->syncPermissions($validPermissions);
         } catch (\Exception $syncError) {
            throw new \Exception('Error al sincronizar permisos: ' . $syncError->getMessage());
         }

         DB::commit();

         return response()->json([
            'status' => 'success',
            'message' => 'Permisos actualizados correctamente',
            'details' => [
               'role_name' => $role->name,
               'permissions_assigned' => count($validPermissions),
               'total_permissions_received' => count($permissions)
            ]
         ]);

      } catch (\Exception $e) {
         DB::rollBack();

         // Determinar el tipo de error y el código de respuesta
         $statusCode = 500;
         $errorMessage = $e->getMessage();

         if (str_contains($errorMessage, 'roles del sistema')) {
            $statusCode = 403;
         } elseif (str_contains($errorMessage, 'no encontrado')) {
            $statusCode = 404;
         } elseif (str_contains($errorMessage, 'inválido')) {
            $statusCode = 400;
         }

         return response()->json([
            'status' => 'error',
            'message' => $errorMessage,
            'details' => [
               'error_type' => class_basename($e),
               'error_code' => $e->getCode(),
               'debug_info' => [
                  'role_id' => $id ?? 'N/A',
                  'company_id' => $this->company->id ?? 'N/A',
                  'user_authenticated' => Auth::check(),
                  'request_method' => $request->method(),
                  'permissions_received' => is_array($request->input('permissions')) ? count($request->input('permissions', [])) : 'Invalid format'
               ]
            ]
         ], $statusCode);
      }
   }
}
