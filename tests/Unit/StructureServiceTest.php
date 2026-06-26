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

    private function agent(string $sexe): Agent
    {
        return Agent::create(['matricule' => 'R' . $sexe, 'nom' => 'NOM', 'prenoms' => 'Prenom', 'sexe' => $sexe]);
    }

    #[Test]
    public function responsable_femme_d_un_service_est_affecte_et_nomme_cheffe(): void
    {
        Fonction::create(['code' => 'CS', 'libelle' => 'Chef de service', 'indemnite_responsabilite' => 30000, 'actif' => true]);
        $agent = $this->agent('F');
        $structure = Structure::create(['code' => 'SVC', 'libelle' => 'Service X', 'type' => TypeStructure::SERVICE->value, 'responsable_agent_id' => $agent->id]);

        app(StructureService::class)->synchroniserResponsable($structure);

        $agent->refresh();
        $this->assertSame($structure->id, $agent->structure_id, 'affecté à la structure');
        $this->assertSame('Cheffe de service', $agent->fonction?->libelle);
        // La variante féminine hérite de l'indemnité de responsabilité.
        $this->assertEquals(30000, $agent->fonction?->indemnite_responsabilite);
    }

    #[Test]
    public function responsable_homme_d_une_direction_est_nomme_directeur(): void
    {
        Fonction::create(['code' => 'DIR2', 'libelle' => 'Directeur', 'indemnite_responsabilite' => 50000, 'actif' => true]);
        $agent = $this->agent('M');
        $structure = Structure::create(['code' => 'DIRX', 'libelle' => 'Direction X', 'type' => TypeStructure::DIRECTION->value, 'responsable_agent_id' => $agent->id]);

        app(StructureService::class)->synchroniserResponsable($structure);

        $agent->refresh();
        $this->assertSame($structure->id, $agent->structure_id);
        $this->assertSame('Directeur', $agent->fonction?->libelle);
    }

    #[Test]
    public function responsable_femme_d_une_direction_est_nommee_directrice(): void
    {
        Fonction::create(['code' => 'DIR2', 'libelle' => 'Directeur', 'indemnite_responsabilite' => 50000, 'actif' => true]);
        $agent = $this->agent('F');
        $structure = Structure::create(['code' => 'DIRY', 'libelle' => 'Direction Y', 'type' => TypeStructure::DIRECTION->value, 'responsable_agent_id' => $agent->id]);

        app(StructureService::class)->synchroniserResponsable($structure);

        $this->assertSame('Directrice', $agent->refresh()->fonction?->libelle);
        $this->assertEquals(50000, $agent->fonction?->indemnite_responsabilite);
    }

    #[Test]
    public function sans_responsable_ne_fait_rien(): void
    {
        $structure = Structure::create(['code' => 'NOP', 'libelle' => 'Sans resp', 'type' => TypeStructure::SERVICE->value]);
        app(StructureService::class)->synchroniserResponsable($structure);
        $this->assertDatabaseCount('agents', 0);
    }
}
