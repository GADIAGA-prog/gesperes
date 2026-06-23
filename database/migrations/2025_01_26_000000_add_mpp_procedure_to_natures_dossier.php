<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rattache (facultativement) une nature de dossier à sa procédure source du
 * référentiel MPP GRH, afin que les natures et leurs délais par défaut puissent
 * être dérivés du Manuel des Processus et Procédures de la GRH.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('natures_dossier', function (Blueprint $table) {
            $table->foreignId('mpp_procedure_id')->nullable()->after('id')
                ->constrained('mpp_procedures')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('natures_dossier', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mpp_procedure_id');
        });
    }
};
