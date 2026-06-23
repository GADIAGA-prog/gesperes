<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    public function index(): View
    {
        $this->authorize('dashboard.view');

        return view('dashboard.index', [
            'cartes'         => $this->dashboard->cartes(),
            'parSexe'        => $this->dashboard->effectifParSexe(),
            'parRegion'      => $this->dashboard->effectifParRegion(),
            'parEmploi'      => $this->dashboard->effectifParEmploi(),
            'parCategorie'   => $this->dashboard->effectifParCategorie(),
            'masse'          => $this->dashboard->masseSalariale(),
            'departsRetraite' => $this->dashboard->departsRetraiteParAnnee(),
            'trancheAge'     => $this->dashboard->trancheAge(),
        ]);
    }
}
