<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CashCount;
use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCashCountRequest;
use App\Http\Requests\UpdateCashCountRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CashCountController extends Controller
{
   public $currencies;
   protected $company;

   public function __construct()
   {
      $this->middleware(function ($request, $next) {
         // Obtener company y currency en una sola consulta
         $userCompany = DB::table('companies')
            ->select('companies.id', 'companies.name', 'companies.country')
            ->where('companies.id', Auth::user()->company_id)
            ->first();
            
         $this->company = $userCompany;
         $this->currencies = DB::table('currencies')
            ->select('id', 'name', 'code', 'symbol', 'country_id')
            ->where('country_id', $userCompany->country)
            ->first();

         return $next($request);
      });
   }
   public function index()
   {
      $currency = $this->currencies;
      $company = $this->company;
      $companyId = $this->company->id;
      
      // Obtener el arqueo actual si existe
      $currentCashCount = CashCount::select('id', 'initial_amount', 'opening_date', 'closing_date')
         ->where('company_id', $companyId)
         ->whereNull('closing_date')
         ->first();

      // Obtener todos los arqueos con paginación (solo campos necesarios)
      $cashCounts = CashCount::select('id', 'initial_amount', 'opening_date', 'closing_date', 'created_at')
         ->where('company_id', $companyId)
         ->orderBy('created_at', 'desc')
         ->paginate(10);

      // Cargar movimientos para los arqueos paginados en una sola consulta
      $cashCountIds = $cashCounts->pluck('id');
      $movements = CashMovement::select('cash_count_id', 'type', 'amount', 'created_at')
         ->whereIn('cash_count_id', $cashCountIds)
         ->get()
         ->groupBy('cash_count_id');

      // Asignar movimientos a cada arqueo
      $cashCounts->getCollection()->transform(function ($cashCount) use ($movements) {
         $cashCount->movements = $movements->get($cashCount->id, collect());
         return $cashCount;
      });

      // Calcular estadísticas del día en una sola consulta consolidada
      $today = Carbon::today();
      $todayStats = DB::select("
         SELECT 
            COALESCE(SUM(CASE WHEN cm.type = 'income' THEN cm.amount ELSE 0 END), 0) as today_income,
            COALESCE(SUM(CASE WHEN cm.type = 'expense' THEN cm.amount ELSE 0 END), 0) as today_expenses,
            COUNT(*) as total_movements
         FROM cash_movements cm
         INNER JOIN cash_counts cc ON cm.cash_count_id = cc.id
         WHERE cc.company_id = ? 
         AND cc.closing_date IS NULL
         AND DATE(cm.created_at) = ?
      ", [$companyId, $today->format('Y-m-d')]);

      $todayIncome = $todayStats[0]->today_income ?? 0;
      $todayExpenses = $todayStats[0]->today_expenses ?? 0;
      $totalMovements = $todayStats[0]->total_movements ?? 0;

      // Calcular balance actual usando los datos ya cargados
      $currentBalance = 0;
      if ($currentCashCount) {
         $currentMovements = $movements->get($currentCashCount->id, collect());
         $income = $currentMovements->where('type', 'income')->sum('amount');
         $expenses = $currentMovements->where('type', 'expense')->sum('amount');
         $currentBalance = $currentCashCount->initial_amount + $income - $expenses;
      }

      // Obtener datos del gráfico en una sola consulta consolidada
      $lastDays = collect(range(6, 0))->map(function ($days) {
         return Carbon::today()->subDays($days);
      });

      $dateRange = [
         $lastDays->first()->format('Y-m-d'),
         $lastDays->last()->format('Y-m-d')
      ];

      $chartDataRaw = DB::select("
         SELECT 
            DATE(cm.created_at) as date,
            cm.type,
            SUM(cm.amount) as total_amount
         FROM cash_movements cm
         INNER JOIN cash_counts cc ON cm.cash_count_id = cc.id
         WHERE cc.company_id = ?
         AND DATE(cm.created_at) BETWEEN ? AND ?
         GROUP BY DATE(cm.created_at), cm.type
         ORDER BY date
      ", [$companyId, $dateRange[0], $dateRange[1]]);

      // Procesar datos del gráfico
      $chartDataByDate = collect($chartDataRaw)->groupBy('date');
      
      $chartData = [
         'labels' => $lastDays->map(fn($date) => $date->format('d/m')),
         'income' => [],
         'expenses' => []
      ];

      foreach ($lastDays as $date) {
         $dateStr = $date->format('Y-m-d');
         $dayData = $chartDataByDate->get($dateStr, collect());
         
         $chartData['income'][] = $dayData->where('type', 'income')->first()->total_amount ?? 0;
         $chartData['expenses'][] = $dayData->where('type', 'expense')->first()->total_amount ?? 0;
      }

      // Calcular total de productos vendidos y comprados en una sola consulta
      $productStats = DB::select("
         SELECT 
            COALESCE(SUM(sd.quantity), 0) as total_products_sold,
            COALESCE(SUM(pd.quantity), 0) as total_products_purchased
         FROM (
            SELECT 
               COALESCE(SUM(sd.quantity), 0) as quantity
            FROM sale_details sd
            INNER JOIN sales s ON sd.sale_id = s.id
            WHERE s.company_id = ?
            AND s.created_at BETWEEN ? AND ?
         ) sd,
         (
            SELECT 
               COALESCE(SUM(pd.quantity), 0) as quantity
            FROM purchase_details pd
            INNER JOIN purchases p ON pd.purchase_id = p.id
            WHERE p.company_id = ?
            AND p.created_at BETWEEN ? AND ?
         ) pd
      ", [
         $companyId, 
         $currentCashCount->opening_date ?? now(), 
         $currentCashCount->closing_date ?? now(),
         $companyId, 
         $currentCashCount->opening_date ?? now(), 
         $currentCashCount->closing_date ?? now()
      ]);

      $totalProducts = $productStats[0]->total_products_sold ?? 0;
      $totalPurchasedProducts = $productStats[0]->total_products_purchased ?? 0;

      return view('admin.cash-counts.index', compact(
         'cashCounts',
         'currentCashCount',
         'todayIncome',
         'todayExpenses',
         'totalMovements',
         'currentBalance',
         'chartData',
         'currency',
         'company',
         'totalProducts',
         'totalPurchasedProducts'
      ));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      try {
         $company = Auth::user()->company;
         $currency = DB::table('currencies')
            ->where('country_id', $company->country)
            ->first();
            
         return view('admin.cash-counts.create', compact('company', 'currency'));
      } catch (\Exception $e) {
         Log::error('Error en CashCountController@create: ' . $e->getMessage());
         return redirect()->route('admin.cash-counts.index')
            ->with('message', 'Error al cargar el formulario de creación')
            ->with('icons', 'error');
      }
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      // Validación de datos
      $validated = $request->validate([
         'initial_amount' => 'required|numeric|min:0',
         'observations' => 'nullable|string|max:1000',
      ], [
         'initial_amount.required' => 'El monto inicial es obligatorio',
         'initial_amount.numeric' => 'El monto inicial debe ser un número',
         'initial_amount.min' => 'El monto inicial no puede ser negativo',
         'observations.max' => 'Las observaciones no pueden exceder los 1000 caracteres',
      ]);

      try {
         DB::beginTransaction();

         // Verificar si existe una caja abierta
         $existingOpenCashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();

         if ($existingOpenCashCount) {
            return redirect()->back()
               ->with('error', 'Ya existe una caja abierta. Debe cerrar la caja actual antes de abrir una nueva.')
               ->with('icons', 'error');
         }

         // Crear el nuevo arqueo
         $cashCount = new CashCount([
            'opening_date' => now(),
            'initial_amount' => $validated['initial_amount'],
            'observations' => $validated['observations'],
            'company_id' => $this->company->id
         ]);

         $cashCount->save();

         DB::commit();

         // Determinar desde dónde viene la petición para redirigir apropiadamente
         $redirectTo = $request->input('redirect_to');
         $redirectRoute = 'admin.cash-counts.index'; // Ruta por defecto
         
         if ($redirectTo) {
            $refererUrl = parse_url($redirectTo, PHP_URL_PATH);
            
            // Detectar desde qué módulo viene la petición
            if (strpos($refererUrl, '/sales') !== false) {
               $redirectRoute = 'admin.sales.index';
            } elseif (strpos($refererUrl, '/purchases') !== false) {
               $redirectRoute = 'admin.purchases.index';
            } elseif (strpos($refererUrl, '/customers') !== false) {
               $redirectRoute = 'admin.customers.index';
            } elseif (strpos($refererUrl, '/products') !== false) {
               $redirectRoute = 'admin.products.index';
            } elseif (strpos($refererUrl, '/suppliers') !== false) {
               $redirectRoute = 'admin.suppliers.index';
            }
            // Si viene del dashboard o cash-counts, mantiene la ruta por defecto
         }

         return redirect()->route($redirectRoute)
            ->with('message', 'Caja abierta correctamente con un monto inicial de ' .
               $this->currencies->symbol . number_format($validated['initial_amount'], 2))
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()
            ->with('message', 'Error al abrir la caja: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Display the specified resource.
    */
   public function show($id)
   {
      try {
         $cashCount = CashCount::with(['movements' => function ($query) {
            $query->orderBy('created_at', 'desc');
         }])->findOrFail($id);

         // Calcular totales de movimientos
         $incomeMovements = $cashCount->movements()->where('type', 'income')->get();
         $expenseMovements = $cashCount->movements()->where('type', 'expense')->get();

         // Estadísticas generales
         $stats = [
            'initial_amount' => $cashCount->initial_amount,
            'final_amount' => $cashCount->final_amount,
            'total_income' => $incomeMovements->sum('amount'),
            'total_expense' => $expenseMovements->sum('amount'),
            'net_difference' => $incomeMovements->sum('amount') - $expenseMovements->sum('amount'),
            'total_movements' => $cashCount->movements()->count(),

            // Detalles de movimientos con información completa
            'movements' => $cashCount->movements->map(function ($movement) {
               return [
                  'type' => $movement->type,
                  'description' => $movement->description,
                  'amount' => $movement->amount,
                  'created_at' => $movement->created_at->format('d/m/Y H:i'),
               ];
            }),

            // Resto de las estadísticas...
            'total_sales' => Sale::where('company_id', $this->company->id)
               ->whereBetween('created_at', [$cashCount->opening_date, $cashCount->closing_date ?? now()])
               ->count(),
            'total_sales_amount' => Sale::where('company_id', $this->company->id)
               ->whereBetween('created_at', [$cashCount->opening_date, $cashCount->closing_date ?? now()])
               ->sum('total_price'),
            'products_sold' => DB::table('sale_details')
               ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
               ->whereBetween('sales.created_at', [
                  $cashCount->opening_date,
                  $cashCount->closing_date ?? now()
               ])
               ->sum('sale_details.quantity'),

            // Compras
            'total_purchases' => Purchase::where('company_id', $this->company->id)
               ->whereBetween('created_at', [$cashCount->opening_date, $cashCount->closing_date ?? now()])
               ->count(),
            'total_purchases_amount' => Purchase::where('company_id', $this->company->id)
               ->whereBetween('created_at', [$cashCount->opening_date, $cashCount->closing_date ?? now()])
               ->sum('total_price'),
            'products_purchased' => DB::table('purchase_details')
               ->join('purchases', 'purchases.id', '=', 'purchase_details.purchase_id')
               ->whereBetween('purchases.created_at', [
                  $cashCount->opening_date,
                  $cashCount->closing_date ?? now()
               ])
               ->sum('purchase_details.quantity'),
         ];

         return response()->json([
            'success' => true,
            'cashCount' => $cashCount,
            'stats' => $stats,
            'currency' => $this->currencies
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al obtener los datos: ' . $e->getMessage()
         ]);
      }
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit($id)
   {
      try {
         // Buscar la caja por ID
         $cashCount = CashCount::findOrFail($id);
         $company = $this->company;
         // Verificar que la caja pertenezca a la compañía actual
         if ($cashCount->company_id !== $this->company->id) {
            return redirect()->route('admin.cash-counts.index')
               ->with('message', 'No tiene permiso para editar esta caja')
               ->with('icons', 'error');
         }

         return view('admin.cash-counts.edit', [
            'cashCount' => $cashCount,
            'currency' => $this->currencies,
            'company',
         ]);
      } catch (\Exception $e) {
         return redirect()->route('admin.cash-counts.index')
            ->with('message', 'Error al cargar la caja: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, $id)
   {
      try {
         // Buscar la caja por ID
         $cashCount = CashCount::findOrFail($id);
         
         // Verificar que la caja pertenezca a la compañía actual
         if ($cashCount->company_id !== $this->company->id) {
            return redirect()->route('admin.cash-counts.index')
               ->with('message', 'No tiene permiso para actualizar esta caja')
               ->with('icons', 'error');
         }

         // Validar datos
         $validated = $request->validate([
            'opening_date' => 'required|date',
            'initial_amount' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
         ], [
            'opening_date.required' => 'La fecha de apertura es obligatoria',
            'opening_date.date' => 'La fecha de apertura debe ser una fecha válida',
            'initial_amount.required' => 'El monto inicial es obligatorio',
            'initial_amount.numeric' => 'El monto inicial debe ser un número',
            'initial_amount.min' => 'El monto inicial no puede ser negativo',
            'observations.max' => 'Las observaciones no pueden exceder los 1000 caracteres',
         ]);

         DB::beginTransaction();

         // Actualizar la caja
         $cashCount->update([
            'opening_date' => $validated['opening_date'],
            'initial_amount' => $validated['initial_amount'],
            'observations' => $validated['observations'],
         ]);

         DB::commit();

         return redirect()->route('admin.cash-counts.index')
            ->with('message', 'Caja actualizada correctamente')
            ->with('icons', 'success');

      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()
            ->with('message', 'Error al actualizar la caja: ' . $e->getMessage())
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
         DB::beginTransaction();

         $cashCount = CashCount::where('company_id', $this->company->id)
            ->findOrFail($id);

         // Verificar si la caja tiene movimientos
         if ($cashCount->movements()->count() > 0) {
            return response()->json([
               'success' => false,
               'message' => 'No se puede eliminar la caja porque tiene movimientos registrados'
            ]);
         }

         // Eliminar la caja
         $cashCount->delete();

         DB::commit();

         return response()->json([
            'success' => true,
            'message' => 'Caja eliminada correctamente'

         ]);
      } catch (\Exception $e) {
         DB::rollBack();
         return response()->json([
            'success' => false,
            'message' => 'Error al eliminar la caja: ' . $e->getMessage()
         ]);
      }
   }

   /**
    * Store a new cash movement.
    */
   public function storeMovement(Request $request)
   {
      try {
         // Validación de datos
         $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
         ], [
            'type.required' => 'El tipo de movimiento es obligatorio',
            'type.in' => 'El tipo de movimiento debe ser ingreso o egreso',
            'amount.required' => 'El monto es obligatorio',
            'amount.numeric' => 'El monto debe ser un número',
            'amount.min' => 'El monto no puede ser negativo',
            'description.max' => 'La descripción no puede exceder los 255 caracteres',
         ]);

         DB::beginTransaction();

         // Obtener la caja abierta actual
         $currentCashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();

         if (!$currentCashCount) {
            return redirect()->back()
               ->with('message', 'No hay una caja abierta para registrar movimientos')
               ->with('icons', 'error');
         }

         // Crear el movimiento
         $movement = $currentCashCount->movements()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'cash_count_id' => $currentCashCount->id,
         ]);

         DB::commit();

         return redirect()->route('admin.cash-counts.index')
            ->with('message', 'Movimiento registrado correctamente por ' .
               $this->currencies->symbol . number_format($validated['amount'], 2))
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()
            ->with('message', 'Error al registrar el movimiento: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Close the current cash count
    */
   public function closeCash(Request $request)
   {
      try {
         DB::beginTransaction();

         // Obtener la caja abierta actual
         $currentCashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->first();
         if (!$currentCashCount) {
            return redirect()->back()
               ->with('message', 'No hay una caja abierta para cerrar')
               ->with('icons', 'error');
         }

         // // Calcular totales de movimientos
         // $totalIncome = $currentCashCount->movements()
         //    ->where('type', 'income')
         //    ->sum('amount');

         // $totalExpense = $currentCashCount->movements()
         //    ->where('type', 'expense')
         //    ->sum('amount');

         // Calcular monto final y diferencia
         // $finalAmount = ($currentCashCount->initial_amount + $totalIncome) - $totalExpense;

         // Actualizar la caja
         $currentCashCount->update([
            'closing_date' => now(),
            'final_amount' => $request->final_amount,
            'observations' => $request->observations
         ]);

         DB::commit();

         return redirect()->route('admin.cash-counts.index')
            ->with('message', 'Caja cerrada correctamente. Monto final: ' .
               $this->currencies->symbol . number_format($request->final_amount, 2))
            ->with('icons', 'success');
      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()
            ->with('message', 'Error al cerrar la caja: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }

   /**
    * Remove the specified cash count.
    */
   public function report()
   {
      $company = $this->company;
      $currency = $this->currencies;
      $cashCounts = CashCount::with(['movements'])->where('company_id', $company->id)->orderBy('created_at', 'desc')->get();
      $pdf = PDF::loadView('admin.cash-counts.report', compact('cashCounts', 'company', 'currency'));
      return $pdf->stream('reporte-caja.pdf');
   }

   /**
    * Show detailed history of a cash count
    */
   public function history($id)
   {
      try {
         Log::info('=== INICIO MÉTODO HISTORY ===');
         Log::info('Iniciando método history para cash count ID: ' . $id);
         
         $currency = $this->currencies;
         
         // Obtener el arqueo específico
         $cashCount = CashCount::where('company_id', $this->company->id)
            ->with(['movements'])
            ->findOrFail($id);

         Log::info('Cash count encontrado: ' . $cashCount->id);
         
         // Calcular estadísticas del arqueo
         Log::info('Calculando estadísticas...');
         try {
            $stats = $this->calculateCashCountStats($cashCount);
            Log::info('Estadísticas calculadas correctamente');
         } catch (\Exception $e) {
            Log::error('Error calculando estadísticas: ' . $e->getMessage());
            $stats = [
               'opening_date' => $cashCount->opening_date,
               'closing_date' => $cashCount->closing_date ?? now(),
               'initial_amount' => $cashCount->initial_amount,
               'final_amount' => $cashCount->final_amount,
               'total_sales' => 0,
               'total_sales_amount' => 0,
               'products_sold' => 0,
               'total_purchases' => 0,
               'total_purchases_amount' => 0,
               'products_purchased' => 0,
               'total_income' => 0,
               'total_expense' => 0,
               'debts_generated' => 0,
               'payments_received' => 0,
               'real_profit' => 0,
               'net_difference' => 0
            ];
         }
         
         // Obtener deudas pendientes del arqueo
         Log::info('Obteniendo deudas pendientes...');
         try {
            $pendingDebts = $this->getPendingDebts($cashCount);
            Log::info('Deudas pendientes obtenidas: ' . $pendingDebts->count());
         } catch (\Exception $e) {
            Log::error('Error obteniendo deudas pendientes: ' . $e->getMessage());
            $pendingDebts = collect([]);
         }
         
         // Obtener deudas de arqueos anteriores
         Log::info('Obteniendo deudas anteriores...');
         try {
            $previousDebts = $this->getPreviousDebts($cashCount);
            Log::info('Deudas anteriores obtenidas: ' . $previousDebts->count());
         } catch (\Exception $e) {
            Log::error('Error obteniendo deudas anteriores: ' . $e->getMessage());
            $previousDebts = collect([]);
         }
         
         // Obtener pagos de deudas anteriores en este arqueo
         Log::info('Obteniendo pagos de deudas anteriores...');
         try {
            $previousDebtPayments = $this->getPreviousDebtPayments($cashCount);
            Log::info('Pagos de deudas anteriores obtenidos: ' . $previousDebtPayments->count());
         } catch (\Exception $e) {
            Log::error('Error obteniendo pagos de deudas anteriores: ' . $e->getMessage());
            $previousDebtPayments = collect([]);
         }

         Log::info('Retornando JSON con todos los datos');
         return response()->json([
            'success' => true,
            'data' => [
               'cashCount' => $cashCount,
               'stats' => $stats,
               'pendingDebts' => $pendingDebts,
               'previousDebts' => $previousDebts,
               'previousDebtPayments' => $previousDebtPayments
            ],
            'currency' => $currency
         ]);

      } catch (\Exception $e) {
         Log::error('Error en CashCountController@history: ' . $e->getMessage());
         Log::error('Stack trace: ' . $e->getTraceAsString());
         return response()->json([
            'success' => false,
            'message' => 'Error al cargar el historial de caja: ' . $e->getMessage()
         ], 500);
      }
   }

   /**
    * Calculate comprehensive statistics for a cash count
    */
   private function calculateCashCountStats($cashCount)
   {
      try {
         Log::info('Iniciando calculateCashCountStats');
         
         $openingDate = $cashCount->opening_date;
         $closingDate = $cashCount->closing_date ?? now();
         
         Log::info('Fechas: ' . $openingDate . ' - ' . $closingDate);
         Log::info('Tipo de opening_date: ' . gettype($openingDate));
         Log::info('Tipo de closing_date: ' . gettype($closingDate));

         // Ventas del arqueo
         Log::info('Consultando ventas...');
         $sales = Sale::where('company_id', $this->company->id)
            ->whereBetween('created_at', [$openingDate, $closingDate])
            ->with(['saleDetails', 'customer']);

         $totalSales = $sales->count();
         $totalSalesAmount = $sales->sum('total_price');
         $productsSold = $sales->get()->sum(function($sale) {
            return $sale->saleDetails->sum('quantity');
         });
         
         Log::info('Ventas calculadas: ' . $totalSales . ' ventas, $' . $totalSalesAmount . ', ' . $productsSold . ' productos');

      // Compras del arqueo
      Log::info('Consultando compras...');
      $purchases = Purchase::where('company_id', $this->company->id)
         ->whereBetween('created_at', [$openingDate, $closingDate])
         ->with(['details']);

      $totalPurchases = $purchases->count();
      $totalPurchasesAmount = $purchases->sum('total_price');
      $productsPurchased = $purchases->get()->sum(function($purchase) {
         return $purchase->details->sum('quantity');
      });
      
      Log::info('Compras calculadas: ' . $totalPurchases . ' compras, $' . $totalPurchasesAmount . ', ' . $productsPurchased . ' productos');

      // Movimientos de caja
      $totalIncome = $cashCount->movements()->where('type', 'income')->sum('amount');
      $totalExpense = $cashCount->movements()->where('type', 'expense')->sum('amount');

      // Deudas generadas en este arqueo (suma de todas las ventas)
      $debtsGenerated = Sale::where('company_id', $this->company->id)
         ->whereBetween('created_at', [$openingDate, $closingDate])
         ->sum('total_price');

      // Pagos recibidos en este arqueo
      $paymentsReceived = $totalIncome - $cashCount->initial_amount;

      // Ganancias reales (pagos - inversión)
      $realProfit = $paymentsReceived - $totalPurchasesAmount;

      return [
         'opening_date' => $openingDate,
         'closing_date' => $closingDate,
         'initial_amount' => $cashCount->initial_amount,
         'final_amount' => $cashCount->final_amount,
         'total_sales' => $totalSales,
         'total_sales_amount' => $totalSalesAmount,
         'products_sold' => $productsSold,
         'total_purchases' => $totalPurchases,
         'total_purchases_amount' => $totalPurchasesAmount,
         'products_purchased' => $productsPurchased,
         'total_income' => $totalIncome,
         'total_expense' => $totalExpense,
         'debts_generated' => $debtsGenerated,
         'payments_received' => $paymentsReceived,
         'real_profit' => $realProfit,
         'net_difference' => $cashCount->final_amount - $cashCount->initial_amount
      ];
      } catch (\Exception $e) {
         Log::error('Error en calculateCashCountStats: ' . $e->getMessage());
         throw $e;
      }
   }

   /**
    * Get pending debts from this cash count
    */
   private function getPendingDebts($cashCount)
   {
      $openingDate = $cashCount->opening_date;
      $closingDate = $cashCount->closing_date ?? now();

      // Obtener clientes con deudas pendientes
      return \App\Models\Customer::where('company_id', $this->company->id)
         ->where('total_debt', '>', 0)
         ->with(['sales' => function($query) use ($openingDate, $closingDate) {
            $query->whereBetween('created_at', [$openingDate, $closingDate]);
         }])
         ->get()
         ->map(function($customer) {
            $salesInPeriod = $customer->sales;
            return [
               'id' => $customer->id,
               'customer_name' => $customer->name,
               'customer_phone' => $customer->phone,
               'sale_date' => $salesInPeriod->first() ? $salesInPeriod->first()->created_at : now(),
               'total_amount' => $customer->total_debt,
               'products_count' => $salesInPeriod->sum(function($sale) {
                  return $sale->saleDetails->count();
               }),
               'total_products' => $salesInPeriod->sum(function($sale) {
                  return $sale->saleDetails->sum('quantity');
               })
            ];
         });
   }

   /**
    * Get debts from previous cash counts
    */
   private function getPreviousDebts($cashCount)
   {
      $openingDate = $cashCount->opening_date;

      // Obtener clientes con deudas de arqueos anteriores
      return \App\Models\Customer::where('company_id', $this->company->id)
         ->where('total_debt', '>', 0)
         ->with(['sales' => function($query) use ($openingDate) {
            $query->where('created_at', '<', $openingDate);
         }])
         ->get()
         ->map(function($customer) {
            $previousSales = $customer->sales;
            return [
               'id' => $customer->id,
               'customer_name' => $customer->name,
               'customer_phone' => $customer->phone,
               'sale_date' => $previousSales->first() ? $previousSales->first()->created_at : now(),
               'total_amount' => $customer->total_debt,
               'products_count' => $previousSales->sum(function($sale) {
                  return $sale->saleDetails->count();
               }),
               'total_products' => $previousSales->sum(function($sale) {
                  return $sale->saleDetails->sum('quantity');
               }),
               'days_pending' => $previousSales->first() ? $previousSales->first()->created_at->diffInDays(now()) : 0
            ];
         });
   }

   /**
    * Get payments of previous debts made in this cash count
    */
   private function getPreviousDebtPayments($cashCount)
   {
      $openingDate = $cashCount->opening_date;
      $closingDate = $cashCount->closing_date ?? now();

      // Obtener todos los movimientos de ingreso
      $incomeMovements = $cashCount->movements()
         ->where('type', 'income')
         ->where('description', 'like', '%pago%deuda%')
         ->orWhere('description', 'like', '%deuda%pago%')
         ->orWhere('description', 'like', '%cliente%')
         ->get();

      return $incomeMovements->map(function($movement) {
         return [
            'id' => $movement->id,
            'amount' => $movement->amount,
            'description' => $movement->description,
            'date' => $movement->created_at,
            'type' => 'previous_debt_payment'
         ];
      });
   }
}
