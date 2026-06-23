<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indemnité de responsabilité rattachée à la fonction de l'agent
 * (montant mensuel forfaitaire, paramétré dans le référentiel des fonctions).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fonctions', function (Blueprint $table) {
            $table->unsignedInteger('indemnite_responsabilite')->default(0)->after('libelle');
        });
    }

    public function down(): void
    {
        Schema::table('fonctions', function (Blueprint $table) {
            $table->dropColumn('indemnite_responsabilite');
        });
    }
};
