<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('company_id', Auth::user()->company_id)->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Obtener las empresas disponibles
            $companies = Company::select('id', 'name')
                ->where('id', Auth::user()->company_id)
                ->orderBy('name')
                ->get();

            // Obtener los roles disponibles (excluyendo roles del sistema si el usuario no es admin)
            $roles = Role::all();

            return view('admin.users.create', compact('companies', 'roles'));

        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('message', 'Error al cargar el formulario de creación: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación del request
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Al menos una mayúscula, una minúscula y un número
            ],
            'company_id' => ['required', 'exists:companies,id'],
            'role' => ['required', 'exists:roles,id'],
        ], [
            'name.required' => 'El nombre es obligatorio',
            'name.max' => 'El nombre no puede exceder los 255 caracteres',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número',
            'company_id.required' => 'La empresa es obligatoria',
            'company_id.exists' => 'La empresa seleccionada no existe',
            'role.required' => 'El rol es obligatorio',
            'role.exists' => 'El rol seleccionado no existe'
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la empresa pertenezca al usuario autenticado
            if (Auth::user()->company_id != $validated['company_id']) {
                throw new \Exception('No tiene permisos para crear usuarios en esta empresa');
            }

            // Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']), // Convertir email a minúsculas
                'password' => Hash::make($validated['password']),
                'company_id' => $validated['company_id'],
                'email_verified_at' => now(), // Usuario verificado por defecto
            ]);

            // Asignar el rol
            $role = Role::findOrFail($validated['role']);
            
            // Verificar que no se asigne un rol de sistema si no es admin
            $systemRoles = ['admin', 'superadmin'];
            if (in_array($role->name, $systemRoles) && !Auth::user()->hasRole('admin')) {
                throw new \Exception('No tiene permisos para asignar roles del sistema');
            }

            $user->assignRole($role);

            

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('message', 'Usuario creado exitosamente')
                ->with('icons', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log del error
            Log::error('Error creating user: ' . $e->getMessage(), [
                'user' => Auth::id(),
                'request' => $request->except('password', 'password_confirmation')
            ]);
            
            return redirect()->back()
                ->with('message', 'Error al crear el usuario: ' . $e->getMessage())
                ->with('icons', 'error')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            // Obtener usuario con sus roles
            $user = User::with('roles')->findOrFail($id);
            
            // Verificar que el usuario pertenezca a la misma empresa
            if ($user->company_id !== Auth::user()->company_id) {
                throw new \Exception('No tiene permisos para editar este usuario');
            }

            // Obtener empresas disponibles
            $companies = Company::select('id', 'name')
                ->where('id', Auth::user()->company_id)
                ->orderBy('name')
                ->get();

            // Obtener roles disponibles
            $roles = Role::when(!Auth::user()->hasRole('admin'), function ($query) {
                return $query->whereNotIn('name', ['admin', 'superadmin']);
            })->get();

            return view('admin.users.edit', compact('user', 'companies', 'roles'));

        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('message', 'Error al cargar el usuario: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Verificar permisos
            if ($user->company_id !== Auth::user()->company_id) {
                throw new \Exception('No tiene permisos para editar este usuario');
            }

            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'company_id' => ['required', 'exists:companies,id'],
                'role' => ['required', 'exists:roles,id'],
            ];

            // Si se está intentando cambiar la contraseña
            if ($request->filled('password')) {
                $rules['current_password'] = ['required', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('La contraseña actual es incorrecta.');
                    }
                }];
                $rules['password'] = [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
                ];
            }

            $validated = $request->validate($rules, [
                'name.required' => 'El nombre es obligatorio',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'El formato del correo electrónico no es válido',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'current_password.required' => 'Debe proporcionar la contraseña actual para cambiarla',
                'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
                'password.regex' => 'La nueva contraseña debe contener al menos una mayúscula, una minúscula y un número',
                'company_id.required' => 'La empresa es obligatoria',
                'role.required' => 'El rol es obligatorio',
            ]);

            DB::beginTransaction();

            // Actualizar datos básicos
            $user->name = $validated['name'];
            $user->email = strtolower($validated['email']);
            $user->company_id = $validated['company_id'];

            // Actualizar contraseña si se proporcionó
            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            // Actualizar rol
            $role = Role::findOrFail($validated['role']);
            
            // Verificar que no se asigne un rol de sistema si no es admin
            $systemRoles = ['admin', 'superadmin'];
            if (in_array($role->name, $systemRoles) && !Auth::user()->hasRole('admin')) {
                throw new \Exception('No tiene permisos para asignar roles del sistema');
            }

            // Sincronizar roles (elimina roles anteriores y asigna el nuevo)
            $user->syncRoles([$role]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('message', 'Usuario actualizado exitosamente')
                ->with('icons', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating user: ' . $e->getMessage(), [
                'user' => Auth::id(),
                'target_user' => $id,
                'request' => $request->except(['password', 'password_confirmation'])
            ]);
            
            return redirect()->back()
                ->with('message', 'Error al actualizar el usuario: ' . $e->getMessage())
                ->with('icons', 'error')
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
