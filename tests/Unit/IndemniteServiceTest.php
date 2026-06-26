<?php

namespace Tests\Unit;

use App\Enums\LieuExercice;
use App\Models\Agent;
use App\Models\BaremeLogement;
use App\Models\BaremeTechnicite;
use App\Models\Categorie;
use App\Models\Echelle;
use App\Models\Emploi;
use App\Models\Indemnite;
use App\Models\Indice;
use App\Models\Structure;
use App\Models\Zone;
use App\Services\IndemniteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndemniteServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function calcule_montant_fixe_et_pourcentage(): void
    {
        config(['grille.point_annuel' => 2331, 'grille.mois_par_an' => 12]);

        // Salaire indiciaire = 300 × 2331 / 12 = 58 275
        $indice = Indice::create(['code' => 'IDX300', 'valeur' => 300, 'actif' => true]);
        $agent = Agent::create([
            'matricule' => '30000001', 'nom' => 'KABORE', 'prenoms' => 'Paul', 'sexe' => 'M',
            'indice_id' => $indice->id,
        ]);

        $fixe = Indemnite::create(['code' => 'LOG', 'libelle' => 'Logement', 'mode' => 'montant_fixe', 'valeur' => 20000, 'actif' => true]);
        $pct  = Indemnite::create(['code' => 'SUJ', 'libelle' => 'Sujétion', 'mode' => 'pourcentage', 'valeur' => 10, 'actif' => true]);

        $service = new IndemniteService();

        $this->assertSame(20000.0, $service->calculer($agent, $fixe));
        $this->assertEqualsWithDelta(5827.5, $service->calculer($agent, $pct), 0.01);
    }

    #[Test]
    public function calcule_un_bareme_de_technicite_selon_l_echelle(): void
    {
        // Le barème de technicité est codé « catégorie + n° d'échelle » : A + ECHL1 = A1.
        $cat = Categorie::create(['code' => 'A', 'libelle' => 'A', 'actif' => true]);
        $echelle = Echelle::create(['code' => 'ECHL1', 'libelle' => 'Échelle 1', 'actif' => true]);
        BaremeTechnicite::create(['echelle_code' => 'A1', 'montant' => 27000, 'actif' => true]);

        $agent = Agent::create([
            'matricule' => '30000002', 'nom' => 'OUEDRAOGO', 'prenoms' => 'Awa', 'sexe' => 'F',
            'categorie_id' => $cat->id, 'echelle_id' => $echelle->id,
        ]);

        $tech = Indemnite::create(['code' => 'TECH', 'libelle' => 'Technicité', 'mode' => 'bareme', 'bareme' => 'technicite', 'valeur' => 0, 'actif' => true]);

        $this->assertSame(27000.0, (new IndemniteService())->calculer($agent, $tech));
    }

    #[Test]
    public function le_logement_depend_de_la_categorie_de_l_enseignement_et_du_lieu(): void
    {
        $cat = Categorie::create(['code' => 'A', 'libelle' => 'A', 'actif' => true]);
        $prof = Emploi::create(['code' => 'PROF', 'libelle' => 'Professeur', 'enseignant' => true, 'actif' => true]);
        $admin = Emploi::create(['code' => 'ADM', 'libelle' => 'Administratif', 'enseignant' => false, 'actif' => true]);

        // Barème : (catégorie, enseignant, en_classe) → montant.
        BaremeLogement::create(['categorie_code' => 'A', 'enseignant' => true, 'en_classe' => true, 'montant' => 69300, 'actif' => true]);
        BaremeLogement::create(['categorie_code' => 'A', 'enseignant' => true, 'en_classe' => false, 'montant' => 55000, 'actif' => true]);
        BaremeLogement::create(['categorie_code' => 'A', 'enseignant' => false, 'en_classe' => false, 'montant' => 50000, 'actif' => true]);

        $service = new IndemniteService();

        // Enseignant en classe → 69 300.
        $enClasse = Agent::create(['matricule' => 'L1', 'nom' => 'A', 'prenoms' => 'B', 'sexe' => 'M',
            'categorie_id' => $cat->id, 'emploi_id' => $prof->id, 'lieu_exercice' => LieuExercice::EN_CLASSE->value]);
        $this->assertSame(69300.0, $service->logement($enClasse));

        // Enseignant au bureau → 55 000.
        $auBureau = Agent::create(['matricule' => 'L2', 'nom' => 'A', 'prenoms' => 'B', 'sexe' => 'M',
            'categorie_id' => $cat->id, 'emploi_id' => $prof->id, 'lieu_exercice' => LieuExercice::AU_BUREAU->value]);
        $this->assertSame(55000.0, $service->logement($auBureau));

        // Non-enseignant au bureau → 50 000.
        $nonEns = Agent::create(['matricule' => 'L3', 'nom' => 'A', 'prenoms' => 'B', 'sexe' => 'M',
            'categorie_id' => $cat->id, 'emploi_id' => $admin->id, 'lieu_exercice' => LieuExercice::AU_BUREAU->value]);
        $this->assertSame(50000.0, $service->logement($nonEns));
    }

    #[Test]
    public function la_zone_est_heritee_de_la_structure_par_cascade(): void
    {
        $semi = Zone::create(['code' => 'semi_urbaine', 'libelle' => 'Semi-urbaine', 'actif' => true]);
        $region = Structure::create(['code' => 'BNK', 'libelle' => 'Bankui', 'type' => 'direction', 'zone_id' => $semi->id]);
        $service = Structure::create(['code' => 'SVC', 'libelle' => 'Service X', 'type' => 'service', 'parent_id' => $region->id]);

        $agent = Agent::create(['matricule' => 'Z1', 'nom' => 'A', 'prenoms' => 'B', 'sexe' => 'M', 'structure_id' => $service->id]);

        // La structure de l'agent n'a pas de zone, mais sa direction parente oui.
        $this->assertSame('semi_urbaine', (new IndemniteService())->zonePour($agent->load('structure')));
    }

    #[Test]
    public function zone_urbaine_par_defaut_pour_l_administration_centrale(): void
    {
        $central = Structure::create(['code' => 'SG', 'libelle' => 'Secrétariat général', 'type' => 'direction']);
        $agent = Agent::create(['matricule' => 'Z2', 'nom' => 'A', 'prenoms' => 'B', 'sexe' => 'M', 'structure_id' => $central->id]);

        $this->assertSame('urbaine', (new IndemniteService())->zonePour($agent->load('structure')));
    }
}
