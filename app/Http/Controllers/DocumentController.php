<?php

namespace App\Http\Controllers;

use App\Enums\TypeDocument;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Agent;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /** Recherche documentaire globale (tous les agents). */
    public function recherche(Request $request): View
    {
        $this->authorize('documents.view');

        $documents = Document::query()
            ->with('agent')
            ->recherche($request->input('q'))
            ->when($request->filled('agent_id'), fn ($q) => $q->where('agent_id', $request->input('agent_id')))
            ->when($request->filled('type_document'), fn ($q) => $q->where('type_document', $request->input('type_document')))
            ->when($request->input('etat') === 'archive', fn ($q) => $q->where('archive', true))
            ->when($request->input('etat') === 'actif', fn ($q) => $q->where('archive', false))
            ->when($request->input('etat') === 'expire', fn ($q) => $q->expires())
            ->latest('date_document')
            ->paginate(25)
            ->withQueryString();

        return view('documents.recherche', [
            'documents' => $documents,
            'types'     => TypeDocument::options(),
            'agents'    => Agent::orderBy('nom')->get(['id', 'matricule', 'nom', 'prenoms']),
            'filtres'   => $request->only(['q', 'agent_id', 'type_document', 'etat']),
        ]);
    }

    public function index(Agent $agent): View
    {
        $this->authorize('documents.view');

        return view('documents.index', [
            'agent'        => $agent,
            'documents'    => $agent->documents()->with('evenementCarriere')->latest()->get(),
            'types'        => TypeDocument::cases(),
            'evenements'   => $agent->evenementsCarriere()->get(),
        ]);
    }

    public function store(StoreDocumentRequest $request, Agent $agent): RedirectResponse
    {
        foreach ($request->file('fichiers') as $fichier) {
            $chemin = $fichier->store("agents/{$agent->id}", 'documents');

            $agent->documents()->create([
                'carriere_evenement_id' => $request->input('carriere_evenement_id'),
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
        }

        $n = count($request->file('fichiers'));

        return back()->with('success', "{$n} document(s) téléversé(s) avec succès.");
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        abort_unless(Storage::disk('documents')->exists($document->chemin), 404);

        return Storage::disk('documents')->download($document->chemin, $document->nom_original);
    }

    /** Bascule l'état d'archivage d'un document. */
    public function archiver(Document $document): RedirectResponse
    {
        $this->authorize('create', Document::class); // documents.upload

        $document->update([
            'archive'     => ! $document->archive,
            'archived_at' => $document->archive ? null : now(),
        ]);

        return back()->with('success', $document->archive ? 'Document archivé.' : 'Document désarchivé.');
    }

    /** Exporte tout le dossier d'un agent en archive ZIP. */
    public function exportZip(Agent $agent): BinaryFileResponse
    {
        $this->authorize('documents.download');

        $documents = $agent->documents()->get();
        abort_if($documents->isEmpty(), 404, 'Aucun document à exporter.');

        $nomZip = 'dossier_' . $agent->matricule . '_' . now()->format('Ymd_His') . '.zip';
        $cheminZip = storage_path('app/' . $nomZip);

        $zip = new \ZipArchive();
        $zip->open($cheminZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($documents as $i => $doc) {
            if (! Storage::disk('documents')->exists($doc->chemin)) {
                continue;
            }
            $prefixe = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) . '_' . $doc->type_document?->value;
            $extension = pathinfo($doc->nom_original, PATHINFO_EXTENSION) ?: 'pdf';
            $zip->addFile(Storage::disk('documents')->path($doc->chemin), "{$prefixe}.{$extension}");
        }
        $zip->close();

        return response()->download($cheminZip, $nomZip)->deleteFileAfterSend(true);
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
