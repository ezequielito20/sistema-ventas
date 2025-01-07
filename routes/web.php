<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/', [AdminController::class, 'index'])->name('admin.index')->middleware('auth');

Route::get('/create-company', [CompanyController::class, 'create'])->name('admin.company.create');
Route::post('/create-company/create', [CompanyController::class, 'store'])->name('admin.company.store');
Route::get('/create-company/{country}', [CompanyController::class, 'search_country'])->name('admin.company.search_country');
Route::get('/search-state/{state}', [CompanyController::class, 'search_state'])->name('admin.company.search_state');

Route::get('/settings', [CompanyController::class, 'edit'])->name('admin.company.edit');
Route::put('/settings/{id}', [CompanyController::class, 'update'])->name('admin.companies.update')->middleware('auth');


// Roles
Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index')->middleware('auth');
Route::get('/roles/create', [RoleController::class, 'create'])->name('admin.roles.create')->middleware('auth');
Route::post('/roles/create', [RoleController::class, 'store'])->name('admin.roles.store')->middleware('auth');
Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])->name('admin.roles.edit')->middleware('auth');
Route::put('/roles/edit/{id}', [RoleController::class, 'update'])->name('admin.roles.update')->middleware('auth');
Route::delete('/roles/delete/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy')->middleware('auth');
Route::get('/roles/{id}', [RoleController::class, 'show'])->name('admin.roles.show')->middleware('auth');

// Users
Route::get('/users', [UserController::class, 'index'])->name('admin.users.index')->middleware('auth');
Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create')->middleware('auth');
Route::post('/users/create', [UserController::class, 'store'])->name('admin.users.store')->middleware('auth');
Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit')->middleware('auth');
Route::put('/users/edit/{id}', [UserController::class, 'update'])->name('admin.users.update')->middleware('auth');
Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy')->middleware('auth');
Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show')->middleware('auth');

// Categories
Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index')->middleware('auth');
Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create')->middleware('auth');
Route::post('/categories/create', [CategoryController::class, 'store'])->name('admin.categories.store')->middleware('auth');
Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit')->middleware('auth');
Route::put('/categories/edit/{id}', [CategoryController::class, 'update'])->name('admin.categories.update')->middleware('auth');
Route::delete('/categories/delete/{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy')->middleware('auth');
Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('admin.categories.show')->middleware('auth');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index')->middleware('auth');
Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create')->middleware('auth');
Route::post('/products/create', [ProductController::class, 'store'])->name('admin.products.store')->middleware('auth');
Route::get('/products/edit/{id}', [ProductController::class, 'edit'])->name('admin.products.edit')->middleware('auth');
Route::put('/products/edit/{id}', [ProductController::class, 'update'])->name('admin.products.update')->middleware('auth');
Route::delete('/products/delete/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy')->middleware('auth');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('admin.products.show')->middleware('auth');

// Suppliers
Route::get('/suppliers', [SupplierController::class, 'index'])->name('admin.suppliers.index')->middleware('auth');
Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('admin.suppliers.create')->middleware('auth');
Route::post('/suppliers/create', [SupplierController::class, 'store'])->name('admin.suppliers.store')->middleware('auth');
Route::get('/suppliers/edit/{id}', [SupplierController::class, 'edit'])->name('admin.suppliers.edit')->middleware('auth');
Route::put('/suppliers/edit/{id}', [SupplierController::class, 'update'])->name('admin.suppliers.update')->middleware('auth');
Route::delete('/suppliers/delete/{id}', [SupplierController::class, 'destroy'])->name('admin.suppliers.destroy')->middleware('auth');
Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->name('admin.suppliers.show')->middleware('auth');

// Purchases
Route::get('/purchases', [PurchaseController::class, 'index'])->name('admin.purchases.index')->middleware('auth');
Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('admin.purchases.create')->middleware('auth');
Route::post('/purchases/create', [PurchaseController::class, 'store'])->name('admin.purchases.store')->middleware('auth');
Route::get('/purchases/edit/{id}', [PurchaseController::class, 'edit'])->name('admin.purchases.edit')->middleware('auth');
Route::put('/purchases/edit/{id}', [PurchaseController::class, 'update'])->name('admin.purchases.update')->middleware('auth');
Route::delete('/purchases/delete/{id}', [PurchaseController::class, 'destroy'])->name('admin.purchases.destroy')->middleware('auth');
Route::get('/purchases/product-details/{code}', [PurchaseController::class, 'getProductDetails'])
    ->name('admin.purchases.product-details')
    ->middleware('auth');
Route::get('/purchases/product-by-code/{code}', [PurchaseController::class, 'getProductByCode'])
    ->name('admin.purchases.product-by-code')
    ->middleware('auth');
Route::get('/purchases/{id}', [PurchaseController::class, 'show'])->name('admin.purchases.show')->middleware('auth');
