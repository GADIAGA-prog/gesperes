<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffectationRequest;
use App\Models\Affectation;
use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Structure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AffectationController extends Controller
{
    public function index(): View
    {
        $this->authorize('affectations.view');

        $affectations = Affectation::with(['agent', 'ancienneStructure', 'nouvelleStructure'])
            ->latest('date_effet')
            ->paginate(20);

        return view('affectations.index', ['affectations' => $affectations]);
    }

    public function create(): View
    {
        $this->authorize('affectations.create');

        return view('affectations.create', [
            'agents'     => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'cascade'    => Structure::cascadeConfig(),
            'fonctions'  => Fonction::orderBy('libelle')->pluck('libelle', 'id'),
        ]);
    }

    /** Situation actuelle d'un agent (ancienne affectation) pour le formulaire d'affectation. */
    public function situationAgent(Agent $agent): JsonResponse
    {
        $this->authorize('affectations.create');
        $agent->load(['structure', 'fonction']);

        return response()->json([
            'structure'        => $agent->structure?->cheminComplet(),
            'fonction'         => $agent->fonction?->libelle,
            'date_affectation' => $agent->date_affectation?->format('d/m/Y'),
        ]);
    }

    public function store(StoreAffectationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $agent = Agent::findOrFail($data['agent_id']);

        DB::transaction(function () use ($agent, $data, $request) {
            // Historise l'affectation
            Affectation::create([
                'agent_id'              => $agent->id,
                'ancienne_structure_id' => $agent->structure_id,
                'nouvelle_structure_id' => $data['nouvelle_structure_id'],
                'ancienne_fonction_id'  => $agent->fonction_id,
                'nouvelle_fonction_id'  => $data['nouvelle_fonction_id'] ?? null,
                'date_effet'            => $data['date_effet'],
                'reference_acte'        => $data['reference_acte'] ?? null,
                'motif'                 => $data['motif'] ?? null,
                'created_by'            => $request->user()->id,
            ]);

            // Met à jour l'affectation courante de l'agent
            $agent->update([
                'structure_id'     => $data['nouvelle_structure_id'],
                'fonction_id'      => $data['nouvelle_fonction_id'] ?? $agent->fonction_id,
                'date_affectation' => $data['date_effet'],
            ]);
        });

        return redirect()->route('affectations.index')
            ->with('success', "Affectation de {$agent->nom_complet} enregistrée.");
    }

    public function show(Affectation $affectation): View
    {
        $this->authorize('affectations.view');
        $affectation->load(['agent', 'ancienneStructure', 'nouvelleStructure', 'ancienneFonction', 'nouvelleFonction', 'createur']);

        return view('affectations.show', ['affectation' => $affectation]);
    }
}
