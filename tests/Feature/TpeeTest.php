<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Emploi;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TpeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    #[Test]
    public function la_saisie_des_previsions_est_enregistree(): void
    {
        $y = (int) now()->year;
        $user = User::factory()->create(['actif' => true]);
        $user->assignRole(RoleName::AGENT_RH->value); // tpee.manage
        $emploi = Emploi::create(['code' => 'PROF', 'libelle' => 'Professeur', 'actif' => true]);

        $this->actingAs($user)->post(route('outils-grh.tpee.store'), [
            'lignes' => [
                $emploi->id => [
                    $y => ['entrees' => 8, 'cible' => 25],
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('previsions_effectifs', [
            'emploi_id'       => $emploi->id,
            'annee'           => $y,
            'structure_id'    => null,
            'entrees_prevues' => 8,
            'effectif_cible'  => 25,
        ]);
    }

    #[Test]
    public function la_saisie_est_interdite_sans_permission_de_gestion(): void
    {
        $y = (int) now()->year;
        $user = User::factory()->create(['actif' => true]);
        $user->assignRole(RoleName::CONSULTATION->value); // pas de tpee.manage
        $emploi = Emploi::create(['code' => 'PROF', 'libelle' => 'Professeur', 'actif' => true]);

        $this->actingAs($user)->post(route('outils-grh.tpee.store'), [
            'lignes' => [$emploi->id => [$y => ['entrees' => 8]]],
        ])->assertForbidden();
    }

    #[Test]
    public function la_consultation_du_tpee_est_accessible_avec_gpec_view(): void
    {
        $user = User::factory()->create(['actif' => true]);
        $user->assignRole(RoleName::CONSULTATION->value); // a gpec.view

        $this->actingAs($user)->get(route('outils-grh.tpee'))->assertOk();
    }

    #[Test]
    public function lexport_pdf_du_tpee_se_genere(): void
    {
        Emploi::create(['code' => 'PROF', 'libelle' => 'Professeur', 'actif' => true]);
        $user = User::factory()->create(['actif' => true]);
        $user->assignRole(RoleName::CONSULTATION->value);

        $this->actingAs($user)->get(route('outils-grh.tpee.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
