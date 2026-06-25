<?php

namespace Tests\Feature;

use App\Enums\TypeStructure;
use App\Models\Structure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le filtre « cascade » (budget personnel) doit inclure une structure ET tous
 * ses services descendants : filtrer la DRH ramène aussi les agents de ses
 * sous-services (ex. gestion des carrières).
 */
class StructureSousArbreTest extends TestCase
{
    use RefreshDatabase;

    public function test_sous_arbre_inclut_la_racine_et_toutes_les_descendantes(): void
    {
        $drh = Structure::create(['code' => 'DRH', 'libelle' => 'DRH', 'type' => TypeStructure::DIRECTION->value]);
        $carrieres = Structure::create(['code' => 'CAR', 'libelle' => 'Gestion des carrières', 'type' => TypeStructure::SERVICE->value, 'parent_id' => $drh->id]);
        $paie = Structure::create(['code' => 'PAI', 'libelle' => 'Paie', 'type' => TypeStructure::SERVICE->value, 'parent_id' => $drh->id]);
        $bureau = Structure::create(['code' => 'BUR', 'libelle' => 'Bureau carrières', 'type' => TypeStructure::SERVICE->value, 'parent_id' => $carrieres->id]);

        // Une autre branche, qui ne doit PAS remonter.
        $autre = Structure::create(['code' => 'DAF', 'libelle' => 'DAF', 'type' => TypeStructure::DIRECTION->value]);

        $ids = Structure::sousArbreIds($drh->id);

        sort($ids);
        $attendu = [$drh->id, $carrieres->id, $paie->id, $bureau->id];
        sort($attendu);

        $this->assertSame($attendu, $ids);
        $this->assertNotContains($autre->id, $ids);
    }

    public function test_sous_arbre_vide_si_aucune_structure(): void
    {
        $this->assertSame([], Structure::sousArbreIds(null));
        $this->assertSame([], Structure::sousArbreIds(0));
    }
}
