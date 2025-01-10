<?php

namespace App\Http\Controllers;

use App\Models\CashCount;
use App\Models\CashMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCashCountRequest;
use App\Http\Requests\UpdateCashCountRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashCountController extends Controller
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
      $currency = $this->currencies;
      // Obtener el arqueo actual si existe
      $currentCashCount = CashCount::where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->first();

      // Obtener todos los arqueos
      $cashCounts = CashCount::with('movements')
         ->where('company_id', $this->company->id)
         ->orderBy('created_at', 'desc')
         ->get();

      // Calcular estadísticas del día
      $today = Carbon::today();
      $todayIncome = CashMovement::whereHas('cashCount', function ($query) {
         $query->where('company_id', $this->company->id);
      })
         ->where('type', 'income')
         ->whereDate('created_at', $today)
         ->sum('amount');

      $todayExpenses = CashMovement::whereHas('cashCount', function ($query) {
         $query->where('company_id', $this->company->id);
      })
         ->where('type', 'expense')
         ->whereDate('created_at', $today)
         ->sum('amount');

      $totalMovements = CashMovement::whereHas('cashCount', function ($query) {
         $query->where('company_id', $this->company->id);
      })
         ->whereDate('created_at', $today)
         ->count();

      // Calcular balance actual
      $currentBalance = $currentCashCount ?
         ($currentCashCount->initial_amount +
            $currentCashCount->movements()->where('type', 'income')->sum('amount') -
            $currentCashCount->movements()->where('type', 'expense')->sum('amount')) : 0;

      // Preparar datos para el gráfico
      $lastDays = collect(range(6, 0))->map(function ($days) {
         return Carbon::today()->subDays($days);
      });

      $chartData = [
         'labels' => $lastDays->map(fn($date) => $date->format('d/m')),
         'income' => [],
         'expenses' => []
      ];

      foreach ($lastDays as $date) {
         $chartData['income'][] = CashMovement::whereHas('cashCount', function ($query) {
            $query->where('company_id', $this->company->id);
         })
            ->where('type', 'income')
            ->whereDate('created_at', $date)
            ->sum('amount');

         $chartData['expenses'][] = CashMovement::whereHas('cashCount', function ($query) {
            $query->where('company_id', $this->company->id);
         })
            ->where('type', 'expense')
            ->whereDate('created_at', $date)
            ->sum('amount');
      }

      // Agregar el cálculo de productos vendidos
      $totalProducts = DB::table('sale_details')
         ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
         ->where('sales.company_id', $this->company->id)
         ->whereDate('sales.created_at', $today)
         ->count();

      return view('admin.cash-counts.index', compact(
         'cashCounts',
         'currentCashCount',
         'todayIncome',
         'todayExpenses',
         'totalMovements',
         'currentBalance',
         'chartData',
         'currency',
         'totalProducts'
      ));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      // Verificar si ya existe una caja abierta
      $existingOpenCashCount = CashCount::where('company_id', $this->company->id)
         ->whereNull('closing_date')
         ->first();

      if ($existingOpenCashCount) {
         return redirect()->route('admin.cash-counts.index')
            ->with('error', 'Ya existe una caja abierta. Debe cerrar la caja actual antes de abrir una nueva.');
      }

      return view('admin.cash-counts.create', [
         'currency' => $this->currencies
      ]);
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

         return redirect()->route('admin.cash-counts.index')
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
   public function show(CashCount $cashCount)
   {
      //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(CashCount $cashCount)
   {
      //
   }

   /**
    * Update the specified resource in storage.
    */
   public function update(UpdateCashCountRequest $request, CashCount $cashCount)
   {
      //
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(CashCount $cashCount)
   {
      //
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

         // Calcular totales de movimientos
         $totalIncome = $currentCashCount->movements()
            ->where('type', 'income')
            ->sum('amount');

         $totalExpense = $currentCashCount->movements()
            ->where('type', 'expense')
            ->sum('amount');

         // Calcular monto final y diferencia
         $finalAmount = ($currentCashCount->initial_amount + $totalIncome) - $totalExpense;
         
         // Actualizar la caja
         $currentCashCount->update([
            'closing_date' => now(),
            'final_amount' => $finalAmount,
         ]);

         DB::commit();

         return redirect()->route('admin.cash-counts.index')
            ->with('message', 'Caja cerrada correctamente. Monto final: ' . 
               $this->currencies->symbol . number_format($finalAmount, 2))
            ->with('icons', 'success');

      } catch (\Exception $e) {
         DB::rollBack();
         return redirect()->back()
            ->with('message', 'Error al cerrar la caja: ' . $e->getMessage())
            ->with('icons', 'error');
      }
   }
}
