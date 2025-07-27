<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Nnjeim\World\Models\Country;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class AdminController extends Controller
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
      $companyId = Auth::user()->company->id;
      $company = $this->company;
      $currency = $this->currencies;
      // Obtener conteos básicos
      $usersCount = User::where('company_id', $companyId)->count();
      $rolesCount = Role::byCompany($companyId)->count();
      $categoriesCount = Category::where('company_id', $companyId)->count();
      $productsCount = Product::where('company_id', $companyId)->count();

      // Usuarios por rol
      $usersByRole = Role::byCompany($companyId)->withCount(['users' => function ($query) use ($companyId) {
         $query->where('company_id', $companyId);
      }])->get()->map(function ($role) {
         return [
            'name' => ucfirst($role->name),
            'count' => $role->users_count
         ];
      });

      // Usuarios por mes (últimos 6 meses)
      $usersPerMonth = User::where('company_id', $companyId)
         ->select(DB::raw('TO_CHAR(created_at, \'Month YYYY\') as month'), DB::raw('count(*) as count'), DB::raw('MIN(created_at) as date_order'))
         ->whereDate('created_at', '>=', now()->subMonths(6))
         ->groupBy(DB::raw('TO_CHAR(created_at, \'Month YYYY\')'))
         ->orderBy('date_order')
         ->get();

      // Productos por categoría
      $productsByCategory = Category::where('company_id', $companyId)
         ->withCount('products')
         ->get()
         ->map(function ($category) {
            return [
               'name' => $category->name,
               'count' => $category->products_count
            ];
         });
      // Obtener conteo de proveedores
      $suppliersCount = DB::table('suppliers')
         ->where('company_id', $companyId)
         ->count();

      // Proveedores más activos (con más productos asociados)
      $topSuppliers = DB::table('suppliers')
         ->select('suppliers.company_name', DB::raw('COUNT(products.id) as products_count'))
         ->leftJoin('products', 'products.supplier_id', '=', 'suppliers.id')
         ->where('suppliers.company_id', $companyId)
         ->groupBy('suppliers.id', 'suppliers.company_name')
         ->orderByDesc('products_count')
         ->limit(5)
         ->get();

      // Valor total de inventario por proveedor
      $supplierInventoryValue = DB::table('suppliers')
         ->select(
            'suppliers.company_name',
            DB::raw('SUM(products.stock * products.purchase_price) as total_value'),
            DB::raw('COUNT(products.id) as products_count')
         )
         ->leftJoin('products', 'suppliers.id', '=', 'products.supplier_id')
         ->where('suppliers.company_id', $companyId)
         ->groupBy('suppliers.id', 'suppliers.company_name')
         ->orderByDesc('total_value')
         ->get();

      // Proveedores agregados por mes (últimos 6 meses)
      $suppliersPerMonth = DB::table('suppliers')
         ->select(
            DB::raw('TO_CHAR(created_at, \'Month YYYY\') as month'),
            DB::raw('count(*) as count'),
            DB::raw('MIN(created_at) as date_order')
         )
         ->where('company_id', $companyId)
         ->whereDate('created_at', '>=', now()->subMonths(6))
         ->groupBy(DB::raw('TO_CHAR(created_at, \'Month YYYY\')'))
         ->orderBy('date_order')
         ->get();

      // Proveedores con productos bajo stock mínimo
      $suppliersWithLowStock = DB::table('suppliers')
         ->select(
            'suppliers.company_name',
            DB::raw('COUNT(DISTINCT products.id) as low_stock_products')
         )
         ->join('products', 'suppliers.id', '=', 'products.supplier_id')
         ->where('suppliers.company_id', $companyId)
         ->whereRaw('products.stock <= products.min_stock')
         ->groupBy('suppliers.id', 'suppliers.company_name')
         ->havingRaw('COUNT(DISTINCT products.id) > 0')
         ->orderByDesc('low_stock_products')
         ->get();

      // Compras mensuales (usando total_price en lugar de total)
      $monthlyPurchases = Purchase::where('company_id', $companyId)
         ->whereMonth('created_at', now()->month)
         ->sum('total_price');

      // Crecimiento mensual
      $lastMonthPurchases = Purchase::where('company_id', $companyId)
         ->whereMonth('created_at', now()->subMonth()->month)
         ->sum('total_price');

      $purchaseGrowth = $lastMonthPurchases > 0 ?
         round((($monthlyPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100, 1) : 0;

      // Producto más comprado
      $topProduct = DB::table('purchase_details')
         ->select(
            'products.name',
            DB::raw('SUM(purchase_details.quantity) as total_quantity')
         )
         ->join('products', 'purchase_details.product_id', '=', 'products.id')
         ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
         ->where('purchases.company_id', $companyId)
         ->groupBy('products.id', 'products.name')
         ->orderByDesc('total_quantity')
         ->first();

      // Proveedor principal (basado en cantidad de productos comprados)
      $topSupplier = DB::table('purchase_details')
         ->select(
            'suppliers.company_name as name',
            DB::raw('SUM(purchase_details.quantity) as total_quantity'),
            DB::raw('SUM(purchase_details.quantity * products.purchase_price) as total_amount')
         )
         ->join('suppliers', 'purchase_details.supplier_id', '=', 'suppliers.id')
         ->join('products', 'purchase_details.product_id', '=', 'products.id')
         ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
         ->where('purchases.company_id', $companyId)
         ->whereMonth('purchases.purchase_date', now()->month)
         ->whereYear('purchases.purchase_date', now()->year)
         ->groupBy('suppliers.id', 'suppliers.company_name')
         ->orderByDesc('total_quantity')
         ->first() ?? (object)[
            'name' => 'N/A',
            'total_quantity' => 0,
            'total_amount' => 0
         ];

      // Datos para gráficos mensuales de compras
      $purchaseMonthlyLabels = [];
      $purchaseMonthlyData = [];

      for ($i = 5; $i >= 0; $i--) {
         $date = now()->subMonths($i);
         $purchaseMonthlyLabels[] = $date->format('M Y');

         // Suma del total de compras por mes
         $monthlyTotal = DB::table('purchases')
            ->where('company_id', $companyId)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->sum('total_price');

         $purchaseMonthlyData[] = $monthlyTotal ?? 0;
      }

      // Datos para gráficos mensuales de ventas
      $salesMonthlyLabels = [];
      $salesMonthlyData = [];

      for ($i = 5; $i >= 0; $i--) {
         $date = now()->subMonths($i);
         $salesMonthlyLabels[] = $date->format('M Y');

         // Suma del total de ventas por mes
         $monthlySalesTotal = DB::table('sales')
            ->where('company_id', $companyId)
            ->whereMonth('sale_date', $date->month)
            ->whereYear('sale_date', $date->year)
            ->sum('total_price');

         $salesMonthlyData[] = $monthlySalesTotal ?? 0;
      }

      // Top 5 productos más comprados
      $topProducts = DB::table('purchase_details as pd')
         ->select(
            'p.name',
            'p.code',
            's.company_name as supplier_name',
            DB::raw('SUM(pd.quantity) as total_quantity'),
            DB::raw('p.purchase_price as unit_price'),
            DB::raw('SUM(pd.quantity * p.purchase_price) as total_invested')
         )
         ->join('products as p', 'pd.product_id', '=', 'p.id')
         ->join('suppliers as s', 'pd.supplier_id', '=', 's.id')
         ->join('purchases as pu', 'pd.purchase_id', '=', 'pu.id')
         ->where('pu.company_id', $companyId)
         ->groupBy('p.id', 'p.name', 'p.code', 's.company_name', 'p.purchase_price')
         ->orderByDesc('total_quantity')
         ->limit(5)
         ->get();

      // Productos con stock bajo
      $lowStockCount = Product::where('company_id', $companyId)
         ->whereRaw('stock <= min_stock')
         ->count();

      // Nuevas variables para la sección de clientes

      // Total de clientes y crecimiento
      $totalCustomers = DB::table('customers')
         ->where('company_id', $companyId)
         ->count();

      $lastMonthCustomers = DB::table('customers')
         ->where('company_id', $companyId)
         ->whereMonth('created_at', now()->subMonth()->month)
         ->count();

      // Calcula el porcentaje de crecimiento de clientes comparando el total actual con el mes anterior
      // Si no hay clientes del mes anterior, retorna 0 para evitar división por cero
      // La fórmula es: ((total_actual - total_mes_anterior) / total_mes_anterior) * 100
      $customerGrowth = $lastMonthCustomers > 0 ?
         round((($totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1) : 0;

      // Nuevos clientes este mes
      $newCustomers = DB::table('customers')
         ->where('company_id', $companyId)
         ->whereMonth('created_at', now()->month)
         ->count();


      // Actividad mensual de clientes (últimos 6 meses)
      $monthlyActivity = [];
      $monthlyLabels = [];

      for ($i = 5; $i >= 0; $i--) {
         $date = now()->subMonths($i);
         $monthlyLabels[] = $date->format('M Y');

         $monthlyActivity[] = DB::table('customers')
            ->where('company_id', $companyId)
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->count();
      }

      // Datos para el gráfico de actividad de clientes (últimos 30 días)
      $activityData = [];
      $activityLabels = [];
      for ($i = 29; $i >= 0; $i--) {
         $date = now()->subDays($i);
         $activityLabels[] = $date->format('d M');
         $activityData[] = DB::table('purchases')
            ->where('company_id', $companyId)
            ->whereDate('created_at', $date)
            ->count();
      }

      // Clientes verificados (con NIT)
      $verifiedCustomers = DB::table('customers')
         ->where('company_id', $companyId)
         ->whereNotNull('nit_number')
         ->count();

      $verifiedPercentage = $totalCustomers > 0
         ? round(($verifiedCustomers / $totalCustomers) * 100, 1)
         : 0;

      // Top 10 productos más vendidos
      $topSellingProducts = DB::table('sale_details as sd')
         ->select(
            'p.name',
            DB::raw('COUNT(sd.id) as times_sold'),
            DB::raw('SUM(sd.quantity) as total_quantity'),
            'p.sale_price',
            DB::raw('SUM(sd.quantity * p.sale_price) as total_revenue')
         )
         ->join('products as p', 'sd.product_id', '=', 'p.id')
         ->join('sales as s', 'sd.sale_id', '=', 's.id')
         ->where('s.company_id', $companyId)
         ->groupBy('p.id', 'p.name', 'p.sale_price')
         ->orderByDesc('total_quantity')
         ->limit(10)
         ->get();

      // Top 5 clientes
      $topCustomers = DB::table('sales as s')
         ->select(
            'c.name',
            DB::raw('SUM(s.total_price) as total_spent'),
            DB::raw('COUNT(DISTINCT sd.product_id) as unique_products'),
            DB::raw('SUM(sd.quantity) as total_products')
         )
         ->join('customers as c', 's.customer_id', '=', 'c.id')
         ->join('sale_details as sd', 's.id', '=', 'sd.sale_id')
         ->where('s.company_id', $companyId)
         ->groupBy('c.id', 'c.name')
         ->orderByDesc('total_spent')
         ->limit(5)
         ->get();

      // Ventas por categoría
      $salesByCategory = DB::table('sale_details as sd')
         ->select(
            DB::raw('COALESCE(cat.name, \'Sin Categoría\') as name'),
            DB::raw('COUNT(sd.id) as total_sales'),
            DB::raw('SUM(sd.quantity) as total_quantity'),
            DB::raw('SUM(sd.quantity * p.sale_price) as total_revenue')
         )
         ->join('products as p', 'sd.product_id', '=', 'p.id')
         ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
         ->join('sales as s', 'sd.sale_id', '=', 's.id')
         ->where('s.company_id', $companyId)
         ->groupBy('cat.id', 'cat.name')
         ->orderByDesc('total_revenue')
         ->get();

      // Widgets adicionales que agregaré:

      // 1. Ventas del día actual
      $todaySales = DB::table('sales')
         ->where('company_id', $companyId)
         ->whereDate('sale_date', now())
         ->sum('total_price');

      // Ventas de la semana actual (desde el lunes)
      $startOfWeek = now()->startOfWeek(); // Lunes
      $weeklySales = DB::table('sales')
         ->where('company_id', $companyId)
         ->whereBetween('sale_date', [$startOfWeek, now()])
         ->sum('total_price');

      // 2. Promedio de venta por cliente
      $averageCustomerSpend = DB::table('sales')
         ->where('company_id', $companyId)
         ->avg('total_price');

      // 3. Productos más rentables (mayor margen de ganancia)
      $mostProfitableProducts = DB::table('sale_details as sd')
         ->select(
            'p.name',
            DB::raw('(p.sale_price - p.purchase_price) as profit_margin'),
            DB::raw('SUM(sd.quantity * (p.sale_price - p.purchase_price)) as total_profit')
         )
         ->join('products as p', 'sd.product_id', '=', 'p.id')
         ->join('sales as s', 'sd.sale_id', '=', 's.id')
         ->where('s.company_id', $companyId)
         ->groupBy('p.id', 'p.name', 'p.sale_price', 'p.purchase_price')
         ->orderByDesc('total_profit')
         ->limit(5)
         ->get();

      // 4. Total por cobrar (deudas pendientes)
      $totalPendingDebt = DB::table('customers')
         ->where('company_id', $companyId)
         ->sum('total_debt');

      // Estadísticas de Arqueo de Caja
      $currentCashCount = DB::table('cash_counts')
         ->where('company_id', $companyId)
         ->whereNull('closing_date')
         ->first();

      // Calcular estadísticas del día para la caja
      $today = now()->startOfDay();
      $todayIncome = DB::table('cash_movements')
         ->join('cash_counts', 'cash_movements.cash_count_id', '=', 'cash_counts.id')
         ->where('cash_counts.company_id', $companyId)
         ->where('cash_movements.type', 'income')
         ->whereDate('cash_movements.created_at', $today)
         ->sum('cash_movements.amount');

      $todayExpenses = DB::table('cash_movements')
         ->join('cash_counts', 'cash_movements.cash_count_id', '=', 'cash_counts.id')
         ->where('cash_counts.company_id', $companyId)
         ->where('cash_movements.type', 'expense')
         ->whereDate('cash_movements.created_at', $today)
         ->sum('cash_movements.amount');

      // Calcular balance actual basado en flujo de caja real
      // ==========================================
      // DATOS DEL ARQUEO ACTUAL
      // ==========================================
      $currentCashData = [
         'sales' => 0,
         'purchases' => 0,
         'debt' => 0,
         'balance' => 0,
         'opening_date' => null
      ];

      if ($currentCashCount) {
         $cashOpenDate = $currentCashCount->opening_date;
         $currentCashData['opening_date'] = $cashOpenDate;
         
         // Ventas desde apertura de caja
         $currentCashData['sales'] = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $cashOpenDate)
            ->sum('total_price');
            
         // Compras desde apertura de caja (dinero gastado)
         $currentCashData['purchases'] = DB::table('purchases')
            ->where('company_id', $companyId)
            ->where('purchase_date', '>=', $cashOpenDate)
            ->sum('total_price');
            
         // Deudas pagadas desde apertura de caja (dinero recibido)
         // Solo contar pagos de deudas del arqueo actual
         if (Schema::hasTable('debt_payments')) {
            // Obtener todos los pagos desde la apertura del arqueo
            $allPayments = DB::table('debt_payments')
               ->where('company_id', $companyId)
               ->where('created_at', '>=', $cashOpenDate)
               ->get();
            
                         // Filtrar solo los pagos que corresponden a deudas del arqueo actual
             $currentCashData['debt_payments'] = $allPayments->sum(function($payment) use ($cashOpenDate) {
                $customer = Customer::find($payment->customer_id);
                if (!$customer) return 0;
                
                // Verificar si el cliente tiene ventas en el arqueo actual
                $salesInCurrentCashCount = $customer->sales()
                   ->where('sale_date', '>=', $cashOpenDate)
                   ->sum('total_price');
                
                // Si el cliente tiene ventas en el arqueo actual, contar el pago
                if ($salesInCurrentCashCount > 0) {
                   return $payment->payment_amount; // Todo el pago cuenta como del arqueo actual
                } else {
                   return 0; // No tiene ventas en el arqueo actual, no contar este pago
                }
             });
         } else {
            // Fallback a cash_movements si no existe debt_payments
            $currentCashData['debt_payments'] = DB::table('cash_movements')
               ->join('cash_counts', 'cash_movements.cash_count_id', '=', 'cash_counts.id')
               ->where('cash_counts.company_id', $companyId)
               ->where('cash_movements.type', 'income')
               ->where('cash_movements.description', 'like', '%pago%')
               ->where('cash_movements.created_at', '>=', $cashOpenDate)
               ->sum('cash_movements.amount');
         }
            
         // Calcular deudas del arqueo actual usando los métodos del modelo Customer
         $currentCashCountDebt = Customer::where('company_id', $companyId)
            ->where('total_debt', '>', 0)
            ->get()
            ->sum(function($customer) {
               return $customer->getCurrentCashCountDebtAmount();
            });
            
         $currentCashData['debt'] = $currentCashCountDebt;
         
         // Información adicional para debug y análisis
         $customersWithDebt = Customer::where('company_id', $companyId)
            ->where('total_debt', '>', 0)
            ->get();
            
         $customersWithCurrentDebt = $customersWithDebt->filter(function($customer) {
            return $customer->getCurrentCashCountDebtAmount() > 0;
         });
         
         $currentCashData['debt_details'] = [
            'current_count_debt' => $currentCashCountDebt,
            'customers_with_current_debt' => $customersWithCurrentDebt->count(),
            'total_customers_with_debt' => $customersWithDebt->count(),
            'debug_info' => [
               'cash_open_date' => $cashOpenDate,
               'customers_debug' => $customersWithDebt->map(function($customer) {
                  return [
                     'id' => $customer->id,
                     'name' => $customer->name,
                     'total_debt' => $customer->total_debt,
                     'current_cash_debt' => $customer->getCurrentCashCountDebtAmount(),
                     'previous_cash_debt' => $customer->getPreviousCashCountDebtAmount(),
                     'sales_in_current' => $customer->sales()
                        ->where('sale_date', '>=', $customer->getCurrentCashCountOpeningDate())
                        ->sum('total_price'),
                     'payments_in_current' => $customer->getTotalPaymentsAfterDate($customer->getCurrentCashCountOpeningDate())
                  ];
               })->toArray()
            ]
         ];
         
         // LÓGICA DE BALANCE: -Compras + Deudas Pagadas (Flujo de caja real)
         // Solo cuenta el dinero que realmente tienes disponible
         $currentCashData['balance'] = -$currentCashData['purchases'] + $currentCashData['debt_payments'];
      }

      // ==========================================
      // DATOS HISTÓRICOS COMPLETOS
      // ==========================================
      
      // Ventas históricas totales
      $totalSales = DB::table('sales')->where('company_id', $companyId)->sum('total_price');
         
      // Compras históricas totales (dinero gastado)
      $historicalPurchases = DB::table('purchases')
         ->where('company_id', $companyId)
         ->sum('total_price') ?? 0;
         
      // Deuda histórica total (clientes morosos + clientes con deuda actual)
      $customersWithDebt = Customer::where('company_id', $companyId)
         ->where('total_debt', '>', 0)
         ->get();
         
      $historicalPendingDebt = $customersWithDebt->sum('total_debt');
      
      // Separar deudas por tipo para análisis detallado
      $defaultersDebt = $customersWithDebt->filter(function($customer) {
         return $customer->isDefaulter();
      })->sum('total_debt');
      
      $currentDebtorsDebt = $customersWithDebt->filter(function($customer) {
         return !$customer->isDefaulter();
      })->sum('total_debt');
      
      // Deudas pagadas históricas totales (dinero recibido)
      $historicalDebtPayments = 0;
      if (Schema::hasTable('debt_payments')) {
         $historicalDebtPayments = DB::table('debt_payments')
            ->where('company_id', $companyId)
            ->sum('payment_amount');
      }
      
      $historicalData = [
         'sales' => $totalSales,
         'purchases' => $historicalPurchases,
         'debt' => $historicalPendingDebt,
         'debt_payments' => $historicalDebtPayments,
         'balance' => 0, // Se calculará después
         'debt_details' => [
            'total_debt' => $historicalPendingDebt,
            'defaulters_debt' => $defaultersDebt,
            'current_debtors_debt' => $currentDebtorsDebt,
            'total_customers_with_debt' => $customersWithDebt->count(),
            'defaulters_count' => $customersWithDebt->filter(function($customer) {
               return $customer->isDefaulter();
            })->count(),
            'current_debtors_count' => $customersWithDebt->filter(function($customer) {
               return !$customer->isDefaulter();
            })->count()
         ]
      ];

      // LÓGICA DE BALANCE HISTÓRICO: -Compras + Deudas Pagadas (Flujo de caja real)
      // Solo cuenta el dinero que realmente tienes disponible
      $historicalData['balance'] = -$historicalData['purchases'] + $historicalData['debt_payments'];

      // ==========================================
      // DATOS DE ARQUEOS CERRADOS
      // ==========================================
      
      // Obtener todos los arqueos cerrados (con fecha de cierre)
      $closedCashCounts = DB::table('cash_counts')
         ->where('company_id', $companyId)
         ->whereNotNull('closing_date')
         ->orderBy('opening_date', 'desc')
         ->get();

      $closedCashCountsData = [];
      
      foreach ($closedCashCounts as $closedCashCount) {
         $openingDate = $closedCashCount->opening_date;
         $closingDate = $closedCashCount->closing_date;
         
         // Ventas durante este arqueo
         $salesInPeriod = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $openingDate)
            ->where('sale_date', '<=', $closingDate)
            ->sum('total_price');
            
         // Compras durante este arqueo
         $purchasesInPeriod = DB::table('purchases')
            ->where('company_id', $companyId)
            ->where('purchase_date', '>=', $openingDate)
            ->where('purchase_date', '<=', $closingDate)
            ->sum('total_price');
            
         // Deudas pagadas durante este arqueo
         $debtPaymentsInPeriod = 0;
         if (Schema::hasTable('debt_payments')) {
            $allPaymentsInPeriod = DB::table('debt_payments')
               ->where('company_id', $companyId)
               ->where('created_at', '>=', $openingDate)
               ->where('created_at', '<=', $closingDate)
               ->get();
            
            // Filtrar solo los pagos que corresponden a deudas de este arqueo
            $debtPaymentsInPeriod = $allPaymentsInPeriod->sum(function($payment) use ($openingDate, $closingDate) {
               $customer = Customer::find($payment->customer_id);
               if (!$customer) return 0;
               
               // Verificar si el cliente tiene ventas en este arqueo
               $salesInThisCashCount = $customer->sales()
                  ->where('sale_date', '>=', $openingDate)
                  ->where('sale_date', '<=', $closingDate)
                  ->sum('total_price');
               
               // Si el cliente tiene ventas en este arqueo, contar el pago
               if ($salesInThisCashCount > 0) {
                  return $payment->payment_amount;
               } else {
                  return 0;
               }
            });
         }
         
         // Calcular deudas pendientes al cierre de este arqueo
         $debtAtClosing = Customer::where('company_id', $companyId)
            ->where('total_debt', '>', 0)
            ->get()
            ->sum(function($customer) use ($openingDate, $closingDate) {
               // Calcular deuda específica de este arqueo
               $salesInThisCashCount = $customer->sales()
                  ->where('sale_date', '>=', $openingDate)
                  ->where('sale_date', '<=', $closingDate)
                  ->sum('total_price');
               
               $paymentsInThisCashCount = $customer->getTotalPaymentsAfterDate($openingDate) - 
                                        $customer->getTotalPaymentsAfterDate($closingDate);
               
               $debt = $salesInThisCashCount - $paymentsInThisCashCount;
               return max(0, $debt);
            });
         
         // Calcular balance: -Compras + Deudas Pagadas
         $balanceInPeriod = -$purchasesInPeriod + $debtPaymentsInPeriod;
         
         // Formatear fechas para mostrar en las opciones
         $openingDateFormatted = Carbon::parse($openingDate)->format('d/m/y');
         $closingDateFormatted = Carbon::parse($closingDate)->format('d/m/y');
         
         $closedCashCountsData[] = [
            'id' => $closedCashCount->id,
            'opening_date' => $openingDate,
            'closing_date' => $closingDate,
            'opening_date_formatted' => $openingDateFormatted,
            'closing_date_formatted' => $closingDateFormatted,
            'option_text' => "Arqueo #{$closedCashCount->id} (desde: {$openingDateFormatted} hasta: {$closingDateFormatted})",
            'sales' => $salesInPeriod,
            'purchases' => $purchasesInPeriod,
            'debt_payments' => $debtPaymentsInPeriod,
            'debt' => $debtAtClosing,
            'balance' => $balanceInPeriod,
            'initial_amount' => $closedCashCount->initial_amount
         ];
      }

      // Mantener variables originales para compatibilidad
      $salesSinceCashOpen = $currentCashData['sales'];
      $purchasesSinceCashOpen = $currentCashData['purchases'];
      $debtSinceCashOpen = $currentCashData['debt'];

      // Datos para el gráfico de ingresos vs egresos (últimos 7 días)
      $lastDays = collect(range(6, 0))->map(function ($days) {
         return now()->subDays($days)->format('Y-m-d');
      });

      $chartData = [
         'labels' => $lastDays->map(fn($date) => Carbon::parse($date)->format('d/m')),
         'income' => [],
         'expenses' => []
      ];

      foreach ($lastDays as $date) {
         $chartData['income'][] = DB::table('cash_movements')
            ->join('cash_counts', 'cash_movements.cash_count_id', '=', 'cash_counts.id')
            ->where('cash_counts.company_id', $companyId)
            ->where('cash_movements.type', 'income')
            ->whereDate('cash_movements.created_at', $date)
            ->sum('cash_movements.amount');

         $chartData['expenses'][] = DB::table('cash_movements')
            ->join('cash_counts', 'cash_movements.cash_count_id', '=', 'cash_counts.id')
            ->where('cash_counts.company_id', $companyId)
            ->where('cash_movements.type', 'expense')
            ->whereDate('cash_movements.created_at', $date)
            ->sum('cash_movements.amount');
      }

      // Datos para gráfico de productos vendidos por día en el mes actual
      $daysInMonth = now()->daysInMonth;
      $dailySalesLabels = [];
      $dailySalesData = [];
      for ($d = 1; $d <= $daysInMonth; $d++) {
         $date = now()->copy()->startOfMonth()->addDays($d - 1);
         $dailySalesLabels[] = $date->format('d/m');
         $totalProductsSold = DB::table('sale_details as sd')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->whereDate('s.sale_date', $date->format('Y-m-d'))
            ->sum('sd.quantity');
         $dailySalesData[] = $totalProductsSold;
      }

      return view('admin.index', compact(
         'currency',
         'company',
         'usersCount',
         'rolesCount',
         'usersByRole',
         'usersPerMonth',
         'categoriesCount',
         'productsCount',
         'productsByCategory',
         'suppliersCount',
         'topSuppliers',
         'supplierInventoryValue',
         'suppliersPerMonth',
         'suppliersWithLowStock',
         'monthlyPurchases',
         'purchaseGrowth',
         'topProduct',
         'topSupplier',
         'lowStockCount',
         'purchaseMonthlyLabels',
         'purchaseMonthlyData',
         'salesMonthlyLabels',
         'salesMonthlyData',
         'topProducts',
         'totalCustomers',
         'customerGrowth',
         'newCustomers',
         'monthlyLabels',
         'monthlyActivity',
         'activityData',
         'activityLabels',
         'verifiedCustomers',
         'verifiedPercentage',
         'topSellingProducts',
         'topCustomers',
         'salesByCategory',
         'todaySales',
         'weeklySales',
         'averageCustomerSpend',
         'mostProfitableProducts',
         'totalPendingDebt',
         'currentCashCount',
         'todayIncome',
         'todayExpenses',

         'salesSinceCashOpen',
         'purchasesSinceCashOpen',
         'debtSinceCashOpen',
         'chartData',
         'dailySalesLabels',
         'dailySalesData',
         // NUEVOS DATOS DUALES
         'currentCashData',
         'historicalData',
         'closedCashCountsData'
      ));
   }
}

