<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Performance (GRH avancée) : évaluations annuelles des agents
 * (objectifs, note, appréciation), base de la GPEC et des plans de formation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->unsignedSmallInteger('periode');           // année d'évaluation
            $table->date('date_evaluation');
            $table->decimal('note', 5, 2)->nullable();         // /20
            $table->text('objectifs')->nullable();
            $table->text('appreciation')->nullable();
            $table->foreignId('evaluateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('statut')->default('brouillon');    // brouillon | valide
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agent_id', 'periode']);
            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
