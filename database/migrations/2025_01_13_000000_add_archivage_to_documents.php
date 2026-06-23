<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Classement & archivage du dossier individuel : un document peut être archivé et
 * rattaché à un acte de carrière (pour le classement des actes scannés).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'archive')) {
                $table->boolean('archive')->default(false)->after('statut');
            }
            if (! Schema::hasColumn('documents', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('archive');
            }
            if (! Schema::hasColumn('documents', 'carriere_evenement_id')) {
                $table->foreignId('carriere_evenement_id')->nullable()->after('agent_id')
                    ->constrained('carriere_evenements')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'carriere_evenement_id')) {
                $table->dropConstrainedForeignId('carriere_evenement_id');
            }
            $table->dropColumn(['archive', 'archived_at']);
        });
    }
};
