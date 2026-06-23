<?php

namespace App\Http\Controllers;

use App\Enums\CauseDifficulte;
use App\Enums\DomaineFormation;
use App\Enums\FrequenceTache;
use App\Enums\NiveauMaitrise;
use App\Enums\SolutionBesoin;
use App\Http\Requests\StoreBesoinFormationRequest;
use App\Models\Agent;
use App\Models\BesoinFormation;
use App\Models\Structure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Recueil des besoins de formation (fiche Annexe 1) et consolidation pour
 * alimenter l'élaboration des actions du plan.
 */
class BesoinFormationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('formations.view');

        $besoins = BesoinFormation::with(['agent', 'structure'])
            ->when($request->filled('annee'), fn ($q) => $q->where('annee_recueil', $request->input('annee')))
            ->when($request->filled('domaine'), fn ($q) => $q->where('domaine', $request->input('domaine')))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->input('statut')))
            ->when($request->filled('structure_id'), fn ($q) => $q->where('structure_id', $request->input('structure_id')))
            ->latest('annee_recueil')
            ->paginate(20)
            ->withQueryString();

        // Consolidation : nombre de besoins par thème souhaité (priorisation).
        $consolidation = BesoinFormation::selectRaw('theme_souhaite, domaine, COUNT(*) as total')
            ->when($request->filled('annee'), fn ($q) => $q->where('annee_recueil', $request->input('annee')))
            ->groupBy('theme_souhaite', 'domaine')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return view('besoins-formation.index', [
            'besoins'       => $besoins,
            'consolidation' => $consolidation,
            'domaines'      => DomaineFormation::options(),
            'annees'        => BesoinFormation::query()->select('annee_recueil')->distinct()->orderByDesc('annee_recueil')->pluck('annee_recueil'),
            'structures'    => Structure::where('actif', true)->orderBy('libelle')->pluck('libelle', 'id'),
            'filtres'       => $request->only(['annee', 'domaine', 'statut', 'structure_id']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('formations.manage');

        return view('besoins-formation.create', $this->formData() + [
            'agentSel' => $request->integer('agent'),
        ]);
    }

    public function store(StoreBesoinFormationRequest $request): RedirectResponse
    {
        BesoinFormation::create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()->route('besoins-formation.index')->with('success', 'Besoin de formation enregistré.');
    }

    public function edit(BesoinFormation $besoins_formation): View
    {
        $this->authorize('formations.manage');

        return view('besoins-formation.edit', $this->formData() + [
            'besoin' => $besoins_formation->load(['agent', 'structure']),
        ]);
    }

    public function update(StoreBesoinFormationRequest $request, BesoinFormation $besoins_formation): RedirectResponse
    {
        $besoins_formation->update($request->validated());

        return redirect()->route('besoins-formation.index')->with('success', 'Besoin de formation mis à jour.');
    }

    public function destroy(BesoinFormation $besoins_formation): RedirectResponse
    {
        $this->authorize('formations.manage');
        $besoins_formation->delete();

        return back()->with('success', 'Besoin de formation supprimé.');
    }

    private function formData(): array
    {
        return [
            'agents'     => Agent::orderBy('nom')->orderBy('prenoms')->get(['id', 'matricule', 'nom', 'prenoms'])
                ->mapWithKeys(fn ($a) => [$a->id => $a->matricule . ' — ' . $a->nom_complet]),
            'structures' => Structure::where('actif', true)->orderBy('libelle')->pluck('libelle', 'id'),
            'domaines'   => DomaineFormation::options(),
            'causes'     => CauseDifficulte::options(),
            'solutions'  => SolutionBesoin::options(),
            'niveaux'    => NiveauMaitrise::options(),
            'frequences' => FrequenceTache::options(),
            'statuts'    => ['exprime' => 'Exprimé', 'retenu' => 'Retenu', 'rejete' => 'Rejeté', 'planifie' => 'Planifié'],
        ];
    }
}
