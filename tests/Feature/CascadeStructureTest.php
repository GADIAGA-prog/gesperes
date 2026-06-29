<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Agent;
use App\Models\Structure;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CascadeStructureTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateurRh(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['actif' => true]);
        $user->assignRole(RoleName::AGENT_RH->value); // pointage.* + suivi.*
        return $user;
    }

    /** @return array{0: Structure, 1: Agent, 2: Agent} [direction, agentDirection, agentService] */
    private function arbreAvecAgents(): array
    {
        $direction = Structure::create(['code' => 'DRH', 'libelle' => 'Direction RH', 'type' => 'direction', 'actif' => true]);
        $service   = Structure::create(['code' => 'SGC', 'libelle' => 'Service Carrières', 'type' => 'service', 'parent_id' => $direction->id, 'actif' => true]);

        $agentDir = Agent::create(['matricule' => 'DIR001', 'nom' => 'OUEDRAOGO', 'prenoms' => 'Awa', 'sexe' => 'F', 'structure_id' => $direction->id]);
        $agentSvc = Agent::create(['matricule' => 'SVC001', 'nom' => 'KABORE', 'prenoms' => 'Paul', 'sexe' => 'M', 'structure_id' => $service->id]);

        return [$direction, $agentDir, $agentSvc];
    }

    #[Test]
    public function le_pointage_charge_aussi_les_agents_des_services_de_la_structure(): void
    {
        $user = $this->utilisateurRh();
        [$direction, $agentDir, $agentSvc] = $this->arbreAvecAgents();

        $reponse = $this->actingAs($user)->get(route('pointages.index', [
            'structure_id' => $direction->id,
            'date' => '2026-06-29',
        ]));

        $reponse->assertOk()
            ->assertSee('OUEDRAOGO')   // agent de la direction
            ->assertSee('KABORE');     // agent du service rattaché (cascade)
    }

    #[Test]
    public function la_recherche_agents_du_suivi_est_limitee_au_sous_arbre(): void
    {
        $user = $this->utilisateurRh();
        [$direction, $agentDir, $agentSvc] = $this->arbreAvecAgents();

        // Un agent hors de la direction ne doit pas remonter.
        $autre = Structure::create(['code' => 'DAF', 'libelle' => 'Direction Finances', 'type' => 'direction', 'actif' => true]);
        Agent::create(['matricule' => 'OUT001', 'nom' => 'SANGARE', 'prenoms' => 'Ali', 'sexe' => 'M', 'structure_id' => $autre->id]);

        $reponse = $this->actingAs($user)->getJson(route('suivi-dossiers.agents', ['structure_id' => $direction->id]));

        $reponse->assertOk();
        $matricules = collect($reponse->json())->pluck('id')->all();

        $this->assertContains($agentDir->id, $matricules);
        $this->assertContains($agentSvc->id, $matricules); // service rattaché inclus
        $this->assertCount(2, $matricules);                // l'agent d'une autre direction est exclu
    }
}
