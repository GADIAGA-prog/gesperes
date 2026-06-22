<?php

namespace App\Http\Controllers;

use App\Enums\TypeDocument;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Agent;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Agent $agent): View
    {
        $this->authorize('documents.view');
        $documents = $agent->documents()->latest()->get();

        return view('documents.index', [
            'agent'     => $agent,
            'documents' => $documents,
            'types'     => TypeDocument::cases(),
        ]);
    }

    public function store(StoreDocumentRequest $request, Agent $agent): RedirectResponse
    {
        $fichier = $request->file('fichier');
        $chemin = $fichier->store("agents/{$agent->id}", 'documents');

        $agent->documents()->create([
            'type_document'   => $request->type_document,
            'reference'       => $request->reference,
            'date_document'   => $request->date_document,
            'date_expiration' => $request->date_expiration,
            'chemin'          => $chemin,
            'nom_original'    => $fichier->getClientOriginalName(),
            'mime'            => $fichier->getClientMimeType(),
            'taille'          => $fichier->getSize(),
            'statut'          => 'valide',
            'commentaire'     => $request->commentaire,
            'created_by'      => $request->user()->id,
        ]);

        return back()->with('success', 'Document téléversé avec succès.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        abort_unless(Storage::disk('documents')->exists($document->chemin), 404);

        return Storage::disk('documents')->download($document->chemin, $document->nom_original);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('documents')->delete($document->chemin);
        $agentId = $document->agent_id;
        $document->delete();

        return redirect()->route('agents.documents.index', $agentId)
            ->with('success', 'Document supprimé.');
    }
}
