<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique de carrière des agents : chaque acte (avancement, promotion, nomination,
 * changement de position) conserve l'état AVANT et APRÈS des dimensions concernées,
 * sur le modèle de la table affectations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carriere_evenements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('type');                          // App\Enums\TypeEvenementCarriere
            $table->date('date_effet');

            // État avant / après (nomenclatures de la grille et de l'emploi)
            $table->foreignId('ancienne_categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('nouvelle_categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('ancienne_echelle_id')->nullable()->constrained('echelles')->nullOnDelete();
            $table->foreignId('nouvelle_echelle_id')->nullable()->constrained('echelles')->nullOnDelete();
            $table->foreignId('ancienne_classe_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('nouvelle_classe_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('ancien_echelon_id')->nullable()->constrained('echelons')->nullOnDelete();
            $table->foreignId('nouvel_echelon_id')->nullable()->constrained('echelons')->nullOnDelete();
            $table->foreignId('ancien_indice_id')->nullable()->constrained('indices')->nullOnDelete();
            $table->foreignId('nouvel_indice_id')->nullable()->constrained('indices')->nullOnDelete();
            $table->foreignId('ancienne_fonction_id')->nullable()->constrained('fonctions')->nullOnDelete();
            $table->foreignId('nouvelle_fonction_id')->nullable()->constrained('fonctions')->nullOnDelete();
            $table->foreignId('ancien_poste_id')->nullable()->constrained('postes')->nullOnDelete();
            $table->foreignId('nouveau_poste_id')->nullable()->constrained('postes')->nullOnDelete();
            $table->foreignId('ancienne_position_id')->nullable()->constrained('positions_administratives')->nullOnDelete();
            $table->foreignId('nouvelle_position_id')->nullable()->constrained('positions_administratives')->nullOnDelete();

            $table->string('reference_acte')->nullable();
            $table->string('description')->nullable();        // résumé lisible généré par le service
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['agent_id', 'date_effet']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriere_evenements');
    }
};
