<?php

namespace App\Http\Controllers;

use App\Services\PermissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
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
            return view('admin.v2.permissions.index');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Error al cargar los permisos: '.$e->getMessage())
                ->with('icons', 'error');
        }
    }

    public function create()
    {
        try {
            return view('admin.v2.permissions.create');
        } catch (\Exception $e) {
            return redirect()->route('admin.permissions.index')
                ->with('message', 'Error al cargar el formulario de creación')
                ->with('icons', 'error');
        }
    }

    public function store(Request $request, PermissionService $permissionService)
    {
        try {
            $validated = $request->validate(
                $permissionService->rulesForCreate(),
                $permissionService->validationMessages()
            );

            $permissionService->createPermission($validated['name']);

            return redirect()->route('admin.permissions.index')
                ->with('message', 'Permiso creado correctamente')
                ->with('icons', 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Error al crear el permiso: '.$e->getMessage())
                ->with('icons', 'error')
                ->withInput();
        }
    }

    public function show(string $id)
    {
        try {
            $permission = Permission::with(['roles'])->findOrFail($id);

            $usersCount = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->where('role_has_permissions.permission_id', $id)
                ->distinct('users.id')
                ->count('users.id');

            $permissionData = [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'roles_count' => $permission->roles->count(),
                'users_count' => $usersCount,
                'created_at' => $permission->created_at->format('d/m/Y H:i'),
                'updated_at' => $permission->updated_at->format('d/m/Y H:i'),
                'roles' => $permission->roles->pluck('name')->toArray(),
                'users' => [],
            ];

            return response()->json([
                'status' => 'success',
                'permission' => $permissionData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudieron obtener los datos del permiso',
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $permission = Permission::findOrFail($id);

            return view('admin.v2.permissions.edit', compact('permission'));
        } catch (\Exception $e) {
            return redirect()->route('admin.permissions.index')
                ->with('message', 'Error al cargar el formulario de edición')
                ->with('icons', 'error');
        }
    }

    public function update(Request $request, string $id, PermissionService $permissionService)
    {
        try {
            $permission = Permission::findOrFail($id);

            $validated = $request->validate(
                $permissionService->rulesForUpdate($permission),
                $permissionService->validationMessages()
            );

            $permissionService->updatePermission($permission, $validated['name']);

            return redirect()->route('admin.permissions.index')
                ->with('message', 'Permiso actualizado correctamente')
                ->with('icons', 'success');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('message', 'Error al actualizar el permiso: '.$e->getMessage())
                ->with('icons', 'error')
                ->withInput();
        }
    }

    public function destroy(string $id, PermissionService $permissionService)
    {
        try {
            $permission = Permission::findOrFail($id);

            try {
                $permissionService->deletePermission($permission);
            } catch (\RuntimeException $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'icons' => 'error',
                ], 422);
            }

            return response()->json([
                'message' => 'Permiso eliminado correctamente',
                'icons' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el permiso',
                'icons' => 'error',
            ], 500);
        }
    }

    public function report(Request $request)
    {
        try {
            $company = $this->company;

            $permissions = Permission::with(['roles'])->orderBy('name', 'asc')->get();

            $usersCountByPermission = DB::table('permissions')
                ->leftJoin('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->leftJoin('roles', 'role_has_permissions.role_id', '=', 'roles.id')
                ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->select('permissions.id', DB::raw('COUNT(DISTINCT users.id) as users_count'))
                ->groupBy('permissions.id')
                ->pluck('users_count', 'permissions.id')
                ->toArray();

            $permissions->transform(function ($permission) use ($usersCountByPermission) {
                $permission->users_count = $usersCountByPermission[$permission->id] ?? 0;

                return $permission;
            });

            $rolesCount = DB::table('roles')
                ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
                ->distinct('roles.id')
                ->count('roles.id');

            $usersCount = DB::table('users')->count();

            $emittedAt = now();
            $filename = 'reporte-permisos-'.$emittedAt->format('Y-m-d_His').'.pdf';

            $pdf = Pdf::loadView('pdf.permissions.report', compact(
                'permissions',
                'company',
                'rolesCount',
                'usersCount',
                'emittedAt'
            ))
                ->setPaper('letter', 'portrait')
                ->setOption('enable_php', true)
                ->addInfo([
                    'Title' => 'Informe de permisos',
                    'Author' => $company->name ?? config('app.name'),
                ]);

            if ($request->boolean('download')) {
                return $pdf->download($filename);
            }

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            return redirect()->route('admin.permissions.index')
                ->with('message', 'Error al generar el reporte: '.$e->getMessage())
                ->with('icons', 'error');
        }
    }
}
