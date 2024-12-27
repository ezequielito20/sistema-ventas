<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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