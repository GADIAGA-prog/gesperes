<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Document;
use App\Models\NotificationRh;
use App\Services\AlerteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlerteController extends Controller
{
    public function __construct(private AlerteService $service) {}

    public function index(): View
    {
        $this->authorize('alertes.view');

        $moisRetraite = (int) config('gesperes.retraite.alerte_mois_avant', 24);
        $joursDoc = 60;

        $retraites = Agent::with(['categorie', 'structure'])
            ->whereNotNull('date_retraite')
            ->whereDate('date_retraite', '>=', now())
            ->whereDate('date_retraite', '<=', now()->addMonths($moisRetraite))
            ->orderBy('date_retraite')
            ->get();

        $docsExpires = Document::with('agent')->where('archive', false)->expires()
            ->orderBy('date_expiration')->get();

        $docsBientot = Document::with('agent')->where('archive', false)
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '>=', now())
            ->whereDate('date_expiration', '<=', now()->addDays($joursDoc))
            ->orderBy('date_expiration')->get();

        $notifications = NotificationRh::with('agent')->latest()->limit(50)->get();

        return view('alertes.index', [
            'retraites'     => $retraites,
            'docsExpires'   => $docsExpires,
            'docsBientot'   => $docsBientot,
            'moisRetraite'  => $moisRetraite,
            'joursDoc'      => $joursDoc,
            'notifications' => $notifications,
            'nonLues'       => NotificationRh::nonLues()->count(),
        ]);
    }

    public function generer(): RedirectResponse
    {
        $this->authorize('alertes.view');
        $n = $this->service->generer();

        return back()->with('success', "{$n} nouvelle(s) alerte(s) générée(s).");
    }

    public function marquerLu(NotificationRh $notification): RedirectResponse
    {
        $this->authorize('alertes.view');
        $notification->update(['lu' => true]);

        return back();
    }

    public function marquerToutLu(): RedirectResponse
    {
        $this->authorize('alertes.view');
        NotificationRh::nonLues()->update(['lu' => true]);

        return back()->with('success', 'Toutes les alertes ont été marquées comme lues.');
    }
}
