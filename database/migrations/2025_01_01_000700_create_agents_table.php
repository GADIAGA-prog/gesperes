<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();

            // --- Identification ---
            $table->string('matricule')->unique();          // numérique unique
            $table->string('cle', 5)->nullable();           // alphabétique majuscule
            $table->string('nom');
            $table->string('prenoms');
            $table->string('sexe', 1);                      // M / F
            $table->date('date_naissance')->nullable();
            $table->string('nationalite')->default('Burkinabè');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();

            // --- Situation administrative ---
            $table->string('statut')->nullable();           // titulaire, contractuel, stagiaire...
            $table->foreignId('emploi_id')->nullable()->constrained('emplois')->nullOnDelete();
            $table->foreignId('fonction_id')->nullable()->constrained('fonctions')->nullOnDelete();
            $table->foreignId('poste_id')->nullable()->constrained('postes')->nullOnDelete();
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('echelle_id')->nullable()->constrained('echelles')->nullOnDelete();
            $table->foreignId('classe_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('echelon_id')->nullable()->constrained('echelons')->nullOnDelete();
            $table->foreignId('indice_id')->nullable()->constrained('indices')->nullOnDelete();
            $table->foreignId('position_administrative_id')->nullable()->constrained('positions_administratives')->nullOnDelete();

            // --- Affectation courante ---
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('commune')->nullable();
            $table->string('etablissement')->nullable();
            $table->foreignId('localite_id')->nullable()->constrained('localites')->nullOnDelete();
            $table->date('date_affectation')->nullable();

            // --- Carrière ---
            $table->date('date_integration')->nullable();
            $table->date('date_effet_emploi')->nullable();
            $table->date('date_nomination')->nullable();
            $table->date('date_retraite')->nullable();      // calculée automatiquement

            // --- Situation familiale ---
            $table->string('situation_matrimoniale')->nullable();
            $table->unsignedSmallInteger('nombre_enfants')->default(0);
            $table->unsignedSmallInteger('personnes_a_charge')->default(0);
            $table->decimal('allocation_familiale', 12, 2)->default(0); // calculée

            // --- Enseignement ---
            $table->foreignId('type_enseignement_id')->nullable()->constrained('type_enseignements')->nullOnDelete();
            $table->foreignId('specialite_id')->nullable()->constrained('specialites')->nullOnDelete();
            $table->unsignedSmallInteger('volume_horaire_du')->nullable();
            $table->unsignedSmallInteger('volume_horaire_assure')->nullable();
            $table->string('lieu_exercice')->nullable();    // en_classe / au_bureau (déduit)

            // --- Autres ---
            $table->string('distinction_honorifique')->nullable();
            $table->text('observations')->nullable();
            $table->string('statut_dossier')->default('brouillon');

            // --- Liens & traçabilité ---
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // compte "agent individuel"
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['nom', 'prenoms']);
            $table->index('sexe');
            $table->index('region');
            $table->index('date_retraite');
        });

        // FK différée du responsable de structure vers agents
        Schema::table('structures', function (Blueprint $table) {
            $table->foreign('responsable_agent_id')->references('id')->on('agents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropForeign(['responsable_agent_id']);
        });
        Schema::dropIfExists('agents');
    }
};
