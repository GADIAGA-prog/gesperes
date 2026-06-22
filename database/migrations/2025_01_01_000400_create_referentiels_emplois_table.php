<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emplois', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('enseignant')->default(false);       // détermine En classe / Au bureau
            $table->unsignedSmallInteger('volume_horaire_defaut')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->index('enseignant');
        });

        Schema::create('fonctions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('postes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('positions_administratives', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->string('categorie');     // App\Enums\CategoriePosition
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->index('categorie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions_administratives');
        Schema::dropIfExists('postes');
        Schema::dropIfExists('fonctions');
        Schema::dropIfExists('emplois');
    }
};
