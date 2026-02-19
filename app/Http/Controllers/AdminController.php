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
            ->select('id', 'name', 'logo', 'country', 'currency')
            ->where('id', Auth::user()->company_id)
            ->first();

         // Obtener la moneda de la empresa configurada
         if ($this->company && $this->company->currency) {
            // Buscar la moneda por código en lugar de por país
            $this->currencies = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('code', $this->company->currency)
               ->first();
         }

         // Fallback si no se encuentra la moneda configurada
         if (!$this->currencies) {
            $this->currencies = DB::table('currencies')
               ->select('id', 'name', 'code', 'symbol', 'country_id')
               ->where('country_id', $this->company->country)
               ->first();
         }

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

      // Verificar si existe la tabla debt_payments una sola vez para evitar consultas repetidas
      $hasDebtPaymentsTable = Schema::hasTable('debt_payments');
      // Obtener conteos básicos optimizados en una sola consulta
      $basicCounts = DB::select("
         SELECT 
            (SELECT COUNT(*) FROM users WHERE company_id = ?) as users_count,
            (SELECT COUNT(*) FROM roles WHERE company_id = ?) as roles_count,
            (SELECT COUNT(*) FROM categories WHERE company_id = ?) as categories_count,
            (SELECT COUNT(*) FROM products WHERE company_id = ?) as products_count,
            (SELECT COUNT(*) FROM suppliers WHERE company_id = ?) as suppliers_count,
            (SELECT COUNT(*) FROM customers WHERE company_id = ?) as customers_count,
            (SELECT COUNT(*) FROM customers WHERE company_id = ? AND nit_number IS NOT NULL) as verified_customers
      ", [$companyId, $companyId, $companyId, $companyId, $companyId, $companyId, $companyId]);

      $counts = $basicCounts[0];
      $usersCount = $counts->users_count;
      $rolesCount = $counts->roles_count;
      $categoriesCount = $counts->categories_count;
      $productsCount = $counts->products_count;
      $suppliersCount = $counts->suppliers_count;
      $totalCustomers = $counts->customers_count;
      $verifiedCustomers = $counts->verified_customers;

      $verifiedPercentage = $totalCustomers > 0
         ? round(($verifiedCustomers / $totalCustomers) * 100, 1)
         : 0;

      // Usuarios por rol (optimizado)
      $usersByRole = DB::table('roles')
         ->select('roles.id', 'roles.name', DB::raw('COUNT(users.id) as users_count'))
         ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
         ->leftJoin('users', function ($join) use ($companyId) {
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
            EXTRACT(MONTH FROM s.sale_date) as month,
            EXTRACT(YEAR FROM s.sale_date) as year,
            SUM(s.total_price) as sale_total,
            SUM(sd.quantity * (p.sale_price - p.purchase_price)) as profit_total,
            COUNT(DISTINCT s.id) as transactions_count
         FROM sales s
         JOIN sale_details sd ON s.id = sd.sale_id
         JOIN products p ON sd.product_id = p.id
         WHERE s.company_id = ? 
         AND s.sale_date >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '5 months')
         GROUP BY EXTRACT(MONTH FROM s.sale_date), EXTRACT(YEAR FROM s.sale_date)
         ORDER BY year, month
      ", [$companyId]);

      // Crear arrays de datos para gráficos
      $purchaseMonthlyLabels = [];
      $purchaseMonthlyData = [];
      $salesMonthlyLabels = [];
      $salesMonthlyData = [];
      $profitMonthlyData = [];
      $transactionsMonthlyData = [];

      foreach ($monthlyData as $data) {
         $purchaseMonthlyLabels[] = $data['month'];
         $salesMonthlyLabels[] = $data['month'];

         // Buscar datos en los resultados
         $purchaseData = collect($monthlyStats)->firstWhere(function ($item) use ($data) {
            return $item->month == $data['month_num'] && $item->year == $data['year'];
         });
         $purchaseMonthlyData[] = $purchaseData ? (float)$purchaseData->purchase_total : 0;

         $saleData = collect($salesMonthlyStats)->firstWhere(function ($item) use ($data) {
            return $item->month == $data['month_num'] && $item->year == $data['year'];
         });
         $salesMonthlyData[] = $saleData ? (float)$saleData->sale_total : 0;
         $profitMonthlyData[] = $saleData ? (float)$saleData->profit_total : 0;
         $transactionsMonthlyData[] = $saleData ? (int)$saleData->transactions_count : 0;
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
            (SELECT COUNT(*) FROM customers WHERE company_id = ? AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month')) as last_month_customers,
            (SELECT COUNT(*) FROM customers WHERE company_id = ? AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)) as new_customers
      ", [$companyId, $companyId]);

      $customerData = $customerStats[0];
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

         $customerData = collect($customersMonthlyStats)->firstWhere(function ($item) use ($data) {
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

      // Obtener estadísticas de ventas y compras en consultas separadas (más eficiente)
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

      $purchaseStats = DB::select("
         SELECT 
            COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchase_date) = EXTRACT(MONTH FROM CURRENT_DATE) 
                              AND EXTRACT(YEAR FROM purchase_date) = EXTRACT(YEAR FROM CURRENT_DATE) 
                              THEN total_price ELSE 0 END), 0) as monthly_purchases,
            COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchase_date) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') 
                              AND EXTRACT(YEAR FROM purchase_date) = EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month') 
                              THEN total_price ELSE 0 END), 0) as last_month_purchases
         FROM purchases 
         WHERE company_id = ?
      ", [$companyId]);

      $stats = $salesStats[0];
      $purchaseData = $purchaseStats[0];

      $todaySales = $stats->today_sales ?? 0;
      $weeklySales = $stats->weekly_sales ?? 0;
      $averageCustomerSpend = $stats->average_customer_spend ?? 0;
      $monthlySales = $stats->monthly_sales ?? 0;


      $monthlyPurchases = $purchaseData->monthly_purchases ?? 0;
      $lastMonthPurchases = $purchaseData->last_month_purchases ?? 0;

      // Calcular ganancia total teórica
      $totalProfit = DB::table('sale_details as sd')
         ->join('products as p', 'sd.product_id', '=', 'p.id')
         ->join('sales as s', 'sd.sale_id', '=', 's.id')
         ->where('s.company_id', $companyId)
         ->sum(DB::raw('sd.quantity * (p.sale_price - p.purchase_price)'));

      $purchaseGrowth = $lastMonthPurchases > 0 ?
         round((($monthlyPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100, 1) : 0;

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

      // Calcular estadísticas del día para la caja en una sola consulta
      $today = now()->startOfDay();
      $todayStats = DB::select("
         SELECT 
            COALESCE(SUM(CASE WHEN cm.type = 'income' THEN cm.amount ELSE 0 END), 0) as today_income,
            COALESCE(SUM(CASE WHEN cm.type = 'expense' THEN cm.amount ELSE 0 END), 0) as today_expenses
         FROM cash_movements cm
         JOIN cash_counts cc ON cm.cash_count_id = cc.id
         WHERE cc.company_id = ? 
         AND DATE(cm.created_at) = ?
      ", [$companyId, $today->format('Y-m-d')]);

      $todayIncome = $todayStats[0]->today_income;
      $todayExpenses = $todayStats[0]->today_expenses;

      // Calcular balance actual basado en flujo de caja real
      // ==========================================
      // DATOS DEL ARQUEO ACTUAL
      // ==========================================
      $currentCashData = [
         'sales' => 0.0,
         'purchases' => 0.0,
         'debt' => 0.0,
         'balance' => 0.0,
         'opening_date' => null
      ];

      if ($currentCashCount) {
         $cashOpenDate = $currentCashCount->opening_date;
         $currentCashData['opening_date'] = $cashOpenDate;

         // Ventas desde apertura de caja
         $currentCashData['sales'] = (float) DB::table('sales')
            ->where('company_id', $companyId)
            ->where('sale_date', '>=', $cashOpenDate)
            ->sum('total_price');

         // Compras desde apertura de caja (dinero gastado)
         $currentCashData['purchases'] = (float) DB::table('purchases')
            ->where('company_id', $companyId)
            ->where('purchase_date', '>=', $cashOpenDate)
            ->sum('total_price');

         // Deudas pagadas desde apertura de caja (dinero recibido)
         // Solo contar pagos de deudas del arqueo actual
         if ($hasDebtPaymentsTable) {
            // Obtener todos los pagos desde la apertura del arqueo (optimizado)
            $allPayments = DB::table('debt_payments')
               ->select('id', 'company_id', 'customer_id', 'payment_amount', 'created_at')
               ->where('company_id', $companyId)
               ->where('created_at', '>=', $cashOpenDate)
               ->get();

            // Filtrar solo los pagos que corresponden a deudas del arqueo actual (optimizado)
            $customerSalesData = DB::table('customers')
               ->select('customers.id', DB::raw('COALESCE(SUM(sales.total_price), 0) as sales_in_period'))
               ->leftJoin('sales', function ($join) use ($cashOpenDate) {
                  $join->on('customers.id', '=', 'sales.customer_id')
                     ->where('sales.sale_date', '>=', $cashOpenDate);
               })
               ->whereIn('customers.id', $allPayments->pluck('customer_id'))
               ->groupBy('customers.id')
               ->get()
               ->keyBy('id');

            $currentCashData['debt_payments'] = (float) $allPayments->sum(function ($payment) use ($customerSalesData) {
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
            $currentCashData['debt_payments'] = (float) DB::table('cash_movements')
               ->join('cash_counts', 'cash_counts.id', '=', 'cash_movements.cash_count_id')
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
            $debtData = [];
            if ($hasDebtPaymentsTable) {
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
            }

            foreach ($debtData as $customerData) {
               $currentDebt = max(0, $customerData->sales_in_current - $customerData->payments_in_current);
               $currentCashCountDebt += $currentDebt;
               $customersWithDebtCount++;

               if ($currentDebt > 0) {
                  $customersWithCurrentDebtCount++;
               }
            }
         }



         $customersWithDebt = DB::table('customers')
            ->where('company_id', $companyId)
            ->where('total_debt', '>', 0)
            ->select('id', 'total_debt', 'name')
            ->get();

         $totalCurrentPeriodDebt = 0;
         $totalOldDebtRecovered = 0; // Dinero que entró pagando deudas viejas

         foreach ($customersWithDebt as $customer) {
            // 1. Calcular Deuda Vieja Inicial (Antes de abrir la caja)
            $oldSales = DB::table('sales')
               ->where('company_id', $companyId)
               ->where('customer_id', $customer->id)
               ->where('sale_date', '<', $cashOpenDate)
               ->sum('total_price');

            $oldPayments = DB::table('debt_payments')
               ->where('company_id', $companyId)
               ->where('customer_id', $customer->id)
               ->where('created_at', '<', $cashOpenDate)
               ->sum('payment_amount');

            $oldDebtInitial = max(0, $oldSales - $oldPayments);

            // 2. Calcular cuánto de esa deuda vieja se pagó en este período
            $recovered = 0;
            $oldDebtRemaining = 0;

            if ($oldDebtInitial > 0) {
               $paymentsInPeriod = DB::table('debt_payments')
                  ->where('customer_id', $customer->id)
                  ->where('created_at', '>=', $cashOpenDate)
                  ->sum('payment_amount');

               // Si hubo pagos, primero cubren la deuda vieja (FIFO)
               $recovered = min($oldDebtInitial, $paymentsInPeriod);
               $oldDebtRemaining = max(0, $oldDebtInitial - $recovered);
            }

            // 3. La deuda que pertenece a ESTE período es el Total menos lo que queda de viejo
            $calculatedCurrentDebt = max(0, $customer->total_debt - $oldDebtRemaining);

            // Obtener ventas de ESTE cliente en ESTE período para limitar la deuda nueva
            // No se puede generar más deuda nueva que lo vendido en el período
            $salesInCurrent = DB::table('sales')
               ->where('company_id', $companyId)
               ->where('customer_id', $customer->id)
               ->where('sale_date', '>=', $cashOpenDate)
               ->sum('total_price');

            // La deuda del período no puede exceder las ventas del período
            $currentPeriodDebt = min($calculatedCurrentDebt, $salesInCurrent);

            $totalCurrentPeriodDebt += $currentPeriodDebt;
            $totalOldDebtRecovered += $recovered;
         }

         $currentCashData['debt'] = (float) $totalCurrentPeriodDebt;

         $currentCashData['debt_details'] = [
            'current_count_debt' => $totalCurrentPeriodDebt,
            'old_debt_recovered' => $totalOldDebtRecovered,
            'total_system_debt' => $customersWithDebt->sum('total_debt'),
            'debug_info' => [
               'cash_open_date' => $cashOpenDate
            ]
         ];

         // Calcular balance actual basado en flujo de caja real
         // ==========================================
         // DATOS DEL ARQUEO ACTUAL
         // ==========================================
         // Balance = (Ventas en periodo) - (Compras en periodo) - (Deuda generada en periodo) + (Deuda vieja recuperada en periodo)
         // NOTA: Las ventas ya incluyen el monto total, por lo que restamos la deuda generada para obtener solo lo que entró en efectivo real de esas ventas.
         // Y sumamos la deuda vieja recuperada que es efectivo real que entró.

         $realCashFromSales = $currentCashData['sales'] - $totalCurrentPeriodDebt;
         $currentCashData['balance'] = (float) ($realCashFromSales - $currentCashData['purchases'] + $totalOldDebtRecovered);
      }

      // ==========================================
      // DATOS HISTÓRICOS COMPLETOS
      // ==========================================

      // Obtener todos los datos históricos en una sola consulta
      $historicalStats = DB::select("
         SELECT 
            (SELECT COALESCE(SUM(total_price), 0) FROM sales WHERE company_id = ?) as total_sales,
            (SELECT COALESCE(SUM(total_price), 0) FROM purchases WHERE company_id = ?) as total_purchases,
            (SELECT COALESCE(SUM(total_debt), 0) FROM customers WHERE company_id = ? AND total_debt > 0) as total_debt,
            (SELECT COUNT(*) FROM customers WHERE company_id = ? AND total_debt > 0) as customers_with_debt,
            (SELECT COALESCE(SUM(payment_amount), 0) FROM debt_payments WHERE company_id = ?) as total_debt_payments
      ", [$companyId, $companyId, $companyId, $companyId, $companyId]);

      $historicalData = $historicalStats[0];
      $totalSales = (float) $historicalData->total_sales;
      $historicalPurchases = (float) $historicalData->total_purchases;
      $historicalPendingDebt = (float) $historicalData->total_debt;
      $customersWithDebtCount = $historicalData->customers_with_debt;
      $historicalDebtPayments = (float) $historicalData->total_debt_payments;

      // Para simplificar, asumimos que la mayoría son deudores actuales
      // En una implementación más compleja, se podría hacer una consulta más detallada
      $defaultersDebt = 0; // Se puede calcular con una consulta más compleja si es necesario
      $currentDebtorsDebt = $historicalPendingDebt;



      $historicalData = [
         'sales' => (float) $totalSales,
         'purchases' => (float) $historicalPurchases,
         'debt' => (float) $historicalPendingDebt,
         'debt_payments' => (float) $historicalDebtPayments,
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

      // LÓGICA DE BALANCE HISTÓRICO: Ventas - Compras - Deuda por Cobrar (Ganancias reales)
      // Balance = Total Ventas - Total Compras - Deuda Restante por Cobrar
      $historicalData['balance'] = (float) ($historicalData['sales'] - $historicalData['purchases'] - $historicalData['debt']);



      // ==========================================
      // DATOS DE ARQUEOS CERRADOS - OPTIMIZADO
      // ==========================================

      // Obtener todos los arqueos cerrados (con fecha de cierre) - optimizado
      $closedCashCounts = DB::table('cash_counts')
         ->select('id', 'company_id', 'opening_date', 'closing_date', 'initial_amount')
         ->where('company_id', $companyId)
         ->whereNotNull('closing_date')
         ->orderBy('opening_date', 'desc')
         ->get();

      $closedCashCountsData = [];
      $closedSalesData = [];

      if ($closedCashCounts->isNotEmpty()) {
         // Obtener todos los datos de arqueos cerrados usando UNION ALL para evitar problemas de GROUP BY
         $allSalesData = collect();
         $allPurchasesData = collect();
         $allDebtPaymentsData = collect();

         foreach ($closedCashCounts as $index => $closedCashCount) {
            $openingDate = $closedCashCount->opening_date;
            $closingDate = $closedCashCount->closing_date;
            $arqueoPeriod = $index + 1;

            // Ventas para este arqueo
            $salesData = DB::table('sales')
               ->select(
                  DB::raw("{$arqueoPeriod} as arqueo_period"),
                  DB::raw('SUM(total_price) as total_sales'),
                  DB::raw('COUNT(*) as sales_count'),
                  DB::raw('AVG(total_price) as avg_sale_price')
               )
               ->where('company_id', $companyId)
               ->where('sale_date', '>=', $openingDate)
               ->where('sale_date', '<=', $closingDate)
               ->first();

            $allSalesData->put($arqueoPeriod, $salesData);

            // Compras para este arqueo
            $purchasesData = DB::table('purchases')
               ->select(
                  DB::raw("{$arqueoPeriod} as arqueo_period"),
                  DB::raw('SUM(total_price) as total_purchases')
               )
               ->where('company_id', $companyId)
               ->where('purchase_date', '>=', $openingDate)
               ->where('purchase_date', '<=', $closingDate)
               ->first();

            $allPurchasesData->put($arqueoPeriod, $purchasesData);

            // Pagos de deudas para este arqueo
            if ($hasDebtPaymentsTable) {
               $debtPaymentsData = DB::table('debt_payments')
                  ->select(
                     DB::raw("{$arqueoPeriod} as arqueo_period"),
                     DB::raw('SUM(payment_amount) as total_payments')
                  )
                  ->where('company_id', $companyId)
                  ->where('created_at', '>=', $openingDate)
                  ->where('created_at', '<=', $closingDate)
                  ->first();

               $allDebtPaymentsData->put($arqueoPeriod, $debtPaymentsData);
            }
         }

         // Procesar cada arqueo con los datos ya obtenidos
         foreach ($closedCashCounts as $index => $closedCashCount) {
            $openingDate = $closedCashCount->opening_date;
            $closingDate = $closedCashCount->closing_date;
            $arqueoPeriod = $index + 1;

            // Obtener datos del arqueo desde las consultas individuales
            $salesData = $allSalesData->get($arqueoPeriod);
            $purchasesData = $allPurchasesData->get($arqueoPeriod);
            $debtPaymentsData = $allDebtPaymentsData->get($arqueoPeriod);

            $salesInPeriod = (float) ($salesData->total_sales ?? 0);
            $purchasesInPeriod = (float) ($purchasesData->total_purchases ?? 0);
            $debtPaymentsInPeriod = (float) ($debtPaymentsData->total_payments ?? 0);
            $averageCustomerSpendInPeriod = (float) ($salesData->avg_sale_price ?? 0);

            // Calcular deudas pendientes al cierre de este arqueo
            $debtAtClosing = 0.0;
            if ($hasDebtPaymentsTable) {
               $debtAtClosing = max(0, $salesInPeriod - $debtPaymentsInPeriod);
            }

            // Calcular balance: Ventas - Compras - Deuda por Cobrar (Ganancias reales)
            $balanceInPeriod = (float) ($salesInPeriod - $purchasesInPeriod - $debtAtClosing);

            // Formatear fechas para mostrar en las opciones
            $openingDateFormatted = Carbon::parse($openingDate)->format('d/m/y');
            $closingDateFormatted = Carbon::parse($closingDate)->format('d/m/y');

            // Datos para closedCashCountsData
            $closedCashCountsData[] = [
               'id' => $closedCashCount->id,
               'opening_date' => $openingDate,
               'closing_date' => $closingDate,
               'opening_date_formatted' => $openingDateFormatted,
               'closing_date_formatted' => $closingDateFormatted,
               'option_text' => "Arqueo #{$closedCashCount->id} (desde: {$openingDateFormatted} hasta: {$closingDateFormatted})",
               'sales' => (float) $salesInPeriod,
               'purchases' => (float) $purchasesInPeriod,
               'debt_payments' => (float) $debtPaymentsInPeriod,
               'debt' => (float) $debtAtClosing,
               'balance' => (float) $balanceInPeriod,
               'initial_amount' => (float) $closedCashCount->initial_amount
            ];

            // Datos para closedSalesData
            $todaySalesInPeriod = 0;
            if (
               Carbon::parse($openingDate)->startOfDay() <= now()->startOfDay() &&
               Carbon::parse($closingDate)->startOfDay() >= now()->startOfDay()
            ) {
               $todaySalesInPeriod = $todaySales;
            }

            // Ventas semanales (simplificado)
            $weeklySalesInPeriod = 0;
            $startOfWeek = now()->startOfWeek();
            if (Carbon::parse($openingDate) <= $startOfWeek && Carbon::parse($closingDate) >= $startOfWeek) {
               $weeklySalesInPeriod = $salesInPeriod * 0.3; // Aproximación
            }

            // Ganancia total teórica (simplificado)
            $totalProfitInPeriod = $salesInPeriod * 0.25; // Aproximación del 25%

            $closedSalesData[] = [
               'id' => $closedCashCount->id,
               'today_sales' => $todaySalesInPeriod,
               'weekly_sales' => $weeklySalesInPeriod,
               'average_customer_spend' => $averageCustomerSpendInPeriod,
               'total_profit' => $totalProfitInPeriod,
               'monthly_sales' => $salesInPeriod,
               'total_sales' => $salesInPeriod
            ];
         }
      }

      // ==========================================
      //           DATOS DE VENTAS POR ARQUEO
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

      // NOTA: $closedSalesData ya se procesó en el bucle combinado anterior

      // Mantener variables originales para compatibilidad
      $salesSinceCashOpen = $currentCashData['sales'];
      $purchasesSinceCashOpen = $currentCashData['purchases'];
      $debtSinceCashOpen = $currentCashData['debt'];

      // 5. Datos para el Gráfico de Flujo de Caja (El Pulso del Dinero)
      // Obtenemos movimientos agrupados por día y cash_count_id para ser reactivos
      $allDailyMovements = DB::select("
          SELECT 
              cm.cash_count_id,
              DATE(cm.created_at) as date,
              SUM(CASE WHEN cm.type = 'income' THEN cm.amount ELSE 0 END) as income,
              SUM(CASE WHEN cm.type = 'expense' THEN cm.amount ELSE 0 END) as expense
          FROM cash_movements cm
          JOIN cash_counts cc ON cm.cash_count_id = cc.id
          WHERE cc.company_id = ?
          AND cm.created_at >= CURRENT_DATE - INTERVAL '1 year' -- Limitamos a un año para no sobrecargar
          GROUP BY cm.cash_count_id, DATE(cm.created_at)
          ORDER BY date ASC
      ", [$companyId]);

      $chartData = [
         'daily_movements' => $allDailyMovements,
         // Mantenemos una versión simplificada de los últimos 30 días para la carga inicial "Histórica"
         'labels' => [],
         'income' => [],
         'expenses' => []
      ];

      // Construir el histórico de 30 días para el arranque rápido
      $last30Days = collect(range(29, 0))->map(fn($days) => now()->subDays($days)->format('Y-m-d'));
      foreach ($last30Days as $date) {
         $dayData = collect($allDailyMovements)->where('date', $date);
         $chartData['labels'][] = Carbon::parse($date)->format('d/m');
         $chartData['income'][] = $dayData->sum('income');
         $chartData['expenses'][] = $dayData->sum('expense');
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
         'profitMonthlyData',
         'transactionsMonthlyData',
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
         'totalProfit',
         'monthlySales',
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
