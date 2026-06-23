<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notifications / alertes RH persistantes, générées par la tâche planifiée
 * `alertes:generer` (retraites proches, documents expirés / bientôt expirés).
 * La clé `cle` garantit l'unicité d'une alerte pour éviter les doublons.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications_rh', function (Blueprint $table) {
            $table->id();
            $table->string('type');                 // retraite | document_expire | document_bientot
            $table->string('cle')->unique();        // identifiant logique (anti-doublon)
            $table->foreignId('agent_id')->nullable()->constrained('agents')->cascadeOnDelete();
            $table->string('titre');
            $table->string('message', 500);
            $table->string('niveau')->default('info'); // info | warning | danger
            $table->boolean('lu')->default(false);
            $table->timestamps();

            $table->index(['lu', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_rh');
    }
};
