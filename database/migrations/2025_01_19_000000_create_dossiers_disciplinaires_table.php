<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Discipline (GRH avancée) : demandes d'explication, sanctions et recours.
 * Chaque acte est rattaché à un agent et conserve motif, nature, décision et statut.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_disciplinaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('type');                   // App\Enums\TypeDiscipline
            $table->date('date_acte');
            $table->string('reference_acte')->nullable();
            $table->text('motif');
            $table->string('nature')->nullable();     // pour les sanctions : avertissement, blâme…
            $table->string('statut')->default('ouvert'); // ouvert | clos
            $table->text('decision')->nullable();
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['agent_id', 'date_acte']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers_disciplinaires');
    }
};
