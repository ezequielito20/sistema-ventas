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
         'topProducts'
      ));
   }
}
