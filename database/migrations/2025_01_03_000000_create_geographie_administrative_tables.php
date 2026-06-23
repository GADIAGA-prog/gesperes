<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Découpage administratif du Burkina Faso : Région → Province → Localité.
 * Établit la hiérarchie (clés étrangères) et rattache les localités à une province.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('libelle');
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('chef_lieu')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::table('localites', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('zone_id')->constrained('provinces')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('localites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('province_id');
        });

        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
