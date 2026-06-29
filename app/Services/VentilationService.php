<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\EnveloppePersonnel;
use App\Models\Structure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ventile l'enveloppe de référence (ligne « Salaire du personnel en activité »)
 * sur les actions budgétaires et les paragraphes de dépense, au prorata de la
 * masse salariale réellement calculée (Dépenses du personnel) :
 *
 *   661 Traitements et salaires      = solde indiciaire
 *   663 Primes et indemnités         = résidence + RESP + LOG + ASTR + SPEC + TECH
 *   664 Cotisations sociales (CARFO) = 8 % du solde
 *   666 Prestations sociales         = allocation familiale
 *
 * Tout est annualisé (× 12 pour les montants mensuels).
 */
class VentilationService
{
    public function __construct(private IndemniteService $indemnites) {}

    public const PARAGRAPHES = [
        661 => 'Traitements et salaires',
        663 => 'Primes et indemnités',
        664 => 'Cotisations sociales (CARFO)',
        666 => 'Prestations sociales',
    ];

    /**
     * Codes d'indemnités rattachés au paragraphe 663 (primes et indemnités).
     * La responsabilité (RESP) n'est PAS ici : elle vient de la fonction
     * (fonction.indemnite_responsabilite), agrégée séparément.
     */
    private const CODES_663 = ['LOG', 'ASTR', 'SPEC', 'TECH'];

    /**
     * Codes d'indemnités disposant déjà d'une colonne propre dans l'annexe.
     * Tout le reste (charge militaire saisie autrement, AUTRES, indemnités
     * créées sur mesure) est agrégé dans la colonne « Autres » (avec la
     * spécifique), pour ne rien laisser de côté.
     */
    private const CODES_COLONNES = ['IR', 'CM', 'RESP', 'ASTR', 'LOG', 'TECH', 'SPEC', 'ALLOC'];

    /** Somme mensuelle des indemnités attribuées d'un agent sans colonne dédiée. */
    private function autresIndemnites(Agent $a): float
    {
        if (! $a->relationLoaded('indemnites')) {
            return 0.0;
        }

        return (float) $a->indemnites
            ->filter(fn ($x) => $x->actif && ! in_array($x->indemnite?->code, self::CODES_COLONNES, true))
            ->sum(fn ($x) => (float) $x->montant);
    }

    /**
     * Masse salariale annuelle courante par action et par paragraphe.
     * Filtres optionnels : structure_id, programme_id, action_id.
     */
    public function masseParAction(array $filtres = []): array
    {
        $point = (float) config('grille.point_annuel', 2331);
        $carfo = (float) config('grille.carfo_taux', 0.08);
        $residence = (float) config('grille.residence_taux', 0.10);
        $allocEnfant = (float) config('gesperes.allocation_familiale.montant_par_enfant', 0);
        $allocMax    = (int) config('gesperes.allocation_familiale.nombre_max_enfants', 6);

        $appliquer = function ($q) use ($filtres) {
            // Filtre structure « cascade » : la structure choisie ET tout son sous-arbre.
            $q->when($filtres['structure_id'] ?? null, fn ($q, $v) => $q->whereIn('ag.structure_id', Structure::sousArbreIds($v)))
                ->when($filtres['programme_id'] ?? null, fn ($q, $v) => $q->where('pr.id', $v))
                ->when($filtres['action_id'] ?? null, fn ($q, $v) => $q->where('ac.id', $v));
            return $q;
        };

        // Solde (indice) + responsabilité (fonction) + nb d'enfants plafonné (pour
        // l'allocation familiale, calculée et non plus lue depuis les attributions) par action.
        $soldes = $appliquer(DB::table('agents as ag')
            ->join('structures as s', 's.id', '=', 'ag.structure_id')
            ->join('actions as ac', 'ac.id', '=', 's.action_id')
            ->join('programmes as pr', 'pr.id', '=', 'ac.programme_id')
            ->join('indices as i', 'i.id', '=', 'ag.indice_id')
            ->leftJoin('fonctions as f', 'f.id', '=', 'ag.fonction_id')
            ->whereNull('ag.deleted_at'))
            ->groupBy('ac.id', 'ac.code', 'ac.libelle', 'pr.code', 'pr.libelle')
            ->selectRaw("ac.id aid, ac.code acode, ac.libelle alib, pr.code pcode, pr.libelle plib, SUM(i.valeur) sind, SUM(COALESCE(f.indemnite_responsabilite,0)) sresp, SUM(CASE WHEN COALESCE(ag.nombre_enfants,0) > {$allocMax} THEN {$allocMax} ELSE COALESCE(ag.nombre_enfants,0) END) snbenf")
            ->get();

        // Indemnités barème (663 : logement, technicité, astreinte, spécifique)
        // calculées PAR LES RÈGLES et agrégées par action (mensuel).
        $baremeParAction = $this->indemnitesBaremeParAction($filtres);

        $masse = [];
        foreach ($soldes as $r) {
            $soldeAnnuel = (float) $r->sind * $point; // indice × point = solde annuel

            // 663 = résidence + responsabilité (fonction) + indemnités barème, annualisé.
            $primes663 = $soldeAnnuel * $residence
                + (float) $r->sresp * 12
                + (float) ($baremeParAction[$r->aid] ?? 0) * 12;
            // 666 = allocation familiale calculée : nb d'enfants plafonné × barème, annualisé.
            $p666 = (float) $r->snbenf * $allocEnfant * 12;

            $masse[$r->aid] = [
                'programme_code'    => $r->pcode,
                'programme_libelle' => $r->plib,
                'action_code'       => $r->acode,
                'action_libelle'    => $r->alib,
                661 => $soldeAnnuel,
                663 => $primes663,
                664 => $soldeAnnuel * $carfo,
                666 => $p666,
            ];
        }

        return $masse;
    }

    /**
     * Indemnités barème (logement + technicité + astreinte + spécifique) calculées
     * PAR LES RÈGLES et agrégées par action (montant mensuel). Astreinte/spécifique
     * retombent sur le montant réel attribué quand la zone est indéterminée.
     *
     * @return array<int, float>  action_id => total mensuel
     */
    private function indemnitesBaremeParAction(array $filtres): array
    {
        // Coûteux (boucle par agent sur tout l'effectif) : mémoïsé 30 min, car la
        // ventilation est une projection pluriannuelle qui évolue rarement.
        $cle = 'ventilation.indem_bareme.' . md5(json_encode($filtres));

        return Cache::remember($cle, now()->addMinutes(30), fn () => $this->calculerIndemnitesBaremeParAction($filtres));
    }

    /** @return array<int, float> action_id => total mensuel */
    private function calculerIndemnitesBaremeParAction(array $filtres): array
    {
        $acc = [];

        Agent::query()
            ->with(['categorie', 'emploi', 'echelle', 'localite.zone',
                    'structure.parent.parent.parent.parent', 'indemnites.indemnite'])
            ->whereHas('structure.action')
            ->when($filtres['structure_id'] ?? null, fn ($q, $v) => $q->whereIn('structure_id', Structure::sousArbreIds($v)))
            ->when($filtres['action_id'] ?? null, fn ($q, $v) => $q->whereHas('structure', fn ($q) => $q->where('action_id', $v)))
            ->when($filtres['programme_id'] ?? null, fn ($q, $v) => $q->whereHas('structure.action', fn ($q) => $q->where('programme_id', $v)))
            ->chunk(500, function ($lot) use (&$acc) {
                foreach ($lot as $a) {
                    $aid = $a->structure?->action_id;
                    if (! $aid) {
                        continue;
                    }
                    $stored = $a->relationLoaded('indemnites')
                        ? $a->indemnites->keyBy(fn ($x) => $x->indemnite?->code) : collect();
                    $m = fn (string $c) => (float) optional($stored->get($c))->montant;

                    $acc[$aid] = ($acc[$aid] ?? 0)
                        + $this->indemnites->logement($a)
                        + $this->indemnites->technicite($a)
                        + ($this->indemnites->astreinte($a) ?? $m('ASTR'))
                        + ($this->indemnites->specifique($a) ?? $m('SPEC'));
                }
            });

        return $acc;
    }

    /**
     * Tableau annexe « Dépenses de personnel » (Tableau II-1) présenté par
     * programme → Direction/Structure, avec colonnes de paie annualisées,
     * Total, provisions (suppléments 3 %, naissances 5 %) et Total général.
     *
     * Filtres optionnels : structure_id, programme_id, action_id.
     */
    public function tableauAnnexe(array $filtres = []): array
    {
        $point = (float) config('grille.point_annuel', 2331);
        $carfoPat = (float) config('grille.carfo_taux', 0.135);
        $residence = (float) config('grille.residence_taux', 0.10);
        $allocEnfant = (float) config('gesperes.allocation_familiale.montant_par_enfant', 0);
        $allocMax    = (int) config('gesperes.allocation_familiale.nombre_max_enfants', 6);

        $appliquer = function ($q) use ($filtres) {
            // Filtre structure « cascade » : la structure choisie ET tout son sous-arbre.
            return $q->when($filtres['structure_id'] ?? null, fn ($q, $v) => $q->whereIn('ag.structure_id', Structure::sousArbreIds($v)))
                ->when($filtres['programme_id'] ?? null, fn ($q, $v) => $q->where('pr.id', $v))
                ->when($filtres['action_id'] ?? null, fn ($q, $v) => $q->where('s.action_id', $v));
        };

        // Solde (indice) + responsabilité (fonction) + nb d'enfants plafonné (allocation
        // familiale calculée) par programme × structure.
        $base = $appliquer(DB::table('agents as ag')
            ->join('structures as s', 's.id', '=', 'ag.structure_id')
            ->join('actions as ac', 'ac.id', '=', 's.action_id')
            ->join('programmes as pr', 'pr.id', '=', 'ac.programme_id')
            ->join('indices as i', 'i.id', '=', 'ag.indice_id')
            ->leftJoin('fonctions as f', 'f.id', '=', 'ag.fonction_id')
            ->whereNull('ag.deleted_at'))
            ->groupBy('pr.code', 'pr.libelle', 's.id', 's.libelle')
            ->selectRaw("pr.code pcode, pr.libelle plib, s.id sid, s.libelle slib, SUM(i.valeur) sind, SUM(COALESCE(f.indemnite_responsabilite,0)) sresp, SUM(CASE WHEN COALESCE(ag.nombre_enfants,0) > {$allocMax} THEN {$allocMax} ELSE COALESCE(ag.nombre_enfants,0) END) snbenf, COUNT(*) n")
            ->get();

        // Indemnités (mensuelles) par programme × structure × code.
        $ind = $appliquer(DB::table('agent_indemnites as ai')
            ->join('agents as ag', 'ag.id', '=', 'ai.agent_id')
            ->join('structures as s', 's.id', '=', 'ag.structure_id')
            ->join('actions as ac', 'ac.id', '=', 's.action_id')
            ->join('programmes as pr', 'pr.id', '=', 'ac.programme_id')
            ->join('indemnites as im', 'im.id', '=', 'ai.indemnite_id')
            ->whereNull('ag.deleted_at'))
            ->groupBy('pr.code', 's.id', 'im.code')
            ->selectRaw('pr.code pcode, s.id sid, im.code code, SUM(ai.montant) total')
            ->get()
            ->groupBy(fn ($r) => $r->pcode . '|' . $r->sid);

        $programmes = [];
        $tot = array_fill_keys(['si', 'ir', 'cm', 'resp', 'astr', 'log', 'tech', 'autres', 'af', 'carfo', 'incidence'], 0.0);

        foreach ($base as $r) {
            $cle = $r->pcode . '|' . $r->sid;
            $imm = $ind->get($cle, collect())->keyBy('code');
            $m = fn ($code) => (float) optional($imm->get($code))->total * 12; // mensuel → annuel
            // « Autres » = spécifique + toute indemnité attribuée sans colonne dédiée (annualisé).
            $autresAnnuel = $m('SPEC') + (float) $imm->reject(fn ($x) => in_array($x->code, self::CODES_COLONNES, true))->sum('total') * 12;

            $si = (float) $r->sind * $point;
            $ligne = [
                'structure' => $r->slib,
                'effectif'  => (int) $r->n,
                'si'        => $si,
                'ir'        => $si * $residence,
                'cm'        => $m('CM'),
                'resp'      => (float) $r->sresp * 12,
                'astr'      => $m('ASTR'),
                'log'       => $m('LOG'),
                'tech'      => $m('TECH'),
                'autres'    => $autresAnnuel, // spécifique + autres indemnités
                'af'        => (float) $r->snbenf * $allocEnfant * 12, // allocation familiale calculée
                'carfo'     => $si * $carfoPat,
            ];
            $ligne['incidence'] = $ligne['si'] + $ligne['ir'] + $ligne['cm'] + $ligne['resp']
                + $ligne['astr'] + $ligne['log'] + $ligne['tech'] + $ligne['autres'] + $ligne['af'] + $ligne['carfo'];

            $programmes[$r->pcode] ??= ['libelle' => $r->plib, 'directions' => []];
            $programmes[$r->pcode]['directions'][] = $ligne;

            foreach ($tot as $k => $v) {
                $tot[$k] += $ligne[$k];
            }
        }
        ksort($programmes);
        foreach ($programmes as &$p) {
            usort($p['directions'], fn ($a, $b) => $b['incidence'] <=> $a['incidence']);
        }

        return [
            'parProgramme' => $programmes,
            'totaux'       => $tot,
            'provisions'   => $this->provisions($tot),
        ];
    }

    /**
     * Lignes de l'annexe PAR AGENT, regroupées par programme → structure.
     * (Détail nominatif du Tableau II-1.) Filtres : structure_id, programme_id, action_id.
     */
    public function lignesAgents(array $filtres = []): array
    {
        ini_set('memory_limit', '2048M');

        $point = (float) config('grille.point_annuel', 2331);
        $carfo = (float) config('grille.carfo_taux', 0.135);
        $residence = (float) config('grille.residence_taux', 0.10);

        $agents = Agent::query()
            ->with(['emploi', 'categorie', 'echelle', 'classe', 'echelon', 'indice', 'fonction', 'localite.zone',
                    'structure.action.programme', 'structure.parent.parent.parent.parent', 'indemnites.indemnite'])
            ->whereHas('structure.action')
            // Filtre structure « cascade » : la structure choisie ET tout son sous-arbre.
            ->when($filtres['structure_id'] ?? null, fn ($q, $v) => $q->whereIn('structure_id', Structure::sousArbreIds($v)))
            ->when($filtres['action_id'] ?? null, fn ($q, $v) => $q->whereHas('structure', fn ($q) => $q->where('action_id', $v)))
            ->when($filtres['programme_id'] ?? null, fn ($q, $v) => $q->whereHas('structure.action', fn ($q) => $q->where('programme_id', $v)))
            ->orderBy('nom')->orderBy('prenoms')
            ->get();

        // Regroupement brut par programme → structure.
        $brut = [];
        foreach ($agents as $a) {
            $prog = $a->structure?->action?->programme;
            $pcode = $prog?->code ?? '—';
            // Regroupement par STRUCTURE (avant-dernier niveau de la cascade),
            // cohérent avec la liste des agents ; le dernier niveau (service) est un détail.
            $slib = $a->structure?->niveauStructure() ?? '—';

            $brut[$pcode]['libelle'] ??= $prog?->libelle ?? '—';
            $brut[$pcode]['structures'][$slib][] = $this->ligneAgent($a, $point, $carfo, $residence);
        }

        // Pour chaque structure : totaux (annuels) + provisions propres.
        $parProgramme = [];
        foreach ($brut as $pcode => $prog) {
            ksort($prog['structures']);
            foreach ($prog['structures'] as $slib => $lignes) {
                $tot = $this->agregerLignes($lignes);
                $parProgramme[$pcode]['libelle'] = $prog['libelle'];
                $parProgramme[$pcode]['structures'][$slib] = [
                    'lignes'     => $lignes,
                    'totaux'     => $tot,
                    'provisions' => $this->provisions($tot),
                ];
            }
        }
        ksort($parProgramme);

        return $parProgramme;
    }

    /** Totaux annuels d'une structure à partir des lignes mensuelles (× 12). */
    private function agregerLignes(array $lignes): array
    {
        $map = ['si' => 'solde', 'ir' => 'ir', 'cm' => 'cm', 'resp' => 'resp', 'astr' => 'astr',
                'log' => 'log', 'tech' => 'tech', 'autres' => 'autres', 'af' => 'allo', 'carfo' => 'carfo'];
        $t = array_fill_keys(array_keys($map), 0.0);
        $t['incidence'] = 0.0;

        foreach ($lignes as $l) {
            foreach ($map as $tk => $lk) {
                $t[$tk] += (float) $l[$lk] * 12; // mensuel → annuel
            }
            $t['incidence'] += (float) $l['incidence']; // déjà annuel
        }

        return $t;
    }

    /**
     * Ligne de paie annexe pour un agent : colonnes MENSUELLES (solde, IR,
     * indemnités, allocation, CARFO 13,5 %) ; seule l'incidence est ANNUELLE
     * (somme mensuelle × 12), identique sur les trois exercices.
     */
    private function ligneAgent(Agent $a, float $point, float $carfo, float $residence): array
    {
        $indice = (int) ($a->indice?->valeur ?? 0);
        $ind = $a->relationLoaded('indemnites') ? $a->indemnites->keyBy(fn ($x) => $x->indemnite?->code) : collect();
        $m = fn (string $code) => (float) optional($ind->get($code))->montant; // mensuel

        $solde = $point > 0 ? round($indice * $point / 12) : 0.0; // solde indiciaire mensuel
        $l = [
            'ref'       => $this->reference($a),
            'matricule' => $a->matricule,
            'nom'       => trim("{$a->nom} {$a->prenoms}"),
            'sexe'      => $a->sexe?->value,
            'indice'    => $indice,
            'solde'     => $solde,
            'ir'        => round($solde * $residence),
            'cm'        => $m('CM'),
            'resp'      => (float) ($a->fonction?->indemnite_responsabilite ?? 0),
            // Indemnités barème calculées (logement/technicité toujours ; astreinte/
            // spécifique selon zone, sinon valeur réelle attribuée). « Autres » = spécifique.
            'astr'      => $this->indemnites->astreinte($a) ?? $m('ASTR'),
            'log'       => $this->indemnites->logement($a),
            'tech'      => $this->indemnites->technicite($a),
            // « Autres » = indemnité spécifique + toute indemnité attribuée sans colonne dédiée.
            'autres'    => ($this->indemnites->specifique($a) ?? $m('SPEC')) + $this->autresIndemnites($a),
            // Allocation familiale : attribuée si présente, sinon calcul auto (enfants).
            'allo'      => $m('ALLOC') ?: $this->indemnites->allocationFamiliale($a),
            'carfo'     => round($solde * $carfo),
        ];
        $mensuel = $l['solde'] + $l['ir'] + $l['cm'] + $l['resp'] + $l['astr'] + $l['log'] + $l['tech'] + $l['autres'] + $l['allo'] + $l['carfo'];
        $l['incidence'] = $mensuel * 12;

        return $l;
    }

    /** Référence : emploi/classement (ex. AASU/B1-1-14). */
    private function reference(Agent $a): string
    {
        $classement = ($a->categorie?->code ?? '')
            . str_replace('ECHL', '', (string) $a->echelle?->code)
            . '-' . str_replace('CL', '', (string) $a->classe?->code)
            . '-' . str_pad(str_replace('ECH', '', (string) $a->echelon?->code), 2, '0', STR_PAD_LEFT);

        $emploi = $a->emploi?->code;

        return $emploi ? "{$emploi}/{$classement}" : $classement;
    }

    /** Provisions (suppléments salariaux 3 %, nouvelles naissances 5 %) et Total général. */
    private function provisions(array $t): array
    {
        $sup = (float) config('grille.provisions.supplements', 0.03);
        $nai = (float) config('grille.provisions.naissances', 0.05);

        // Base des suppléments : SI + IR + CM + IT(technicité) + CARFO.
        $ba = $t['si'] + $t['ir'] + $t['cm'] + $t['tech'] + $t['carfo'];
        $f = $ba * $sup;
        $g = ($ba + $f) * $sup + $f;
        $h = ($ba + $g) * $sup + $g;

        $i1 = $t['af'] * $nai;
        $i2 = ($t['af'] + $i1) * $nai + $i1;
        $i3 = ($t['af'] + $i2) * $nai + $i2;

        $t1 = $t['incidence'];

        return [
            'a' => $t['si'] * $sup, 'b' => $t['ir'] * $sup, 'c' => $t['cm'] * $sup,
            'd' => $t['tech'] * $sup, 'e' => $t['carfo'] * $sup,
            'ba' => $ba, 'f' => $f, 'g' => $g, 'h' => $h,
            'i1' => $i1, 'i2' => $i2, 'i3' => $i3,
            't1' => $t1, 't2' => $t1, 't3' => $t1,
            'tg1' => $t1 + $f + $i1, 'tg2' => $t1 + $g + $i2, 'tg3' => $t1 + $h + $i3,
        ];
    }

    /** Ventilation de l'enveloppe : lignes détaillées + totaux par exercice. */
    public function ventiler(EnveloppePersonnel $enveloppe): array
    {
        $masse = $this->masseParAction();
        $cible = $this->cibleParAnnee($enveloppe);

        $totalMasse = 0.0;
        foreach ($masse as $m) {
            $totalMasse += $m[661] + $m[663] + $m[664] + $m[666];
        }

        $facteurs = array_map(fn ($c) => $totalMasse > 0 ? $c / $totalMasse : 0, $cible);

        $lignes = [];
        foreach ($masse as $m) {
            foreach (self::PARAGRAPHES as $code => $libelle) {
                if ($m[$code] <= 0) {
                    continue;
                }
                $lignes[] = [
                    'programme_code'    => $m['programme_code'],
                    'programme_libelle' => $m['programme_libelle'],
                    'action_code'       => $m['action_code'],
                    'action_libelle'    => $m['action_libelle'],
                    'paragraphe'        => $code,
                    'paragraphe_libelle' => $libelle,
                    'montants'          => array_map(fn ($f) => round($m[$code] * $f), $facteurs),
                ];
            }
        }

        // Tri par programme, action, paragraphe.
        usort($lignes, fn ($a, $b) => [$a['programme_code'], $a['action_code'], $a['paragraphe']]
            <=> [$b['programme_code'], $b['action_code'], $b['paragraphe']]);

        return [
            'lignes'      => $lignes,
            'cible'       => $cible,
            'total_masse' => $totalMasse,
            'totaux'      => $this->totaux($lignes),
        ];
    }

    /** Montants cibles (ligne « Salaire du personnel en activité ») par exercice. */
    private function cibleParAnnee(EnveloppePersonnel $enveloppe): array
    {
        foreach ($enveloppe->lignes as $l) {
            $n = Str::of($l->libelle)->ascii()->lower();
            if ($n->contains('salaire') && $n->contains('activit')) {
                return [(float) $l->montant_n1, (float) $l->montant_n2, (float) $l->montant_n3];
            }
        }

        // À défaut, le total de l'enveloppe.
        return $enveloppe->totaux;
    }

    private function totaux(array $lignes): array
    {
        $t = [0.0, 0.0, 0.0];
        foreach ($lignes as $l) {
            foreach ($l['montants'] as $i => $v) {
                $t[$i] += $v;
            }
        }
        return $t;
    }
}
