<?php

namespace App\Http\Controllers;

use App\Enums\CategorieAbsence;
use App\Enums\StatutConge;
use App\Models\Agent;
use App\Models\Conge;
use App\Models\MotifAbsence;
use App\Services\SoldeCongeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CongeController extends Controller
{
    public function __construct(private SoldeCongeService $soldes) {}

    public function index(Request $request): View
    {
        $this->authorize('conges.view');

        $conges = Conge::with(['agent', 'motifAbsence', 'validateur'])
            ->when($request->filled('agent_id'), fn ($q) => $q->where('agent_id', $request->integer('agent_id')))
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->input('statut')))
            ->latest('date_debut')
            ->paginate(20)
            ->withQueryString();

        // Solde de l'agent sélectionné (le cas échéant).
        $agent = $request->filled('agent_id') ? Agent::find($request->integer('agent_id')) : null;
        $solde = $agent ? $this->soldes->pour($agent) : null;

        return view('conges.index', [
            'conges'  => $conges,
            'agents'  => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'statuts' => StatutConge::options(),
            'agent'   => $agent,
            'solde'   => $solde,
            'filtres' => $request->only(['agent_id', 'statut']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('conges.request');

        return view('conges.create', [
            'agents' => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'motifs' => $this->motifsDemandables(),
            'agentPreselect' => $request->integer('agent_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('conges.request');

        $data = $request->validate([
            'agent_id'           => ['required', 'exists:agents,id'],
            'motif_absence_id'   => ['required', 'exists:motifs_absence,id'],
            'date_debut'         => ['required', 'date'],
            'date_fin'           => ['required', 'date', 'after_or_equal:date_debut'],
            'nombre_jours'       => ['nullable', 'integer', 'min:1'],
            'motif'              => ['nullable', 'string', 'max:1000'],
            'reference_decision' => ['nullable', 'string', 'max:255'],
        ]);

        $jours = $data['nombre_jours']
            ?? SoldeCongeService::joursOuvres(Carbon::parse($data['date_debut']), Carbon::parse($data['date_fin']));

        Conge::create([
            'agent_id'           => $data['agent_id'],
            'motif_absence_id'   => $data['motif_absence_id'],
            'date_debut'         => $data['date_debut'],
            'date_fin'           => $data['date_fin'],
            'nombre_jours'       => $jours,
            'statut'             => StatutConge::DEMANDE->value,
            'motif'              => $data['motif'] ?? null,
            'reference_decision' => $data['reference_decision'] ?? null,
            'saisi_par'          => $request->user()->id,
        ]);

        return redirect()->route('conges.index', ['agent_id' => $data['agent_id']])
            ->with('success', "Demande de congé enregistrée ({$jours} jour(s)).");
    }

    public function valider(Conge $conge, Request $request): RedirectResponse
    {
        $this->authorize('conges.validate');

        $conge->update([
            'statut'          => StatutConge::VALIDE->value,
            'valide_par'      => $request->user()->id,
            'date_validation' => now(),
        ]);

        return back()->with('success', 'Congé validé.');
    }

    public function refuser(Conge $conge, Request $request): RedirectResponse
    {
        $this->authorize('conges.validate');

        $conge->update([
            'statut'          => StatutConge::REFUSE->value,
            'valide_par'      => $request->user()->id,
            'date_validation' => now(),
        ]);

        return back()->with('success', 'Demande refusée.');
    }

    public function annuler(Conge $conge): RedirectResponse
    {
        $this->authorize('conges.request');

        $conge->update(['statut' => StatutConge::ANNULE->value]);

        return back()->with('success', 'Demande annulée.');
    }

    /** Motifs susceptibles de faire l'objet d'une demande (congé ou autorisation). */
    private function motifsDemandables()
    {
        return MotifAbsence::where('actif', true)
            ->whereIn('categorie', [CategorieAbsence::CONGE->value, CategorieAbsence::AUTORISATION->value])
            ->orderBy('libelle')
            ->get();
    }
}
