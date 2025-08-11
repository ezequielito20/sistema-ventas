<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CashCountController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DebtPaymentController;

// Ruta pública para pedidos de clientes
Route::get('/', function () {
    return view('public.order-system');
})->name('public.orders');

Route::get('/test-livewire', function () {
    return view('test-livewire');
})->name('test.livewire');

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Dashboard administrativo (requiere autenticación)
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index')->middleware('auth');

Route::get('/create-company/{country}', [CompanyController::class, 'search_country'])->name('admin.company.search_country');
Route::get('/search-state/{state}', [CompanyController::class, 'search_state'])->name('admin.company.search_state');

// Configuración de empresa
Route::get('/create-company', [CompanyController::class, 'create'])->name('admin.company.create');
Route::post('/create-company', [CompanyController::class, 'store'])->name('admin.company.store');

Route::get('/settings', [CompanyController::class, 'edit'])->name('admin.company.edit')->middleware(['auth', 'can:companies.edit']);
Route::put('/settings/{id}', [CompanyController::class, 'update'])->name('admin.companies.update')->middleware(['auth', 'can:companies.update']);


// Rutas para reportes
Route::get('/users/report', [UserController::class, 'report'])->name('admin.users.report')->middleware(['auth', 'can:users.report']);
Route::get('/roles/report', [RoleController::class, 'report'])->name('admin.roles.report')->middleware(['auth', 'can:roles.report']);
Route::get('/categories/report', [CategoryController::class, 'report'])->name('admin.categories.report')->middleware(['auth', 'can:categories.report']);
Route::get('/products/report', [ProductController::class, 'report'])->name('admin.products.report')->middleware(['auth', 'can:products.report']);
Route::get('/suppliers/report', [SupplierController::class, 'report'])->name('admin.suppliers.report')->middleware(['auth', 'can:suppliers.report']);
Route::get('/purchases/report', [PurchaseController::class, 'report'])->name('admin.purchases.report')->middleware(['auth', 'can:purchases.report']);
Route::get('/customers/report', [CustomerController::class, 'report'])->name('admin.customers.report')->middleware(['auth', 'can:customers.report']);
Route::get('/sales/report', [SaleController::class, 'report'])->name('admin.sales.report')->middleware(['auth', 'can:sales.report']);
Route::get('/cash-counts/report', [CashCountController::class, 'report'])->name('admin.cash-counts.report')->middleware(['auth', 'can:cash-counts.report']);
Route::get('/permissions/report', [PermissionController::class, 'report'])->name('admin.permissions.report')->middleware(['auth', 'can:permissions.report']);

// Roles
Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index')->middleware(['auth', 'can:roles.index']);
Route::get('/roles/create', [RoleController::class, 'create'])->name('admin.roles.create')->middleware(['auth', 'can:roles.create']);
Route::post('/roles/create', [RoleController::class, 'store'])->name('admin.roles.store')->middleware(['auth', 'can:roles.create']);
Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])->name('admin.roles.edit')->middleware(['auth', 'can:roles.edit']);
Route::put('/roles/edit/{id}', [RoleController::class, 'update'])->name('admin.roles.update')->middleware(['auth', 'can:roles.edit']);
Route::delete('/roles/delete/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy')->middleware(['auth', 'can:roles.destroy']);
Route::get('/roles/{id}', [RoleController::class, 'show'])->name('admin.roles.show')->middleware(['auth', 'can:roles.show']);
Route::get('/roles/{id}/permissions', [RoleController::class, 'permissions'])->name('admin.roles.permissions')->middleware(['auth', 'can:roles.permissions']);
Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions'])->name('admin.roles.assign.permissions')->middleware(['auth', 'can:roles.assign.permissions']);

// Users
Route::get('/users', [UserController::class, 'index'])->name('admin.users.index')->middleware(['auth', 'can:users.index']);
Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create')->middleware(['auth', 'can:users.create']);
Route::post('/users/create', [UserController::class, 'store'])->name('admin.users.store')->middleware(['auth', 'can:users.create']);
Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit')->middleware(['auth', 'can:users.edit']);
Route::put('/users/edit/{id}', [UserController::class, 'update'])->name('admin.users.update')->middleware(['auth', 'can:users.edit']);
Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy')->middleware(['auth', 'can:users.destroy']);
Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show')->middleware(['auth', 'can:users.show']);

// Categories
Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index')->middleware(['auth', 'can:categories.index']);
Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create')->middleware(['auth', 'can:categories.create']);
Route::post('/categories/create', [CategoryController::class, 'store'])->name('admin.categories.store')->middleware(['auth', 'can:categories.create']);
Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit')->middleware(['auth', 'can:categories.edit']);
Route::put('/categories/edit/{id}', [CategoryController::class, 'update'])->name('admin.categories.update')->middleware(['auth', 'can:categories.edit']);
Route::delete('/categories/delete/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy')->middleware(['auth', 'can:categories.destroy']);
Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('admin.categories.show')->middleware(['auth', 'can:categories.show']);

// Products
Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index')->middleware(['auth', 'can:products.index']);
Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create')->middleware(['auth', 'can:products.create']);
Route::post('/products/create', [ProductController::class, 'store'])->name('admin.products.store')->middleware(['auth', 'can:products.create']);
Route::get('/products/edit/{id}', [ProductController::class, 'edit'])->name('admin.products.edit')->middleware(['auth', 'can:products.edit']);
Route::put('/products/edit/{id}', [ProductController::class, 'update'])->name('admin.products.update')->middleware(['auth', 'can:products.edit']);
Route::delete('/products/delete/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy')->middleware(['auth', 'can:products.destroy']);
Route::get('/products/{id}', [ProductController::class, 'show'])->name('admin.products.show')->middleware(['auth', 'can:products.show']);

// Suppliers
Route::get('/suppliers', [SupplierController::class, 'index'])->name('admin.suppliers.index')->middleware(['auth', 'can:suppliers.index']);
Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('admin.suppliers.create')->middleware(['auth', 'can:suppliers.create']);
Route::post('/suppliers/create', [SupplierController::class, 'store'])->name('admin.suppliers.store')->middleware(['auth', 'can:suppliers.create']);
Route::get('/suppliers/edit/{id}', [SupplierController::class, 'edit'])->name('admin.suppliers.edit')->middleware(['auth', 'can:suppliers.edit']);
Route::put('/suppliers/edit/{id}', [SupplierController::class, 'update'])->name('admin.suppliers.update')->middleware(['auth', 'can:suppliers.edit']);
Route::delete('/suppliers/delete/{id}', [SupplierController::class, 'destroy'])->name('admin.suppliers.destroy')->middleware(['auth', 'can:suppliers.destroy']);
Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->name('admin.suppliers.show')->middleware(['auth', 'can:suppliers.show']);

// Purchases
Route::get('/purchases', [PurchaseController::class, 'index'])->name('admin.purchases.index')->middleware(['auth', 'can:purchases.index']);
Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('admin.purchases.create')->middleware(['auth', 'can:purchases.create']);
Route::post('/purchases/create', [PurchaseController::class, 'store'])->name('admin.purchases.store')->middleware(['auth', 'can:purchases.create']);
Route::get('/purchases/edit/{id}', [PurchaseController::class, 'edit'])->name('admin.purchases.edit')->middleware(['auth', 'can:purchases.edit']);
Route::put('/purchases/edit/{id}', [PurchaseController::class, 'update'])->name('admin.purchases.update')->middleware(['auth', 'can:purchases.edit']);
Route::delete('/purchases/delete/{id}', [PurchaseController::class, 'destroy'])->name('admin.purchases.destroy')->middleware(['auth', 'can:purchases.destroy']);
Route::get('/purchases/{id}/details', [PurchaseController::class, 'getDetails'])->name('admin.purchases.details')->middleware(['auth', 'can:purchases.details']);
Route::get('/purchases/product-details/{code}', [PurchaseController::class, 'getProductDetails'])->name('admin.purchases.product-details')->middleware(['auth', 'can:purchases.product-details']);
Route::get('/purchases/product-by-code/{code}', [PurchaseController::class, 'getProductByCode'])->name('admin.purchases.product-by-code')->middleware(['auth', 'can:purchases.product-by-code']);

// Customers
Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index')->middleware(['auth', 'can:customers.index']);
Route::get('/customers/create', [CustomerController::class, 'create'])->name('admin.customers.create')->middleware(['auth', 'can:customers.create']);
Route::post('/customers/create', [CustomerController::class, 'store'])->name('admin.customers.store')->middleware(['auth', 'can:customers.create']);
Route::get('/customers/edit/{id}', [CustomerController::class, 'edit'])->name('admin.customers.edit')->middleware(['auth', 'can:customers.edit']);
Route::put('/customers/edit/{id}', [CustomerController::class, 'update'])->name('admin.customers.update')->middleware(['auth', 'can:customers.edit']);
Route::delete('/customers/delete/{id}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy')->middleware(['auth', 'can:customers.destroy']);
Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('admin.customers.show')->middleware(['auth', 'can:customers.show']);
Route::post('/admin/customers/{customer}/update-debt', [CustomerController::class, 'updateDebt'])->name('admin.customers.update-debt');
Route::get('/admin/customers/debt-report', [App\Http\Controllers\CustomerController::class, 'debtReportModal'])
    ->name('admin.customers.debt-report');
Route::get('/admin/customers/debt-report/download', [App\Http\Controllers\CustomerController::class, 'debtReport'])
    ->name('admin.customers.debt-report.download');
Route::post('/admin/customers/{customer}/register-payment', [App\Http\Controllers\CustomerController::class, 'registerDebtPayment'])
    ->name('admin.customers.register-payment');
Route::get('/admin/customers/payment-history', [App\Http\Controllers\CustomerController::class, 'paymentHistory'])
    ->name('admin.customers.payment-history');
Route::get('/admin/customers/payment-history/export', [App\Http\Controllers\CustomerController::class, 'exportPaymentHistory'])
    ->name('admin.customers.payment-history.export');
Route::delete('/admin/customers/payment-history/{payment}', [App\Http\Controllers\CustomerController::class, 'deletePayment'])
    ->name('admin.customers.payment.delete');

// Sales
Route::get('/sales', [SaleController::class, 'index'])->name('admin.sales.index')->middleware(['auth', 'can:sales.index']);
Route::get('/sales/create', [SaleController::class, 'create'])->name('admin.sales.create')->middleware(['auth', 'can:sales.create']);
Route::post('/sales/create', [SaleController::class, 'store'])->name('admin.sales.store')->middleware(['auth', 'can:sales.create']);
Route::get('/sales/edit/{id}', [SaleController::class, 'edit'])->name('admin.sales.edit')->middleware(['auth', 'can:sales.edit']);
Route::put('/sales/edit/{id}', [SaleController::class, 'update'])->name('admin.sales.update')->middleware(['auth', 'can:sales.edit']);
Route::delete('/sales/delete/{id}', [SaleController::class, 'destroy'])->name('admin.sales.destroy')->middleware(['auth', 'can:sales.destroy']);
Route::get('/sales/{id}/details', [SaleController::class, 'getDetails'])->name('admin.sales.details')->middleware(['auth', 'can:sales.details']);
Route::get('/test-sales-details/{id}', [SaleController::class, 'getDetails'])->name('test.sales.details')->middleware(['auth']);
Route::get('/sales/product-details/{code}', [SaleController::class, 'getProductDetails'])->name('admin.sales.product-details')->middleware(['auth', 'can:sales.product-details']);
Route::get('/sales/product-by-code/{code}', [SaleController::class, 'getProductByCode'])->name('admin.sales.product-by-code')->middleware(['auth', 'can:sales.product-by-code']);
Route::get('/sales/print/{id}', [SaleController::class, 'printSale'])->name('admin.sales.print')->middleware(['auth', 'can:sales.print']);

// Cash Counts
Route::get('/cash-counts', [CashCountController::class, 'index'])->name('admin.cash-counts.index')->middleware(['auth', 'can:cash-counts.index']);
Route::get('/cash-counts/create', [CashCountController::class, 'create'])->name('admin.cash-counts.create')->middleware(['auth', 'can:cash-counts.create']);
Route::post('/cash-counts/create', [CashCountController::class, 'store'])->name('admin.cash-counts.store')->middleware(['auth', 'can:cash-counts.create']);
Route::get('/cash-counts/edit/{id}', [CashCountController::class, 'edit'])->name('admin.cash-counts.edit')->middleware(['auth', 'can:cash-counts.edit']);
Route::put('/cash-counts/edit/{id}', [CashCountController::class, 'update'])->name('admin.cash-counts.update')->middleware(['auth', 'can:cash-counts.edit']);
Route::delete('/cash-counts/delete/{id}', [CashCountController::class, 'destroy'])->name('admin.cash-counts.destroy')->middleware(['auth', 'can:cash-counts.destroy']);
Route::get('/cash-counts/{id}', [CashCountController::class, 'show'])->name('admin.cash-counts.show')->middleware(['auth', 'can:cash-counts.show']);
Route::post('/cash-counts/store-movement', [CashCountController::class, 'storeMovement'])->name('admin.cash-counts.store-movement')->middleware(['auth', 'can:cash-counts.store-movement']);
Route::put('/cash-counts/close/{id}', [CashCountController::class, 'closeCash'])->name('admin.cash-counts.close')->middleware(['auth', 'can:cash-counts.close']);
Route::get('/cash-counts/{id}/history', [CashCountController::class, 'history'])->name('admin.cash-counts.history')->middleware(['auth', 'can:cash-counts.show']);


// Permissions
Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index')->middleware(['auth', 'can:permissions.index']);
Route::get('/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create')->middleware(['auth', 'can:permissions.create']);
Route::post('/permissions/create', [PermissionController::class, 'store'])->name('admin.permissions.store')->middleware(['auth', 'can:permissions.create']);
Route::get('/permissions/edit/{id}', [PermissionController::class, 'edit'])->name('admin.permissions.edit')->middleware(['auth', 'can:permissions.edit']);
Route::put('/permissions/edit/{id}', [PermissionController::class, 'update'])->name('admin.permissions.update')->middleware(['auth', 'can:permissions.edit']);
Route::delete('/permissions/delete/{id}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy')->middleware(['auth', 'can:permissions.destroy']);
Route::get('/permissions/{id}', [PermissionController::class, 'show'])->name('admin.permissions.show')->middleware(['auth', 'can:permissions.show']);

    // Temporal: Limpiar movimientos huérfanos de caja
    Route::get('/admin/clean-orphan-movements', function () {
        try {
            DB::beginTransaction();
            
            // Obtener todos los movimientos de caja que mencionan compras o ventas
            $purchaseMovements = DB::table('cash_movements')
                ->where('description', 'like', 'Compra #%')
                ->get();
                
            $saleMovements = DB::table('cash_movements')
                ->where('description', 'like', 'Venta #%')
                ->get();
            
            $deletedCount = 0;
            
            // Verificar movimientos de compras
            foreach ($purchaseMovements as $movement) {
                $purchaseId = str_replace('Compra #', '', $movement->description);
                $purchaseExists = DB::table('purchases')->where('id', $purchaseId)->exists();
                
                if (!$purchaseExists) {
                    DB::table('cash_movements')->where('id', $movement->id)->delete();
                    $deletedCount++;
                }
            }
            
            // Verificar movimientos de ventas
            foreach ($saleMovements as $movement) {
                $saleId = str_replace('Venta #', '', $movement->description);
                $saleExists = DB::table('sales')->where('id', $saleId)->exists();
                
                if (!$saleExists) {
                    DB::table('cash_movements')->where('id', $movement->id)->delete();
                    $deletedCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.index')
                ->with('message', "Se eliminaron {$deletedCount} movimientos huérfanos de caja")
                ->with('icons', 'success');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.index')
                ->with('message', 'Error al limpiar movimientos: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    })->middleware(['auth'])->name('admin.clean-orphan-movements');

    // Temporal: Limpiar pagos de deuda huérfanos
    Route::get('/admin/clean-orphan-debt-payments', function () {
        try {
            DB::beginTransaction();
            
            // Obtener todos los pagos de deuda
            $debtPayments = DB::table('debt_payments')->get();
            
            $deletedCount = 0;
            
            // Verificar si las ventas asociadas existen
            foreach ($debtPayments as $payment) {
                $saleExists = DB::table('sales')->where('id', $payment->sale_id)->exists();
                
                if (!$saleExists) {
                    DB::table('debt_payments')->where('id', $payment->id)->delete();
                    $deletedCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.index')
                ->with('message', "Se eliminaron {$deletedCount} pagos de deuda huérfanos")
                ->with('icons', 'success');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.index')
                ->with('message', 'Error al limpiar pagos de deuda: ' . $e->getMessage())
                ->with('icons', 'error');
        }
    })->middleware(['auth'])->name('admin.clean-orphan-debt-payments');

    // Rutas para manejo de pagos de deuda
    Route::prefix('admin/debt-payments')->middleware(['auth'])->group(function () {
        Route::delete('/{id}', [DebtPaymentController::class, 'destroy'])->name('admin.debt-payments.destroy');
        Route::get('/sale/{saleId}', [DebtPaymentController::class, 'getPaymentsBySale'])->name('admin.debt-payments.by-sale');
        Route::delete('/sale/{saleId}/all', [DebtPaymentController::class, 'deletePaymentsBySale'])->name('admin.debt-payments.delete-by-sale');
    });

    // Rutas para manejo de pedidos (Admin)
    Route::prefix('admin/orders')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/{order}/process', [App\Http\Controllers\OrderController::class, 'process'])->name('admin.orders.process');
        Route::post('/{order}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('admin.orders.cancel');
    });

    // Rutas para notificaciones (Admin)
    Route::prefix('admin/notifications')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::post('/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('admin.notifications.unread-count');
        Route::get('/recent', [App\Http\Controllers\NotificationController::class, 'getRecentNotifications'])->name('admin.notifications.recent');
        Route::post('/{notification}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
    });

    // Rutas de Debugbar (solo cuando esté habilitada)
    if (config('app.debug') && config('debugbar.enabled')) {
        Route::get('_debugbar/assets/stylesheets', [
            'as' => 'debugbar.assets.css',
            'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@css'
        ]);
        
        Route::get('_debugbar/assets/javascript', [
            'as' => 'debugbar.assets.js',
            'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@js'
        ]);
        
        Route::get('_debugbar/open', [
            'as' => 'debugbar.open',
            'uses' => '\Barryvdh\Debugbar\Controllers\OpenHandlerController@handle'
        ]);
    }
