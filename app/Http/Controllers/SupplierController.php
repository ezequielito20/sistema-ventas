<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Obtener todos los proveedores de la compañía actual
            $suppliers = Supplier::where('company_id', Auth::user()->company_id)
                ->latest()
                ->get();

            // Estadísticas para los widgets
            $totalSuppliers = $suppliers->count();

            // Proveedores activos (podemos agregar un campo 'status' en el futuro)
            $activeSuppliers = $totalSuppliers;

            // Proveedores nuevos este mes
            $recentSuppliers = $suppliers->where('created_at', '>=', now()->startOfMonth())->count();

            // Proveedores inactivos (para futura implementación con campo 'status')
            $inactiveSuppliers = 0;

            // Retornar vista con datos
            return view('admin.suppliers.index', compact(
                'suppliers',
                'totalSuppliers',
                'activeSuppliers',
                'recentSuppliers',
                'inactiveSuppliers'
            ))
            ->with('message', 'Proveedores cargados correctamente')
            ->with('icons', 'success') ;
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en SupplierController@index: ' . $e->getMessage());

            // Redireccionar con mensaje de error
            return redirect()->route('admin.dashboard')
                ->with('message', 'Hubo un problema al cargar los proveedores: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('admin.suppliers.create');
        } catch (\Exception $e) {
            Log::error('Error en SupplierController@create: ' . $e->getMessage());
            return redirect()->route('admin.suppliers.index')
                ->with('message', 'Hubo un problema al cargar el formulario: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación personalizada
            $validated = $request->validate([
                'company_name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:suppliers,company_name,NULL,id,company_id,' . Auth::user()->company_id,
                ],
                'company_address' => ['required', 'string', 'max:255'],
                'company_phone' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
                ],
                'company_email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:suppliers,company_email,NULL,id,company_id,' . Auth::user()->company_id,
                ],
                'supplier_name' => ['required', 'string', 'max:255'],
                'supplier_phone' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
                ],
            ], [
                'company_name.required' => 'El nombre de la empresa es obligatorio.',
                'company_name.unique' => 'Esta empresa ya está registrada en su compañía.',
                'company_address.required' => 'La dirección de la empresa es obligatoria.',
                'company_phone.required' => 'El teléfono de la empresa es obligatorio.',
                'company_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
                'company_email.required' => 'El correo electrónico es obligatorio.',
                'company_email.email' => 'Ingrese un correo electrónico válido.',
                'company_email.unique' => 'Este correo electrónico ya está registrado.',
                'supplier_name.required' => 'El nombre del contacto es obligatorio.',
                'supplier_phone.required' => 'El teléfono del contacto es obligatorio.',
                'supplier_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
            ]);

            // Preparar los datos para guardar
            $supplierData = array_merge($validated, [
                'company_id' => Auth::user()->company_id,
            ]);

            // Crear el proveedor
            $supplier = Supplier::create($supplierData);

            // Log de la acción
            Log::info('Proveedor creado exitosamente', [
                'user_id' => Auth::user()->id,
                'supplier_id' => $supplier->id,
                'company_id' => Auth::user()->company_id
            ]);

            // Redireccionar con mensaje de éxito
            return redirect()->route('admin.suppliers.index')
                ->with('message', '¡Proveedor creado exitosamente!')
                ->with('icons', 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('message', 'Por favor, corrija los errores en el formulario.')
                ->with('icons', 'error');
        } catch (\Exception $e) {
            Log::error('Error al crear proveedor: ' . $e->getMessage(), [
                'user_id' => Auth::user()->id,
                'company_id' => Auth::user()->company_id,
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('message', 'Hubo un problema al crear el proveedor. Por favor, inténtelo de nuevo.')
                ->with('icons', 'error');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            // Verificar que el proveedor pertenece a la compañía del usuario
            if ($supplier->company_id !== Auth::user()->company_id) {
                return response()->json([
                    'icons' => 'error',
                    'message' => 'No tiene permiso para ver este proveedor'
                ], 403);
            }

            return response()->json([
                'icons' => 'success',
                'supplier' => [
                    'company_name' => $supplier->company_name,
                    'company_email' => $supplier->company_email,
                    'company_phone' => $supplier->company_phone,
                    'company_address' => $supplier->company_address,
                    'supplier_name' => $supplier->supplier_name,
                    'supplier_phone' => $supplier->supplier_phone,
                    'created_at' => $supplier->created_at->format('d/m/Y H:i'),
                    'updated_at' => $supplier->updated_at->format('d/m/Y H:i'),
                    'stats' => [
                        'months' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                        'products' => [10, 15, 8, 20, 12, 18]
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al mostrar proveedor: ' . $e->getMessage());
            
            return response()->json([
                'icons' => 'error',
                'message' => 'Error al cargar los datos del proveedor'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            // Buscar el proveedor
            $supplier = Supplier::findOrFail($id);

            // Verificar que el proveedor pertenece a la compañía del usuario
            if ($supplier->company_id !== Auth::user()->company_id) {
                Log::warning('Intento de acceso no autorizado a proveedor', [
                    'user_id' => Auth::user()->id,
                    'supplier_id' => $id
                ]);
                
                return redirect()->route('admin.suppliers.index')
                    ->with('message', 'No tiene permiso para editar este proveedor')
                    ->with('icons', 'error');
            }

            // Retornar vista con datos del proveedor
            return view('admin.suppliers.edit', compact('supplier'));

        } catch (\Exception $e) {
            Log::error('Error en SupplierController@edit: ' . $e->getMessage(), [
                'user_id' => Auth::user()->id,
                'supplier_id' => $id
            ]);

            return redirect()->route('admin.suppliers.index')
                ->with('message', 'No se pudo cargar el formulario de edición')
                ->with('icons', 'error');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Buscar el proveedor
            $supplier = Supplier::findOrFail($id);

            // Verificar que el proveedor pertenece a la compañía del usuario
            if ($supplier->company_id !== Auth::user()->company_id) {
                Log::warning('Intento de actualización no autorizada de proveedor', [
                    'user_id' => Auth::user()->id,
                    'supplier_id' => $id
                ]);
                
                return redirect()->route('admin.suppliers.index')
                    ->with('message', 'No tiene permiso para actualizar este proveedor')
                    ->with('icons', 'error');
            }

            // Validación personalizada
            $validated = $request->validate([
                'company_name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:suppliers,company_name,' . $id . ',id,company_id,' . Auth::user()->company_id,
                ],
                'company_address' => ['required', 'string', 'max:255'],
                'company_phone' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
                ],
                'company_email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:suppliers,company_email,' . $id . ',id,company_id,' . Auth::user()->company_id,
                ],
                'supplier_name' => ['required', 'string', 'max:255'],
                'supplier_phone' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^\(\d{3}\)\s\d{3}-\d{4}$/',
                ],
            ], [
                'company_name.required' => 'El nombre de la empresa es obligatorio.',
                'company_name.unique' => 'Esta empresa ya está registrada en su compañía.',
                'company_address.required' => 'La dirección de la empresa es obligatoria.',
                'company_phone.required' => 'El teléfono de la empresa es obligatorio.',
                'company_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
                'company_email.required' => 'El correo electrónico es obligatorio.',
                'company_email.email' => 'Ingrese un correo electrónico válido.',
                'company_email.unique' => 'Este correo electrónico ya está registrado.',
                'supplier_name.required' => 'El nombre del contacto es obligatorio.',
                'supplier_phone.required' => 'El teléfono del contacto es obligatorio.',
                'supplier_phone.regex' => 'El formato del teléfono debe ser (123) 456-7890.',
            ]);

            // Actualizar el proveedor
            $supplier->update($validated);

            // Log de la actualización
            Log::info('Proveedor actualizado exitosamente', [
                'user_id' => Auth::user()->id,
                'supplier_id' => $supplier->id,
                'company_id' => Auth::user()->company_id
            ]);

            // Redireccionar con mensaje de éxito
            return redirect()->route('admin.suppliers.index')
                ->with('message', '¡Proveedor actualizado exitosamente!')
                ->with('icons', 'success');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('message', 'Por favor, corrija los errores en el formulario.')
                ->with('icons', 'error');

        } catch (\Exception $e) {
            Log::error('Error al actualizar proveedor: ' . $e->getMessage(), [
                'user_id' => Auth::user()->id,
                'supplier_id' => $id,
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('message', 'Hubo un problema al actualizar el proveedor. Por favor, inténtelo de nuevo.')
                ->with('icons', 'error');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Buscar el proveedor
            $supplier = Supplier::findOrFail($id);

            // Verificar que el proveedor pertenece a la compañía del usuario
            if ($supplier->company_id !== Auth::user()->company_id) {
                Log::warning('Intento de eliminación no autorizada de proveedor', [
                    'user_id' => Auth::user()->id,
                    'supplier_id' => $id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para eliminar este proveedor',
                    'icons' => 'error'
                ], 403);
            }

            // Guardar información para el log antes de eliminar
            $supplierInfo = [
                'id' => $supplier->id,
                'company_name' => $supplier->company_name,
                'company_id' => $supplier->company_id
            ];

            // Eliminar el proveedor
            $supplier->delete();

            // Log de la eliminación
            Log::info('Proveedor eliminado exitosamente', [
                'user_id' => Auth::user()->id,
                'supplier_info' => $supplierInfo
            ]);

            // Retornar respuesta exitosa
            return response()->json([
                'success' => true,
                'message' => '¡Proveedor eliminado exitosamente!',
                'icons' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar proveedor: ' . $e->getMessage(), [
                'user_id' => Auth::user()->id,
                'supplier_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Hubo un problema al eliminar el proveedor. Por favor, inténtelo de nuevo.',
                'icons' => 'error'
            ], 500);
        }
    }
}