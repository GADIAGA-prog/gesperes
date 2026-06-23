<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Compétences (GRH avancée / GPEC) : référentiel de compétences et
 * rattachement aux agents avec niveau de maîtrise.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competences', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->string('domaine')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('agent_competence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->foreignId('competence_id')->constrained('competences')->cascadeOnDelete();
            $table->string('niveau')->default('intermediaire'); // debutant|intermediaire|avance|expert
            $table->date('date_acquisition')->nullable();
            $table->string('source')->nullable();               // formation | experience
            $table->timestamps();

            $table->unique(['agent_id', 'competence_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_competence');
        Schema::dropIfExists('competences');
    }
};
