<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/create-company', [App\Http\Controllers\CompanyController::class, 'create'])->name('admin.company.create');
Route::post('/create-company', [App\Http\Controllers\CompanyController::class, 'store'])->name('admin.company.store');