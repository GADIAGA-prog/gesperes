<?php

namespace App\Services;

use App\Enums\TypeEvenementCarriere;
use App\Models\Agent;
use App\Models\Categorie;
use App\Models\CarriereEvenement;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Fonction;
use App\Models\Indice;
use App\Models\PositionAdministrative;
use App\Models\Poste;
use Illuminate\Support\Facades\DB;

/**
 * Enregistre les actes de carrière d'un agent (avancement, promotion, nomination,
 * changement de position) : historise l'état avant/après, recalcule l'indice depuis
 * la grille (catégorie × échelle × classe × échelon) et la date de retraite (R7),
 * puis met à jour la situation courante de l'agent — le tout dans une transaction.
 */
class CarriereService
{
    public function __construct(private RetraiteService $retraite) {}

    /** Dimensions de carrière : champ agent => [colonne_ancien, colonne_nouveau, clé_formulaire]. */
    private const DIMENSIONS = [
        'categorie_id'                 => ['ancienne_categorie_id', 'nouvelle_categorie_id', 'nouvelle_categorie_id'],
        'echelle_id'                   => ['ancienne_echelle_id', 'nouvelle_echelle_id', 'nouvelle_echelle_id'],
        'classe_id'                    => ['ancienne_classe_id', 'nouvelle_classe_id', 'nouvelle_classe_id'],
        'echelon_id'                   => ['ancien_echelon_id', 'nouvel_echelon_id', 'nouvel_echelon_id'],
        'fonction_id'                  => ['ancienne_fonction_id', 'nouvelle_fonction_id', 'nouvelle_fonction_id'],
        'poste_id'                     => ['ancien_poste_id', 'nouveau_poste_id', 'nouveau_poste_id'],
        'position_administrative_id'   => ['ancienne_position_id', 'nouvelle_position_id', 'nouvelle_position_id'],
    ];

    public function enregistrer(Agent $agent, array $data, ?int $userId = null): CarriereEvenement
    {
        $type = TypeEvenementCarriere::from($data['type']);

        $evt = [
            'agent_id'   => $agent->id,
            'type'       => $type->value,
            'date_effet' => $data['date_effet'],
        ];

        // État avant/après de chaque dimension (valeur courante si non modifiée).
        $nouveau = [];
        foreach (self::DIMENSIONS as $champ => [$colAncien, $colNouveau, $cle]) {
            $ancien = $agent->{$champ};
            $valeur = isset($data[$cle]) && $data[$cle] !== '' ? (int) $data[$cle] : $ancien;
            $evt[$colAncien]   = $ancien;
            $evt[$colNouveau]  = $valeur;
            $nouveau[$champ]   = $valeur;
        }

        // Recalcul automatique de l'indice depuis la grille.
        $nouvelIndice = $this->indicePour(
            $nouveau['categorie_id'], $nouveau['echelle_id'], $nouveau['classe_id'], $nouveau['echelon_id']
        ) ?? $agent->indice_id;
        $evt['ancien_indice_id'] = $agent->indice_id;
        $evt['nouvel_indice_id'] = $nouvelIndice;
        $nouveau['indice_id']    = $nouvelIndice;

        $evt['reference_acte'] = $data['reference_acte'] ?? null;
        $evt['observation']    = $data['observation'] ?? null;
        $evt['description']    = $this->resume($evt);
        $evt['created_by']     = $userId;

        return DB::transaction(function () use ($agent, $evt, $nouveau, $type, $data) {
            $evenement = CarriereEvenement::create($evt);

            $maj = $nouveau;
            if ($type === TypeEvenementCarriere::NOMINATION) {
                $maj['date_nomination'] = $data['date_effet'];
            }

            // R7 : la date de retraite dépend de la catégorie → recalcul si elle change.
            if ($evt['ancienne_categorie_id'] !== $evt['nouvelle_categorie_id']) {
                $code = Categorie::find($nouveau['categorie_id'])?->code;
                $maj['date_retraite'] = $this->retraite
                    ->dateRetraite($agent->date_naissance, $code)?->toDateString();
            }

            $agent->update($maj);

            return $evenement;
        });
    }

    /** Recherche l'indice correspondant au quadruplet de la grille. */
    private function indicePour(?int $categorie, ?int $echelle, ?int $classe, ?int $echelon): ?int
    {
        if (! ($categorie && $echelle && $classe && $echelon)) {
            return null;
        }

        return Indice::where('categorie_id', $categorie)
            ->where('echelle_id', $echelle)
            ->where('classe_id', $classe)
            ->where('echelon_id', $echelon)
            ->value('id');
    }

    /** Construit un résumé lisible des changements (dimensions réellement modifiées). */
    private function resume(array $evt): string
    {
        $dims = [
            ['Catégorie', Categorie::class, 'code', 'ancienne_categorie_id', 'nouvelle_categorie_id'],
            ['Échelle', Echelle::class, 'libelle', 'ancienne_echelle_id', 'nouvelle_echelle_id'],
            ['Classe', Classe::class, 'libelle', 'ancienne_classe_id', 'nouvelle_classe_id'],
            ['Échelon', Echelon::class, 'libelle', 'ancien_echelon_id', 'nouvel_echelon_id'],
            ['Indice', Indice::class, 'valeur', 'ancien_indice_id', 'nouvel_indice_id'],
            ['Fonction', Fonction::class, 'libelle', 'ancienne_fonction_id', 'nouvelle_fonction_id'],
            ['Poste', Poste::class, 'libelle', 'ancien_poste_id', 'nouveau_poste_id'],
            ['Position', PositionAdministrative::class, 'libelle', 'ancienne_position_id', 'nouvelle_position_id'],
        ];

        $parts = [];
        foreach ($dims as [$label, $classe, $attr, $cleAncien, $cleNouveau]) {
            if (($evt[$cleAncien] ?? null) === ($evt[$cleNouveau] ?? null)) {
                continue;
            }
            $ancien = $evt[$cleAncien] ? ($classe::find($evt[$cleAncien])?->{$attr} ?? '—') : '—';
            $nouveau = $evt[$cleNouveau] ? ($classe::find($evt[$cleNouveau])?->{$attr} ?? '—') : '—';
            $parts[] = "{$label} : {$ancien} → {$nouveau}";
        }

        return implode(' ; ', $parts);
    }
}
