<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\EspaceAgent\AgentSessionController;
use App\Http\Controllers\EspaceAgent\InscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/connexion',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/connexion', [AuthenticatedSessionController::class, 'store']);
    Route::get('/mot-de-passe-oublie',      [PasswordResetLinkController::class,  'create'])->name('password.request');
    Route::post('/mot-de-passe-oublie',     [PasswordResetLinkController::class,  'store'])->name('password.email');
    Route::get('/reinitialiser/{token}',    [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reinitialiser',           [NewPasswordController::class, 'store'])->name('password.store');

    /* Espace agent — connexion par matricule + inscription self-service (3 facteurs) */
    Route::prefix('espace-agent')->name('espace-agent.')->group(function () {
        Route::get('/connexion',  [AgentSessionController::class, 'create'])->name('connexion');
        Route::post('/connexion', [AgentSessionController::class, 'store'])->name('connexion.store');

        Route::get('/inscription',  [InscriptionController::class, 'create'])->name('inscription');
        Route::post('/inscription', [InscriptionController::class, 'store'])->name('inscription.store');
    });
});

Route::middleware('auth')->post('/deconnexion', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
