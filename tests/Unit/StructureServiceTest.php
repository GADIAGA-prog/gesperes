<?php

namespace Tests\Unit;

use App\Enums\TypeStructure;
use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Structure;
use App\Services\StructureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StructureServiceTest extends TestCase
{
    use RefreshDatabase;

    private function agent(): Agent
    {
        return Agent::create(['matricule' => 'R1', 'nom' => 'NOM', 'prenoms' => 'Prenom', 'sexe' => 'M']);
    }

    /** Nomme le responsable et renvoie sa fonction. */
    private function nommer(array $structureAttrs): Fonction
    {
        $agent = $this->agent();
        $structure = Structure::create($structureAttrs + ['responsable_agent_id' => $agent->id]);
        app(StructureService::class)->synchroniserResponsable($structure);

        $agent->refresh();
        $this->assertSame($structure->id, $agent->structure_id, 'affecté à la structure');

        return $agent->fonction;
    }

    #[Test]
    public function responsable_de_service_est_chef_de_service_nomme_par_arrete(): void
    {
        $f = $this->nommer(['code' => 'SVC', 'libelle' => 'Service X', 'type' => TypeStructure::SERVICE->value]);
        $this->assertSame('Chef de service nommé par arrêté', $f->libelle);
        $this->assertEquals(10500, $f->indemnite_responsabilite);
    }

    #[Test]
    public function responsable_de_direction_est_directeur_central(): void
    {
        $f = $this->nommer(['code' => 'DIRX', 'libelle' => 'Direction X', 'type' => TypeStructure::DIRECTION->value]);
        $this->assertSame('Directeur central', $f->libelle);
        $this->assertEquals(18500, $f->indemnite_responsabilite);
    }

    #[Test]
    public function responsable_du_cabinet_est_directeur_de_cabinet(): void
    {
        $f = $this->nommer(['code' => 'CAB', 'libelle' => 'Cabinet', 'type' => TypeStructure::DIRECTION->value]);
        $this->assertSame('Directeur de cabinet', $f->libelle);
        $this->assertEquals(80000, $f->indemnite_responsabilite);
    }

    #[Test]
    public function responsable_du_secretariat_general_est_secretaire_general(): void
    {
        $f = $this->nommer(['code' => 'SG', 'libelle' => 'Secrétariat général', 'type' => TypeStructure::DIRECTION->value]);
        $this->assertSame('Secrétaire général', $f->libelle);
        $this->assertEquals(60000, $f->indemnite_responsabilite);
    }

    #[Test]
    public function utilise_la_fonction_existante_du_decret_si_presente(): void
    {
        // Si la fonction existe déjà (seeder décret) avec un montant officiel, on la réutilise.
        Fonction::create(['code' => 'SGEXIST', 'libelle' => 'Secrétaire général', 'indemnite_responsabilite' => 60000, 'actif' => true]);
        $f = $this->nommer(['code' => 'SG2', 'libelle' => 'Secrétariat général', 'type' => TypeStructure::DIRECTION->value]);
        $this->assertSame('SGEXIST', $f->code);
    }

    #[Test]
    public function sans_responsable_ne_fait_rien(): void
    {
        $structure = Structure::create(['code' => 'NOP', 'libelle' => 'Sans resp', 'type' => TypeStructure::SERVICE->value]);
        app(StructureService::class)->synchroniserResponsable($structure);
        $this->assertDatabaseCount('agents', 0);
    }
}
