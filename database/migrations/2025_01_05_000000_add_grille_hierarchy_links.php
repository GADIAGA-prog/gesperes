<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hiérarchie de la grille indiciaire : Catégorie → Échelle → Classe → Échelon → Indice.
 * On rattache chaque classe à une échelle, et chaque échelon à une classe.
 * (L'échelle est déjà reliée à la catégorie ; l'indice est déjà relié à l'échelon.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('echelle_id')->nullable()->after('libelle')->constrained('echelles')->nullOnDelete();
        });

        Schema::table('echelons', function (Blueprint $table) {
            $table->foreignId('classe_id')->nullable()->after('libelle')->constrained('classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('echelons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('classe_id');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('echelle_id');
        });
    }
};
