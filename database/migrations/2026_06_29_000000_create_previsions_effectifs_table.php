<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hypothèses de planification du TPEE (Tableau Prévisionnel des Effectifs et
 * des Emplois) : par emploi et par année, les entrées prévues (recrutements) et
 * l'effectif cible. Les départs (retraite) et l'effectif courant sont dérivés
 * automatiquement des données agents — non stockés ici.
 *
 * `structure_id` = portée : NULL pour une prévision nationale, sinon la
 * direction concernée (cohérent avec le filtre du tableau).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('previsions_effectifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emploi_id')->constrained('emplois')->cascadeOnDelete();
            $table->unsignedSmallInteger('annee');
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->unsignedInteger('entrees_prevues')->default(0);
            $table->unsignedInteger('effectif_cible')->nullable();
            $table->string('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['emploi_id', 'annee', 'structure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('previsions_effectifs');
    }
};
