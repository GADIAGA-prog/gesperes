<?php

namespace App\Http\Controllers;

use App\Services\GpecService;
use Illuminate\View\View;

class GpecController extends Controller
{
    public function __construct(private GpecService $service) {}

    public function index(): View
    {
        $this->authorize('gpec.view');

        $annees = 5;

        return view('gpec.index', [
            'annees'        => $annees,
            'departs'       => $this->service->departsParAnnee($annees),
            'effectifs'     => $this->service->effectifParEmploi(),
            'besoins'       => $this->service->besoinsParEmploi($annees),
            'competences'   => $this->service->cartographieCompetences(),
        ]);
    }
}
