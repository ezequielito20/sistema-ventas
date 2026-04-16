<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Services\RoleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        try {
            return view('admin.v2.roles.index');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Error al cargar los roles')
                ->with('icons', 'error');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.v2.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, RoleService $roleService)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where(function ($query) {
                    return $query->where('guard_name', 'web')
                        ->where('company_id', $this->company->id);
                }),
                'regex:/^[a-zA-Z0-9\s-]+$/',
            ],
        ], [
            'name.required' => 'El nombre del rol es obligatorio',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'name.unique' => 'Este nombre de rol ya existe',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
        ]);

        try {
            $roleService->createRole($this->company->id, $validated['name']);

            return redirect()->route('admin.roles.index')
                ->with('message', 'Rol creado exitosamente')
                ->with('icons', 'success');
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->with('message', $e->getMessage())
                ->with('icons', 'error')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Error al crear el rol: '.$e->getMessage())
                ->with('icons', 'error')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::where('id', $id)
            ->where('company_id', $this->company->id)
            ->firstOrFail();

        return view('admin.v2.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, RoleService $roleService)
    {
        $role = Role::where('id', $id)
            ->where('company_id', $this->company->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id)->where(function ($query) {
                    return $query->where('guard_name', 'web')
                        ->where('company_id', $this->company->id);
                }),
                'regex:/^[a-zA-Z0-9\s-]+$/',
            ],
        ], [
            'name.required' => 'El nombre del rol es obligatorio',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'name.unique' => 'Este nombre de rol ya existe',
            'name.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
        ]);

        try {
            $roleService->updateRole($role, $validated['name']);

            return redirect()->route('admin.roles.index')
                ->with('message', 'Rol actualizado exitosamente')
                ->with('icons', 'success');
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->with('message', $e->getMessage())
                ->with('icons', 'error')
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Error al actualizar el rol: '.$e->getMessage())
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
            if (! is_numeric($id) || $id <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID de rol inválido: '.$id,
                    'details' => ['error_type' => 'validation_error'],
                ], 400);
            }

            $role = Role::where('id', $id)
                ->where('company_id', $this->company->id)
                ->first();

            if (! $role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rol no encontrado (ID: '.$id.', Empresa: '.$this->company->id.')',
                    'details' => ['error_type' => 'not_found'],
                ], 404);
            }

            // Verificar si es un rol del sistema
            $systemRoles = ['admin', 'user', 'superadmin'];
            if (in_array($role->name, $systemRoles)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pueden eliminar roles del sistema ('.$role->name.')',
                    'details' => ['error_type' => 'system_role', 'role_name' => $role->name],
                ], 403);
            }

            // Verificar si el rol tiene usuarios asignados
            $usersCount = $role->users()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar un rol que tiene '.$usersCount.' usuario(s) asignado(s)',
                    'details' => ['error_type' => 'has_users', 'users_count' => $usersCount],
                ], 403);
            }

            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Rol "'.$role->name.'" eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al eliminar el rol: '.$e->getMessage(),
                'details' => [
                    'error_type' => class_basename($e),
                    'debug_info' => [
                        'role_id' => $id,
                        'company_id' => $this->company ? $this->company->id : 'N/A',
                    ],
                ],
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
            if (! is_numeric($id) || $id <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID de rol inválido: '.$id,
                    'details' => ['error_type' => 'validation_error'],
                ], 400);
            }

            $role = Role::with('permissions', 'users')
                ->where('id', $id)
                ->where('company_id', $this->company->id)
                ->first();

            if (! $role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rol no encontrado (ID: '.$id.', Empresa: '.$this->company->id.')',
                    'details' => ['error_type' => 'not_found'],
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
                    'is_system_role' => in_array($role->name, ['admin', 'user', 'superadmin']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al obtener los datos del rol: '.$e->getMessage(),
                'details' => [
                    'error_type' => class_basename($e),
                    'debug_info' => [
                        'role_id' => $id,
                        'company_id' => $this->company ? $this->company->id : 'N/A',
                    ],
                ],
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
            if (! is_numeric($id) || $id <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID de rol inválido: '.$id,
                    'details' => ['error_type' => 'validation_error'],
                ], 400);
            }

            // Verificar que la empresa existe
            if (! $this->company || ! $this->company->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo determinar la empresa del usuario autenticado',
                    'details' => ['error_type' => 'company_not_found'],
                ], 500);
            }

            $role = Role::where('id', $id)
                ->where('company_id', $this->company->id)
                ->first();

            if (! $role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rol no encontrado (ID: '.$id.', Empresa: '.$this->company->id.')',
                    'details' => ['error_type' => 'not_found'],
                ], 404);
            }

            // Obtener permisos con información adicional
            $permissions = $role->permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                    'created_at' => $permission->created_at->format('d/m/Y H:i'),
                ];
            });

            return response()->json([
                'status' => 'success',
                'permissions' => $permissions,
                'role_info' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'is_system_role' => in_array($role->name, ['admin', 'superadmin', 'administrator', 'root']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al obtener los permisos del rol: '.$e->getMessage(),
                'details' => [
                    'error_type' => class_basename($e),
                    'debug_info' => [
                        'role_id' => $id,
                        'company_id' => $this->company ? $this->company->id : 'N/A',
                        'user_authenticated' => Auth::check(),
                    ],
                ],
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
            if (! is_numeric($id) || $id <= 0) {
                throw new \Exception('ID de rol inválido: '.$id);
            }

            // Verificar que la empresa existe
            if (! $this->company || ! $this->company->id) {
                throw new \Exception('No se pudo determinar la empresa del usuario autenticado');
            }

            // Buscar el rol
            $role = Role::where('id', $id)
                ->where('company_id', $this->company->id)
                ->first();

            if (! $role) {
                throw new \Exception('Rol no encontrado (ID: '.$id.', Empresa: '.$this->company->id.')');
            }

            // Verificar si es un rol del sistema
            $systemRoles = ['admin', 'superadmin', 'administrator', 'root'];
            if (in_array($role->name, $systemRoles)) {
                throw new \Exception('No se pueden modificar los permisos de roles del sistema ('.$role->name.')');
            }

            // Validar los permisos recibidos
            $permissions = $request->input('permissions', []);

            // Verificar que los permisos sean válidos
            if (! is_array($permissions)) {
                throw new \Exception('Los permisos deben ser un array. Tipo recibido: '.gettype($permissions));
            }

            // Filtrar permisos válidos
            $validPermissions = [];
            if (! empty($permissions)) {
                $validPermissions = Permission::whereIn('id', $permissions)->pluck('id')->toArray();
            }

            // Verificar que el rol puede ser modificado
            if (! $role->exists) {
                throw new \Exception('El rol no existe en la base de datos');
            }

            // Intentar sincronizar los permisos
            try {
                $role->syncPermissions($validPermissions);
            } catch (\Exception $syncError) {
                throw new \Exception('Error al sincronizar permisos: '.$syncError->getMessage());
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Permisos actualizados correctamente',
                'details' => [
                    'role_name' => $role->name,
                    'permissions_assigned' => count($validPermissions),
                    'total_permissions_received' => count($permissions),
                ],
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
                        'permissions_received' => is_array($request->input('permissions')) ? count($request->input('permissions', [])) : 'Invalid format',
                    ],
                ],
            ], $statusCode);
        }
    }
}
