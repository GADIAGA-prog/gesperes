<?php

namespace App\Http\Controllers;

use App\Enums\AxeFormation;
use App\Enums\DomaineFormation;
use App\Enums\NiveauCompetence;
use App\Enums\PublicCibleFormation;
use App\Enums\StatutActionFormation;
use App\Enums\StatutPlanFormation;
use App\Enums\StrategieFormation;
use App\Enums\TypeFormationModalite;
use App\Http\Requests\StorePlanFormationRequest;
use App\Http\Requests\StoreProgrammeFormationRequest;
use App\Http\Requests\UpdatePlanFormationRequest;
use App\Models\PlanFormation;
use App\Models\ProgrammeFormation;
use App\Services\PlanFormationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanFormationController extends Controller
{
    public function __construct(private PlanFormationService $service) {}

    public function index(): View
    {
        $this->authorize('formations.view');

        $plans = PlanFormation::withCount('programmes')
            ->with('programmes.actions:id,programme_formation_id,cout,nombre_agents')
            ->latest('annee_debut')
            ->paginate(15);

        return view('plan-formation.index', ['plans' => $plans]);
    }

    public function create(): View
    {
        $this->authorize('formations.manage');

        return view('plan-formation.create', [
            'statuts' => StatutPlanFormation::options(),
        ]);
    }

    public function store(StorePlanFormationRequest $request): RedirectResponse
    {
        $plan = $this->service->creerPlan($request->validated(), $request->user()->id);

        return redirect()->route('plan-formation.show', $plan)
            ->with('success', "Plan « {$plan->intitule} » créé avec {$plan->programmes()->count()} programme(s) annuel(s).");
    }

    public function show(PlanFormation $plan_formation): View
    {
        $this->authorize('formations.view');

        $plan_formation->load(['programmes.actions']);

        // Indicateurs de réalisation + totaux par programme.
        $synthese = [];
        foreach ($plan_formation->programmes as $programme) {
            $this->service->decorerRealisation($programme->actions);
            $synthese[$programme->id] = $this->service->totauxActions($programme->actions);
        }

        return view('plan-formation.show', [
            'plan'      => $plan_formation,
            'synthese'  => $synthese,
            'enums'     => $this->actionEnums(),
        ]);
    }

    public function edit(PlanFormation $plan_formation): View
    {
        $this->authorize('formations.manage');

        return view('plan-formation.edit', [
            'plan'    => $plan_formation,
            'statuts' => StatutPlanFormation::options(),
        ]);
    }

    public function update(UpdatePlanFormationRequest $request, PlanFormation $plan_formation): RedirectResponse
    {
        $plan_formation->update($request->validated());

        return redirect()->route('plan-formation.show', $plan_formation)
            ->with('success', 'Plan de formation mis à jour.');
    }

    public function destroy(PlanFormation $plan_formation): RedirectResponse
    {
        $this->authorize('formations.manage');
        $plan_formation->delete();

        return redirect()->route('plan-formation.index')->with('success', 'Plan de formation supprimé.');
    }

    /** Export PDF du plan complet (programmes + synthèse des actions). */
    public function pdf(PlanFormation $plan_formation)
    {
        $this->authorize('formations.view');

        $plan_formation->load(['programmes.actions']);
        $synthese = [];
        foreach ($plan_formation->programmes as $programme) {
            $synthese[$programme->id] = $this->service->totauxActions($programme->actions);
        }

        return Pdf::loadView('plan-formation.pdf', [
            'plan'     => $plan_formation,
            'synthese' => $synthese,
        ])->setPaper('a4', 'landscape')->download('plan-formation-' . $plan_formation->id . '.pdf');
    }

    /* ── Programmes annuels ────────────────────────────────── */

    public function storeProgramme(StoreProgrammeFormationRequest $request, PlanFormation $plan_formation): RedirectResponse
    {
        ProgrammeFormation::updateOrCreate(
            ['plan_formation_id' => $plan_formation->id, 'annee' => $request->validated()['annee']],
            $request->validated()
        );

        return back()->with('success', 'Programme annuel enregistré.');
    }

    public function destroyProgramme(PlanFormation $plan_formation, ProgrammeFormation $programme): RedirectResponse
    {
        $this->authorize('formations.manage');
        abort_unless($programme->plan_formation_id === $plan_formation->id, 404);
        $programme->delete();

        return back()->with('success', 'Programme annuel supprimé.');
    }

    /** Listes d'options des enums utilisés par les formulaires d'action. */
    private function actionEnums(): array
    {
        return [
            'modalites'  => TypeFormationModalite::options(),
            'domaines'   => DomaineFormation::options(),
            'axes'       => AxeFormation::options(),
            'strategies' => StrategieFormation::options(),
            'niveaux'    => NiveauCompetence::options(),
            'publics'    => PublicCibleFormation::options(),
            'statuts'    => StatutActionFormation::options(),
        ];
    }
}
