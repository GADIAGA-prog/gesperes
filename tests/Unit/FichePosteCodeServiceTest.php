<?php

namespace Tests\Unit;

use App\Enums\PositionHierarchique;
use App\Enums\TypePoste;
use App\Models\EmploiType;
use App\Models\FamilleProfessionnelle;
use App\Models\FichePoste;
use App\Services\FichePosteCodeService;
use Tests\TestCase;

class FichePosteCodeServiceTest extends TestCase
{
    private FichePosteCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FichePosteCodeService();
    }

    public function test_sigle_multi_mots_donne_les_initiales(): void
    {
        $this->assertSame('CPR', $this->service->sigle('Chargé de la planification du recrutement'));
        $this->assertSame('DGFP', $this->service->sigle('Directeur général de la Fonction publique'));
        $this->assertSame('AL', $this->service->sigle('Agent de liaison'));
    }

    public function test_sigle_mot_unique_donne_les_quatre_premieres_lettres(): void
    {
        $this->assertSame('COND', $this->service->sigle('Conducteur'));
    }

    public function test_code_operationnel_complet(): void
    {
        $fiche = new FichePoste([
            'intitule' => 'Chargé de la planification du recrutement',
            'type_poste' => TypePoste::OPERATIONNEL->value,
            'position_hierarchique' => PositionHierarchique::AGENT->value,
        ]);
        $fiche->setRelation('familleProfessionnelle', new FamilleProfessionnelle(['code' => 'GRH']));
        $fiche->setRelation('emploiType', new EmploiType(['code' => 'RT']));

        $this->assertSame('GRH-RT-CPR-4', $this->service->generer($fiche));
    }

    public function test_code_fonction_avec_position(): void
    {
        $fiche = new FichePoste([
            'intitule' => 'Directeur général de la Fonction publique',
            'type_poste' => TypePoste::FONCTION->value,
            'position_hierarchique' => PositionHierarchique::DG->value,
        ]);
        $fiche->setRelation('familleProfessionnelle', new FamilleProfessionnelle(['code' => 'GRH']));

        $this->assertSame('GRH-DGFP-1', $this->service->generer($fiche));
    }

    public function test_code_soutien(): void
    {
        $fiche = new FichePoste([
            'intitule' => 'Conducteur',
            'type_poste' => TypePoste::SOUTIEN->value,
            'position_hierarchique' => PositionHierarchique::AGENT->value,
        ]);

        $this->assertSame('COND-4', $this->service->generer($fiche));
    }
}
