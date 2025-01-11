<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
      $companyId = Auth::user()->company_id;
      $currency = $this->currencies;
      // Obtener conteos básicos
      $usersCount = User::where('company_id', $companyId)->count();
      $rolesCount = Role::count();
      $categoriesCount = Category::where('company_id', $companyId)->count();
      $productsCount = Product::where('company_id', $companyId)->count();

      // Usuarios por rol
      $usersByRole = Role::withCount(['users' => function ($query) use ($companyId) {
         $query->where('company_id', $companyId);
      }])->get()->map(function ($role) {
         return [
            'name' => ucfirst($role->name),
            'count' => $role->users_count
         ];
      });

      // Usuarios por mes (últimos 6 meses)
      $usersPerMonth = User::where('company_id', $companyId)
         ->select(DB::raw('DATE_FORMAT(created_at, "%M %Y") as month'), DB::raw('count(*) as count'))
         ->whereDate('created_at', '>=', now()->subMonths(6))
         ->groupBy('month')
         ->orderBy('created_at')
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
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as month'),
            DB::raw('count(*) as count')
         )
         ->where('company_id', $companyId)
         ->whereDate('created_at', '>=', now()->subMonths(6))
         ->groupBy('month')
         ->orderBy('created_at')
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
         ->having('low_stock_products', '>', 0)
         ->orderByDesc('low_stock_products')
         ->get();

      // Compras mensuales (usando total_price en lugar de total)
      $monthlyPurchases = Purchase::whereMonth('created_at', now()->month)
         ->sum('total_price');

      // Crecimiento mensual
      $lastMonthPurchases = Purchase::whereMonth('created_at', now()->subMonth()->month)
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
            ->whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->sum('total_price');

         $purchaseMonthlyData[] = $monthlyTotal ?? 0;
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
      $lowStockCount = Product::where('stock', '<=', DB::raw('min_stock'))->count();

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
            DB::raw('COALESCE(cat.name, "Sin Categoría") as name'),
            DB::raw('COUNT(sd.id) as total_sales'),
            DB::raw('SUM(sd.quantity) as total_quantity'),
            DB::raw('SUM(sd.quantity * p.sale_price) as total_revenue')
         )
         ->join('products as p', 'sd.product_id', '=', 'p.id')
         ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
         ->join('sales as s', 'sd.sale_id', '=', 's.id')
         ->where('s.company_id', $companyId)
         ->groupBy('cat.id', DB::raw('COALESCE(cat.name, "Sin Categoría")'))
         ->orderByDesc('total_revenue')
         ->get();

      // Widgets adicionales que agregaré:

      // 1. Ventas del día actual
      $todaySales = DB::table('sales')
         ->where('company_id', $companyId)
         ->whereDate('sale_date', now())
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

      // Calcular balance actual
      $currentBalance = $currentCashCount ? 
         ($currentCashCount->initial_amount + 
            DB::table('cash_movements')
               ->where('cash_count_id', $currentCashCount->id)
               ->where('type', 'income')
               ->sum('amount') -
            DB::table('cash_movements')
               ->where('cash_count_id', $currentCashCount->id)
               ->where('type', 'expense')
               ->sum('amount')
         ) : 0;

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

      return view('admin.index', compact(
         'currency',
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
         'averageCustomerSpend',
         'mostProfitableProducts',
         'currentCashCount',
         'todayIncome',
         'todayExpenses',
         'currentBalance',
         'chartData'
      ));
   }
}

