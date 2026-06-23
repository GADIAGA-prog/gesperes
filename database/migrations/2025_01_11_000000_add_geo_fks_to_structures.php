<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Localisation normalisée des structures : rattachement à la région et à la
 * province/circonscription par clé étrangère (la commune reste portée par localite_id).
 * Les colonnes texte region/province sont conservées (libellés synchronisés par le modèle).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('action_id')->constrained('regions')->nullOnDelete();
            $table->foreignId('province_id')->nullable()->after('region_id')->constrained('provinces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('region_id');
        });
    }
};
