<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActionFormationRequest;
use App\Models\ActionFormation;
use Illuminate\Http\RedirectResponse;

/**
 * Actions de formation d'un programme annuel (créées / modifiées depuis la fiche
 * du plan). Pas de vues dédiées : les formulaires sont intégrés à la vue du plan.
 */
class ActionFormationController extends Controller
{
    public function store(StoreActionFormationRequest $request): RedirectResponse
    {
        $action = ActionFormation::create($request->validated());

        return redirect()
            ->route('plan-formation.show', $action->programme->plan_formation_id)
            ->with('success', 'Action de formation ajoutée.');
    }

    public function update(StoreActionFormationRequest $request, ActionFormation $action): RedirectResponse
    {
        $action->update($request->validated());

        return redirect()
            ->route('plan-formation.show', $action->programme->plan_formation_id)
            ->with('success', 'Action de formation mise à jour.');
    }

    public function destroy(ActionFormation $action): RedirectResponse
    {
        $this->authorize('formations.manage');

        $planId = $action->programme->plan_formation_id;
        $action->delete();

        return redirect()->route('plan-formation.show', $planId)
            ->with('success', 'Action de formation supprimée.');
    }
}
