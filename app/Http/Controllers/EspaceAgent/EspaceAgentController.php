<?php

namespace App\Http\Controllers\EspaceAgent;

use App\Enums\TypeDocument;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Document;
use App\Models\NotificationRh;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Espace agent (self-service). Toutes les données sont strictement limitées au
 * dossier de l'agent rattaché au compte connecté ; aucune permission Spatie
 * d'administration n'est requise — le cloisonnement repose sur le middleware
 * EstAgentIndividuel et le périmètre par user_id.
 */
class EspaceAgentController extends Controller
{
    /** Dossier de l'agent connecté (le middleware garantit son existence). */
    private function agent(Request $request): Agent
    {
        return $request->user()->agent;
    }

    public function dashboard(Request $request): View
    {
        $agent = $this->agent($request)->load([
            'emploi', 'fonction', 'structure', 'positionAdministrative',
        ]);

        return view('espace-agent.dashboard', [
            'agent'             => $agent,
            'nbActes'           => $agent->documents()->where('archive', false)->count(),
            'nbNotifsNonLues'   => NotificationRh::where('agent_id', $agent->id)->nonLues()->count(),
            'dernieresNotifs'   => NotificationRh::where('agent_id', $agent->id)->latest()->take(5)->get(),
        ]);
    }

    public function profil(Request $request): View
    {
        $agent = $this->agent($request)->load([
            'emploi', 'fonction', 'poste', 'categorie', 'echelle', 'classe', 'echelon',
            'indice', 'positionAdministrative', 'structure', 'specialite', 'typeEnseignement',
        ]);

        return view('espace-agent.profil', ['agent' => $agent]);
    }

    public function actes(Request $request): View
    {
        $agent = $this->agent($request);

        $documents = $agent->documents()
            ->where('archive', false)
            ->when($request->filled('type'), fn ($q) => $q->where('type_document', $request->input('type')))
            ->latest('date_document')
            ->paginate(20)
            ->withQueryString();

        return view('espace-agent.actes', [
            'agent'     => $agent,
            'documents' => $documents,
            'types'     => TypeDocument::options(),
            'filtres'   => $request->only(['type']),
        ]);
    }

    /** Téléchargement sécurisé : uniquement un document du dossier de l'agent. */
    public function telecharger(Request $request, Document $document): StreamedResponse
    {
        abort_unless($document->agent_id === $this->agent($request)->id, 403);
        abort_if($document->archive, 404);
        abort_unless(Storage::disk('documents')->exists($document->chemin), 404);

        return Storage::disk('documents')->download($document->chemin, $document->nom_original);
    }

    public function notifications(Request $request): View
    {
        $agent = $this->agent($request);

        return view('espace-agent.notifications', [
            'agent'         => $agent,
            'notifications' => NotificationRh::where('agent_id', $agent->id)
                ->latest()
                ->paginate(25),
        ]);
    }

    public function marquerLue(Request $request, NotificationRh $notification): RedirectResponse
    {
        abort_unless($notification->agent_id === $this->agent($request)->id, 403);

        $notification->update(['lu' => true]);

        return back();
    }

    public function marquerToutesLues(Request $request): RedirectResponse
    {
        NotificationRh::where('agent_id', $this->agent($request)->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return back()->with('success', 'Toutes vos notifications ont été marquées comme lues.');
    }
}
