<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\CashCount;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
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
   public function index(Request $request)
   {
      try {
         $company = $this->company;
         $companyId = $company->id;
         $currency = $this->currencies;
         $cashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();

         // Consulta base de compras con relaciones
         $query = Purchase::select(['id', 'purchase_date', 'payment_receipt', 'total_price', 'company_id'])
            ->with([
               'details' => function($query) {
                  $query->select(['id', 'purchase_id', 'product_id', 'supplier_id', 'quantity']);
               },
               'details.product' => function($query) {
                  $query->select(['id', 'name', 'code', 'image']);
               }
            ])
            ->where('company_id', $companyId);

         // Búsqueda por texto: recibo de pago, fecha
         if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
               $q->where('payment_receipt', 'ILIKE', "%{$search}%")
                 ->orWhere('purchase_date', 'ILIKE', "%{$search}%");
            });
         }

         // Filtro por producto específico
         if ($request->filled('product_id')) {
            $query->whereHas('details', function($q) use ($request) {
               $q->where('product_id', $request->input('product_id'));
            });
         }

         // Filtro por estado de pago (con recibo = completado, sin recibo = pendiente)
         if ($request->filled('payment_status')) {
            $status = $request->input('payment_status');
            if ($status === 'completed') {
               $query->whereNotNull('payment_receipt');
            } elseif ($status === 'pending') {
               $query->whereNull('payment_receipt');
            }
         }

         // Filtro por rango de fechas
         if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->input('date_from'));
         }

         if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->input('date_to'));
         }

         // Filtro por rango de montos
         if ($request->filled('amount_min')) {
            $query->where('total_price', '>=', $request->input('amount_min'));
         }

         if ($request->filled('amount_max')) {
            $query->where('total_price', '<=', $request->input('amount_max'));
         }

         // Paginación del lado del servidor
         $purchases = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

         // Calcular estadísticas usando consultas directas de base de datos (más eficiente)
         $totalPurchases = DB::table('purchase_details')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->where('purchases.company_id', $companyId)
            ->distinct('purchase_details.product_id')
            ->count('purchase_details.product_id');
            
         $totalAmount = DB::table('purchases')
            ->where('company_id', $companyId)
            ->sum('total_price');
            
         $monthlyPurchases = DB::table('purchases')
            ->where('company_id', $companyId)
            ->whereYear('purchase_date', now()->year)
            ->whereMonth('purchase_date', now()->month)
            ->count();
            
         $pendingDeliveries = DB::table('purchases')
            ->where('company_id', $companyId)
            ->whereNull('payment_receipt')
            ->count();

         // Obtener productos para el filtro (solo campos necesarios)
         $products = Product::select(['id', 'name', 'code', 'category_id', 'company_id'])
            ->with(['category' => function($query) {
               $query->select(['id', 'name']);
            }])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

         // Optimización de gates
         $permissions = [
            'can_show' => Gate::allows('purchases.show'),
            'can_edit' => Gate::allows('purchases.edit'),
            'can_destroy' => Gate::allows('purchases.destroy'),
            'can_create' => Gate::allows('purchases.create'),
            'can_report' => Gate::allows('purchases.report'),
         ];

         return view('admin.purchases.index', compact(
            'purchases',
            'currency',
            'company',
            'totalPurchases',
            'totalAmount',
            'monthlyPurchases',
            'pendingDeliveries',
            'cashCount',
            'permissions',
            'products'
         ));
      } catch (\Exception $e) {

         return redirect()->back()
            ->with('message', 'Error al cargar las compras')
            ->with('icon', 'error');
      }
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create(Request $request)
   {
      try {
         // Obtener productos y proveedores de la compañía actual
         $company = $this->company;
         $companyId = $company->id;
         $currency = $this->currencies;
         $products = Product::select(['id', 'name', 'code', 'purchase_price', 'stock', 'image', 'category_id', 'company_id'])
            ->with(['category' => function($query) {
               $query->select(['id', 'name']);
            }])
            ->where('company_id', $companyId)
            ->get()->each(function($product) {
               $product->append('image_url');
            });

         $suppliers = Supplier::select(['id', 'company_name', 'supplier_name', 'company_id'])
            ->where('company_id', $companyId)
            ->get();

         // Capturar la URL de referencia para redirección posterior
         $referrerUrl = $request->header('referer');
         
         // Solo guardar la URL si no es del mismo formulario de compra y es una URL válida
         if ($referrerUrl && 
             !str_contains($referrerUrl, 'purchases/create') && 
             !str_contains($referrerUrl, 'purchases/edit') &&
             filter_var($referrerUrl, FILTER_VALIDATE_URL)) {
            session(['purchases_referrer' => $referrerUrl]);
         }

         // Optimización de gates
         $permissions = [
            'can_create' => Gate::allows('purchases.create'),
            'can_edit' => Gate::allows('purchases.edit'),
            'can_show' => Gate::allows('purchases.show'),
         ];

         return view('admin.purchases.create', compact('products', 'suppliers', 'currency', 'company', 'permissions'));
      } catch (\Exception $e) {

         return redirect()->route('admin.purchases.index')
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
         // Validación de los datos
         $validated = $request->validate([
            'purchase_date' => 'required|date',
            'purchase_time' => 'required|date_format:H:i',
            'items' => 'required|array|min:1',
            'total_price' => 'required|numeric|min:0',
         ]);

         $payment_receipt = str_replace('-', '', $validated['purchase_date']) . count($request->items) . str_pad((int)$validated['total_price'], '0', STR_PAD_LEFT);
         // dd($payment_receipt);

         DB::beginTransaction();

         // Verificar si hay una caja abierta
         $currentCashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();

         if (!$currentCashCount) {
            return redirect()->back()
               ->with('message', 'No hay una caja abierta. Debe abrir una caja antes de realizar compras.')
               ->with('icons', 'error');
         }

         // Crear la compra principal
         $purchase = Purchase::create([
            'purchase_date' => $validated['purchase_date'] . ' ' . $validated['purchase_time'],
            'payment_receipt' => $payment_receipt,
            'total_price' => $validated['total_price'],
            'company_id' => Auth::user()->company_id,
         ]);

         // Procesar cada producto en la compra
         foreach ($request->items as $key => $item) {
            // dd($key, $item);
            // Obtener el producto
            $product = Product::where('id', $key)
               ->where('company_id', Auth::user()->company_id)
               ->firstOrFail();

            // Crear el detalle de la compra
            PurchaseDetail::create([
               'quantity' => $item['quantity'],
               'purchase_id' => $purchase->id,
               'supplier_id' => $product->supplier_id,
               'product_id' => $product->id,
            ]);

            // Actualizar el stock del producto
            $product->stock += $item['quantity'];
            $product->save();
         }

         // Registrar el movimiento en la caja
         $currentCashCount->movements()->create([
            'type' => 'expense',
            'amount' => $validated['total_price'],
            'description' => 'Compra #' . $purchase->id,
            'cash_count_id' => $purchase->id,
         ]);



         DB::commit();

         // Verificar la acción solicitada
         if ($request->has('action') && $request->action === 'save_and_new') {
            // Redirigir al formulario de creación para hacer otra compra
            return redirect()->route('admin.purchases.create')
                ->with('message', '¡Compra registrada exitosamente! Puede crear otra compra.')
                ->with('icons', 'success');
         }

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('purchases_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('purchases_referrer');
            
            return redirect($referrerUrl)
                ->with('message', '¡Compra registrada exitosamente!')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de compras
         return redirect()->route('admin.purchases.index')
            ->with('message', '¡Compra registrada exitosamente!')
            ->with('icons', 'success');
      } catch (\Illuminate\Validation\ValidationException $e) {


         return redirect()->back()
            ->withInput()
            ->withErrors($e->errors())
            ->with('message', 'Error de validación: ' . $e->getMessage())
            ->with('icons', 'error');
      } catch (\Exception $e) {
         DB::rollBack();


         return redirect()->back()
            ->withInput()
            ->with('message', 'Error al registrar la compra: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(Request $request, $id)
   {
      try {
         $company = $this->company;
         $companyId = $company->id;
         $currency = $this->currencies;

         // Obtener la compra con sus detalles y productos (solo campos necesarios)
         $purchase = Purchase::select(['id', 'purchase_date', 'payment_receipt', 'total_price', 'company_id'])
            ->with([
               'details' => function($query) {
                  $query->select(['id', 'purchase_id', 'product_id', 'supplier_id', 'quantity']);
               },
               'details.product' => function($query) {
                  $query->select(['id', 'name', 'code', 'purchase_price', 'stock', 'image', 'category_id', 'company_id']);
               },
               'details.product.category' => function($query) {
                  $query->select(['id', 'name']);
               }
            ])
            ->where('company_id', $companyId)
            ->findOrFail($id);

         // Formatear los detalles para JavaScript
         $purchaseDetails = $purchase->details->map(function ($detail) {
            return [
               'id' => $detail->product->id,
               'code' => $detail->product->code,
               'name' => $detail->product->name,
               'quantity' => $detail->quantity,
               'price' => $detail->product->purchase_price,
               'purchase_price' => $detail->product->purchase_price,
               'supplier_id' => $detail->supplier_id,
               'subtotal' => $detail->quantity * $detail->product->purchase_price,
               'image_url' => $detail->product->image_url,
               'stock' => $detail->product->stock,
               'category' => $detail->product->category
            ];
         });

         // Obtener productos y proveedores (solo campos necesarios)
         $products = Product::select(['id', 'name', 'code', 'purchase_price', 'stock', 'image', 'category_id', 'company_id'])
            ->with(['category' => function($query) {
               $query->select(['id', 'name']);
            }])
            ->where('company_id', $companyId)->get()->each(function($product) {
            $product->append('image_url');
         });
         $suppliers = Supplier::select(['id', 'company_name', 'supplier_name', 'company_id'])
            ->where('company_id', $companyId)->get();

         // Capturar la URL de referencia para redirección posterior
         $referrerUrl = $request->header('referer');
         if ($referrerUrl && !str_contains($referrerUrl, 'purchases/edit')) {
            session(['purchases_referrer' => $referrerUrl]);
         }

         // Optimización de gates
         $permissions = [
            'can_edit' => Gate::allows('purchases.edit'),
            'can_show' => Gate::allows('purchases.show'),
            'can_create' => Gate::allows('purchases.create'),
         ];

         return view('admin.purchases.edit', compact('purchase', 'products', 'suppliers', 'purchaseDetails', 'currency', 'company', 'permissions'));
      } catch (\Exception $e) {

         return redirect()->route('admin.purchases.index')
            ->with('message', 'Error al cargar el formulario de edición')
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      // dd($request->all());
      try {
         // Validación de los datos
         $validated = $request->validate([
            'purchase_date' => 'required|date',
            'purchase_time' => 'nullable|date_format:H:i',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
         ]);

         DB::beginTransaction();

         // Obtener la compra
         $purchase = Purchase::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

         // Guardar el estado anterior para el log
         $previousState = [
            'purchase_date' => $purchase->purchase_date,
            'total_price' => $purchase->total_price,
            'details' => $purchase->details->map(function ($detail) {
               return [
                  'product_id' => $detail->product_id,
                  'quantity' => $detail->quantity,
                  'price' => $detail->product->purchase_price,
               ];
            })->toArray()
         ];

         // Actualizar fecha y total
         if (isset($validated['purchase_time'])) {
            $purchase->purchase_date = $validated['purchase_date'] . ' ' . $validated['purchase_time'];
         } else {
         $purchase->purchase_date = $validated['purchase_date'];
         }
         $purchase->total_price = $validated['total_price'];
         $purchase->save();

         // Obtener los IDs de los detalles actuales
         $currentDetailIds = $purchase->details->pluck('id')->toArray();
         $newDetailIds = [];

         // Procesar cada producto en la compra
         foreach ($request->items as $productId => $item) {
            $product = Product::where('id', $productId)
               ->where('company_id', Auth::user()->company_id)
               ->firstOrFail();

            // Buscar si ya existe el detalle
            $detail = PurchaseDetail::where('purchase_id', $purchase->id)
               ->where('product_id', $productId)
               ->first();

            if ($detail) {
               // Actualizar stock: restar la cantidad anterior y sumar la nueva
               $stockDifference = $item['quantity'] - $detail->quantity;
               $product->stock += $stockDifference;

               // Actualizar detalle existente
               $detail->update([
                  'quantity' => $item['quantity'],
               ]);

               $newDetailIds[] = $detail->id;
            } else {
               // Crear nuevo detalle
               $detail = PurchaseDetail::create([
                  'quantity' => $item['quantity'],
                  'purchase_id' => $purchase->id,
                  'supplier_id' => $product->supplier_id,
                  'product_id' => $product->id,
               ]);

               // Actualizar stock sumando la nueva cantidad
               $product->stock += $item['quantity'];
               $newDetailIds[] = $detail->id;
            }

            $product->save();
         }

         // Eliminar detalles que ya no están en la compra
         $detailsToDelete = array_diff($currentDetailIds, $newDetailIds);
         foreach ($detailsToDelete as $detailId) {
            $detail = PurchaseDetail::find($detailId);
            if ($detail) {
               // Restar del stock la cantidad que se elimina
               $product = Product::find($detail->product_id);
               if ($product) {
                  $product->stock -= $detail->quantity;
                  $product->save();
               }
               $detail->delete();
            }
         }



         DB::commit();

         // Verificar si hay una URL de referencia guardada
         $referrerUrl = session('purchases_referrer');
         if ($referrerUrl) {
            // Limpiar la session
            session()->forget('purchases_referrer');
            
            return redirect($referrerUrl)
                ->with('message', '¡Compra actualizada exitosamente!')
                ->with('icons', 'success');
         }

         // Fallback: redirigir a la lista de compras
         return redirect()->route('admin.purchases.index')
            ->with('message', '¡Compra actualizada exitosamente!')
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();


         return redirect()->back()
            ->withInput()
            ->with('message', 'Error al actualizar la compra: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy($id)
   {
      try {
         // Buscar la compra
         $purchase = Purchase::findOrFail($id);

         // Verificar que la compra pertenece a la compañía del usuario
         if ($purchase->company_id !== Auth::user()->company_id) {


            return response()->json([
               'success' => false,
               'message' => 'No tiene permiso para eliminar esta compra',
               'icons' => 'error'
            ], 403);
         }

         // Iniciar transacción
         DB::beginTransaction();

         // Guardar información para el log antes de eliminar
         $purchaseInfo = [
            'id' => $purchase->id,
            'payment_receipt' => $purchase->payment_receipt,
            'total_price' => $purchase->total_price,
            'company_id' => $purchase->company_id
         ];

         // Verificar si al revertir el stock algún producto quedaría con stock negativo
         $productsWithNegativeStock = [];
         
         foreach ($purchase->details as $detail) {
            $product = $detail->product;
            $newStock = $product->stock - $detail->quantity;

            if ($newStock < 0) {
               $productsWithNegativeStock[] = [
                  'name' => $product->name,
                  'current_stock' => $product->stock,
                  'quantity_to_remove' => $detail->quantity,
                  'new_stock' => $newStock
               ];
            }
         }
         
         if (!empty($productsWithNegativeStock)) {
            $productList = '';
            foreach ($productsWithNegativeStock as $product) {
               $productList .= "• {$product['name']}: Stock actual {$product['current_stock']} - {$product['quantity_to_remove']} = {$product['new_stock']}\n";
            }
            
            return response()->json([
               'success' => false,
               'message' => "⚠️ No se puede eliminar esta compra porque algunos productos quedarían con stock negativo.\n\n" .
                           "📊 Productos afectados:\n" . $productList . "\n" .
                           "🔧 Acción requerida:\n" .
                           "Primero debes vender o ajustar el stock de estos productos antes de poder eliminar la compra.",
               'icons' => 'warning',
               'has_negative_stock' => true,
               'products_affected' => $productsWithNegativeStock
            ], 422);
         }

         // Revertir el stock de los productos
         foreach ($purchase->details as $detail) {
            $product = $detail->product;
            $product->stock -= $detail->quantity;
            $product->save();
         }

         // Eliminar movimientos de caja asociados a esta compra (si existen)
         $deletedMovements = CashMovement::where('description', 'Compra #' . $purchase->id)
            ->whereHas('cashCount', function($query) {
               $query->where('company_id', Auth::user()->company_id);
            })
            ->delete();
            


         // Eliminar la compra (esto también eliminará los detalles por la relación cascade)
         $purchase->delete();

         // Confirmar transacción
         DB::commit();



         // Retornar respuesta exitosa
         return response()->json([
            'success' => true,
            'message' => '¡Compra eliminada exitosamente!',
            'icons' => 'success'
         ]);
      } catch (\Exception $e) {
         // Revertir transacción en caso de error
         DB::rollBack();



         return response()->json([
            'success' => false,
            'message' => 'Hubo un problema al eliminar la compra. Por favor, inténtelo de nuevo.',
            'icons' => 'error'
         ], 500);
      }
   }

   public function getProductDetails($code)
   {
      try {
         $product = Product::select(['id', 'code', 'name', 'purchase_price', 'stock', 'image', 'company_id'])
            ->where('code', $code)
            ->where('company_id', Auth::user()->company_id)
            ->first();

         if (!$product) {
            return response()->json([
               'success' => false,
               'message' => 'Producto no encontrado'
            ], 404);
         }

         return response()->json([
            'success' => true,
            'product' => [
               'id' => $product->id,
               'code' => $product->code,
               'image' => $product->image_url,
               'name' => $product->name,
               'price' => $product->purchase_price,
               'purchase_price' => $product->purchase_price,
               'stock' => $product->stock
            ]
         ]);
      } catch (\Exception $e) {

         return response()->json([
            'success' => false,
            'message' => 'Error al cargar los datos del producto'
         ], 500);
      }
   }

   public function getProductByCode($code)
   {
      try {
         $product = Product::select(['id', 'code', 'name', 'purchase_price', 'stock', 'image', 'category_id', 'company_id'])
            ->with(['category' => function($query) {
               $query->select(['id', 'name']);
            }])
            ->where('code', $code)
            ->where('company_id', Auth::user()->company_id)
            ->first();

         if (!$product) {
            return response()->json([
               'success' => false,
               'message' => 'Producto no encontrado'
            ], 404);
         }

         return response()->json([
            'success' => true,
            'product' => [
               'id' => $product->id,
               'code' => $product->code,
               'image_url' => $product->image_url,
               'name' => $product->name,
               'price' => $product->purchase_price,
               'purchase_price' => $product->purchase_price,
               'stock' => $product->stock,
               'category' => [
                  'name' => $product->category->name ?? 'Sin categoría'
               ]
            ]
         ]);
      } catch (\Exception $e) {

         return response()->json([
            'success' => false,
            'message' => 'Error al buscar el producto'
         ], 500);
      }
   }

   public function getDetails($id)
   {
      try {
         // Optimizar consulta con select específicos y eager loading
         $purchaseDetails = PurchaseDetail::select(['id', 'purchase_id', 'product_id', 'quantity'])
            ->with([
               'product' => function($query) {
                  $query->select(['id', 'code', 'name', 'purchase_price', 'image', 'category_id', 'company_id']);
               },
               'product.category' => function($query) {
                  $query->select(['id', 'name']);
               }
            ])
            ->where('purchase_id', $id)
            ->get();

         $purchase = Purchase::select(['id', 'purchase_date', 'payment_receipt', 'total_price'])
            ->find($id);

         $details = $purchaseDetails->map(function ($detail) {
            return [
               'quantity' => $detail->quantity,
               'product_price' => $detail->product->purchase_price,
               'product' => [
                  'code' => $detail->product->code,
                  'name' => $detail->product->name,
                  'category' => $detail->product->category->name ?? 'N/A',
                  'image_url' => $detail->product->image_url,
               ],
               'subtotal' => $detail->quantity * $detail->product->purchase_price
            ];
         });

         return response()->json([
            'success' => true,
            'purchase' => [
               'id' => $purchase->id,
               'date' => $purchase->purchase_date->format('d/m/Y'),
               'payment_receipt' => $purchase->payment_receipt,
               'total_price' => $purchase->total_price
            ],
            'details' => $details
         ]);
      } catch (\Exception $e) {

         return response()->json([
            'success' => false,
            'message' => 'Error al cargar los detalles de la compra'
         ], 500);
      }
   }

   public function report()
   {
      $company = $this->company;
      $currency = $this->currencies;
               $purchases = Purchase::select(['id', 'purchase_date', 'payment_receipt', 'total_price', 'company_id'])
            ->with([
               'details' => function($query) {
                  $query->select(['id', 'purchase_id', 'product_id', 'supplier_id', 'quantity']);
               },
               'details.product' => function($query) {
                  $query->select(['id', 'code', 'name', 'purchase_price']);
               },
               'details.supplier' => function($query) {
                  $query->select(['id', 'company_name']);
               }
            ])
            ->where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();
      $pdf = PDF::loadView('admin.purchases.report', compact('purchases', 'company', 'currency'));
      return $pdf->stream('reporte-compras.pdf');
   }
}
