<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Agent;
use App\Models\Pointage;
use App\Models\Structure;
use App\Models\User;
use App\Services\FichePresenceService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichePresenceTest extends TestCase
{
    use RefreshDatabase;

    private function ligne(array $fiche, string $matricule): array
    {
        return collect($fiche['lignes'])->firstWhere('matricule', $matricule);
    }

    #[Test]
    public function la_fiche_b_liste_tout_leffectif_avec_0_si_pas_dabsence(): void
    {
        $y = (int) now()->year;
        $mois = (int) now()->month;

        $dir = Structure::create(['code' => 'DRH', 'libelle' => 'Direction RH', 'type' => 'direction', 'actif' => true]);
        $svc = Structure::create(['code' => 'SGC', 'libelle' => 'Service Carrières', 'type' => 'service', 'parent_id' => $dir->id, 'actif' => true]);

        $present = Agent::create(['matricule' => 'PRES1', 'nom' => 'A', 'prenoms' => 'x', 'sexe' => 'M', 'structure_id' => $dir->id]);
        $absent  = Agent::create(['matricule' => 'ABS1', 'nom' => 'B', 'prenoms' => 'y', 'sexe' => 'F', 'structure_id' => $svc->id]);

        // 1 jour d'absence pour l'agent du service rattaché.
        Pointage::create([
            'agent_id' => $absent->id, 'structure_id' => $svc->id,
            'date_pointage' => sprintf('%04d-%02d-15', $y, $mois),
            'present' => false, 'duree_jours' => 1, 'duree_heures' => 0,
        ]);

        $fiche = app(FichePresenceService::class)->ficheB($dir->id, $mois, $y);

        // Tout l'effectif (cascade) figure, pas seulement l'absent.
        $this->assertCount(2, $fiche['lignes']);
        $this->assertSame('0', $this->ligne($fiche, 'PRES1')['total_jours']);
        $this->assertSame('0', $this->ligne($fiche, 'PRES1')['total_heures']);
        $this->assertSame('1', $this->ligne($fiche, 'ABS1')['total_jours']);
    }

    #[Test]
    public function la_fiche_c_couvre_tout_le_ministere(): void
    {
        $y = (int) now()->year;
        $mois = (int) now()->month;

        $a = Structure::create(['code' => 'D1', 'libelle' => 'Dir 1', 'type' => 'direction', 'actif' => true]);
        $b = Structure::create(['code' => 'D2', 'libelle' => 'Dir 2', 'type' => 'direction', 'actif' => true]);
        Agent::create(['matricule' => 'M1', 'nom' => 'A', 'prenoms' => 'x', 'sexe' => 'M', 'structure_id' => $a->id]);
        Agent::create(['matricule' => 'M2', 'nom' => 'B', 'prenoms' => 'y', 'sexe' => 'M', 'structure_id' => $b->id]);

        $fiche = app(FichePresenceService::class)->ficheC($mois, $y);

        $this->assertCount(2, $fiche['lignes']); // toutes structures confondues
    }

    #[Test]
    public function la_fiche_d_somme_les_absences_du_trimestre(): void
    {
        $y = (int) now()->year;
        // Trimestre courant et son premier mois.
        $trimestre = (int) ceil(now()->month / 3);
        $premierMois = ($trimestre - 1) * 3 + 1;

        $dir = Structure::create(['code' => 'DRH', 'libelle' => 'Direction RH', 'type' => 'direction', 'actif' => true]);
        $agent = Agent::create(['matricule' => 'TRIM1', 'nom' => 'A', 'prenoms' => 'x', 'sexe' => 'M', 'structure_id' => $dir->id]);

        // Deux absences dans le trimestre : 2 j + 3 j = 5 j.
        Pointage::create(['agent_id' => $agent->id, 'structure_id' => $dir->id, 'date_pointage' => sprintf('%04d-%02d-05', $y, $premierMois), 'present' => false, 'duree_jours' => 2, 'duree_heures' => 0]);
        Pointage::create(['agent_id' => $agent->id, 'structure_id' => $dir->id, 'date_pointage' => sprintf('%04d-%02d-20', $y, $premierMois), 'present' => false, 'duree_jours' => 3, 'duree_heures' => 0]);

        $fiche = app(FichePresenceService::class)->ficheD($dir->id, $trimestre, $y);

        $this->assertSame('5', $this->ligne($fiche, 'TRIM1')['total_jours']);
    }

    #[Test]
    public function le_pdf_de_la_fiche_d_se_genere(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create(['actif' => true]);
        $user->assignRole(RoleName::AGENT_RH->value); // presence.reports

        $dir = Structure::create(['code' => 'DRH', 'libelle' => 'Direction RH', 'type' => 'direction', 'actif' => true]);
        Agent::create(['matricule' => 'PDF1', 'nom' => 'A', 'prenoms' => 'x', 'sexe' => 'M', 'structure_id' => $dir->id]);

        $this->actingAs($user)
            ->get(route('fiches.d', ['structure_id' => $dir->id, 'trimestre' => 1, 'annee' => now()->year]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
