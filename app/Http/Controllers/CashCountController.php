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

      return view('admin.cash-counts.index', compact(
         'cashCounts',
         'currentCashCount',
         'todayIncome',
         'todayExpenses',
         'totalMovements',
         'currentBalance',
         'chartData',
         'currency'
      ));
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      //
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(StoreCashCountRequest $request)
   {
      //
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
         $currentCashCount = CashCount::where('company_id', $this->company->id)
            ->whereNull('closing_date')
            ->firstOrFail();

         $movement = new CashMovement([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
         ]);

         $currentCashCount->movements()->save($movement);

         return response()->json([
            'success' => true,
            'message' => 'Movimiento registrado correctamente'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error al registrar el movimiento: ' . $e->getMessage()
         ], 500);
      }
   }
}
