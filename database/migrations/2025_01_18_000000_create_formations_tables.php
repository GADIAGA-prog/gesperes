<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Formation (GRH avancée) : sessions de formation et participants (bénéficiaires),
 * avec coûts et résultats — base des compétences acquises et de la GPEC.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('intitule');
            $table->string('organisme')->nullable();
            $table->string('lieu')->nullable();
            $table->string('type')->nullable();            // interne | externe
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->decimal('cout', 12, 2)->default(0);
            $table->string('statut')->default('planifiee'); // planifiee|en_cours|terminee|annulee
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('date_debut');
        });

        Schema::create('formation_agent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->string('resultat')->nullable();        // admis | echec | en_cours
            $table->string('observation')->nullable();
            $table->timestamps();

            $table->unique(['formation_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_agent');
        Schema::dropIfExists('formations');
    }
};
