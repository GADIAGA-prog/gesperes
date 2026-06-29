<?php

namespace App\Http\Controllers;

use App\Enums\ModeIndemnite;
use App\Http\Requests\StoreAgentIndemniteRequest;
use App\Http\Requests\StoreIndemniteRequest;
use App\Models\Agent;
use App\Models\AgentIndemnite;
use App\Models\Indemnite;
use App\Services\IndemniteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class IndemniteController extends Controller
{
    public function __construct(private IndemniteService $service) {}

    /* ───────── Référentiel des indemnités ───────── */

    public function index(): View
    {
        $this->authorize('indemnites.view');

        return view('indemnites.index', [
            'indemnites' => Indemnite::orderBy('libelle')->withCount('attributions')->get(),
            'modes'      => ModeIndemnite::options(),
        ]);
    }

    public function store(StoreIndemniteRequest $request): RedirectResponse
    {
        Indemnite::create($request->validated());

        return back()->with('success', 'Indemnité ajoutée au référentiel.');
    }

    public function update(StoreIndemniteRequest $request, Indemnite $indemnite): RedirectResponse
    {
        $indemnite->update($request->validated());

        return back()->with('success', 'Indemnité mise à jour.');
    }

    public function destroy(Indemnite $indemnite): RedirectResponse
    {
        $this->authorize('indemnites.manage');
        $indemnite->delete();

        return back()->with('success', 'Indemnité supprimée.');
    }

    /* ───────── Attribution aux agents ───────── */

    public function agent(Agent $agent): View
    {
        $this->authorize('indemnites.view');

        $attributions = $agent->indemnites()->with('indemnite')->get();
        $calculees = $this->service->pourAgent($agent->load(['emploi', 'fonction', 'categorie', 'echelle', 'localite.zone']));

        return view('indemnites.agent', [
            'agent'        => $agent,
            'attributions' => $attributions,
            'total'        => $attributions->where('actif', true)->sum('montant'),
            'indemnites'   => Indemnite::where('actif', true)->orderBy('libelle')->get(),
            'calculees'    => $calculees,
            'totalCalcule' => collect($calculees)->sum('montant'),
        ]);
    }

    public function attribuer(StoreAgentIndemniteRequest $request, Agent $agent): RedirectResponse
    {
        $data = $request->validated();
        $indemnite = Indemnite::findOrFail($data['indemnite_id']);

        // Montant : valeur saisie sinon calcul automatique depuis le référentiel.
        $montant = $data['montant'] ?? $this->service->calculer($agent, $indemnite);

        $agent->indemnites()->create([
            'indemnite_id' => $indemnite->id,
            'montant'      => $montant,
            'date_debut'   => $data['date_debut'] ?? null,
            'date_fin'     => $data['date_fin'] ?? null,
            'actif'        => true,
            'observation'  => $data['observation'] ?? null,
            'created_by'   => $request->user()->id,
        ]);

        return back()->with('success', "Indemnité « {$indemnite->libelle} » attribuée.");
    }

    public function retirer(AgentIndemnite $attribution): RedirectResponse
    {
        $this->authorize('indemnites.manage');
        $agentId = $attribution->agent_id;
        $attribution->delete();

        return redirect()->route('agents.indemnites.agent', $agentId)
            ->with('success', 'Attribution retirée.');
    }

    /**
     * Calcule les indemnités automatiques et les fige comme attributions :
     * responsabilité (RESP), allocation familiale (ALLOC) ET barèmes — toute
     * indemnité dont le montant calculé est strictement positif.
     */
    public function figer(Agent $agent): RedirectResponse
    {
        $this->authorize('indemnites.manage');

        // 'fonction' est requise pour la responsabilité, 'indice' pour les % (résidence).
        $agent->load(['emploi', 'fonction', 'categorie', 'echelle', 'localite.zone', 'indice']);
        $n = 0;

        foreach ($this->service->pourAgent($agent) as $c) {
            if ($c['montant'] <= 0) {
                continue;
            }
            $agent->indemnites()->updateOrCreate(
                ['indemnite_id' => $c['indemnite']->id],
                ['montant' => $c['montant'], 'actif' => true, 'date_debut' => now()->toDateString(), 'created_by' => auth()->id()]
            );
            $n++;
        }

        return back()->with('success', "{$n} indemnité(s) calculée(s) et figée(s) (responsabilité, allocation familiale et barèmes).");
    }

    /** Bulletin de rémunération (salaire indiciaire + indemnités) au format PDF. */
    public function bulletin(Agent $agent): Response
    {
        $this->authorize('indemnites.view');

        $agent->load(['emploi', 'fonction', 'categorie', 'echelle', 'classe', 'echelon', 'indice', 'localite.zone', 'structure']);

        // Vue complète : barèmes + responsabilité + allocation + attributions manuelles.
        $indemnites = collect($this->service->remuneration($agent))->filter(fn ($c) => $c['montant'] > 0)->values();
        $salaire = (float) ($agent->indice?->salaire_indiciaire ?? 0);
        $totalIndem = (float) $indemnites->sum('montant');

        return Pdf::loadView('indemnites.bulletin', [
            'agent'      => $agent,
            'salaire'    => $salaire,
            'indemnites' => $indemnites,
            'totalIndem' => $totalIndem,
            'brut'       => $salaire + $totalIndem,
        ])->setPaper('a4', 'portrait')->download('bulletin_' . $agent->matricule . '_' . now()->format('Ymd') . '.pdf');
    }
}
