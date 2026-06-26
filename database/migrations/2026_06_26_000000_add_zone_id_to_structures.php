<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zone (rurale/semi-urbaine/urbaine) portée par la structure : permet de dériver
 * la zone d'astreinte/spécifique d'un agent depuis sa direction régionale/
 * provinciale, sans dépendre d'un préfixe de libellé (DRESFPT/DPESFPT).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('localite_id')->constrained('zones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('zone_id');
        });
    }
};
