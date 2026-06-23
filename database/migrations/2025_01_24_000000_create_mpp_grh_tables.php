<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel MPP GRH : Manuel des Processus et Procédures de la GRH.
 * Hiérarchie : Processus → Procédures → Opérations (avec tâches, intervenants,
 * structure responsable, fait générateur, résultats et délais).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpp_processus', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('numero');
            $table->string('code')->nullable();
            $table->string('libelle');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('mpp_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpp_processus_id')->constrained('mpp_processus')->cascadeOnDelete();
            $table->string('libelle');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('mpp_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mpp_procedure_id')->constrained('mpp_procedures')->cascadeOnDelete();
            $table->string('libelle', 500)->nullable();
            $table->string('structure_responsable', 500)->nullable();
            $table->text('fait_generateur')->nullable();
            $table->text('taches')->nullable();
            $table->text('intervenants')->nullable();
            $table->text('resultats')->nullable();
            $table->string('delais', 255)->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpp_operations');
        Schema::dropIfExists('mpp_procedures');
        Schema::dropIfExists('mpp_processus');
    }
};
