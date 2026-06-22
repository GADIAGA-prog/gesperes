<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // urbaine / semi_urbaine / rurale
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('localites', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('libelle');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('region')->nullable();
            $table->string('province')->nullable();
            $table->string('commune')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->index('region');
        });

        Schema::create('type_enseignements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('specialites', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->foreignId('type_enseignement_id')->nullable()->constrained('type_enseignements')->nullOnDelete();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialites');
        Schema::dropIfExists('type_enseignements');
        Schema::dropIfExists('localites');
        Schema::dropIfExists('zones');
    }
};
