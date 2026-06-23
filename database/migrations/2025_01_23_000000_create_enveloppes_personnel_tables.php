<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enveloppe de référence (DPBEP) des dépenses de personnel sur 3 exercices
 * glissants (n+1 à n+3), fournie par l'utilisateur. Une enveloppe = un en-tête
 * + des lignes (IDR, régularisations, salaire du personnel en activité…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enveloppes_personnel', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('annee_debut');
            $table->string('intitule')->default("Enveloppe de référence du DPBEP des dépenses de personnel");
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('enveloppe_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enveloppe_personnel_id')->constrained('enveloppes_personnel')->cascadeOnDelete();
            $table->string('libelle');
            $table->decimal('montant_n1', 20, 2)->default(0);
            $table->decimal('montant_n2', 20, 2)->default(0);
            $table->decimal('montant_n3', 20, 2)->default(0);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enveloppe_lignes');
        Schema::dropIfExists('enveloppes_personnel');
    }
};
