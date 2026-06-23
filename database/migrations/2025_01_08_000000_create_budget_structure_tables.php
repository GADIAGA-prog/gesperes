<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module BUDGET STRUCTURE.
 *
 * L'ACTIVITÉ est le pivot : rattachée à une action (→ programme) et exécutée par
 * une structure (chapitre). Elle porte son programme d'activité annuel (indicateur,
 * cible, localité, montant, ventilation trimestrielle) et ses lignes budgétaires
 * (article/paragraphe → AE/CP).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activites', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('exercice');                 // NO_EX (2026)
            $table->string('code');                                   // CD_ACTIVITE (1040301)
            $table->string('libelle');                                // LIB_ACTIVITE
            $table->foreignId('action_id')->nullable()->constrained('actions')->nullOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();

            // Chapitre = structure exécutante (référence budgétaire)
            $table->string('code_chapitre')->nullable();              // CD_CHAPITRE (1009000311)
            $table->string('libelle_chapitre')->nullable();           // LIB_CHAP (DRH)

            // Programme d'activité annuel
            $table->text('objectif_strategique')->nullable();
            $table->text('objectif_operationnel')->nullable();
            $table->text('indicateur')->nullable();
            $table->string('valeur_initiale')->nullable();
            $table->string('cible')->nullable();
            $table->string('localite')->nullable();
            $table->decimal('montant', 15, 2)->default(0);            // montant planifié
            $table->decimal('trimestre_1', 8, 4)->default(0);
            $table->decimal('trimestre_2', 8, 4)->default(0);
            $table->decimal('trimestre_3', 8, 4)->default(0);
            $table->decimal('trimestre_4', 8, 4)->default(0);

            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['exercice', 'code']);
            $table->index('structure_id');
        });

        Schema::create('budget_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_id')->constrained('activites')->cascadeOnDelete();
            $table->unsignedSmallInteger('exercice');
            $table->string('code_article')->nullable();               // CD_ARTICLE (60, 61)
            $table->string('code_paragraphe')->nullable();            // CD_PARAGRAPHE (601, 611)
            $table->string('libelle_categorie')->nullable();          // LIB_CATEGORIE
            $table->decimal('montant_ae', 15, 2)->default(0);         // Autorisation d'Engagement
            $table->decimal('montant_cp', 15, 2)->default(0);         // Crédit de Paiement
            $table->timestamps();

            $table->index(['activite_id', 'exercice']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lignes');
        Schema::dropIfExists('activites');
    }
};
