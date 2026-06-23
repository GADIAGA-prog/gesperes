<?php

namespace Tests\Unit;

use App\Models\Action;
use App\Models\Agent;
use App\Models\EnveloppePersonnel;
use App\Models\Indice;
use App\Models\Programme;
use App\Models\Structure;
use App\Services\VentilationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VentilationServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function la_ventilation_somme_a_la_cible_par_exercice(): void
    {
        $prog = Programme::create(['code' => '104', 'libelle' => 'Pilotage', 'actif' => true]);
        $a1 = Action::create(['code' => '10401', 'libelle' => 'A1', 'programme_id' => $prog->id, 'actif' => true]);
        $a2 = Action::create(['code' => '10402', 'libelle' => 'A2', 'programme_id' => $prog->id, 'actif' => true]);

        $s1 = Structure::create(['code' => 'S1', 'libelle' => 'S1', 'type' => 'direction', 'action_id' => $a1->id, 'actif' => true]);
        $s2 = Structure::create(['code' => 'S2', 'libelle' => 'S2', 'type' => 'direction', 'action_id' => $a2->id, 'actif' => true]);

        $i1 = Indice::create(['code' => 'I1', 'valeur' => 600, 'libelle' => 'I1', 'actif' => true]);
        $i2 = Indice::create(['code' => 'I2', 'valeur' => 1200, 'libelle' => 'I2', 'actif' => true]);

        Agent::create(['matricule' => 'A1', 'nom' => 'X', 'prenoms' => 'Y', 'sexe' => 'M', 'structure_id' => $s1->id, 'indice_id' => $i1->id]);
        Agent::create(['matricule' => 'A2', 'nom' => 'X', 'prenoms' => 'Y', 'sexe' => 'M', 'structure_id' => $s2->id, 'indice_id' => $i2->id]);

        $env = EnveloppePersonnel::create(['annee_debut' => 2027, 'intitule' => 'E', 'actif' => true]);
        $env->lignes()->create(['libelle' => 'Salaire du personnel en activité', 'montant_n1' => 1000000, 'montant_n2' => 2000000, 'montant_n3' => 3000000]);
        $env->load('lignes');

        $v = app(VentilationService::class)->ventiler($env);

        // Le total ventilé = la cible (à l'arrondi près).
        $this->assertEqualsWithDelta(1000000, $v['totaux'][0], 5);
        $this->assertEqualsWithDelta(2000000, $v['totaux'][1], 5);
        $this->assertEqualsWithDelta(3000000, $v['totaux'][2], 5);

        // Deux actions × (661 + 663 + 664) = 6 lignes. Le 663 existe via la
        // résidence (solde × 10 %), même sans indemnité ; pas de 666 (pas d'allocation).
        $this->assertCount(6, $v['lignes']);

        // L'action 2 (indice double) pèse deux fois l'action 1 sur le paragraphe 661.
        $p661 = collect($v['lignes'])->where('paragraphe', 661)->keyBy('action_code');
        $this->assertEqualsWithDelta(2.0, $p661['10402']['montants'][0] / $p661['10401']['montants'][0], 0.01);
    }

    #[Test]
    public function le_tableau_annexe_calcule_totaux_carfo_patronal_et_provisions(): void
    {
        $prog = Programme::create(['code' => '104', 'libelle' => 'Pilotage', 'actif' => true]);
        $action = Action::create(['code' => '10401', 'libelle' => 'A', 'programme_id' => $prog->id, 'actif' => true]);
        $structure = Structure::create(['code' => 'S1', 'libelle' => 'DRH', 'type' => 'direction', 'action_id' => $action->id, 'actif' => true]);
        $indice = Indice::create(['code' => 'I1', 'valeur' => 600, 'libelle' => 'I', 'actif' => true]);

        Agent::create(['matricule' => 'A1', 'nom' => 'X', 'prenoms' => 'Y', 'sexe' => 'M', 'structure_id' => $structure->id, 'indice_id' => $indice->id]);

        $a = app(VentilationService::class)->tableauAnnexe();
        $t = $a['totaux'];
        $p = $a['provisions'];

        // SI = indice × point (2331) ; IR = 10 % ; CARFO patronal = 13,5 %.
        $this->assertSame(600 * 2331.0, $t['si']);
        $this->assertEqualsWithDelta($t['si'] * 0.10, $t['ir'], 0.01);
        $this->assertEqualsWithDelta($t['si'] * 0.135, $t['carfo'], 0.01);

        // BA = SI + IR + CM + IT + CARFO ; f = BA × 3% ; g = (BA+f)×3% + f.
        $ba = $t['si'] + $t['ir'] + $t['cm'] + $t['tech'] + $t['carfo'];
        $this->assertEqualsWithDelta($ba, $p['ba'], 0.01);
        $this->assertEqualsWithDelta($ba * 0.03, $p['f'], 0.01);
        $this->assertEqualsWithDelta(($ba + $p['f']) * 0.03 + $p['f'], $p['g'], 0.01);

        // Total général TG1 = T1 + f + I1 (ici AF = 0 donc I1 = 0).
        $this->assertEqualsWithDelta($p['t1'] + $p['f'], $p['tg1'], 0.01);
    }
}
