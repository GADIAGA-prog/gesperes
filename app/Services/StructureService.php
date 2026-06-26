<?php

namespace App\Services;

use App\Enums\TypeStructure;
use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Structure;
use Illuminate\Support\Str;

/**
 * Logique métier des structures : nomination automatique du responsable.
 *
 * Dès qu'un responsable est désigné pour une structure, l'agent est d'office
 * affecté à la structure et nommé à la fonction du décret 2014-427 correspondant
 * au niveau de la structure (l'indemnité de responsabilité suit la fonction) :
 *   - Secrétariat général → Secrétaire général          (60 000)
 *   - Cabinet             → Directeur de cabinet         (80 000)
 *   - Direction (région, province, direction centrale) → Directeur central (18 500)
 *   - Service / commune   → Chef de service nommé par arrêté (10 500)
 */
class StructureService
{
    public function synchroniserResponsable(Structure $structure): void
    {
        if (! $structure->responsable_agent_id) {
            return;
        }

        $agent = Agent::find($structure->responsable_agent_id);
        if (! $agent) {
            return;
        }

        $fonction = $this->fonctionResponsable($structure);

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

    /** Fonction (décret 2014-427) du responsable selon la structure. */
    private function fonctionResponsable(Structure $structure): Fonction
    {
        [$libelle, $montantDefaut] = $this->cibleFonction($structure);

        $fonction = Fonction::whereRaw('LOWER(libelle) = ?', [mb_strtolower($libelle)])->first();
        if ($fonction) {
            return $fonction;
        }

        // La fonction du décret devrait exister (seeder) ; sinon on la crée avec le montant officiel.
        return Fonction::create([
            'code' => $this->code($libelle),
            'libelle' => $libelle,
            'indemnite_responsabilite' => $montantDefaut,
            'actif' => true,
        ]);
    }

    /** @return array{0:string,1:int} libellé de fonction, montant par défaut (décret). */
    private function cibleFonction(Structure $structure): array
    {
        $nom = (string) Str::of($structure->libelle)->ascii()->lower()->squish();

        return match (true) {
            $nom === 'cabinet'              => ['Directeur de cabinet', 80000],
            $nom === 'secretariat general'  => ['Secrétaire général', 60000],
            $structure->type === TypeStructure::SERVICE => ['Chef de service nommé par arrêté', 10500],
            default                         => ['Directeur central', 18500], // Direction (région, province, centrale)
        };
    }

    private function code(string $libelle): string
    {
        return (string) Str::of($libelle)->ascii()->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '_')->trim('_')->limit(40, '');
    }
}
