<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/connexion',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/connexion', [AuthenticatedSessionController::class, 'store']);
    Route::get('/mot-de-passe-oublie',      [PasswordResetLinkController::class,  'create'])->name('password.request');
    Route::post('/mot-de-passe-oublie',     [PasswordResetLinkController::class,  'store'])->name('password.email');
    Route::get('/reinitialiser/{token}',    [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reinitialiser',           [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->post('/deconnexion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
