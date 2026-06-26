<?php

namespace App\Services;

use App\Enums\Sexe;
use App\Enums\TypeStructure;
use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Structure;
use Illuminate\Support\Str;

/**
 * Logique métier des structures : notamment la nomination automatique du
 * responsable. Lorsqu'on désigne un responsable de structure/service, l'agent
 * est d'office affecté à la structure et nommé selon le type de structure et
 * son genre (Directeur/Directrice, Chef/Cheffe de service).
 */
class StructureService
{
    /**
     * Affecte le responsable à sa structure et le nomme (fonction genrée).
     * Sans responsable, ne fait rien.
     */
    public function synchroniserResponsable(Structure $structure): void
    {
        if (! $structure->responsable_agent_id) {
            return;
        }

        $agent = Agent::find($structure->responsable_agent_id);
        if (! $agent) {
            return;
        }

        $fonction = $this->fonctionResponsable($structure->type, $agent->sexe);

        // Nomination + affectation d'office.
        $agent->fonction_id = $fonction->id;
        $agent->structure_id = $structure->id;

        // Géographie héritée de la structure (même règle qu'à l'affectation).
        $structure->loadMissing('localite');
        if ($structure->region_id) {
            $agent->region_id = $structure->region_id;
            $agent->region = $structure->region;
        }
        if ($structure->province_id) {
            $agent->province_id = $structure->province_id;
            $agent->province = $structure->province;
        }
        if ($structure->localite_id) {
            $agent->localite_id = $structure->localite_id;
            $agent->commune = $structure->localite?->libelle;
        }

        $agent->save();
    }

    /**
     * Fonction (genrée) du responsable selon le type de structure :
     *  - Service              → Chef de service / Cheffe de service
     *  - autres (Direction…)  → Directeur / Directrice
     *
     * La variante féminine est créée à la volée en héritant de l'indemnité de
     * responsabilité de la fonction de base, pour ne pas perdre la prime.
     */
    private function fonctionResponsable(?TypeStructure $type, ?Sexe $sexe): Fonction
    {
        $feminin = $sexe === Sexe::F;

        [$baseLibelle, $libelleGenre] = $type === TypeStructure::SERVICE
            ? ['Chef de service', $feminin ? 'Cheffe de service' : 'Chef de service']
            : ['Directeur', $feminin ? 'Directrice' : 'Directeur'];

        $base = Fonction::where('libelle', $baseLibelle)->first();

        if ($libelleGenre === $baseLibelle && $base) {
            return $base;
        }

        return Fonction::firstOrCreate(
            ['libelle' => $libelleGenre],
            [
                'code'  => $this->code($libelleGenre),
                'indemnite_responsabilite' => $base?->indemnite_responsabilite ?? 0,
                'actif' => true,
            ],
        );
    }

    private function code(string $libelle): string
    {
        return (string) Str::of($libelle)->ascii()->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')->trim('_')->limit(40, '');
    }
}
