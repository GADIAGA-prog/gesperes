<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mouvements du personnel : changements de position administrative (sorties
 * temporaires : disponibilité, détachement… ; sorties définitives : retraite,
 * décès, démission… ; réintégration en activité). Historise l'ancienne et la
 * nouvelle position ; la famille (Activité / Sortie temporaire / Sortie définitive)
 * découle de la position cible et pilote l'effectif actif de l'agent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('ancienne_position_id')->nullable()->constrained('positions_administratives')->nullOnDelete();
            $table->foreignId('nouvelle_position_id')->constrained('positions_administratives')->restrictOnDelete();
            $table->date('date_effet');
            $table->date('date_fin')->nullable();           // fin prévue d'une sortie temporaire
            $table->string('reference_acte')->nullable();
            $table->text('motif')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agent_id', 'date_effet']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements');
    }
};
