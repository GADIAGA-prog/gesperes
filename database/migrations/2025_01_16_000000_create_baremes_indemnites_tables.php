<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barèmes d'indemnités (décret 2014-427), alimentés depuis les fichiers GESPER.
 * Les montants dépendent de plusieurs dimensions (emploi, zone, catégorie, échelle,
 * caractère enseignant / en classe) : on stocke chaque barème par CODES, résolus à la
 * volée par IndemniteService. Le référentiel `indemnites` porte le mode « bareme ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indemnites', function (Blueprint $table) {
            $table->string('bareme')->nullable()->after('mode'); // astreinte|logement|specifique|technicite
        });

        // Astreinte : emploi × zone
        Schema::create('bareme_astreintes', function (Blueprint $table) {
            $table->id();
            $table->string('emploi_code')->index();
            $table->string('zone_code')->index();
            $table->decimal('montant', 12, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->unique(['emploi_code', 'zone_code']);
        });

        // Indemnité spécifique harmonisée : emploi × zone
        Schema::create('bareme_specifiques', function (Blueprint $table) {
            $table->id();
            $table->string('emploi_code')->index();
            $table->string('zone_code')->index();
            $table->decimal('montant', 12, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->unique(['emploi_code', 'zone_code']);
        });

        // Logement : catégorie × enseignant × en classe
        Schema::create('bareme_logements', function (Blueprint $table) {
            $table->id();
            $table->string('categorie_code')->index();
            $table->boolean('enseignant')->default(false);
            $table->boolean('en_classe')->default(false);
            $table->decimal('montant', 12, 2)->default(0);
            $table->boolean('actif')->default(true);
            $table->unique(['categorie_code', 'enseignant', 'en_classe']);
        });

        // Technicité : échelle
        Schema::create('bareme_technicites', function (Blueprint $table) {
            $table->id();
            $table->string('echelle_code')->unique();
            $table->decimal('montant', 12, 2)->default(0);
            $table->boolean('actif')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bareme_technicites');
        Schema::dropIfExists('bareme_logements');
        Schema::dropIfExists('bareme_specifiques');
        Schema::dropIfExists('bareme_astreintes');
        Schema::table('indemnites', function (Blueprint $table) {
            $table->dropColumn('bareme');
        });
    }
};
