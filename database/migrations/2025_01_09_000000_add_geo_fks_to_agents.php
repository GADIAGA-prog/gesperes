<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affectation géographique normalisée : on rattache l'agent à la région et à la
 * province/circonscription par clé étrangère (la commune reste portée par localite_id).
 * Les colonnes texte region/province/commune sont conservées (libellés synchronisés)
 * pour la compatibilité avec l'export, le PDF, l'import Excel et les filtres existants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('structure_id')->constrained('regions')->nullOnDelete();
            $table->foreignId('province_id')->nullable()->after('region_id')->constrained('provinces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('region_id');
        });
    }
};
