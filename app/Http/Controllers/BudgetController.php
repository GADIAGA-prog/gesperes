<?php

namespace App\Http\Controllers;

use App\Imports\BudgetParImport;
use App\Models\Action;
use App\Models\Activite;
use App\Models\Agent;
use App\Models\BudgetLigne;
use App\Models\Categorie;
use App\Models\Emploi;
use App\Models\Programme;
use App\Models\Structure;
use App\Services\PaiePersonnelService;
use App\Services\VentilationService;
use App\Support\BudgetControle;
use App\Support\TableFiltre;
use App\Support\TableTri;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Module Budget Structure : budget (AE/CP) et programme d'activités par structure.
 */
class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('budget.view');

        $activites = Activite::query()
            ->with(['action.programme', 'structure', 'lignes'])
            ->when($request->filled('exercice'), fn ($q) => $q->where('exercice', $request->integer('exercice')))
            ->when($request->filled('structure_id'), fn ($q) => $q->where('structure_id', $request->integer('structure_id')))
            ->when($request->filled('programme_id'), fn ($q) => $q->whereHas('action', fn ($a) => $a->where('programme_id', $request->integer('programme_id'))))
            ->orderBy('code')
            ->paginate(25)
            ->withQueryString();

        // Totaux globaux (sur le filtre courant, hors pagination).
        $base = Activite::query()
            ->when($request->filled('exercice'), fn ($q) => $q->where('exercice', $request->integer('exercice')))
            ->when($request->filled('structure_id'), fn ($q) => $q->where('structure_id', $request->integer('structure_id')))
            ->when($request->filled('programme_id'), fn ($q) => $q->whereHas('action', fn ($a) => $a->where('programme_id', $request->integer('programme_id'))));

        $totaux = [
            'activites' => (clone $base)->count(),
            'montant'   => (clone $base)->sum('montant'),
            'ae'        => BudgetLigne::whereIn('activite_id', (clone $base)->select('id'))->sum('montant_ae'),
            'cp'        => BudgetLigne::whereIn('activite_id', (clone $base)->select('id'))->sum('montant_cp'),
            'anomalies' => (clone $base)
                ->whereRaw('(trimestre_1 + trimestre_2 + trimestre_3 + trimestre_4) > 0')
                ->whereRaw('ABS((trimestre_1 + trimestre_2 + trimestre_3 + trimestre_4) - 1) > ?', [BudgetControle::TOLERANCE])
                ->count(),
        ];

        return view('budget.index', [
            'activites'  => $activites,
            'totaux'     => $totaux,
            'exercices'  => Activite::distinct()->orderByDesc('exercice')->pluck('exercice'),
            'structures' => Structure::directions()->orderBy('libelle')->pluck('libelle', 'id'),
            'programmes' => Programme::orderBy('libelle')->pluck('libelle', 'id'),
            'filtres'    => $request->only(['exercice', 'structure_id', 'programme_id']),
        ]);
    }

    /**
     * Sous-module « Dépenses du personnel » : état de paie agrégé par agent
     * (solde indiciaire + indemnités) destiné au chiffrage budgétaire.
     *
     * ⚠ En préparation : certaines colonnes (résidence, responsabilité, CARFO,
     * « autres », rattachement agent → action) reposent sur des règles métier
     * non encore documentées. Voir la vue pour le détail des règles attendues.
     */
    public function personnel(Request $request, PaiePersonnelService $paie): View
    {
        $this->authorize('budget.view');

        $modes = ['agent', 'structure', 'programme', 'action'];
        $mode = in_array($request->input('mode'), $modes, true) ? $request->input('mode') : 'agent';

        // Requête filtrée commune à tous les modes (closure : un builder neuf à chaque appel).
        $base = fn () => Agent::query()
            ->when($request->filled('q'), fn ($q) => $q->recherche($request->input('q')))
            ->when($request->filled('emploi_id'), fn ($q) => $q->where('emploi_id', $request->input('emploi_id')))
            ->when($request->filled('categorie_id'), fn ($q) => $q->where('categorie_id', $request->input('categorie_id')))
            // Filtre structure « cascade » : inclut la structure choisie ET tous ses
            // services/sous-structures (ex. DRH → service de gestion des carrières).
            ->when($request->filled('structure_id'), fn ($q) =>
                $q->whereIn('structure_id', Structure::sousArbreIds($request->integer('structure_id'))));

        $agents = null;
        $synthese = null;
        $libelleColonne = null;

        if ($mode !== 'agent') {
            // Agrégation des dépenses de personnel par structure / programme / action.
            // Mémoire bornée (accumulateur par clé) ; calcul par lots pour les gros effectifs.
            $libelleColonne = match ($mode) {
                'structure' => 'Structure (rattachement)',
                'programme' => 'Programme',
                'action'    => 'Action',
            };

            $acc = [];
            $base()->with(['indice', 'indemnites.indemnite', 'fonction', 'categorie', 'emploi', 'echelle',
                    'localite.zone', 'structure.action.programme', 'structure.parent.parent.parent.parent'])
                ->chunk(500, function ($lot) use (&$acc, $paie, $mode) {
                    foreach ($lot as $agent) {
                        $p = $paie->ligne($agent);
                        [$cle, $libelle] = $this->cleSynthesePersonnel($agent, $mode);
                        $acc[$cle] ??= ['libelle' => $libelle, 'effectif' => 0, 'mois' => 0.0, 'annuel' => 0.0];
                        $acc[$cle]['effectif']++;
                        $acc[$cle]['mois']   += $p['total_mois'];
                        $acc[$cle]['annuel'] += $p['total_annuel'];
                    }
                });

            $synthese = collect($acc)->sortBy('libelle')->values();
        } else {
            $query = $base()
                ->with(['emploi', 'poste', 'fonction', 'categorie', 'echelle', 'classe', 'echelon', 'indice',
                    'indemnites.indemnite', 'localite.zone', 'structure.action', 'structure.parent.parent.parent.parent']);

            // Filtre par colonne + tri par en-tête (sur les colonnes stockées en base ;
            // les montants de paie sont calculés en PHP et ne sont pas triables côté serveur).
            TableFiltre::appliquer($query, $request, [
                'matricule' => 'matricule',
                'nom'       => 'nom',
                'prenoms'   => 'prenoms',
                'emploi'    => fn ($q, $v) => $q->whereHas('emploi', fn ($e) => $e->where('libelle', 'like', "%{$v}%")),
            ]);
            TableTri::appliquer($query, $request, [
                'matricule' => 'matricule',
                'nom'       => 'nom',
                'prenoms'   => 'prenoms',
                'sexe'      => 'sexe',
                'emploi'    => fn ($q, $s) => $q->orderBy(Emploi::select('libelle')->whereColumn('emplois.id', 'agents.emploi_id'), $s),
            ], 'nom', 'asc');
            $query->orderBy('prenoms');

            $agents = $query->paginate(50)->withQueryString();

            foreach ($agents as $agent) {
                $agent->paie = $paie->ligne($agent);
            }
        }

        return view('budget.personnel', [
            'mode'           => $mode,
            'agents'         => $agents,
            'synthese'       => $synthese,
            'libelleColonne' => $libelleColonne,
            'emplois'        => Emploi::orderBy('libelle')->pluck('libelle', 'id'),
            'categories'     => Categorie::orderBy('code')->pluck('code', 'id'),
            'structures'     => Structure::orderBy('libelle')->pluck('libelle', 'id'),
            'filtres'        => $request->only(['q', 'emploi_id', 'categorie_id', 'structure_id']),
        ]);
    }

    /**
     * Clé + libellé d'agrégation d'un agent selon le mode de synthèse choisi.
     * Programme/Action sont dérivés de la structure d'affectation (structure → action → programme).
     *
     * @return array{0: int|string, 1: string}
     */
    private function cleSynthesePersonnel(Agent $agent, string $mode): array
    {
        return match ($mode) {
            'programme' => (function () use ($agent) {
                $prog = $agent->structure?->action?->programme;
                return $prog
                    ? [$prog->id, trim($prog->code . ' — ' . $prog->libelle, ' —')]
                    : [0, 'Sans programme'];
            })(),
            'action' => (function () use ($agent) {
                $action = $agent->structure?->action;
                return $action
                    ? [$action->id, trim($action->code . ' — ' . $action->libelle, ' —')]
                    : [0, 'Sans action'];
            })(),
            default => [ // structure
                $agent->structure_id ?: 0,
                $agent->structure?->cheminComplet() ?: 'Sans structure',
            ],
        };
    }

    /**
     * Sous-module « Tableaux annexes » : masse salariale agrégée par programme,
     * action et paragraphe (661 traitements, 663 primes/indemnités, 664 CARFO,
     * 666 prestations), annualisée, avec filtres structure / programme / action.
     */
    public function annexes(Request $request, VentilationService $ventilation): View
    {
        $this->authorize('budget.view');

        $filtres = array_filter($request->only(['structure_id', 'programme_id', 'action_id']));

        // Détail nominatif (par programme → structure) dès qu'un filtre est posé.
        $detail = (isset($filtres['structure_id']) || isset($filtres['programme_id']))
            ? $ventilation->lignesAgents($filtres)
            : null;

        return view('budget.annexes', [
            'detail'     => $detail,
            'annees'     => $this->anneesBudget(),
            'structures' => Structure::directions()->orderBy('libelle')->pluck('libelle', 'id'),
            'programmes' => Programme::orderBy('code')->pluck('libelle', 'id'),
            'actions'    => Action::orderBy('code')->get(['id', 'code', 'libelle', 'programme_id']),
            'filtres'    => $request->only(['structure_id', 'programme_id', 'action_id']),
        ]);
    }

    /** Trois exercices de l'avant-projet (n+1 à n+3). */
    private function anneesBudget(): array
    {
        $base = (int) now()->year + 1;
        return [$base, $base + 1, $base + 2];
    }

    /** Export Excel du tableau annexe (détail par agent, scope filtré). */
    public function annexesExcel(Request $request, VentilationService $ventilation): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('budget.view');
        $filtres = array_filter($request->only(['structure_id', 'programme_id', 'action_id']));

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AnnexePersonnelExport($ventilation->lignesAgents($filtres), $this->anneesBudget()),
            'tableau_annexe_personnel_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /** Export PDF (A4 paysage, paginé) du tableau annexe. */
    public function annexesPdf(Request $request, VentilationService $ventilation): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('budget.view');
        $filtres = array_filter($request->only(['structure_id', 'programme_id', 'action_id']));

        $pdf = Pdf::loadView('budget.pdf.annexe', [
            'detail' => $ventilation->lignesAgents($filtres),
            'annees' => $this->anneesBudget(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('tableau_annexe_personnel_' . now()->format('Ymd_His') . '.pdf');
    }

    public function show(Activite $activite): View
    {
        $this->authorize('budget.view');
        $activite->load(['action.programme', 'structure', 'lignes']);

        return view('budget.show', [
            'activite' => $activite,
            'controle' => BudgetControle::pour($activite),
        ]);
    }

    public function create(): View
    {
        $this->authorize('budget.manage');
        return view('budget.create', $this->referentiels());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('budget.manage');
        $data = $this->valider($request);

        $action = Action::findOrFail($data['action_id']);
        $code = $action->code . str_pad((string) $data['numero_activite'], 2, '0', STR_PAD_LEFT);

        if (Activite::where('exercice', $data['exercice'])->where('code', $code)->exists()) {
            return back()->withInput()->with('error', "L'activité {$code} existe déjà pour l'exercice {$data['exercice']}.");
        }

        $activite = Activite::create($this->donnees($data, $code));

        return redirect()->route('budget.show', $activite)
            ->with('success', "Activité {$code} créée. Ajoutez ses lignes budgétaires.");
    }

    public function edit(Activite $activite): View
    {
        $this->authorize('budget.manage');
        return view('budget.edit', array_merge(['activite' => $activite], $this->referentiels()));
    }

    public function update(Request $request, Activite $activite): RedirectResponse
    {
        $this->authorize('budget.manage');
        $data = $this->valider($request);

        $action = Action::findOrFail($data['action_id']);
        $code = $action->code . str_pad((string) $data['numero_activite'], 2, '0', STR_PAD_LEFT);

        if (Activite::where('exercice', $data['exercice'])->where('code', $code)->where('id', '!=', $activite->id)->exists()) {
            return back()->withInput()->with('error', "L'activité {$code} existe déjà pour l'exercice {$data['exercice']}.");
        }

        $activite->update($this->donnees($data, $code));

        return redirect()->route('budget.show', $activite)->with('success', 'Activité mise à jour.');
    }

    public function destroy(Activite $activite): RedirectResponse
    {
        $this->authorize('budget.manage');
        $activite->delete();

        return redirect()->route('budget.index')->with('success', 'Activité supprimée.');
    }

    public function storeLigne(Request $request, Activite $activite): RedirectResponse
    {
        $this->authorize('budget.manage');
        $data = $request->validate([
            'code_article'      => ['nullable', 'string', 'max:20'],
            'code_paragraphe'   => ['nullable', 'string', 'max:20'],
            'libelle_categorie' => ['nullable', 'string', 'max:255'],
            'montant_ae'        => ['required', 'numeric', 'min:0'],
            'montant_cp'        => ['required', 'numeric', 'min:0'],
        ]);
        $data['exercice'] = $activite->exercice;
        $activite->lignes()->create($data);

        return back()->with('success', 'Ligne budgétaire ajoutée.');
    }

    public function destroyLigne(Activite $activite, BudgetLigne $ligne): RedirectResponse
    {
        $this->authorize('budget.manage');
        abort_unless($ligne->activite_id === $activite->id, 404);
        $ligne->delete();

        return back()->with('success', 'Ligne supprimée.');
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'exercice'              => ['required', 'integer', 'between:2000,2100'],
            'structure_id'          => ['nullable', 'exists:structures,id'],
            'action_id'             => ['required', 'exists:actions,id'],
            'numero_activite'       => ['required', 'integer', 'between:1,99'],
            'libelle'               => ['required', 'string', 'max:255'],
            'objectif_strategique'  => ['nullable', 'string'],
            'objectif_operationnel' => ['nullable', 'string'],
            'indicateur'            => ['nullable', 'string'],
            'valeur_initiale'       => ['nullable', 'string', 'max:120'],
            'cible'                 => ['nullable', 'string', 'max:120'],
            'localite'              => ['nullable', 'string', 'max:255'],
            'montant'               => ['nullable', 'numeric', 'min:0'],
            'trimestre_1'           => ['nullable', 'numeric', 'min:0'],
            'trimestre_2'           => ['nullable', 'numeric', 'min:0'],
            'trimestre_3'           => ['nullable', 'numeric', 'min:0'],
            'trimestre_4'           => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function donnees(array $data, string $code): array
    {
        return array_merge(
            collect($data)->except('numero_activite')->all(),
            ['code' => $code, 'actif' => true],
        );
    }

    private function referentiels(): array
    {
        return [
            'programmes' => Programme::orderBy('code')->get()->mapWithKeys(fn ($p) => [$p->id => $p->code . ' — ' . $p->libelle]),
            'actions'    => Action::orderBy('code')->get(['id', 'code', 'libelle', 'programme_id']),
            'structures' => Structure::directions()->orderBy('libelle')->pluck('libelle', 'id'),
        ];
    }

    public function importForm(): View
    {
        $this->authorize('budget.manage');

        return view('budget.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('budget.manage');

        $request->validate([
            'fichier'  => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'exercice' => ['nullable', 'integer', 'between:2000,2100'],
        ], [], ['fichier' => 'fichier']);

        $import = new BudgetParImport();
        $import->run($request->file('fichier')->getRealPath(), $request->integer('exercice') ?: (int) now()->year);

        return redirect()->route('budget.index')
            ->with('success', "{$import->activites} activité(s), {$import->lignes} ligne(s) budgétaire(s) importée(s).")
            ->with('error', implode(' ', $import->infos));
    }

    /** Programme d'activité (PAR) d'une structure / exercice en PDF. */
    public function parPdf(Request $request): Response
    {
        $this->authorize('budget.view');

        $data = $request->validate([
            'structure_id' => ['nullable', 'exists:structures,id'],
            'exercice'     => ['required', 'integer', 'between:2000,2100'],
        ]);

        $activites = Activite::with(['action.programme', 'structure', 'lignes'])
            ->where('exercice', $data['exercice'])
            ->when($request->filled('structure_id'), fn ($q) => $q->where('structure_id', $request->integer('structure_id')))
            ->orderBy('code')
            ->get();

        $structure = $request->filled('structure_id') ? Structure::find($request->integer('structure_id')) : null;

        $pdf = Pdf::loadView('budget.pdf.par', [
            'activites' => $activites,
            'structure' => $structure,
            'exercice'  => $data['exercice'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('PAR_' . ($structure?->code ?? 'tous') . '_' . $data['exercice'] . '.pdf');
    }
}
