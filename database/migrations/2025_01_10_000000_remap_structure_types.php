<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Réforme du typage des structures : les niveaux hiérarchiques (niveau_1..4) et les
 * types LOLF (programme/action/structure_operationnelle) sont remplacés par la NATURE
 * de la structure (ministère / direction / service / établissement). La profondeur
 * hiérarchique est désormais déduite dynamiquement de la chaîne des parents.
 */
return new class extends Migration
{
    private array $mapping = [
        'niveau_1'                 => 'direction',
        'niveau_2'                 => 'direction',
        'niveau_3'                 => 'service',
        'niveau_4'                 => 'etablissement',
        'programme'                => 'service',
        'action'                   => 'service',
        'structure_operationnelle' => 'service',
    ];

    public function up(): void
    {
        foreach ($this->mapping as $ancien => $nouveau) {
            DB::table('structures')->where('type', $ancien)->update(['type' => $nouveau]);
        }
    }

    public function down(): void
    {
        // Remappage de données non réversible (les niveaux d'origine ne sont pas conservés).
    }
};
