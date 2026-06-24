<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fiches de poste — Lot 2 : workflow de validation/adoption (historique)
 * et rattachement d'une fiche de poste à un agent (titulaire).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Historique des étapes du workflow (soumission, adoption, révision).
        Schema::create('fiche_poste_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiche_poste_id')->constrained('fiches_poste')->cascadeOnDelete();
            $table->string('etape');                 // soumission / adoption / revision
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('version')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });

        // Rattachement de la fiche de poste à l'agent (titulaire du poste).
        Schema::table('agents', function (Blueprint $table) {
            $table->foreignId('fiche_poste_id')->nullable()->after('poste_id')
                ->constrained('fiches_poste')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fiche_poste_id');
        });
        Schema::dropIfExists('fiche_poste_validations');
    }
};
