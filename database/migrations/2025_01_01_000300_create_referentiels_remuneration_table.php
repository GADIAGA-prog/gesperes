<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();          // A, B, C, D, E, P...
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('echelles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('echelons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->unsignedSmallInteger('rang')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('indices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedInteger('valeur')->nullable();   // valeur de l'indice
            $table->string('libelle')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indices');
        Schema::dropIfExists('echelons');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('echelles');
        Schema::dropIfExists('categories');
    }
};
