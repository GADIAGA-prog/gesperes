<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indemnités (décret 2014-427) — structure PARAMÉTRABLE : les types d'indemnités et
 * leurs montants/taux sont saisis dans le référentiel `indemnites` (aucun chiffre codé
 * en dur). `agent_indemnites` attribue une indemnité à un agent avec son montant calculé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indemnites', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->string('mode')->default('montant_fixe');  // App\Enums\ModeIndemnite
            $table->decimal('valeur', 12, 2)->default(0);      // FCFA si fixe, % si pourcentage
            $table->string('reference_texte')->nullable();     // ex. décret 2014-427
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('agent_indemnites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('indemnite_id')->constrained('indemnites')->cascadeOnDelete();
            $table->decimal('montant', 12, 2)->default(0);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->string('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_indemnites');
        Schema::dropIfExists('indemnites');
    }
};
