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

class AdminController extends Controller
{
   public function index()
   {
      // Obtener conteos básicos
      $usersCount = User::where('company_id', Auth::user()->company_id)->count();
      $rolesCount = Role::count();
      $categoriesCount = Category::where('company_id', Auth::user()->company_id)->count();
      $productsCount = Product::where('company_id', Auth::user()->company_id)->count();

      // Usuarios por rol
      $usersByRole = Role::withCount(['users' => function ($query) {
         $query->where('company_id', Auth::user()->company_id);
      }])->get()->map(function ($role) {
         return [
            'name' => ucfirst($role->name),
            'count' => $role->users_count
         ];
      });

      // Usuarios por mes (últimos 6 meses)
      $usersPerMonth = User::where('company_id', Auth::user()->company_id)
         ->select(DB::raw('DATE_FORMAT(created_at, "%M %Y") as month'), DB::raw('count(*) as count'))
         ->whereDate('created_at', '>=', now()->subMonths(6))
         ->groupBy('month')
         ->orderBy('created_at')
         ->get();

      // Productos por categoría
      $productsByCategory = Category::where('company_id', Auth::user()->company_id)
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
         ->where('company_id', Auth::user()->company_id)
         ->count();

      // Proveedores más activos (con más productos asociados)
      $topSuppliers = DB::table('suppliers')
         ->select('suppliers.company_name', DB::raw('COUNT(products.id) as products_count'))
         ->leftJoin('products', 'products.supplier_id', '=', 'suppliers.id')
         ->where('suppliers.company_id', Auth::user()->company_id)
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
         ->where('suppliers.company_id', Auth::user()->company_id)
         ->groupBy('suppliers.id', 'suppliers.company_name')
         ->orderByDesc('total_value')
         ->get();

      // Proveedores agregados por mes (últimos 6 meses)
      $suppliersPerMonth = DB::table('suppliers')
         ->select(
            DB::raw('DATE_FORMAT(created_at, "%M %Y") as month'),
            DB::raw('count(*) as count')
         )
         ->where('company_id', Auth::user()->company_id)
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
         ->where('suppliers.company_id', Auth::user()->company_id)
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

      // Proveedor principal
      $topSupplier = DB::table('purchase_details')
         ->select(
            'suppliers.company_name as name',
            DB::raw('SUM(purchase_details.quantity * purchase_details.product_price) as total_amount')
         )
         ->join('suppliers', 'purchase_details.supplier_id', '=', 'suppliers.id')
         ->whereMonth('purchase_details.created_at', now()->month())
         ->groupBy('suppliers.id', 'suppliers.company_name')
         ->orderByDesc('total_amount')
         ->first() ?? (object)[
            'name' => 'N/A',
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

      // Top 5 productos más comprados (con precio unitario)
      $topProducts = DB::table('purchase_details')
         ->select(
            'products.name',
            DB::raw('SUM(purchase_details.quantity) as total_quantity'),
            DB::raw('AVG(purchase_details.product_price) as unit_price')
         )
         ->join('products', 'purchase_details.product_id', '=', 'products.id')
         ->groupBy('products.id', 'products.name')
         ->orderByDesc('total_quantity')
         ->limit(5)
         ->get();

      // Productos con stock bajo
      $lowStockCount = Product::where('stock', '<=', DB::raw('min_stock'))->count();

      // Nuevas variables para la sección de clientes
      
      // Total de clientes y crecimiento
      $totalCustomers = DB::table('customers')
         ->where('company_id', Auth::user()->company_id)
         ->count();

      $lastMonthCustomers = DB::table('customers')
         ->where('company_id', Auth::user()->company_id)
         ->whereMonth('created_at', now()->subMonth()->month)
         ->count();

      $customerGrowth = $lastMonthCustomers > 0 ? 
         round((($totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1) : 0;

      // Nuevos clientes este mes
      $newCustomers = DB::table('customers')
         ->where('company_id', Auth::user()->company_id)
         ->whereMonth('created_at', now()->month)
         ->count();

      // Top 5 clientes por compras
      $topCustomers = DB::table('customers')
         ->select(
            'customers.name',
            'customers.nit_number',
            'customers.phone',
            'customers.email',
            DB::raw('COUNT(purchases.id) as total_purchases'),
            DB::raw('SUM(purchases.total_price) as total_spent')
         )
         ->leftJoin('purchases', 'customers.id', '=', 'purchases.customer_id')
         ->where('customers.company_id', Auth::user()->company_id)
         ->groupBy('customers.id', 'customers.name', 'customers.nit_number', 'customers.phone', 'customers.email')
         ->orderByDesc('total_spent')
         ->limit(5)
         ->get();

      // Actividad mensual de clientes (últimos 6 meses)
      $monthlyActivity = [];
      $monthlyLabels = [];

      for ($i = 5; $i >= 0; $i--) {
         $date = now()->subMonths($i);
         $monthlyLabels[] = $date->format('M Y');
         
         $monthlyActivity[] = DB::table('customers')
            ->where('company_id', Auth::user()->company_id)
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
            ->where('company_id', Auth::user()->company_id)
            ->whereDate('created_at', $date)
            ->count();
      }


      // Frecuencia de compras
      $frequencyData = DB::table('purchases')
         ->where('purchases.company_id', Auth::user()->company_id)
         ->select(DB::raw('COUNT(*) as purchase_count'))
         ->groupBy('customer_id')
         ->get()
         ->groupBy('purchase_count')
         ->map->count()
         ->values()
         ->toArray();

      $frequencyLabels = ['1 compra', '2-3 compras', '4-6 compras', '7+ compras'];

      // Segmentación de clientes
      $segmentationData = [
         DB::table('customers')
            ->where('company_id', Auth::user()->company_id)
            ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                  ->from('purchases')
                  ->whereColumn('purchases.customer_id', 'customers.id')
                  ->havingRaw('COUNT(*) >= 7');
            })->count(),
         DB::table('customers')
            ->where('company_id', Auth::user()->company_id)
            ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                  ->from('purchases')
                  ->whereColumn('purchases.customer_id', 'customers.id')
                  ->havingRaw('COUNT(*) BETWEEN 4 AND 6');
            })->count(),
         DB::table('customers')
            ->where('company_id', Auth::user()->company_id)
            ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                  ->from('purchases')
                  ->whereColumn('purchases.customer_id', 'customers.id')
                  ->havingRaw('COUNT(*) BETWEEN 2 AND 3');
            })->count(),
         DB::table('customers')
            ->where('company_id', Auth::user()->company_id)
            ->whereExists(function ($query) {
               $query->select(DB::raw(1))
                  ->from('purchases')
                  ->whereColumn('purchases.customer_id', 'customers.id')
                  ->havingRaw('COUNT(*) = 1');
            })->count()
      ];

      $segmentationLabels = ['VIP', 'Frecuentes', 'Ocasionales', 'Nuevos'];

     

      $averagePurchaseInterval = DB::table('purchases')
         ->where('company_id', Auth::user()->company_id)
         ->select('customer_id')
         ->selectRaw('DATEDIFF(MAX(purchase_date), MIN(purchase_date)) as days_between')
         ->groupBy('customer_id')
         ->havingRaw('COUNT(*) > 1')
         ->get()
         ->avg('days_between') ?? 0;

      $churnRate = DB::table('customers')
         ->where('company_id', Auth::user()->company_id)
         ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
               ->from('purchases')
               ->whereColumn('purchases.customer_id', 'customers.id')
               ->where('purchases.created_at', '>=', now()->subMonths(3));
         })
         ->whereExists(function ($query) {
            $query->select(DB::raw(1))
               ->from('purchases')
               ->whereColumn('purchases.customer_id', 'customers.id');
         })
         ->count();

      $churnRate = $totalCustomers > 0 ? 
         round(($churnRate / $totalCustomers) * 100, 1) : 0;

      // Agregar las nuevas variables al compact existente
      return view('admin.index', compact(
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
         'topCustomers',
         'averagePurchaseInterval',
         'churnRate',
         'monthlyLabels',
         'monthlyActivity',
         'activityData',
         'activityLabels',
         'frequencyData',
         'frequencyLabels',
         'segmentationData',
         'segmentationLabels'
      ));
   }
}

