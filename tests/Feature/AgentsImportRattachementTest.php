<?php

namespace Tests\Feature;

use App\Enums\TypeStructure;
use App\Imports\AgentsImport;
use App\Models\Agent;
use App\Models\Region;
use App\Models\Structure;
use App\Services\AgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Vérifie que l'import résout la structure de rattachement à partir des colonnes
 * de cascade (niveau_1 … service) et en déduit la géographie, conformément à
 * l'harmonisation import/export.
 */
class AgentsImportRattachementTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0:Structure,1:Structure,2:Structure} ministère, direction, service */
    private function hierarchie(): array
    {
        $region = Region::create(['code' => 'SAH', 'libelle' => 'Sahel']);

        $ministere = Structure::create([
            'code'    => 'MIN',
            'libelle' => 'MESFPTT',
            'type'    => TypeStructure::MINISTERE->value,
        ]);
        $direction = Structure::create([
            'code'      => 'SG',
            'libelle'   => 'Secrétariat général',
            'type'      => TypeStructure::DIRECTION->value,
            'parent_id' => $ministere->id,
        ]);
        $service = Structure::create([
            'code'      => 'DR-LIP',
            'libelle'   => 'DRESFPT-Liptako',
            'type'      => TypeStructure::SERVICE->value,
            'parent_id' => $direction->id,
            'region_id' => $region->id,
        ]);

        return [$ministere, $direction, $service];
    }

    private function importer(array $ligne): AgentsImport
    {
        $import = new AgentsImport(app(AgentService::class));
        $import->collection(new Collection([new Collection($ligne)]));

        $this->assertSame([], $import->erreurs, 'Erreurs import : ' . implode(' | ', $import->erreurs));

        return $import;
    }

    public function test_resout_la_structure_via_la_cascade_et_deduit_la_region(): void
    {
        [, , $service] = $this->hierarchie();

        $this->importer([
            'matricule' => '100001',
            'nom'       => 'SAWADOGO',
            'prenoms'   => 'Harouna',
            'sexe'      => 'M',
            'niveau_1'  => 'MESFPTT',
            'niveau_2'  => 'Secrétariat général',
            'niveau_3'  => 'DRESFPT-Liptako',
        ]);

        $agent = Agent::where('matricule', '100001')->firstOrFail();

        $this->assertSame($service->id, $agent->structure_id);
        // « Structure » = avant-dernier niveau, « Service » = dernier niveau.
        $this->assertSame('Secrétariat général', $agent->structure->niveauStructure());
        $this->assertSame('DRESFPT-Liptako', $agent->structure->niveauService());
        // Région déduite de la structure (jamais saisie en texte libre).
        $this->assertSame('Sahel', $agent->region);
    }

    public function test_resout_par_suffixe_structure_service(): void
    {
        [, , $service] = $this->hierarchie();

        $this->importer([
            'matricule' => '100002',
            'nom'       => 'KABORE',
            'prenoms'   => 'Salif',
            'sexe'      => 'M',
            'structure' => 'Secrétariat général',
            'service'   => 'DRESFPT-Liptako',
        ]);

        $this->assertSame($service->id, Agent::where('matricule', '100002')->value('structure_id'));
    }

    public function test_laisse_le_rattachement_vide_si_introuvable(): void
    {
        $this->hierarchie();

        $this->importer([
            'matricule' => '100003',
            'nom'       => 'TRAORE',
            'prenoms'   => 'Issa',
            'sexe'      => 'M',
            'niveau_1'  => 'Structure inexistante',
        ]);

        $this->assertNull(Agent::where('matricule', '100003')->value('structure_id'));
    }
}
