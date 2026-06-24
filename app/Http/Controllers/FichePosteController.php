<?php

namespace App\Http\Controllers;

use App\Enums\NiveauCompetencePoste;
use App\Enums\PositionHierarchique;
use App\Enums\PositionMission;
use App\Enums\StatutFichePoste;
use App\Enums\TypeCompetence;
use App\Enums\TypePoste;
use App\Http\Requests\StoreFichePosteRequest;
use App\Http\Requests\UpdateFichePosteRequest;
use App\Models\Categorie;
use App\Models\Competence;
use App\Models\Emploi;
use App\Models\EmploiType;
use App\Models\FamilleProfessionnelle;
use App\Models\FichePoste;
use App\Models\Structure;
use App\Services\FichePosteCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FichePosteController extends Controller
{
    public function __construct(private FichePosteCodeService $codeService) {}

    public function index(Request $request): View
    {
        $this->authorize('fiches-poste.view');

        $fiches = FichePoste::query()
            ->with(['structure', 'familleProfessionnelle', 'emploiType'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) =>
                $w->where('intitule', 'like', '%' . $request->input('q') . '%')
                  ->orWhere('code', 'like', '%' . $request->input('q') . '%')))
            ->when($request->filled('structure_id'), fn ($q) => $q->where('structure_id', $request->input('structure_id')))
            ->when($request->filled('famille_professionnelle_id'), fn ($q) => $q->where('famille_professionnelle_id', $request->input('famille_professionnelle_id')))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->input('statut')))
            ->orderBy('intitule')
            ->paginate(20)
            ->withQueryString();

        return view('outils-grh.fiches-poste.index', [
            'fiches'    => $fiches,
            'familles'  => FamilleProfessionnelle::orderBy('libelle')->pluck('libelle', 'id'),
            'structures' => Structure::orderBy('libelle')->pluck('libelle', 'id'),
            'statuts'   => StatutFichePoste::cases(),
            'filtres'   => $request->only(['q', 'structure_id', 'famille_professionnelle_id', 'statut']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('fiches-poste.manage');

        return view('outils-grh.fiches-poste.create', $this->referentiels());
    }

    public function store(StoreFichePosteRequest $request): RedirectResponse
    {
        $this->authorize('fiches-poste.manage');

        $fiche = FichePoste::create($this->donnees($request) + ['created_by' => $request->user()->id]);
        $this->synchroniserEnfants($fiche, $request);
        $this->codifier($fiche, $request);

        return redirect()->route('fiches-poste.show', $fiche)
            ->with('success', "Fiche de poste « {$fiche->intitule} » créée.");
    }

    public function show(FichePoste $fichePoste): View
    {
        $this->authorize('fiches-poste.view');

        $fichePoste->load([
            'familleProfessionnelle', 'emploiType', 'emploi', 'categorie', 'structure',
            'activites', 'indicateurs', 'competences', 'createur',
        ]);

        return view('outils-grh.fiches-poste.show', ['fiche' => $fichePoste]);
    }

    public function edit(FichePoste $fichePoste): View
    {
        $this->authorize('fiches-poste.manage');

        $fichePoste->load(['activites', 'indicateurs', 'competences']);

        return view('outils-grh.fiches-poste.edit', array_merge(['fiche' => $fichePoste], $this->referentiels()));
    }

    public function update(UpdateFichePosteRequest $request, FichePoste $fichePoste): RedirectResponse
    {
        $this->authorize('fiches-poste.manage');

        $fichePoste->update($this->donnees($request));
        $this->synchroniserEnfants($fichePoste, $request);
        $this->codifier($fichePoste, $request);

        return redirect()->route('fiches-poste.show', $fichePoste)
            ->with('success', "Fiche de poste « {$fichePoste->intitule} » mise à jour.");
    }

    public function destroy(FichePoste $fichePoste): RedirectResponse
    {
        $this->authorize('fiches-poste.manage');

        $libelle = $fichePoste->intitule;
        $fichePoste->delete();

        return redirect()->route('fiches-poste.index')
            ->with('success', "Fiche de poste « {$libelle} » supprimée.");
    }

    public function pdf(FichePoste $fichePoste)
    {
        $this->authorize('fiches-poste.view');

        $fichePoste->load([
            'familleProfessionnelle', 'emploiType', 'emploi', 'categorie', 'structure',
            'activites', 'indicateurs', 'competences',
        ]);

        $nom = 'fiche_poste_' . ($fichePoste->code ?: $fichePoste->id) . '.pdf';

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('outils-grh.fiches-poste.pdf', ['fiche' => $fichePoste])
            ->setPaper('a4', 'portrait')
            ->download($nom);
    }

    /** Champs scalaires de la fiche (hors enfants). */
    private function donnees(Request $request): array
    {
        return $request->only([
            'code', 'intitule', 'type_poste', 'position_mission', 'position_hierarchique',
            'famille_professionnelle_id', 'emploi_type_id', 'emploi_id', 'famille_emplois',
            'categorie_id', 'structure_id', 'mission',
            'niveau_hierarchique_superieur', 'niveau_hierarchique_inferieur',
            'relations_internes', 'relations_externes',
            'moyens_generaux', 'moyens_specifiques',
            'niveau_etudes', 'domaine', 'specialite', 'experience_pro',
            'statut', 'version',
        ]);
    }

    /** (Re)crée activités, indicateurs et compétences depuis le formulaire. */
    private function synchroniserEnfants(FichePoste $fiche, Request $request): void
    {
        $fiche->activites()->delete();
        foreach (array_values($request->input('activites', [])) as $i => $ligne) {
            if (! empty($ligne['libelle'])) {
                $fiche->activites()->create([
                    'libelle' => $ligne['libelle'],
                    'taux_contribution' => $ligne['taux_contribution'] ?? null,
                    'ordre' => $i,
                ]);
            }
        }

        $fiche->indicateurs()->delete();
        foreach (array_values($request->input('indicateurs', [])) as $i => $ligne) {
            if (! empty($ligne['libelle'])) {
                $fiche->indicateurs()->create([
                    'libelle' => $ligne['libelle'],
                    'nature' => $ligne['nature'] ?? null,
                    'ordre' => $i,
                ]);
            }
        }

        $pivot = [];
        foreach ($request->input('competences', []) as $ligne) {
            if (! empty($ligne['competence_id'])) {
                $pivot[$ligne['competence_id']] = [
                    'type' => $ligne['type'] ?? TypeCompetence::METIER->value,
                    'niveau' => $ligne['niveau'] ?? NiveauCompetencePoste::APPLICATION->value,
                ];
            }
        }
        $fiche->competences()->sync($pivot);
    }

    /** Codification automatique si l'utilisateur n'a pas saisi de code manuel. */
    private function codifier(FichePoste $fiche, Request $request): void
    {
        if (filled($request->input('code'))) {
            return;
        }
        $fiche->loadMissing(['familleProfessionnelle', 'emploiType']);
        $fiche->update(['code' => $this->codeService->generer($fiche)]);
    }

    private function referentiels(): array
    {
        return [
            'famillesPro'  => FamilleProfessionnelle::orderBy('libelle')->get(['id', 'code', 'libelle']),
            'emploisTypes' => EmploiType::orderBy('libelle')->get(['id', 'code', 'libelle']),
            'emplois'      => Emploi::orderBy('libelle')->pluck('libelle', 'id'),
            'categories'   => Categorie::orderBy('code')->pluck('code', 'id'),
            'structures'   => Structure::orderBy('libelle')->pluck('libelle', 'id'),
            'competences'  => Competence::orderBy('libelle')->get(['id', 'libelle', 'domaine']),
            'typesPoste'   => TypePoste::options(),
            'positionsMission' => PositionMission::options(),
            'positionsHierarchique' => PositionHierarchique::options(),
            'typesCompetence' => TypeCompetence::options(),
            'niveauxCompetence' => NiveauCompetencePoste::options(),
            'statuts'      => StatutFichePoste::options(),
        ];
    }
}
