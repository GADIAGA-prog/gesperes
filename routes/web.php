<?php

use App\Http\Controllers\AffectationController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentImportExportController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\EnveloppePersonnelController;
use App\Http\Controllers\CarriereController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\GpecController;
use App\Http\Controllers\OutilsGrhController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndemniteController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FichePresenceController;
use App\Http\Controllers\IndiceImportController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PointageController;
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
    Route::get('/manuel', [\App\Http\Controllers\ManuelController::class, 'index'])->name('manuel.index');

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

        /* Import / Export — routes statiques placées AVANT /{agent} pour ne pas être masquées */
        Route::get('/import',     [AgentImportExportController::class, 'form'])->name('import.form');
        Route::post('/import',    [AgentImportExportController::class, 'import'])->name('import');
        Route::get('/export',     [AgentImportExportController::class, 'export'])->name('export');
        Route::get('/export/pdf', [AgentImportExportController::class, 'exportPdfListe'])->name('export.pdf');

        Route::get('/{agent}',    [AgentController::class, 'show'])->name('show');
        Route::get('/{agent}/fiche-pdf', [AgentImportExportController::class, 'exportPdfFiche'])->name('pdf');
        Route::get('/{agent}/modifier', [AgentController::class, 'edit'])->name('edit');
        Route::put('/{agent}',    [AgentController::class, 'update'])->name('update');
        Route::delete('/{agent}', [AgentController::class, 'destroy'])->name('destroy');

        /* Documents par agent (dossier individuel) */
        Route::prefix('/{agent}/documents')->name('documents.')->group(function () {
            Route::get('/',       [DocumentController::class, 'index'])->name('index');
            Route::get('/export', [DocumentController::class, 'exportZip'])->name('export');
            Route::post('/',      [DocumentController::class, 'store'])->name('store');
        });

        /* Indemnités d'un agent */
        Route::prefix('/{agent}/indemnites')->name('indemnites.')->group(function () {
            Route::get('/',         [IndemniteController::class, 'agent'])->name('agent');
            Route::get('/bulletin', [IndemniteController::class, 'bulletin'])->name('bulletin');
            Route::post('/',        [IndemniteController::class, 'attribuer'])->name('attribuer');
            Route::post('/figer',   [IndemniteController::class, 'figer'])->name('figer');
        });

        /* Compétences d'un agent */
        Route::prefix('/{agent}/competences')->name('competences.')->group(function () {
            Route::get('/',             [CompetenceController::class, 'agent'])->name('agent');
            Route::post('/',            [CompetenceController::class, 'attribuer'])->name('attribuer');
            Route::delete('/{competence}', [CompetenceController::class, 'retirer'])->name('retirer');
        });
    });

    /* Documents — recherche globale, téléchargement, archivage, suppression */
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/',                       [DocumentController::class, 'recherche'])->name('recherche');
        Route::get('/{document}/telecharger', [DocumentController::class, 'download'])->name('download');
        Route::post('/{document}/archiver',   [DocumentController::class, 'archiver'])->name('archiver');
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

    /* Carrière — avancements, promotions, nominations */
    Route::prefix('carriere')->name('carriere.')->group(function () {
        Route::get('/',      [CarriereController::class, 'index'])->name('index');
        Route::get('/creer', [CarriereController::class, 'create'])->name('create');
        Route::post('/',     [CarriereController::class, 'store'])->name('store');
    });

    /* Mouvements du personnel — sorties temporaires / définitives, réintégration */
    Route::prefix('mouvements')->name('mouvements.')->group(function () {
        Route::get('/',      [MouvementController::class, 'index'])->name('index');
        Route::get('/sorties-temporaires', [MouvementController::class, 'sortiesTemporaires'])->name('sorties-temporaires');
        Route::get('/sorties-definitives', [MouvementController::class, 'sortiesDefinitives'])->name('sorties-definitives');
        Route::get('/creer', [MouvementController::class, 'create'])->name('create');
        Route::post('/',     [MouvementController::class, 'store'])->name('store');
    });

    /* Formation — sessions et bénéficiaires */
    /* Présence — pointage journalier (Fiche A) */
    Route::prefix('pointages')->name('pointages.')->group(function () {
        Route::get('/',  [PointageController::class, 'index'])->name('index');
        Route::post('/', [PointageController::class, 'store'])->name('store');
    });

    /* Budget des structures (activités + budget AE/CP) */
    Route::prefix('budget')->name('budget.')->group(function () {
        Route::get('/',            [BudgetController::class, 'index'])->name('index');
        Route::get('/creer',       [BudgetController::class, 'create'])->name('create');
        Route::post('/',           [BudgetController::class, 'store'])->name('store');
        Route::get('/import',      [BudgetController::class, 'importForm'])->name('import.form');
        Route::post('/import',     [BudgetController::class, 'import'])->name('import');
        Route::get('/par',         [BudgetController::class, 'parPdf'])->name('par');
        // Sous-modules budget (déclarés avant /{activite} pour ne pas être capturés)
        Route::get('/personnel',   [BudgetController::class, 'personnel'])->name('personnel');
        Route::get('/annexes',         [BudgetController::class, 'annexes'])->name('annexes');
        Route::get('/annexes/excel',   [BudgetController::class, 'annexesExcel'])->name('annexes.excel');
        Route::get('/annexes/pdf',     [BudgetController::class, 'annexesPdf'])->name('annexes.pdf');
        Route::get('/enveloppe',              [EnveloppePersonnelController::class, 'index'])->name('enveloppe.index');
        Route::post('/enveloppe',             [EnveloppePersonnelController::class, 'store'])->name('enveloppe.store');
        Route::get('/enveloppe/{enveloppe}',  [EnveloppePersonnelController::class, 'show'])->name('enveloppe.show');
        Route::get('/enveloppe/{enveloppe}/ventilation', [EnveloppePersonnelController::class, 'ventilation'])->name('enveloppe.ventilation');
        Route::put('/enveloppe/{enveloppe}',  [EnveloppePersonnelController::class, 'update'])->name('enveloppe.update');
        Route::delete('/enveloppe/{enveloppe}', [EnveloppePersonnelController::class, 'destroy'])->name('enveloppe.destroy');
        Route::get('/{activite}',  [BudgetController::class, 'show'])->name('show');
        Route::get('/{activite}/modifier', [BudgetController::class, 'edit'])->name('edit');
        Route::put('/{activite}',  [BudgetController::class, 'update'])->name('update');
        Route::delete('/{activite}', [BudgetController::class, 'destroy'])->name('destroy');
        Route::post('/{activite}/lignes', [BudgetController::class, 'storeLigne'])->name('lignes.store');
        Route::delete('/{activite}/lignes/{ligne}', [BudgetController::class, 'destroyLigne'])->name('lignes.destroy');
    });

    /* Fiches officielles de présence (A / B / C) — PDF & Excel */
    Route::prefix('fiches')->name('fiches.')->group(function () {
        Route::get('/',       [FichePresenceController::class, 'index'])->name('index');
        Route::get('/a',      [FichePresenceController::class, 'ficheA'])->name('a');
        Route::get('/b',      [FichePresenceController::class, 'ficheB'])->name('b');
        Route::get('/c',      [FichePresenceController::class, 'ficheC'])->name('c');
    });

    /* Congés & autorisations d'absence */
    Route::prefix('conges')->name('conges.')->group(function () {
        Route::get('/',        [CongeController::class, 'index'])->name('index');
        Route::get('/creer',   [CongeController::class, 'create'])->name('create');
        Route::post('/',       [CongeController::class, 'store'])->name('store');
        Route::post('/{conge}/valider', [CongeController::class, 'valider'])->name('valider');
        Route::post('/{conge}/refuser', [CongeController::class, 'refuser'])->name('refuser');
        Route::post('/{conge}/annuler', [CongeController::class, 'annuler'])->name('annuler');
    });

    /* Référentiels (CRUD générique) */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');

        /* Import des indices (catégorie × classe × échelon) — avant la route générique {type} */
        Route::get('/indices/modele', [IndiceImportController::class, 'template'])->name('indices.template');
        Route::get('/indices/import', [IndiceImportController::class, 'form'])->name('indices.import.form');
        Route::post('/indices/import', [IndiceImportController::class, 'import'])->name('indices.import');

        /* Export / Import Excel d'un référentiel (format adapté à chaque type) */
        Route::get('/{type}/export', [ReferentielController::class, 'export'])->name('export');
        Route::get('/{type}/modele', [ReferentielController::class, 'modele'])->name('modele');
        Route::get('/{type}/import', [ReferentielController::class, 'importForm'])->name('import.form');
        Route::post('/{type}/import', [ReferentielController::class, 'import'])->name('import');

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

    /* Discipline — demandes d'explication, sanctions, recours */
    Route::prefix('discipline')->name('discipline.')->group(function () {
        Route::get('/',                     [DisciplineController::class, 'index'])->name('index');
        Route::get('/creer',                [DisciplineController::class, 'create'])->name('create');
        Route::post('/',                    [DisciplineController::class, 'store'])->name('store');
        Route::get('/{discipline}/modifier', [DisciplineController::class, 'edit'])->name('edit');
        Route::put('/{discipline}',         [DisciplineController::class, 'update'])->name('update');
        Route::delete('/{discipline}',      [DisciplineController::class, 'destroy'])->name('destroy');
    });

    /* Compétences — référentiel */
    Route::prefix('competences')->name('competences.')->group(function () {
        Route::get('/',              [CompetenceController::class, 'index'])->name('index');
        Route::post('/',             [CompetenceController::class, 'store'])->name('store');
        Route::put('/{competence}',  [CompetenceController::class, 'update'])->name('update');
        Route::delete('/{competence}', [CompetenceController::class, 'destroy'])->name('destroy');
    });

    /* Performance — évaluations annuelles */
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/',                      [EvaluationController::class, 'index'])->name('index');
        Route::get('/creer',                 [EvaluationController::class, 'create'])->name('create');
        Route::post('/',                     [EvaluationController::class, 'store'])->name('store');
        Route::get('/{performance}/modifier', [EvaluationController::class, 'edit'])->name('edit');
        Route::put('/{performance}',         [EvaluationController::class, 'update'])->name('update');
        Route::delete('/{performance}',      [EvaluationController::class, 'destroy'])->name('destroy');
    });

    /* Outils GRH — GPEC + fiches de poste, TPEE, référentiels MPP, plan de formation */
    Route::get('/gpec', [GpecController::class, 'index'])->name('gpec.index');
    Route::prefix('outils-grh')->name('outils-grh.')->group(function () {
        Route::get('/fiches-poste',     [OutilsGrhController::class, 'fichesPoste'])->name('fiches-poste');
        Route::get('/tpee',             [OutilsGrhController::class, 'tpee'])->name('tpee');
        Route::get('/referentiels-mpp', [OutilsGrhController::class, 'referentielsMpp'])->name('referentiels-mpp');
        Route::get('/plan-formation',   [OutilsGrhController::class, 'planFormation'])->name('plan-formation');
    });

    /* Indemnités — référentiel paramétrable (décret 2014-427) */
    Route::prefix('indemnites')->name('indemnites.')->group(function () {
        Route::get('/',            [IndemniteController::class, 'index'])->name('index');
        Route::post('/',           [IndemniteController::class, 'store'])->name('store');
        Route::delete('/attributions/{attribution}', [IndemniteController::class, 'retirer'])->name('attributions.destroy');
        Route::put('/{indemnite}', [IndemniteController::class, 'update'])->name('update');
        Route::delete('/{indemnite}', [IndemniteController::class, 'destroy'])->name('destroy');
    });

    /* Alertes RH — retraites proches, documents expirés + notifications persistantes */
    Route::prefix('alertes')->name('alertes.')->group(function () {
        Route::get('/',               [AlerteController::class, 'index'])->name('index');
        Route::post('/generer',       [AlerteController::class, 'generer'])->name('generer');
        Route::post('/tout-lu',       [AlerteController::class, 'marquerToutLu'])->name('tout-lu');
        Route::post('/{notification}/lu', [AlerteController::class, 'marquerLu'])->name('lu');
    });

    /* Audit */
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});
