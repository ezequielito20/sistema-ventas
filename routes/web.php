<?php

use App\Http\Controllers\Admin\V2\PurchaseV2Controller;
use App\Http\Controllers\Admin\V2\SaleV2Controller;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\Auth\SecurityQuestionsController;
use App\Http\Controllers\CashCountController;
use App\Http\Controllers\CatalogCheckoutPageController;
use App\Http\Controllers\CatalogDeliveryMethodsController;
use App\Http\Controllers\CatalogPaymentMethodsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DebtPaymentController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderSummaryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicCatalogCartController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Services\ImageUrlService;
use App\Services\PlanEntitlementService;
use Barryvdh\Debugbar\Controllers\AssetController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Ruta pública para pedidos de clientes
// Ruta pública para pedidos de clientes, muestra la vista 'public.order-system'
// Route::get('/', function () {
//     return view('public.order-system');
// })->name('public.orders');

Auth::routes(['register' => false]);

// Proxy de imágenes desde el disco por defecto (S3/R2 en producción)
Route::get('/img/{path}', function (string $path) {
    $image = ImageUrlService::serve($path);
    if (! $image) {
        abort(404);
    }

    return response($image['content'], 200, [
        'Content-Type' => $image['mime'],
        'Content-Length' => $image['size'],
        'Cache-Control' => 'public, max-age=86400',
        'ETag' => '"'.md5($image['content']).'"',
    ]);
})->where('path', '.*')->name('image.serve');

// Recuperación de contraseña por preguntas de seguridad (v2)
Route::get('/password/recovery', [PasswordRecoveryController::class, 'showRecoveryForm'])
    ->name('password.recovery')
    ->middleware('guest');
Route::post('/password/recovery', [PasswordRecoveryController::class, 'findUser'])
    ->name('password.recovery.find')
    ->middleware('guest');
Route::get('/password/recovery/questions', [PasswordRecoveryController::class, 'showQuestions'])
    ->name('password.recovery.questions')
    ->middleware('guest');
Route::post('/password/recovery/questions', [PasswordRecoveryController::class, 'verifyQuestions'])
    ->name('password.recovery.verify')
    ->middleware('guest');
Route::get('/password/recovery/reset', [PasswordRecoveryController::class, 'showResetForm'])
    ->name('password.recovery.reset')
    ->middleware('guest');
Route::post('/password/recovery/reset', [PasswordRecoveryController::class, 'resetPassword'])
    ->name('password.recovery.update')
    ->middleware('guest');

// Configuración de preguntas de seguridad (requiere auth)
Route::get('/security-questions/setup', [SecurityQuestionsController::class, 'setup'])
    ->name('security-questions.setup')
    ->middleware('auth');
Route::post('/security-questions/setup', [SecurityQuestionsController::class, 'store'])
    ->name('security-questions.store')
    ->middleware('auth');

// Cambio de contraseña desde el perfil
Route::get('/profile/change-password', fn () => view('auth.v2.change-password'))
    ->name('profile.change-password')
    ->middleware('auth');
Route::post('/profile/change-password', [ChangePasswordController::class, 'update'])
    ->name('profile.change-password.update')
    ->middleware('auth');

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Dashboard administrativo (requiere autenticación)
Route::get('/', [AdminController::class, 'index'])->name('admin.index')->middleware('auth');
Route::view('/ui/notifications-preview', 'admin.ui.notifications-preview')
    ->name('admin.ui.notifications.preview')
    ->middleware('auth');
Route::view('/ui/design-system-preview', 'admin.ui.design-system-preview')
    ->name('admin.ui.design-system.preview')
    ->middleware('auth');
Route::view('/ui/charts-preview', 'admin.ui.charts-preview')
    ->name('admin.ui.charts.preview')
    ->middleware('auth');
Route::view('/ui/shell-preview', 'admin.ui.shell-preview')
    ->name('admin.ui.shell.preview')
    ->middleware('auth');

Route::view('/my-plan', 'admin.v2.my-plan')
    ->name('admin.my-plan')
    ->middleware(['auth', 'can:my-plan.view']);

Route::get('/settings', fn () => view('admin.v2.settings.index'))->name('admin.company.edit')->middleware(['auth', 'can:companies.edit']);
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
Route::get('/purchases', [PurchaseV2Controller::class, 'index'])->name('admin.purchases.index')->middleware(['auth', 'can:purchases.index']);
Route::get('/purchases/create', fn () => view('admin.v2.purchases.create'))->name('admin.purchases.create')->middleware(['auth', 'can:purchases.create']);
Route::post('/purchases/create', [PurchaseController::class, 'store'])->name('admin.purchases.store')->middleware(['auth', 'can:purchases.create']);
Route::get('/purchases/edit/{id}', fn ($id) => view('admin.v2.purchases.edit', ['purchaseId' => (int) $id]))->name('admin.purchases.edit')->middleware(['auth', 'can:purchases.edit']);
Route::put('/purchases/edit/{id}', [PurchaseController::class, 'update'])->name('admin.purchases.update')->middleware(['auth', 'can:purchases.edit']);
Route::delete('/purchases/delete/{id}', [PurchaseController::class, 'destroy'])->name('admin.purchases.destroy')->middleware(['auth', 'can:purchases.destroy']);
Route::get('/purchases/{id}/details', [PurchaseController::class, 'getDetails'])->name('admin.purchases.details')->middleware(['auth', 'can:purchases.details']);
Route::get('/purchases/product-details/{code}', [PurchaseController::class, 'getProductDetails'])->name('admin.purchases.product-details')->middleware(['auth', 'can:purchases.product-details']);
Route::get('/purchases/product-by-code/{code}', [PurchaseController::class, 'getProductByCode'])->name('admin.purchases.product-by-code')->middleware(['auth', 'can:purchases.product-by-code']);

// Customers
Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index')->middleware(['auth', 'can:customers.index']);
Route::get('/customers/create', [CustomerController::class, 'create'])->name('admin.customers.create')->middleware(['auth', 'can:customers.create']);
Route::post('/customers/create', [CustomerController::class, 'store'])->name('admin.customers.store')->middleware(['auth', 'can:customers.create']);
Route::get('/customers/debt-alerts', [CustomerController::class, 'getDebtAlerts'])->name('admin.customers.debt-alerts')->middleware(['auth', 'can:customers.index']);
Route::post('/customers/debt-alerts/accept', [CustomerController::class, 'acceptDebtAlerts'])->name('admin.customers.debt-alerts.accept')->middleware(['auth', 'can:customers.index']);
Route::get('/customers/edit/{id}', [CustomerController::class, 'edit'])->name('admin.customers.edit')->middleware(['auth', 'can:customers.edit']);
Route::put('/customers/edit/{id}', [CustomerController::class, 'update'])->name('admin.customers.update')->middleware(['auth', 'can:customers.edit']);
Route::delete('/customers/delete/{id}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy')->middleware(['auth', 'can:customers.destroy']);
Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('admin.customers.show')->middleware(['auth', 'can:customers.show']);
Route::post('/admin/customers/{customer}/update-debt', [CustomerController::class, 'updateDebt'])->name('admin.customers.update-debt');
Route::get('/admin/customers/debt-report', [CustomerController::class, 'debtReportModal'])
    ->name('admin.customers.debt-report');
Route::get('/admin/customers/debt-report/download', [CustomerController::class, 'debtReport'])
    ->name('admin.customers.debt-report.download');
Route::get('/admin/customers/test-pdf', [CustomerController::class, 'testPdf'])
    ->name('admin.customers.test-pdf');
Route::post('/admin/customers/{customer}/register-payment', [CustomerController::class, 'registerDebtPayment'])
    ->name('admin.customers.register-payment');
Route::post('/admin/customers/{customer}/register-payment-ajax', [CustomerController::class, 'registerDebtPaymentAjax'])
    ->name('admin.customers.register-payment-ajax');
Route::get('/admin/customers/{customer}/payment-data', [CustomerController::class, 'getCustomerPaymentData'])
    ->name('admin.customers.payment-data');
Route::get('/admin/customers/{customer}/sales-history', [CustomerController::class, 'getCustomerSalesHistory'])
    ->name('admin.customers.sales-history');
Route::get('/admin/customers/payment-history', [CustomerController::class, 'paymentHistory'])
    ->name('admin.customers.payment-history');
Route::get('/admin/customers/payment-history/export', [CustomerController::class, 'exportPaymentHistory'])
    ->name('admin.customers.payment-history.export');
Route::delete('/admin/customers/payment-history/{payment}', [CustomerController::class, 'deletePayment'])
    ->name('admin.customers.payment.delete');

// Tasa de Cambio BCV
Route::get('/admin/exchange-rate/current', [ExchangeRateController::class, 'current'])
    ->name('admin.exchange-rate.current')->middleware(['auth']);
Route::post('/admin/exchange-rate/update', [ExchangeRateController::class, 'forceUpdate'])
    ->name('admin.exchange-rate.update')->middleware(['auth']);

// Escaner de precios OCR (solo super admin)
Route::get('/admin/scanner', [ScannerController::class, 'index'])
    ->name('admin.scanner.index')->middleware(['auth', 'superadmin']);
Route::post('/admin/scanner/ocr', [ScannerController::class, 'ocr'])
    ->name('admin.scanner.ocr')->middleware(['auth', 'superadmin']);

// Sales v2 (override legacy with same route name)
Route::get('/sales', [SaleV2Controller::class, 'index'])->name('admin.sales.index')->middleware(['auth', 'can:sales.index']);

// Sales v2 create/edit (override legacy views — store/update still use legacy controller)
Route::get('/sales/create', fn () => view('admin.v2.sales.create'))->name('admin.sales.create')->middleware(['auth', 'can:sales.create']);
Route::get('/sales/edit/{id}', fn ($id) => view('admin.v2.sales.edit', ['saleId' => (int) $id]))->name('admin.sales.edit')->middleware(['auth', 'can:sales.edit']);

// Sales (legacy routes — store, update, destroy, etc.)
Route::post('/sales/create', [SaleController::class, 'store'])->name('admin.sales.store')->middleware(['auth', 'can:sales.create']);
Route::post('/sales/bulk-store', [SaleController::class, 'bulkStore'])->name('admin.sales.bulk-store')->middleware(['auth', 'can:sales.create']);
Route::put('/sales/edit/{id}', [SaleController::class, 'update'])->name('admin.sales.update')->middleware(['auth', 'can:sales.edit']);
Route::delete('/sales/delete/{id}', [SaleController::class, 'destroy'])->name('admin.sales.destroy')->middleware(['auth', 'can:sales.destroy']);
Route::get('/sales/{id}/details', [SaleController::class, 'getDetails'])->name('admin.sales.details')->middleware(['auth', 'can:sales.details']);
Route::get('/sales/product-details/{code}', [SaleController::class, 'getProductDetails'])->name('admin.sales.product-details')->middleware(['auth', 'can:sales.product-details']);
Route::get('/sales/product-by-code/{code}', [SaleController::class, 'getProductByCode'])->name('admin.sales.product-by-code')->middleware(['auth', 'can:sales.product-by-code']);
Route::get('/sales/today-details', [SaleController::class, 'getTodaySales'])->name('admin.sales.today-details')->middleware(['auth', 'can:sales.index']);
Route::get('/sales/print/{id}', [SaleController::class, 'printSale'])->name('admin.sales.print')->middleware(['auth', 'can:sales.print']);

// Cash Counts v2 (nuevo index)
Route::get('/cash-counts', fn () => view('admin.v2.cash-counts.index'))->name('admin.cash-counts.index')->middleware(['auth', 'can:cash-counts.index']);
Route::get('/cash-counts/create', fn () => view('admin.v2.cash-counts.create'))->name('admin.cash-counts.create')->middleware(['auth', 'can:cash-counts.create']);
Route::get('/cash-counts/edit/{id}', fn ($id) => view('admin.v2.cash-counts.edit', ['cashCountId' => (int) $id]))->name('admin.cash-counts.edit')->middleware(['auth', 'can:cash-counts.edit']);

// Cash Counts Legacy (index legacy movido)
Route::get('/cash-counts/legacy', [CashCountController::class, 'index'])->name('admin.cash-counts.legacy.index')->middleware(['auth', 'can:cash-counts.index']);
Route::get('/cash-counts/legacy/create', [CashCountController::class, 'create'])->name('admin.cash-counts.legacy.create')->middleware(['auth', 'can:cash-counts.create']);
Route::post('/cash-counts/create', [CashCountController::class, 'store'])->name('admin.cash-counts.store')->middleware(['auth', 'can:cash-counts.create']);
Route::get('/cash-counts/create-movement', [CashCountController::class, 'createMovement'])->name('admin.cash-counts.create-movement')->middleware(['auth', 'can:cash-counts.store-movement']);
Route::put('/cash-counts/edit/{id}', [CashCountController::class, 'update'])->name('admin.cash-counts.update')->middleware(['auth', 'can:cash-counts.edit']);
Route::delete('/cash-counts/delete/{id}', [CashCountController::class, 'destroy'])->name('admin.cash-counts.destroy')->middleware(['auth', 'can:cash-counts.destroy']);
Route::get('/cash-counts/{id}', [CashCountController::class, 'show'])->name('admin.cash-counts.show')->middleware(['auth', 'can:cash-counts.show']);
Route::post('/cash-counts/store-movement', [CashCountController::class, 'storeMovement'])->name('admin.cash-counts.store-movement')->middleware(['auth', 'can:cash-counts.store-movement']);
Route::put('/cash-counts/close/{id}', [CashCountController::class, 'closeCash'])->name('admin.cash-counts.close')->middleware(['auth', 'can:cash-counts.close']);
Route::get('/cash-counts/{id}/history', [CashCountController::class, 'history'])->name('admin.cash-counts.history')->middleware(['auth', 'can:cash-counts.show']);

// Rutas para detalles del arqueo de caja
Route::get('/cash-counts/{id}/details', [CashCountController::class, 'getDetails'])->name('admin.cash-counts.details')->middleware(['auth', 'can:cash-counts.show']);
Route::get('/cash-counts/{id}/customers', [CashCountController::class, 'getCustomers'])->name('admin.cash-counts.customers')->middleware(['auth', 'can:cash-counts.show']);
Route::get('/cash-counts/{id}/sales', [CashCountController::class, 'getSales'])->name('admin.cash-counts.sales')->middleware(['auth', 'can:cash-counts.show']);
Route::get('/cash-counts/{id}/purchases', [CashCountController::class, 'getPurchases'])->name('admin.cash-counts.purchases')->middleware(['auth', 'can:cash-counts.show']);
Route::get('/cash-counts/{id}/products', [CashCountController::class, 'getProducts'])->name('admin.cash-counts.products')->middleware(['auth', 'can:cash-counts.show']);

// Permissions
Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index')->middleware(['auth', 'can:permissions.index']);
Route::get('/permissions/create', [PermissionController::class, 'create'])->name('admin.permissions.create')->middleware(['auth', 'can:permissions.create']);
Route::post('/permissions/create', [PermissionController::class, 'store'])->name('admin.permissions.store')->middleware(['auth', 'can:permissions.create']);
Route::get('/permissions/edit/{id}', [PermissionController::class, 'edit'])->name('admin.permissions.edit')->middleware(['auth', 'can:permissions.edit']);
Route::put('/permissions/edit/{id}', [PermissionController::class, 'update'])->name('admin.permissions.update')->middleware(['auth', 'can:permissions.edit']);
Route::delete('/permissions/delete/{id}', [PermissionController::class, 'destroy'])->name('admin.permissions.destroy')->middleware(['auth', 'can:permissions.destroy']);
Route::get('/permissions/{id}', [PermissionController::class, 'show'])->name('admin.permissions.show')->middleware(['auth', 'can:permissions.show']);

// Rutas para manejo de pagos de deuda
Route::prefix('admin/debt-payments')->middleware(['auth'])->group(function () {
    Route::delete('/{id}', [DebtPaymentController::class, 'destroy'])->name('admin.debt-payments.destroy');
    Route::get('/sale/{saleId}', [DebtPaymentController::class, 'getPaymentsBySale'])->name('admin.debt-payments.by-sale');
    Route::delete('/sale/{saleId}/all', [DebtPaymentController::class, 'deletePaymentsBySale'])->name('admin.debt-payments.delete-by-sale');
});

Route::get('/admin/order-catalog-settings', function () {
    $user = auth()->user();
    $svc = app(PlanEntitlementService::class);
    if ($svc->tenantUserMayBrowseCatalogPayments($user)) {
        return redirect()->route('admin.catalog-payment-methods.index');
    }
    if ($svc->tenantUserMayBrowseCatalogDeliveries($user)) {
        return redirect()->route('admin.catalog-delivery-methods.index');
    }
    abort(403);
})->middleware(['auth'])->name('admin.order-catalog-settings.index');

Route::view('/admin/catalog-payment-methods', 'admin.catalog-payment-methods.index')
    ->name('admin.catalog-payment-methods.index')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_payment_methods']);

Route::get('/admin/catalog-payment-methods/create', [CatalogPaymentMethodsController::class, 'create'])
    ->name('admin.catalog-payment-methods.create')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_payment_methods']);

Route::get('/admin/catalog-payment-methods/{id}/edit', [CatalogPaymentMethodsController::class, 'edit'])
    ->whereNumber('id')
    ->name('admin.catalog-payment-methods.edit')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_payment_methods']);

Route::get('/admin/catalog-payment-methods/report', [CatalogPaymentMethodsController::class, 'report'])
    ->name('admin.catalog-payment-methods.report')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_payment_methods']);

Route::view('/admin/catalog-delivery-methods', 'admin.catalog-delivery-methods.index')
    ->name('admin.catalog-delivery-methods.index')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_delivery_methods']);

Route::get('/admin/catalog-delivery-methods/create', [CatalogDeliveryMethodsController::class, 'create'])
    ->name('admin.catalog-delivery-methods.create')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_delivery_methods']);

Route::get('/admin/catalog-delivery-methods/{id}/edit', [CatalogDeliveryMethodsController::class, 'edit'])
    ->whereNumber('id')
    ->name('admin.catalog-delivery-methods.edit')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_delivery_methods']);

Route::get('/admin/catalog-delivery-methods/report', [CatalogDeliveryMethodsController::class, 'report'])
    ->name('admin.catalog-delivery-methods.report')
    ->middleware(['auth', 'tenant.catalog-checkout:catalog_delivery_methods']);

// Rutas para manejo de pedidos (Admin)
Route::prefix('admin/orders')->middleware(['auth', 'tenant.orders:browse'])->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::get('/{order}/pdf', [OrderController::class, 'pdf'])->middleware('can:orders.update')->name('admin.orders.pdf');
    Route::post('/{order}/paid', [OrderController::class, 'markPaid'])->middleware('can:orders.update')->name('admin.orders.paid');
    Route::post('/{order}/delivered', [OrderController::class, 'markDelivered'])->middleware('can:orders.update')->name('admin.orders.delivered');
    Route::post('/{order}/regenerate-summary', [OrderController::class, 'regenerateSummary'])->middleware('can:orders.update')->name('admin.orders.regenerate-summary');
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->middleware('can:orders.cancel')->name('admin.orders.cancel');
});

// Rutas para notificaciones (Admin)
Route::prefix('admin/notifications')->middleware(['auth'])->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('admin.notifications.unread-count');
    Route::get('/recent', [NotificationController::class, 'getRecentNotifications'])->name('admin.notifications.recent');
    Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
});

// Rutas de Debugbar (solo si el paquete está instalado; compatible con Laravel 13 sin barryvdh/laravel-debugbar)
if (
    class_exists(AssetController::class)
    && config('app.debug')
    && config('debugbar.enabled')
) {
    Route::get('_debugbar/assets/stylesheets', [
        'as' => 'debugbar.assets.css',
        'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@css',
    ]);

    Route::get('_debugbar/assets/javascript', [
        'as' => 'debugbar.assets.js',
        'uses' => '\Barryvdh\Debugbar\Controllers\AssetController@js',
    ]);

    Route::get('_debugbar/open', [
        'as' => 'debugbar.open',
        'uses' => '\Barryvdh\Debugbar\Controllers\OpenHandlerController@handle',
    ]);
}

Route::prefix('super-admin')
    ->middleware(['auth', 'superadmin'])
    ->name('super-admin.')
    ->group(function () {
        Route::get('/', fn () => view('super-admin.dashboard'))->name('dashboard');
        Route::get('/companies', fn () => view('super-admin.companies.index'))->name('companies.index');
        Route::get('/companies/create', fn () => view('super-admin.companies.create'))->name('companies.create');
        Route::get('/companies/{id}', fn ($id) => view('super-admin.companies.show', ['companyId' => (int) $id]))->name('companies.show');
        Route::get('/plans', fn () => view('super-admin.plans.index'))->name('plans.index');
        Route::get('/payments', fn () => view('super-admin.payments.index'))->name('payments.index');
    });

// =========================================================================
// Resumen público de pedido (token)
// =========================================================================
Route::get('/resumen/{token}', [OrderSummaryController::class, 'show'])->name('order.summary.show');
Route::get('/resumen/{token}/pdf', [OrderSummaryController::class, 'pdf'])->name('order.summary.pdf');
Route::post('/resumen/{token}/cancelar', [OrderSummaryController::class, 'cancel'])
    ->middleware('throttle:10,1')
    ->name('order.summary.cancel');

// =========================================================================
// MÓDULO HOGAR
// =========================================================================
Route::prefix('home')->middleware(['auth', 'home.enabled'])->name('admin.home.')->group(function () {
    Route::get('/', fn () => view('admin.v2.home.index'))->name('index')
        ->middleware('can:home.inventory.index');

    // Inventory
    Route::get('/inventory', fn () => view('admin.v2.home.inventory.index'))->name('inventory.index')
        ->middleware('can:home.inventory.index');
    Route::get('/inventory/create', fn () => view('admin.v2.home.inventory.create'))->name('inventory.create')
        ->middleware('can:home.inventory.create');
    Route::get('/inventory/{id}/edit', fn ($id) => view('admin.v2.home.inventory.edit', ['productId' => (int) $id]))->name('inventory.edit')
        ->middleware('can:home.inventory.edit');

    // Shopping List
    Route::get('/shopping-list', fn () => view('admin.v2.home.shopping-list.index'))->name('shopping-list.index')
        ->middleware('can:home.shopping_list.index');
    Route::get('/shopping-list/mobile', fn () => view('admin.v2.home.shopping-list.mobile'))->name('shopping-list.mobile')
        ->middleware('can:home.shopping_list.index');

    // Finances
    Route::get('/finances', fn () => view('admin.v2.home.finances.dashboard'))->name('finances.dashboard')
        ->middleware('can:home.finances.index');
    Route::get('/finances/services', fn () => view('admin.v2.home.finances.services'))->name('finances.services')
        ->middleware('can:home.finances.services');
    Route::get('/finances/bills', fn () => view('admin.v2.home.finances.bills'))->name('finances.bills')
        ->middleware('can:home.finances.bills');
    Route::get('/finances/transactions', fn () => view('admin.v2.home.finances.transactions'))->name('finances.transactions')
        ->middleware('can:home.finances.transactions');
    Route::get('/finances/accounts', fn () => view('admin.v2.home.finances.accounts'))->name('finances.accounts')
        ->middleware('can:home.finances.accounts');

    // Scan
    Route::get('/scan', fn () => view('admin.v2.home.scan.index'))->name('scan.index')
        ->middleware('can:home.scan_deduct');
});

// =========================================================================
// CATÁLOGO PÚBLICO — fallback routes (MUST be last in file)
// =========================================================================
Route::middleware('throttle:120,1')->group(function () {
    Route::get('/{company:slug}/catalog-api/cart', [PublicCatalogCartController::class, 'show'])->name('catalog.cart.show');
    Route::post('/{company:slug}/catalog-api/cart/sync', [PublicCatalogCartController::class, 'sync'])->name('catalog.cart.sync');
    Route::delete('/{company:slug}/catalog-api/cart/{product}', [PublicCatalogCartController::class, 'remove'])->name('catalog.cart.remove');
});

Route::get('/{company:slug}/checkout', [CatalogCheckoutPageController::class, 'show'])->name('catalog.checkout');

Route::get('/{company:slug}/og-logo', [PublicCatalogController::class, 'ogLogo'])
    ->name('catalog.og-logo');
Route::get('/{company:slug}', [PublicCatalogController::class, 'index'])
    ->name('catalog.index');
Route::get('/{company:slug}/producto/{product}', [PublicCatalogController::class, 'show'])
    ->name('catalog.product');
