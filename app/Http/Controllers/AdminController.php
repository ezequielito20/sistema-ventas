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
   protected $companyId;
   protected $companyCountry;
   
   public function __construct()
   {
      $this->middleware(function ($request, $next) {
         // Obtener la company directamente para evitar N+1 queries (optimizado)
         $this->company = DB::table('companies')
            ->select('id', 'name', 'logo', 'country')
            ->where('id', Auth::user()->company_id)
            ->first();
         $this->currencies = DB::table('currencies')
            ->select('id', 'name', 'code', 'symbol', 'country_id')
            ->where('country_id', $this->company->country)
            ->first();
         
         // Almacenar valores comúnmente usados para evitar accesos repetidos
         $this->companyId = $this->company->id;
         $this->companyCountry = $this->company->country;
         return $next($request);
      });
   }
   public function index()
   {
      $companyId = $this->companyId;
      $company = $this->company;
      $currency = $this->currencies;
      // Obtener conteos básicos optimizados
      $basicCounts = DB::select("
         SELECT 
            (SELECT COUNT(*) FROM users WHERE company_id = ?) as users_count,
            (SELECT COUNT(*) FROM roles WHERE company_id = ?) as roles_count,
            (SELECT COUNT(*) FROM categories WHERE company_id = ?) as categories_count,
            (SELECT COUNT(*) FROM products WHERE company_id = ?) as products_count
      ", [$companyId, $companyId, $companyId, $companyId]);
      
      $counts = $basicCounts[0];
      $usersCount = $counts->users_count;
      $rolesCount = $counts->roles_count;
      $categoriesCount = $counts->categories_count;
      $productsCount = $counts->products_count;

      // Usuarios por rol (optimizado)
      $usersByRole = DB::table('roles')
         ->select('roles.id', 'roles.name', DB::raw('COUNT(users.id) as users_count'))
         ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
         ->leftJoin('users', function($join) use ($companyId) {
            $join->on('model_has_roles.model_id', '=', 'users.id')
                 ->where('model_has_roles.model_type', 'App\\Models\\User')
                 ->where('users.company_id', $companyId);
         })
         ->where('roles.company_id', $companyId)
         ->groupBy('roles.id', 'roles.name')
         ->get()
         ->map(function ($role) {
            return [
               'name' => ucfirst($role->name),
               'count' => $role->users_count
            ];
         });

      // Usuarios por mes (últimos 6 meses) - optimizado
      $usersPerMonth = DB::table('users')
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

      // Productos por categoría (optimizado)
      $productsByCategory = DB::table('categories')
         ->select('categories.id', 'categories.name', DB::raw('COUNT(products.id) as products_count'))
         ->leftJoin('products', 'categories.id', '=', 'products.category_id')
         ->where('categories.company_id', $companyId)
         ->groupBy('categories.id', 'categories.name')
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

      // Compras mensuales optimizadas
      $currentMonthStats = DB::select("
         SELECT 
            (SELECT COALESCE(SUM(total_price), 0) FROM purchases WHERE company_id = ? AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)) as current_month_purchases,
            (SELECT COALESCE(SUM(total_price), 0) FROM purchases WHERE company_id = ? AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')) as last_month_purchases
      ", [$companyId, $companyId]);
      
      $monthlyStats = $currentMonthStats[0];
      $monthlyPurchases = $monthlyStats->current_month_purchases;
      $lastMonthPurchases = $monthlyStats->last_month_purchases;

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

      // Datos para gráficos mensuales optimizados
      $monthlyData = [];
      for ($i = 5; $i >= 0; $i--) {
         $date = now()->subMonths($i);
         $monthlyData[] = [
            'month' => $date->format('M Y'),
            'month_num' => $date->month,
            'year' => $date->year
         ];
      }
      
      // Obtener todos los datos mensuales en una sola consulta
      $monthlyStats = DB::select("
         SELECT 
            EXTRACT(MONTH FROM created_at) as month,
            EXTRACT(YEAR FROM created_at) as year,
            SUM(total_price) as purchase_total
         FROM purchases 
         WHERE company_id = ? 
         AND created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '5 months')
         GROUP BY EXTRACT(MONTH FROM created_at), EXTRACT(YEAR FROM created_at)
         ORDER BY year, month
      ", [$companyId]);
      
      $salesMonthlyStats = DB::select("
         SELECT 
            EXTRACT(MONTH FROM sale_date) as month,
            EXTRACT(YEAR FROM sale_date) as year,
            SUM(total_price) as sale_total
         FROM sales 
         WHERE company_id = ? 
         AND sale_date >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '5 months')
         GROUP BY EXTRACT(MONTH FROM sale_date), EXTRACT(YEAR FROM sale_date)
         ORDER BY year, month
      ", [$companyId]);
      
      // Crear arrays de datos para gráficos
      $purchaseMonthlyLabels = [];
      $purchaseMonthlyData = [];
      $salesMonthlyLabels = [];
      $salesMonthlyData = [];
      
      foreach ($monthlyData as $data) {
         $purchaseMonthlyLabels[] = $data['month'];
         $salesMonthlyLabels[] = $data['month'];
         
         // Buscar datos en los resultados
         $purchaseData = collect($monthlyStats)->firstWhere(function($item) use ($data) {
            return $item->month == $data['month_num'] && $item->year == $data['year'];
         });
         $purchaseMonthlyData[] = $purchaseData ? $purchaseData->purchase_total : 0;
         
         $saleData = collect($salesMonthlyStats)->firstWhere(function($item) use ($data) {
            return $item->month == $data['month_num'] && $item->year == $data['year'];
         });
         $salesMonthlyData[] = $saleData ? $saleData->sale_total : 0;
      }

      // Datos para gráfico de ventas por categoría
      $salesByCategoryStats = DB::select("
         SELECT 
            c.name as category_name,
            SUM(sd.quantity * sd.unit_price) as total_sales
         FROM sale_details sd
         JOIN sales s ON sd.sale_id = s.id
         JOIN products p ON sd.product_id = p.id
         JOIN categories c ON p.category_id = c.id
         WHERE s.company_id = ? 
         AND s.sale_date >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '6 months')
         GROUP BY c.id, c.name
         ORDER BY total_sales DESC
         LIMIT 6
      ", [$companyId]);
      
      $salesByCategoryLabels = [];
      $salesByCategoryData = [];
      
      foreach ($salesByCategoryStats as $category) {
         $salesByCategoryLabels[] = $category->category_name;
         $salesByCategoryData[] = $category->total_sales;
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

      // Datos de clientes optimizados
      $customerStats = DB::select("
         SELECT 
            (SELECT COUNT(*) FROM customers WHERE company_id = ?) as total_customers,
            (SELECT COUNT(*) FROM customers WHERE company_id = ? AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')) as last_month_customers,
            (SELECT COUNT(*) FROM customers WHERE company_id = ? AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)) as new_customers
      ", [$companyId, $companyId, $companyId]);
      
      $customerData = $customerStats[0];
      $totalCustomers = $customerData->total_customers;
      $lastMonthCustomers = $customerData->last_month_customers;
      $newCustomers = $customerData->new_customers;
      
      $customerGrowth = $lastMonthCustomers > 0 ?
         round((($totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1) : 0;


      // Actividad mensual de clientes optimizada
      $customersMonthlyStats = DB::select("
         SELECT 
            EXTRACT(MONTH FROM created_at) as month,
            EXTRACT(YEAR FROM created_at) as year,
            COUNT(*) as customer_count
         FROM customers 
         WHERE company_id = ? 
         AND created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '5 months')
         GROUP BY EXTRACT(MONTH FROM created_at), EXTRACT(YEAR FROM created_at)
         ORDER BY year, month
      ", [$companyId]);
      
      $monthlyLabels = [];
      $monthlyActivity = [];
      
      foreach ($monthlyData as $data) {
         $monthlyLabels[] = $data['month'];
         
         $customerData = collect($customersMonthlyStats)->firstWhere(function($item) use ($data) {
            return $item->month == $data['month_num'] && $item->year == $data['year'];
         });
         $monthlyActivity[] = $customerData ? $customerData->customer_count : 0;
      }

      // Datos para el gráfico de actividad de clientes optimizado (últimos 30 días)
      $activityData = [];
      $activityLabels = [];
      
      // Obtener todos los datos de actividad en una sola consulta
      $activityStats = DB::select("
         SELECT 
            DATE(created_at) as date,
            COUNT(*) as purchase_count
         FROM purchases 
         WHERE company_id = ? 
         AND created_at >= CURRENT_DATE - INTERVAL '30 days'
         GROUP BY DATE(created_at)
         ORDER BY date
      ", [$companyId]);
      
      for ($i = 29; $i >= 0; $i--) {
         $date = now()->subDays($i);
         $dateStr = $date->format('Y-m-d');
         $activityLabels[] = $date->format('d M');
         
         // Buscar datos en los resultados
         $dayData = collect($activityStats)->firstWhere('date', $dateStr);
         $activityData[] = $dayData ? $dayData->purchase_count : 0;
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

      // Widgets adicionales optimizados

      // Obtener estadísticas de ventas en una sola consulta
      $salesStats = DB::select("
         SELECT 
            COALESCE(SUM(CASE WHEN DATE(sale_date) = CURRENT_DATE THEN total_price ELSE 0 END), 0) as today_sales,
            COALESCE(SUM(CASE WHEN sale_date >= DATE_TRUNC('week', CURRENT_DATE) THEN total_price ELSE 0 END), 0) as weekly_sales,
            COALESCE(AVG(total_price), 0) as average_customer_spend,
            COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM sale_date) = EXTRACT(MONTH FROM CURRENT_DATE) 
                              AND EXTRACT(YEAR FROM sale_date) = EXTRACT(YEAR FROM CURRENT_DATE) 
                              THEN total_price ELSE 0 END), 0) as monthly_sales
         FROM sales 
         WHERE company_id = ?
      ", [$companyId]);
      
      $stats = $salesStats[0];
      $todaySales = $stats->today_sales;
      $weeklySales = $stats->weekly_sales;
      $averageCustomerSpend = $stats->average_customer_spend;
      $monthlySales = $stats->monthly_sales;

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

      // Estadísticas de Arqueo de Caja (optimizado)
      $currentCashCount = DB::table('cash_counts')
         ->select('id', 'company_id', 'opening_date', 'closing_date', 'initial_amount')
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
            // Obtener todos los pagos desde la apertura del arqueo (optimizado)
            $allPayments = DB::table('debt_payments')
               ->select('id', 'company_id', 'customer_id', 'payment_amount', 'created_at')
               ->where('company_id', $companyId)
               ->where('created_at', '>=', $cashOpenDate)
               ->get();
            
                         // Filtrar solo los pagos que corresponden a deudas del arqueo actual (optimizado)
             $customerSalesData = DB::table('customers')
                ->select('customers.id', DB::raw('COALESCE(SUM(sales.total_price), 0) as sales_in_period'))
                ->leftJoin('sales', function($join) use ($cashOpenDate) {
                   $join->on('customers.id', '=', 'sales.customer_id')
                        ->where('sales.sale_date', '>=', $cashOpenDate);
                })
                ->whereIn('customers.id', $allPayments->pluck('customer_id'))
                ->groupBy('customers.id')
                ->get()
                ->keyBy('id');
             
             $currentCashData['debt_payments'] = $allPayments->sum(function($payment) use ($customerSalesData) {
                $customerData = $customerSalesData->get($payment->customer_id);
                if (!$customerData) return 0;
                
                // Si el cliente tiene ventas en el arqueo actual, contar el pago
                if ($customerData->sales_in_period > 0) {
                   return $payment->payment_amount;
                } else {
                   return 0;
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
            
         // Calcular deudas del arqueo actual optimizado
         $currentCashCountDebt = 0;
         $customersWithDebtCount = 0;
         $customersWithCurrentDebtCount = 0;
         
         if ($cashOpenDate) {
            // Obtener deudas del arqueo actual en una sola consulta
            $debtData = DB::select("
               SELECT 
                  c.id,
                  c.name,
                  c.total_debt,
                  COALESCE(sales_data.sales_in_current, 0) as sales_in_current,
                  COALESCE(payments_data.payments_in_current, 0) as payments_in_current
               FROM customers c
               LEFT JOIN (
                  SELECT 
                     customer_id,
                     SUM(total_price) as sales_in_current
                  FROM sales 
                  WHERE company_id = ? AND sale_date >= ?
                  GROUP BY customer_id
               ) sales_data ON c.id = sales_data.customer_id
               LEFT JOIN (
                  SELECT 
                     customer_id,
                     SUM(payment_amount) as payments_in_current
                  FROM debt_payments 
                  WHERE company_id = ? AND created_at >= ?
                  GROUP BY customer_id
               ) payments_data ON c.id = payments_data.customer_id
               WHERE c.company_id = ? AND c.total_debt > 0
            ", [$companyId, $cashOpenDate, $companyId, $cashOpenDate, $companyId]);
            
            foreach ($debtData as $customerData) {
               $currentDebt = max(0, $customerData->sales_in_current - $customerData->payments_in_current);
               $currentCashCountDebt += $currentDebt;
               $customersWithDebtCount++;
               
               if ($currentDebt > 0) {
                  $customersWithCurrentDebtCount++;
               }
            }
         }
         
         $currentCashData['debt'] = $currentCashCountDebt;
         
         $currentCashData['debt_details'] = [
            'current_count_debt' => $currentCashCountDebt,
            'customers_with_current_debt' => $customersWithCurrentDebtCount,
            'total_customers_with_debt' => $customersWithDebtCount,
            'debug_info' => [
               'cash_open_date' => $cashOpenDate
            ]
         ];
         
         // LÓGICA DE BALANCE: Ventas - Compras + Deudas Pagadas (Flujo de caja real)
         // Solo cuenta el dinero que realmente tienes disponible
         $currentCashData['balance'] = $currentCashData['sales'] - $currentCashData['purchases'] + $currentCashData['debt_payments'];
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
         
      // Deuda histórica total optimizada
      $debtStats = DB::select("
         SELECT 
            SUM(total_debt) as total_pending_debt,
            COUNT(*) as customers_with_debt
         FROM customers 
         WHERE company_id = ? AND total_debt > 0
      ", [$companyId]);
      
      $historicalPendingDebt = $debtStats[0]->total_pending_debt ?? 0;
      $customersWithDebtCount = $debtStats[0]->customers_with_debt ?? 0;
      
      // Para simplificar, asumimos que la mayoría son deudores actuales
      // En una implementación más compleja, se podría hacer una consulta más detallada
      $defaultersDebt = 0; // Se puede calcular con una consulta más compleja si es necesario
      $currentDebtorsDebt = $historicalPendingDebt;
      
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
            'total_customers_with_debt' => $customersWithDebtCount,
            'defaulters_count' => 0, // Simplificado por ahora
            'current_debtors_count' => $customersWithDebtCount
         ]
      ];

      // LÓGICA DE BALANCE HISTÓRICO: Ventas - Compras + Deudas Pagadas (Flujo de caja real)
      // Solo cuenta el dinero que realmente tienes disponible
      $historicalData['balance'] = $historicalData['sales'] - $historicalData['purchases'] + $historicalData['debt_payments'];

      // ==========================================
      // DATOS DE ARQUEOS CERRADOS
      // ==========================================
      
      // Obtener todos los arqueos cerrados (con fecha de cierre) - optimizado
      $closedCashCounts = DB::table('cash_counts')
         ->select('id', 'company_id', 'opening_date', 'closing_date', 'initial_amount')
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
            
         // Deudas pagadas durante este arqueo (optimizado)
         $debtPaymentsInPeriod = 0;
         if (Schema::hasTable('debt_payments')) {
            $allPaymentsInPeriod = DB::table('debt_payments')
               ->select('id', 'company_id', 'customer_id', 'payment_amount', 'created_at')
               ->where('company_id', $companyId)
               ->where('created_at', '>=', $openingDate)
               ->where('created_at', '<=', $closingDate)
               ->get();
            
            if ($allPaymentsInPeriod->isNotEmpty()) {
               // Obtener datos de ventas de clientes en una sola consulta
               $customerSalesInPeriod = DB::table('customers')
                  ->select('customers.id', DB::raw('COALESCE(SUM(sales.total_price), 0) as sales_in_period'))
                  ->leftJoin('sales', function($join) use ($openingDate, $closingDate) {
                     $join->on('customers.id', '=', 'sales.customer_id')
                          ->where('sales.sale_date', '>=', $openingDate)
                          ->where('sales.sale_date', '<=', $closingDate);
                  })
                  ->whereIn('customers.id', $allPaymentsInPeriod->pluck('customer_id'))
                  ->groupBy('customers.id')
                  ->get()
                  ->keyBy('id');
               
               // Filtrar solo los pagos que corresponden a deudas de este arqueo
               $debtPaymentsInPeriod = $allPaymentsInPeriod->sum(function($payment) use ($customerSalesInPeriod) {
                  $customerData = $customerSalesInPeriod->get($payment->customer_id);
                  if (!$customerData) return 0;
                  
                  // Si el cliente tiene ventas en este arqueo, contar el pago
                  if ($customerData->sales_in_period > 0) {
                     return $payment->payment_amount;
                  } else {
                     return 0;
                  }
               });
            }
         }
         
         // Calcular deudas pendientes al cierre de este arqueo optimizado
         $debtAtClosing = 0;
         if (Schema::hasTable('debt_payments')) {
            $debtData = DB::select("
               SELECT 
                  COALESCE(sales_data.sales_in_period, 0) as sales_in_period,
                  COALESCE(payments_data.payments_in_period, 0) as payments_in_period
               FROM (
                  SELECT SUM(total_price) as sales_in_period
                  FROM sales 
                  WHERE company_id = ? AND sale_date >= ? AND sale_date <= ?
               ) sales_data
               CROSS JOIN (
                  SELECT SUM(payment_amount) as payments_in_period
                  FROM debt_payments 
                  WHERE company_id = ? AND created_at >= ? AND created_at <= ?
               ) payments_data
            ", [$companyId, $openingDate, $closingDate, $companyId, $openingDate, $closingDate]);
            
            if (!empty($debtData)) {
               $debtAtClosing = max(0, $debtData[0]->sales_in_period - $debtData[0]->payments_in_period);
            }
         }
         
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

       // ==========================================
       // DATOS DE VENTAS POR ARQUEO
       // ==========================================
       
       // Datos de ventas del arqueo actual
       $currentSalesData = [
         'today_sales' => 0,
         'weekly_sales' => 0,
         'average_customer_spend' => 0,
         'total_profit' => 0,
         'monthly_sales' => 0
       ];

       if ($currentCashCount) {
         $cashOpenDate = $currentCashCount->opening_date;
         
         // Usar las estadísticas ya calculadas para evitar consultas duplicadas
         $currentSalesData['today_sales'] = $todaySales;
         $currentSalesData['weekly_sales'] = $weeklySales;
         $currentSalesData['monthly_sales'] = $monthlySales;
         
         // Promedio de venta por cliente en el arqueo actual
         $currentSalesData['average_customer_spend'] = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $cashOpenDate)
            ->avg('total_price') ?? 0;
            
         // Ganancia total teórica en el arqueo actual
         $currentSalesData['total_profit'] = DB::table('sale_details as sd')
            ->select(DB::raw('SUM(sd.quantity * (p.sale_price - p.purchase_price)) as total_profit'))
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->where('s.sale_date', '>=', $cashOpenDate)
            ->value('total_profit') ?? 0;
       }

       // Datos históricos de ventas (usando estadísticas ya calculadas)
       $historicalSalesData = [
         'today_sales' => $todaySales,
         'weekly_sales' => $weeklySales,
         'average_customer_spend' => $averageCustomerSpend ?? 0,
         'total_profit' => $mostProfitableProducts->sum('total_profit'),
         'monthly_sales' => $monthlySales // Usar la variable ya calculada
       ];

       // Datos de ventas por arqueo cerrado
       $closedSalesData = [];
       
       foreach ($closedCashCounts as $closedCashCount) {
         $openingDate = $closedCashCount->opening_date;
         $closingDate = $closedCashCount->closing_date;
         
         // Ventas de hoy en este arqueo (si el arqueo estaba abierto hoy)
         $todaySalesInPeriod = 0;
         if (Carbon::parse($openingDate)->startOfDay() <= now()->startOfDay() && 
             Carbon::parse($closingDate)->startOfDay() >= now()->startOfDay()) {
            $todaySalesInPeriod = $todaySales; // Usar la variable ya calculada
         }
         
         // Ventas de la semana en este arqueo
         $startOfWeek = now()->startOfWeek();
         $weeklySalesInPeriod = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $openingDate)
            ->where('sale_date', '<=', $closingDate)
            ->where('sale_date', '>=', $startOfWeek)
            ->sum('total_price');
            
         // Promedio de venta por cliente en este arqueo
         $averageCustomerSpendInPeriod = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $openingDate)
            ->where('sale_date', '<=', $closingDate)
            ->avg('total_price') ?? 0;
            
         // Ganancia total teórica en este arqueo
         $totalProfitInPeriod = DB::table('sale_details as sd')
            ->select(DB::raw('SUM(sd.quantity * (p.sale_price - p.purchase_price)) as total_profit'))
            ->join('products as p', 'sd.product_id', '=', 'p.id')
            ->join('sales as s', 'sd.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->where('s.sale_date', '>=', $openingDate)
            ->where('s.sale_date', '<=', $closingDate)
            ->value('total_profit') ?? 0;
            
         // Ventas totales del arqueo
         $totalSalesInPeriod = DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $openingDate)
            ->where('sale_date', '<=', $closingDate)
            ->sum('total_price');
         
         $closedSalesData[$closedCashCount->id] = [
            'today_sales' => $todaySalesInPeriod,
            'weekly_sales' => $weeklySalesInPeriod,
            'average_customer_spend' => $averageCustomerSpendInPeriod,
            'total_profit' => $totalProfitInPeriod,
            'monthly_sales' => $totalSalesInPeriod, // Para arqueos cerrados, mostramos el total del arqueo
            'total_sales' => $totalSalesInPeriod
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

      // Obtener todos los datos de cash_movements en una sola consulta
      $cashMovementsStats = DB::select("
         SELECT 
            DATE(cm.created_at) as date,
            cm.type,
            SUM(cm.amount) as total_amount
         FROM cash_movements cm
         JOIN cash_counts cc ON cm.cash_count_id = cc.id
         WHERE cc.company_id = ? 
         AND cm.created_at >= CURRENT_DATE - INTERVAL '30 days'
         AND cm.type IN ('income', 'expense')
         GROUP BY DATE(cm.created_at), cm.type
         ORDER BY date
      ", [$companyId]);
      
      foreach ($lastDays as $date) {
         $dateStr = $date; // $date ya es un string en formato 'Y-m-d'
         
         // Buscar datos en los resultados
         $incomeAmount = 0;
         $expenseAmount = 0;
         
         foreach ($cashMovementsStats as $stat) {
            if ($stat->date == $dateStr) {
               if ($stat->type === 'income') {
                  $incomeAmount = $stat->total_amount;
               } elseif ($stat->type === 'expense') {
                  $expenseAmount = $stat->total_amount;
               }
            }
         }
         
         $chartData['income'][] = $incomeAmount;
         $chartData['expenses'][] = $expenseAmount;
      }

      // Datos para gráfico de productos vendidos por día optimizado
      $daysInMonth = now()->daysInMonth;
      $dailySalesLabels = [];
      $dailySalesData = [];
      
      // Obtener todos los datos diarios en una sola consulta
      $dailySalesStats = DB::select("
         SELECT 
            EXTRACT(DAY FROM s.sale_date) as day,
            SUM(sd.quantity) as total_quantity
         FROM sale_details sd
         JOIN sales s ON sd.sale_id = s.id
         WHERE s.company_id = ? 
         AND s.sale_date >= DATE_TRUNC('month', CURRENT_DATE)
         AND s.sale_date < DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'
         GROUP BY EXTRACT(DAY FROM s.sale_date)
         ORDER BY day
      ", [$companyId]);
      
      // Crear arrays de datos para gráficos
      for ($d = 1; $d <= $daysInMonth; $d++) {
         $date = now()->copy()->startOfMonth()->addDays($d - 1);
         $dailySalesLabels[] = $date->format('d/m');
         
         // Buscar datos en los resultados
         $dayData = collect($dailySalesStats)->firstWhere('day', $d);
         $dailySalesData[] = $dayData ? $dayData->total_quantity : 0;
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
         'salesByCategoryLabels',
         'salesByCategoryData',
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
         'closedCashCountsData',
         // DATOS DE VENTAS POR ARQUEO
         'currentSalesData',
         'historicalSalesData',
         'closedSalesData'
      ));
   }
}

