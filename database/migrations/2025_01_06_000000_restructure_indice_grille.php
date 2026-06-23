<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'indice croise CATÉGORIE × ÉCHELLE × CLASSE × ÉCHELON et porte la valeur d'indice.
 * On ajoute le lien échelle et on remplace l'unicité par le quadruplet complet.
 * (Catégorie, échelle, classe, échelon redeviennent de simples nomenclatures.)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter le lien échelle.
        Schema::table('indices', function (Blueprint $table) {
            $table->foreignId('echelle_id')->nullable()->after('categorie_id')->constrained('echelles')->nullOnDelete();
        });

        // 2. Créer le nouvel index unique (couvre categorie_id en tête → supporte sa clé étrangère).
        Schema::table('indices', function (Blueprint $table) {
            $table->unique(['categorie_id', 'echelle_id', 'classe_id', 'echelon_id'], 'indices_grille_unique');
        });

        // 3. Supprimer l'ancien index unique.
        Schema::table('indices', function (Blueprint $table) {
            $table->dropUnique('indices_combinaison_unique');
        });
    }

    public function down(): void
    {
        Schema::table('indices', function (Blueprint $table) {
            $table->dropUnique('indices_grille_unique');
            $table->dropConstrainedForeignId('echelle_id');
            $table->unique(['categorie_id', 'classe_id', 'echelon_id'], 'indices_combinaison_unique');
        });
    }
};
