<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Présence & Congés.
 *
 *  - motifs_absence : nomenclature des motifs (autorisation, congé, justifiée, injustifiée).
 *  - conges         : demandes de congé / d'autorisation d'absence (imputées si validées).
 *  - pointages      : situation journalière de présence des agents (Fiche A).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motifs_absence', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->string('categorie')->index();   // App\Enums\CategorieAbsence
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('motif_absence_id')->nullable()->constrained('motifs_absence')->nullOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->unsignedSmallInteger('nombre_jours')->default(0);
            $table->string('statut')->default('demande')->index();  // App\Enums\StatutConge
            $table->text('motif')->nullable();
            $table->string('reference_decision')->nullable();
            $table->text('observation')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_validation')->nullable();
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agent_id', 'date_debut']);
        });

        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->date('date_pointage');
            $table->boolean('present')->default(true);
            $table->time('heure_arrivee')->nullable();
            $table->time('heure_depart')->nullable();
            // Si absent et motif null ⇒ absence injustifiée.
            $table->foreignId('motif_absence_id')->nullable()->constrained('motifs_absence')->nullOnDelete();
            $table->decimal('duree_jours', 4, 2)->default(0);
            $table->decimal('duree_heures', 5, 2)->default(0);
            $table->string('reference_piece')->nullable();   // Fiche B : référence pièce justificative
            $table->string('mesure_prise')->nullable();      // Fiche B : mesures prises
            $table->text('observation')->nullable();
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['agent_id', 'date_pointage']);
            $table->index(['structure_id', 'date_pointage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
        Schema::dropIfExists('conges');
        Schema::dropIfExists('motifs_absence');
    }
};
