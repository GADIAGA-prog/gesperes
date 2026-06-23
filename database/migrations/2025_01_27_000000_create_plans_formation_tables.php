<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Plan de formation (planification pluriannuelle), modélisé d'après le
 * « Plan Triennal de Formation du MFPTPS » :
 *
 *   plans_formation       → plan pluriannuel (vision, finalité, objectifs)
 *     └ programmes_formation → déclinaison annuelle (objectif stratégique, budget)
 *         └ actions_formation  → actions planifiées (synthèse 1→7 du plan)
 *   besoins_formation     → recueil des besoins par agent (Annexe 1 de la fiche)
 *
 * La réalisation (sessions) reste portée par la table `formations` existante,
 * rattachée à une action via `action_formation_id` (migration suivante).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans_formation', function (Blueprint $table) {
            $table->id();
            $table->string('intitule');
            $table->year('annee_debut');
            $table->year('annee_fin');
            $table->text('vision')->nullable();
            $table->text('finalite')->nullable();
            $table->text('objectifs')->nullable();
            $table->string('statut')->default('brouillon'); // App\Enums\StatutPlanFormation
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index(['annee_debut', 'annee_fin']);
        });

        Schema::create('programmes_formation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_formation_id')->constrained('plans_formation')->cascadeOnDelete();
            $table->year('annee');
            $table->string('objectif_strategique')->nullable();
            $table->decimal('budget_previsionnel', 14, 2)->default(0);
            $table->string('statut')->default('brouillon'); // App\Enums\StatutPlanFormation
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['plan_formation_id', 'annee']);
        });

        Schema::create('actions_formation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_formation_id')->constrained('programmes_formation')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero_ordre')->default(0);
            $table->string('action');                          // « Actions de formation »
            $table->string('theme_module')->nullable();        // Thèmes/modules (1)
            $table->string('type_modalite')->nullable();       // (2) intra/extra/délocalisé/extra délocalisé
            $table->string('domaine')->nullable();             // Schéma 3 : domaine de formation
            $table->string('axe')->nullable();                 // Schéma 3 : axe de formation
            $table->string('strategie')->nullable();           // Tableau 9 : stratégie de mise en œuvre
            $table->string('niveau_competence')->nullable();   // Schéma 3 : analyse/transfert/innovation
            $table->json('public_cible')->nullable();          // (3) multi-sélection
            $table->unsignedSmallInteger('nombre_jours')->default(0);   // (4)
            $table->unsignedInteger('nombre_agents')->default(0);       // (5)
            $table->decimal('cout', 14, 2)->default(0);                 // (6)
            $table->string('source_financement')->nullable();          // (7) PMAP/DAF, PTF…
            $table->string('statut')->default('planifiee');    // App\Enums\StatutActionFormation
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index(['programme_formation_id', 'numero_ordre']);
        });

        Schema::create('besoins_formation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->year('annee_recueil');
            $table->string('theme_souhaite');                  // besoin exprimé
            $table->text('activite')->nullable();              // activité exécutée avec difficulté
            $table->text('taches')->nullable();
            $table->text('difficultes')->nullable();
            $table->string('cause')->nullable();               // App\Enums\CauseDifficulte
            $table->string('solution')->nullable();            // App\Enums\SolutionBesoin
            $table->string('niveau_maitrise')->nullable();     // App\Enums\NiveauMaitrise
            $table->string('frequence')->nullable();           // App\Enums\FrequenceTache
            $table->string('domaine')->nullable();             // rattachement domaine de formation
            $table->string('statut')->default('exprime');      // exprime | retenu | rejete | planifie
            $table->foreignId('action_formation_id')->nullable()->constrained('actions_formation')->nullOnDelete();
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['annee_recueil', 'statut']);
            $table->index('structure_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('besoins_formation');
        Schema::dropIfExists('actions_formation');
        Schema::dropIfExists('programmes_formation');
        Schema::dropIfExists('plans_formation');
    }
};
