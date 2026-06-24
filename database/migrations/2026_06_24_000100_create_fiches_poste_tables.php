<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sous-module « Fiches de poste » (Outils GRH) — conforme au guide méthodologique
 * de description des postes de travail (MFPTPS, 2025).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Référentiel : familles professionnelles (sigle = code, max 3 lettres).
        Schema::create('familles_professionnelles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // sigle significatif (ex. GRH)
            $table->string('libelle');
            $table->string('metier')->nullable();      // métier de rattachement
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Référentiel : emplois-types (sigle = code, max 2 lettres).
        Schema::create('emplois_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();          // sigle significatif (ex. RT)
            $table->string('libelle');
            $table->foreignId('famille_professionnelle_id')->nullable()->constrained('familles_professionnelles')->nullOnDelete();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Cœur : la fiche de poste (descriptif + profil).
        Schema::create('fiches_poste', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->index();          // codification auto (ex. GRH-RT-CPR-4)
            $table->string('intitule');
            $table->string('type_poste')->default('operationnel'); // App\Enums\TypePoste
            $table->string('position_mission')->nullable();        // App\Enums\PositionMission
            $table->string('position_hierarchique')->nullable();   // App\Enums\PositionHierarchique (pour le chiffre du code)

            // Identification — rattachements
            $table->foreignId('famille_professionnelle_id')->nullable()->constrained('familles_professionnelles')->nullOnDelete();
            $table->foreignId('emploi_type_id')->nullable()->constrained('emplois_types')->nullOnDelete();
            $table->foreignId('emploi_id')->nullable()->constrained('emplois')->nullOnDelete();
            $table->string('famille_emplois')->nullable();
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete(); // unité administrative

            // Mission
            $table->text('mission')->nullable();

            // Relations du poste
            $table->string('niveau_hierarchique_superieur')->nullable();
            $table->string('niveau_hierarchique_inferieur')->nullable();
            $table->text('relations_internes')->nullable();
            $table->text('relations_externes')->nullable();

            // Profil — dimension
            $table->text('moyens_generaux')->nullable();
            $table->text('moyens_specifiques')->nullable();

            // Profil — conditions d'accès
            $table->string('niveau_etudes')->nullable();
            $table->string('domaine')->nullable();
            $table->string('specialite')->nullable();
            $table->string('experience_pro')->nullable();

            // Cycle de vie
            $table->string('statut')->default('brouillon');        // App\Enums\StatutFichePoste
            $table->string('version')->nullable();
            $table->timestamp('adoptee_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['structure_id', 'type_poste']);
        });

        // Activités permanentes du poste.
        Schema::create('fiche_poste_activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_poste_id')->constrained('fiches_poste')->cascadeOnDelete();
            $table->string('libelle');
            $table->string('taux_contribution')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });

        // Indicateurs de performance du poste.
        Schema::create('fiche_poste_indicateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_poste_id')->constrained('fiches_poste')->cascadeOnDelete();
            $table->string('libelle');
            $table->string('nature')->nullable();   // quantitatif / qualitatif
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });

        // Compétences requises (réutilise le dictionnaire `competences`).
        Schema::create('fiche_poste_competence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_poste_id')->constrained('fiches_poste')->cascadeOnDelete();
            $table->foreignId('competence_id')->constrained('competences')->cascadeOnDelete();
            $table->string('type')->default('metier');     // App\Enums\TypeCompetence
            $table->string('niveau')->default('application'); // App\Enums\NiveauCompetence
            $table->timestamps();
            $table->unique(['fiche_poste_id', 'competence_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiche_poste_competence');
        Schema::dropIfExists('fiche_poste_indicateurs');
        Schema::dropIfExists('fiche_poste_activites');
        Schema::dropIfExists('fiches_poste');
        Schema::dropIfExists('emplois_types');
        Schema::dropIfExists('familles_professionnelles');
    }
};
