<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'indice est fonction de la catégorie, de la classe et de l'échelon.
 * On rattache donc chaque indice à ce triplet et on garantit son unicité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indices', function (Blueprint $table) {
            $table->foreignId('categorie_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->foreignId('classe_id')->nullable()->after('categorie_id')->constrained('classes')->nullOnDelete();
            $table->foreignId('echelon_id')->nullable()->after('classe_id')->constrained('echelons')->nullOnDelete();

            // Un seul indice par combinaison catégorie / classe / échelon.
            $table->unique(['categorie_id', 'classe_id', 'echelon_id'], 'indices_combinaison_unique');
        });
    }

    public function down(): void
    {
        Schema::table('indices', function (Blueprint $table) {
            $table->dropUnique('indices_combinaison_unique');
            $table->dropConstrainedForeignId('categorie_id');
            $table->dropConstrainedForeignId('classe_id');
            $table->dropConstrainedForeignId('echelon_id');
        });
    }
};
