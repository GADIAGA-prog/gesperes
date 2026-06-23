<?php

namespace App\Http\Controllers;

use App\Enums\EtapeDossier;
use App\Enums\StatutSuiviDossier;
use App\Http\Requests\StoreNatureDossierRequest;
use App\Http\Requests\StoreSuiviDossierRequest;
use App\Http\Requests\TransmettreDossierRequest;
use App\Http\Requests\UpdateSuiviDossierRequest;
use App\Models\Agent;
use App\Models\NatureDossier;
use App\Models\Structure;
use App\Models\SuiviDossier;
use App\Services\SuiviDossierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuiviDossierController extends Controller
{
    public function __construct(private SuiviDossierService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('suivi.view');

        $dossiers = SuiviDossier::with(['structure', 'nature', 'serviceActuel', 'agentActuel'])
            ->when($request->filled('recherche'), fn ($q) => $q->where('reference_bordereau', 'like', '%' . $request->input('recherche') . '%'))
            ->when($request->filled('nature_id'), fn ($q) => $q->where('nature_id', $request->input('nature_id')))
            ->when($request->filled('etape'), fn ($q) => $q->where('etape', $request->input('etape')))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->input('statut')))
            ->when($request->filled('structure_id'), fn ($q) => $q->where('structure_id', $request->input('structure_id')))
            ->latest('date_reception')
            ->paginate(20)
            ->withQueryString();

        // Filtre « en retard » appliqué après calcul (le délai n'est pas stocké).
        if ($request->boolean('en_retard')) {
            $dossiers->setCollection($dossiers->getCollection()->filter->en_retard->values());
        }

        return view('suivi-dossiers.index', [
            'dossiers'   => $dossiers,
            'natures'    => NatureDossier::orderBy('libelle')->pluck('libelle', 'id'),
            'etapes'     => EtapeDossier::options(),
            'statuts'    => StatutSuiviDossier::options(),
            'structures' => $this->structuresOptions(),
            'filtres'    => $request->only(['recherche', 'nature_id', 'etape', 'statut', 'structure_id', 'en_retard']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('suivi.manage');

        return view('suivi-dossiers.create', $this->formData() + [
            'structureSel' => $request->integer('structure'),
        ]);
    }

    public function store(StoreSuiviDossierRequest $request): RedirectResponse
    {
        $dossier = $this->service->creer($request->validated(), $request->user()->id);

        return redirect()->route('suivi-dossiers.show', $dossier)
            ->with('success', "Dossier {$dossier->reference_bordereau} enregistré.");
    }

    public function show(SuiviDossier $suivi_dossier): View
    {
        $this->authorize('suivi.view');

        return view('suivi-dossiers.show', [
            'dossier' => $suivi_dossier->load([
                'structure', 'nature', 'serviceActuel', 'agentActuel', 'createur',
                'etapes.service', 'etapes.agent', 'etapes.createur',
            ]),
            'etapes'  => EtapeDossier::options(),
            'services' => $this->structuresOptions(),
            'agents'  => $this->agentsOptions(),
        ]);
    }

    public function edit(SuiviDossier $suivi_dossier): View
    {
        $this->authorize('suivi.manage');

        return view('suivi-dossiers.edit', $this->formData() + [
            'dossier' => $suivi_dossier,
        ]);
    }

    public function update(UpdateSuiviDossierRequest $request, SuiviDossier $suivi_dossier): RedirectResponse
    {
        $suivi_dossier->update($request->validated());

        return redirect()->route('suivi-dossiers.show', $suivi_dossier)
            ->with('success', 'Dossier mis à jour.');
    }

    public function destroy(SuiviDossier $suivi_dossier): RedirectResponse
    {
        $this->authorize('suivi.manage');
        $suivi_dossier->delete();

        return redirect()->route('suivi-dossiers.index')->with('success', 'Dossier supprimé.');
    }

    /** Transmission du dossier à une nouvelle étape / un nouveau service. */
    public function transmettre(TransmettreDossierRequest $request, SuiviDossier $suivi_dossier): RedirectResponse
    {
        $this->service->transmettre($suivi_dossier, $request->validated(), $request->user()->id);

        return redirect()->route('suivi-dossiers.show', $suivi_dossier)
            ->with('success', 'Mouvement enregistré.');
    }

    /** Clôture du dossier (fige le calcul du respect du délai). */
    public function cloturer(Request $request, SuiviDossier $suivi_dossier): RedirectResponse
    {
        $this->authorize('suivi.manage');

        $data = $request->validate([
            'date_traitement' => ['nullable', 'date', 'after_or_equal:' . $suivi_dossier->date_reception->toDateString()],
            'commentaire'     => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->cloturer($suivi_dossier, $data, $request->user()->id);

        return redirect()->route('suivi-dossiers.show', $suivi_dossier)
            ->with('success', 'Dossier clôturé.');
    }

    /* ── Référentiel des natures de dossier ────────────────── */

    public function natures(): View
    {
        $this->authorize('suivi.manage');

        return view('suivi-dossiers.natures', [
            'natures' => NatureDossier::withCount('dossiers')->orderBy('libelle')->get(),
        ]);
    }

    public function storeNature(StoreNatureDossierRequest $request): RedirectResponse
    {
        NatureDossier::create($request->validated() + ['actif' => $request->boolean('actif', true)]);

        return back()->with('success', 'Nature de dossier ajoutée.');
    }

    public function updateNature(StoreNatureDossierRequest $request, NatureDossier $nature): RedirectResponse
    {
        $nature->update($request->validated() + ['actif' => $request->boolean('actif')]);

        return back()->with('success', 'Nature de dossier mise à jour.');
    }

    public function destroyNature(NatureDossier $nature): RedirectResponse
    {
        $this->authorize('suivi.manage');
        $nature->delete();

        return back()->with('success', 'Nature de dossier supprimée.');
    }

    /* ── Helpers ───────────────────────────────────────────── */

    private function formData(): array
    {
        return [
            'natures'    => NatureDossier::where('actif', true)->orderBy('libelle')->get(),
            'structures' => $this->structuresOptions(),
            'agents'     => $this->agentsOptions(),
            'etapes'     => EtapeDossier::options(),
            'statuts'    => StatutSuiviDossier::options(),
        ];
    }

    private function structuresOptions()
    {
        return Structure::where('actif', true)->orderBy('libelle')->pluck('libelle', 'id');
    }

    private function agentsOptions()
    {
        return Agent::orderBy('nom')->orderBy('prenoms')->get(['id', 'matricule', 'nom', 'prenoms'])
            ->mapWithKeys(fn ($a) => [$a->id => $a->matricule . ' — ' . $a->nom_complet]);
    }
}
