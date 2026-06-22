<?php

use App\Http\Controllers\AffectationController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentImportExportController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\IndiceImportController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferentielController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/* ─────────────────────────────────────────────────────
 *  AUTHENTIFICATION (Breeze)
 * ──────────────────────────────────────────────────── */
require __DIR__ . '/auth.php';

/* ─────────────────────────────────────────────────────
 *  ROUTES PROTÉGÉES — utilisateur authentifié et actif
 * ──────────────────────────────────────────────────── */
Route::middleware(['auth', 'verified'])->group(function () {

    /* Racine → tableau de bord */
    Route::get('/', fn () => redirect()->route('dashboard'));

    /* Tableau de bord */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* Profil */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /* Agents */
    Route::prefix('agents')->name('agents.')->group(function () {
        Route::get('/',           [AgentController::class, 'index'])->name('index');
        Route::get('/creer',      [AgentController::class, 'create'])->name('create');
        Route::post('/',          [AgentController::class, 'store'])->name('store');
        Route::get('/{agent}',    [AgentController::class, 'show'])->name('show');
        Route::get('/{agent}/modifier', [AgentController::class, 'edit'])->name('edit');
        Route::put('/{agent}',    [AgentController::class, 'update'])->name('update');
        Route::delete('/{agent}', [AgentController::class, 'destroy'])->name('destroy');

        /* Import / Export */
        Route::get('/import',     [AgentImportExportController::class, 'form'])->name('import.form');
        Route::post('/import',    [AgentImportExportController::class, 'import'])->name('import');
        Route::get('/export',     [AgentImportExportController::class, 'export'])->name('export');

        /* Documents par agent */
        Route::prefix('/{agent}/documents')->name('documents.')->group(function () {
            Route::get('/',   [DocumentController::class, 'index'])->name('index');
            Route::post('/',  [DocumentController::class, 'store'])->name('store');
        });
    });

    /* Documents (téléchargement / suppression) */
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/{document}/telecharger', [DocumentController::class, 'download'])->name('download');
        Route::delete('/{document}',          [DocumentController::class, 'destroy'])->name('destroy');
    });

    /* Structures */
    Route::prefix('structures')->name('structures.')->group(function () {
        Route::get('/',                   [StructureController::class, 'index'])->name('index');
        Route::get('/creer',              [StructureController::class, 'create'])->name('create');
        Route::post('/',                  [StructureController::class, 'store'])->name('store');
        Route::get('/{structure}',        [StructureController::class, 'show'])->name('show');
        Route::get('/{structure}/modifier', [StructureController::class, 'edit'])->name('edit');
        Route::put('/{structure}',        [StructureController::class, 'update'])->name('update');
        Route::delete('/{structure}',     [StructureController::class, 'destroy'])->name('destroy');
    });

    /* Affectations */
    Route::prefix('affectations')->name('affectations.')->group(function () {
        Route::get('/',                     [AffectationController::class, 'index'])->name('index');
        Route::get('/creer',                [AffectationController::class, 'create'])->name('create');
        Route::post('/',                    [AffectationController::class, 'store'])->name('store');
        Route::get('/{affectation}',        [AffectationController::class, 'show'])->name('show');
    });

    /* Référentiels (CRUD générique) */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');

        /* Import des indices (catégorie × classe × échelon) — avant la route générique {type} */
        Route::get('/indices/modele', [IndiceImportController::class, 'template'])->name('indices.template');
        Route::get('/indices/import', [IndiceImportController::class, 'form'])->name('indices.import.form');
        Route::post('/indices/import', [IndiceImportController::class, 'import'])->name('indices.import');

        Route::get('/{type}', [ReferentielController::class, 'show'])->name('show');
        Route::post('/{type}', [ReferentielController::class, 'store'])->name('store');
        Route::put('/{type}/{id}', [ReferentielController::class, 'update'])->name('update');
        Route::delete('/{type}/{id}', [ReferentielController::class, 'destroy'])->name('destroy');
    });

    /* Utilisateurs */
    Route::prefix('utilisateurs')->name('users.')->group(function () {
        Route::get('/',                   [UserController::class, 'index'])->name('index');
        Route::get('/creer',              [UserController::class, 'create'])->name('create');
        Route::post('/',                  [UserController::class, 'store'])->name('store');
        Route::get('/{user}/modifier',    [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',             [UserController::class, 'update'])->name('update');
        Route::delete('/{user}',          [UserController::class, 'destroy'])->name('destroy');
    });

    /* Rôles & permissions */
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/',              [RoleController::class, 'index'])->name('index');
        Route::get('/{role}/droits', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}/droits', [RoleController::class, 'update'])->name('update');
    });

    /* Audit */
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});
