<?php

namespace Tests\Unit;

use App\Enums\CategoriePosition;
use App\Models\Agent;
use App\Models\PositionAdministrative;
use App\Services\MouvementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MouvementServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sortie_definitive_met_a_jour_la_position_et_desactive_l_agent(): void
    {
        $enPoste = PositionAdministrative::create(['code' => 'ENPOSTE', 'libelle' => 'En poste', 'categorie' => CategoriePosition::ACTIVITE->value, 'actif' => true]);
        $retraite = PositionAdministrative::create(['code' => 'RETR', 'libelle' => 'Retraite', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value, 'actif' => true]);

        $agent = Agent::create([
            'matricule' => '20000001', 'nom' => 'SAWADOGO', 'prenoms' => 'Ali', 'sexe' => 'M',
            'position_administrative_id' => $enPoste->id,
        ]);
        $this->assertTrue($agent->est_actif);

        $mouvement = (new MouvementService())->enregistrer($agent, [
            'nouvelle_position_id' => $retraite->id,
            'date_effet'           => '2026-01-01',
            'reference_acte'       => 'ARR-2026-001',
        ], null);

        $agent->refresh()->load('positionAdministrative');
        $this->assertSame($retraite->id, $agent->position_administrative_id);
        $this->assertFalse($agent->est_actif, "Une sortie définitive doit retirer l'agent de l'effectif actif.");
        $this->assertSame($enPoste->id, $mouvement->ancienne_position_id);
    }

    #[Test]
    public function un_agent_repris_quitte_la_liste_des_sorties_temporaires(): void
    {
        $enPoste = PositionAdministrative::create(['code' => 'ENPOSTE', 'libelle' => 'En poste', 'categorie' => CategoriePosition::ACTIVITE->value, 'actif' => true]);
        $dispo   = PositionAdministrative::create(['code' => 'DISPO', 'libelle' => 'Disponibilité', 'categorie' => CategoriePosition::SORTIE_TEMPORAIRE->value, 'actif' => true]);

        $service = new MouvementService();

        // Agent repris : sortie puis réintégration en activité → ne doit plus
        // figurer dans la situation des sorties temporaires.
        $repris = Agent::create(['matricule' => '20000010', 'nom' => 'OUEDRAOGO', 'prenoms' => 'Awa', 'sexe' => 'F', 'position_administrative_id' => $enPoste->id]);
        $service->enregistrer($repris, ['nouvelle_position_id' => $dispo->id, 'date_effet' => '2024-01-01', 'date_fin' => '2025-01-01'], null);
        $service->enregistrer($repris, ['nouvelle_position_id' => $enPoste->id, 'date_effet' => '2024-12-15'], null);

        // Agent dont la fin prévue est imminente (alerte) et non repris.
        $enAlerte = Agent::create(['matricule' => '20000011', 'nom' => 'KABORE', 'prenoms' => 'Issa', 'sexe' => 'M', 'position_administrative_id' => $enPoste->id]);
        $service->enregistrer($enAlerte, ['nouvelle_position_id' => $dispo->id, 'date_effet' => now()->subYear()->toDateString(), 'date_fin' => now()->addMonth()->toDateString()], null);

        $page = Agent::query()
            ->whereHas('positionAdministrative', fn ($q) => $q->where('categorie', CategoriePosition::SORTIE_TEMPORAIRE->value))
            ->with(['positionAdministrative', 'dernierMouvement'])
            ->paginate(30);

        $service->decorerSortiesTemporaires($page);

        $ids = collect($page->items())->pluck('id');
        $this->assertNotContains($repris->id, $ids, "L'agent repris (position courante = activité) ne doit pas apparaître en sortie temporaire.");
        $this->assertContains($enAlerte->id, $ids);

        $sortieAlerte = collect($page->items())->firstWhere('id', $enAlerte->id);
        $this->assertNull($sortieAlerte->date_reprise);
        $this->assertTrue($sortieAlerte->en_alerte, 'Une fin prévue dans moins de 2 mois doit déclencher l\'alerte.');
        $this->assertSame('1 an 1 mois', $sortieAlerte->duree_libelle);
    }

    #[Test]
    public function la_situation_definitive_alerte_les_retraites_de_l_annee_en_cours(): void
    {
        $enPoste  = PositionAdministrative::create(['code' => 'ENPOSTE', 'libelle' => 'En poste', 'categorie' => CategoriePosition::ACTIVITE->value, 'actif' => true]);
        $retraite = PositionAdministrative::create(['code' => 'RETR', 'libelle' => 'Retraite', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value, 'actif' => true]);
        $deces    = PositionAdministrative::create(['code' => 'DECES', 'libelle' => 'Décès', 'categorie' => CategoriePosition::SORTIE_DEFINITIVE->value, 'actif' => true]);

        $service = new MouvementService();

        $retraiteAnnee = Agent::create(['matricule' => '20000020', 'nom' => 'ZONGO', 'prenoms' => 'Paul', 'sexe' => 'M', 'position_administrative_id' => $enPoste->id]);
        $service->enregistrer($retraiteAnnee, ['nouvelle_position_id' => $retraite->id, 'date_effet' => now()->startOfYear()->addMonths(2)->toDateString()], null);

        $deceseAgent = Agent::create(['matricule' => '20000021', 'nom' => 'SORE', 'prenoms' => 'Marie', 'sexe' => 'F', 'position_administrative_id' => $enPoste->id]);
        $service->enregistrer($deceseAgent, ['nouvelle_position_id' => $deces->id, 'date_effet' => now()->toDateString()], null);

        $page = Agent::query()
            ->whereHas('positionAdministrative', fn ($q) => $q->where('categorie', CategoriePosition::SORTIE_DEFINITIVE->value))
            ->with(['positionAdministrative', 'dernierMouvement'])
            ->paginate(30);

        $service->decorerSortiesDefinitives($page);

        $r = collect($page->items())->firstWhere('id', $retraiteAnnee->id);
        $d = collect($page->items())->firstWhere('id', $deceseAgent->id);

        $this->assertTrue($r->en_alerte, 'Un retraité de l\'année en cours doit être en alerte.');
        $this->assertFalse($d->en_alerte, 'Un décès n\'est pas concerné par l\'alerte retraite.');
    }
}
