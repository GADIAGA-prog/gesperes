<?php

namespace Tests\Unit;

use App\Enums\LieuExercice;
use App\Models\Agent;
use App\Models\BaremeAstreinte;
use App\Models\BaremeLogement;
use App\Models\BaremeSpecifique;
use App\Models\BaremeTechnicite;
use App\Models\Categorie;
use App\Models\Echelle;
use App\Models\Emploi;
use App\Models\Indice;
use App\Services\PaiePersonnelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaiePersonnelServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function compose_la_ligne_de_paie_grille_plus_indemnites(): void
    {
        $indice = Indice::create(['code' => 'A111', 'valeur' => 638, 'libelle' => 'Test', 'actif' => true]);
        $fonction = \App\Models\Fonction::create(['code' => 'DIR', 'libelle' => 'Directeur', 'indemnite_responsabilite' => 10500, 'actif' => true]);
        $cat = Categorie::create(['code' => 'A', 'libelle' => 'A', 'actif' => true]);
        $echelle = Echelle::create(['code' => 'ECHL1', 'libelle' => 'Échelle 1', 'actif' => true]);
        $emploi = Emploi::create(['code' => 'PROF', 'libelle' => 'Professeur', 'enseignant' => true, 'actif' => true]);

        // Barèmes (décret 2014-427) : les indemnités sont CALCULÉES, pas stockées.
        BaremeLogement::create(['categorie_code' => 'A', 'enseignant' => true, 'en_classe' => true, 'montant' => 69300, 'actif' => true]);
        BaremeTechnicite::create(['echelle_code' => 'A1', 'montant' => 27000, 'actif' => true]);
        // Agent central (sans structure DRESFPT/DPESFPT) → zone urbaine.
        BaremeAstreinte::create(['emploi_code' => 'PROF', 'zone_code' => 'urbaine', 'montant' => 30500, 'actif' => true]);
        BaremeSpecifique::create(['emploi_code' => 'PROF', 'zone_code' => 'urbaine', 'montant' => 15000, 'actif' => true]);

        $agent = Agent::create([
            'matricule' => 'T1', 'nom' => 'TEST', 'prenoms' => 'Agent', 'sexe' => 'M',
            'indice_id' => $indice->id, 'fonction_id' => $fonction->id,
            'categorie_id' => $cat->id, 'echelle_id' => $echelle->id, 'emploi_id' => $emploi->id,
            'lieu_exercice' => LieuExercice::EN_CLASSE->value,
        ]);

        $agent->load(['indice', 'fonction', 'indemnites.indemnite', 'categorie', 'echelle', 'emploi', 'localite.zone', 'structure']);
        $ligne = app(PaiePersonnelService::class)->ligne($agent);

        // Grille (indice 638) — cf. GrilleIndiciaireServiceTest.
        $this->assertSame(123932.0, $ligne['solde']);
        $this->assertSame(12393.0, $ligne['residence']);
        $this->assertSame(16731.0, $ligne['carfo']);

        // Indemnités calculées depuis les barèmes.
        $this->assertSame(69300.0, $ligne['logement']);
        $this->assertSame(27000.0, $ligne['technicite']);
        $this->assertSame(30500.0, $ligne['astreinte']);
        $this->assertSame(15000.0, $ligne['specifique']);
        $this->assertSame(10500.0, $ligne['responsabilite']);

        // Total mensuel = solde + résidence + indemnités (CARFO hors total).
        $this->assertSame(123932.0 + 12393.0 + 10500.0 + 69300.0 + 30500.0 + 15000.0 + 27000.0, $ligne['total_mois']);
        $this->assertSame($ligne['total_mois'] * 12, $ligne['total_annuel']);
    }

    #[Test]
    public function lallocation_familiale_est_calculee_depuis_les_enfants_sans_attribution(): void
    {
        config([
            'gesperes.allocation_familiale.montant_par_enfant' => 2000,
            'gesperes.allocation_familiale.nombre_max_enfants'  => 6,
        ]);

        // Agent avec 3 enfants, AUCUNE indemnité ALLOC figée/attribuée.
        $agent = Agent::create([
            'matricule' => 'AF1', 'nom' => 'NIKIEMA', 'prenoms' => 'Salif', 'sexe' => 'M',
            'nombre_enfants' => 3,
        ]);
        $agent->load(['indice', 'fonction', 'indemnites.indemnite', 'categorie', 'echelle', 'emploi', 'localite.zone', 'structure']);

        $ligne = app(PaiePersonnelService::class)->ligne($agent);

        // 3 × 2 000 = 6 000, pris en compte dans la ligne de paie sans figer.
        $this->assertSame(6000.0, $ligne['allocation']);
        $this->assertSame(6000.0, $ligne['total_mois']);
    }
}
