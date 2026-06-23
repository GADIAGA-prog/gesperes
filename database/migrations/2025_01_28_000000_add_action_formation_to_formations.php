<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Relie une session réelle (table `formations`) à l'action planifiée dont elle
 * découle, afin de suivre l'exécution du plan (prévu vs réalisé : nombre de
 * sessions, agents formés, coût réel par rapport au coût prévisionnel).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->foreignId('action_formation_id')->nullable()->after('id')
                ->constrained('actions_formation')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('action_formation_id');
        });
    }
};
