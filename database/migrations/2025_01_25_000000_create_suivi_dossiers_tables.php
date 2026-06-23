<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Suivi des dossiers : traçabilité du circuit administratif d'un dossier
 * (bordereau) au sein des structures du MESFPTT.
 *
 * - natures_dossier      : référentiel paramétrable des natures de dossier
 *                          (chaque nature porte un délai de traitement par défaut).
 * - suivi_dossiers       : un dossier suivi (référence bordereau, nature, étape,
 *                          service & agent où il se situe, délai de traitement).
 * - suivi_dossier_etapes : historique des mouvements du dossier (transmissions
 *                          successives d'un service/agent à un autre).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('natures_dossier', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('libelle');
            $table->unsignedSmallInteger('delai_defaut_jours')->nullable(); // délai réglementaire par défaut
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('libelle');
        });

        Schema::create('suivi_dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_bordereau');
            $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete(); // structure concernée / d'origine
            $table->foreignId('nature_id')->nullable()->constrained('natures_dossier')->nullOnDelete();
            $table->string('objet')->nullable();
            $table->string('etape');                       // App\Enums\EtapeDossier
            $table->string('statut')->default('en_cours'); // App\Enums\StatutSuiviDossier

            // Localisation courante du dossier : à quel niveau il se situe.
            $table->foreignId('service_actuel_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('agent_actuel_id')->nullable()->constrained('agents')->nullOnDelete();

            // Délai de traitement.
            $table->date('date_reception');
            $table->unsignedSmallInteger('delai_jours')->default(0); // délai accordé en jours
            $table->date('date_traitement')->nullable();             // date de clôture effective

            $table->text('observation')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('reference_bordereau');
            $table->index(['statut', 'etape']);
            $table->index(['structure_id', 'date_reception']);
        });

        Schema::create('suivi_dossier_etapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suivi_dossier_id')->constrained('suivi_dossiers')->cascadeOnDelete();
            $table->string('etape');
            $table->foreignId('service_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->date('date_mouvement');
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['suivi_dossier_id', 'date_mouvement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suivi_dossier_etapes');
        Schema::dropIfExists('suivi_dossiers');
        Schema::dropIfExists('natures_dossier');
    }
};
