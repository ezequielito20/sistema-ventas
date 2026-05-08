<?php

use Illuminate\Support\Facades\Route;
use MiEmpresa\Core\Http\Controllers\Auth\LoginController;
use MiEmpresa\Core\Http\Controllers\Auth\PasswordRecoveryController;
use MiEmpresa\Core\Http\Controllers\Auth\SecurityQuestionsController;

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Password recovery (security questions)
Route::get('/password/recovery', [PasswordRecoveryController::class, 'showRecoveryForm'])
    ->name('password.recovery')->middleware('guest');
Route::post('/password/recovery', [PasswordRecoveryController::class, 'findUser'])
    ->name('password.recovery.find')->middleware('guest');
Route::get('/password/recovery/questions', [PasswordRecoveryController::class, 'showQuestions'])
    ->name('password.recovery.questions')->middleware('guest');
Route::post('/password/recovery/questions', [PasswordRecoveryController::class, 'verifyQuestions'])
    ->name('password.recovery.verify')->middleware('guest');
Route::get('/password/recovery/reset', [PasswordRecoveryController::class, 'showResetForm'])
    ->name('password.recovery.reset')->middleware('guest');
Route::post('/password/recovery/reset', [PasswordRecoveryController::class, 'resetPassword'])
    ->name('password.recovery.update')->middleware('guest');

// Security questions setup
Route::get('/security-questions/setup', [SecurityQuestionsController::class, 'setup'])
    ->name('security-questions.setup')->middleware('auth');
Route::post('/security-questions/setup', [SecurityQuestionsController::class, 'store'])
    ->name('security-questions.store')->middleware('auth');
