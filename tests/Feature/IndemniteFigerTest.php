<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Indemnite;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndemniteFigerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function figer_persiste_la_responsabilite_et_lallocation_et_pas_seulement_les_baremes(): void
    {
        $this->seed(RoleSeeder::class);
        config([
            'gesperes.allocation_familiale.montant_par_enfant' => 2000,
            'gesperes.allocation_familiale.nombre_max_enfants'  => 6,
        ]);

        $user = User::factory()->create(['actif' => true]);
        $user->assignRole(RoleName::AGENT_RH->value); // possède indemnites.manage

        $fonction = Fonction::create(['code' => 'PROV', 'libelle' => 'Proviseur', 'indemnite_responsabilite' => 12000, 'actif' => true]);
        $agent = Agent::create([
            'matricule' => 'FIG001', 'nom' => 'TRAORE', 'prenoms' => 'Adama', 'sexe' => 'M',
            'fonction_id' => $fonction->id, 'nombre_enfants' => 4,
        ]);

        Indemnite::create(['code' => 'RESP', 'libelle' => 'Responsabilité', 'mode' => 'montant_fixe', 'valeur' => 0, 'actif' => true]);
        Indemnite::create(['code' => 'ALLOC', 'libelle' => 'Allocation familiale', 'mode' => 'montant_fixe', 'valeur' => 0, 'actif' => true]);

        $this->actingAs($user)
            ->post(route('agents.indemnites.figer', $agent))
            ->assertRedirect();

        // Responsabilité (12 000) et allocation (4 × 2000 = 8 000) doivent être figées.
        $this->assertDatabaseHas('agent_indemnites', [
            'agent_id' => $agent->id,
            'indemnite_id' => Indemnite::where('code', 'RESP')->value('id'),
            'montant' => 12000,
        ]);
        $this->assertDatabaseHas('agent_indemnites', [
            'agent_id' => $agent->id,
            'indemnite_id' => Indemnite::where('code', 'ALLOC')->value('id'),
            'montant' => 8000,
        ]);
    }
}
