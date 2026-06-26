<?php

namespace App\Console\Commands;

use App\Models\Structure;
use App\Services\StructureService;
use Illuminate\Console\Command;

/**
 * Ré-applique la nomination automatique à toutes les structures ayant un
 * responsable : aligne leur fonction et leur indemnité de responsabilité sur
 * le décret 2014-427 selon le niveau de la structure. Idempotent.
 */
class RenommerResponsablesStructures extends Command
{
    protected $signature = 'structures:renommer-responsables';

    protected $description = 'Aligne la fonction/indemnité des responsables de structure sur le décret 2014-427.';

    public function handle(StructureService $service): int
    {
        $structures = Structure::whereNotNull('responsable_agent_id')->get();

        foreach ($structures as $structure) {
            $service->synchroniserResponsable($structure);
        }

        $this->info("✓ {$structures->count()} responsable(s) de structure (re)nommé(s) selon le décret 2014-427.");

        return self::SUCCESS;
    }
}
