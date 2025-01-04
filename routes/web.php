<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

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
Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show')->middleware('auth');
