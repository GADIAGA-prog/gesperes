<p class="text-sm text-gray-500 mb-3">{{ $agents->total() }} agent(s) trouvé(s)</p>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <x-tri.entete cle="matricule">Matricule</x-tri.entete>
                <x-tri.entete cle="nom">Nom & prénoms</x-tri.entete>
                <x-tri.entete cle="emploi">Emploi</x-tri.entete>
                <x-tri.entete cle="structure">Structure</x-tri.entete>
                <th class="table-head">Service</th>
                <x-tri.entete cle="statut">Dossier</x-tri.entete>
                <th class="table-head text-right">Actions</th>
            </tr>
            {{-- Filtres par colonne : appliqués en AJAX au "change" (Entrée / sortie de champ). --}}
            @php $f = (array) request('f', []); @endphp
            <tr class="bg-gray-50/70">
                <th class="px-3 py-1.5 font-normal"><input type="text" data-filtre name="f[matricule]" value="{{ $f['matricule'] ?? '' }}" placeholder="Mle…" class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400"></th>
                <th class="px-3 py-1.5 font-normal"><input type="text" data-filtre name="f[nom]" value="{{ $f['nom'] ?? '' }}" placeholder="Nom/prénoms…" class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400"></th>
                <th class="px-3 py-1.5 font-normal"><input type="text" data-filtre name="f[emploi]" value="{{ $f['emploi'] ?? '' }}" placeholder="Emploi…" class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400"></th>
                <th class="px-3 py-1.5 font-normal" colspan="2"><input type="text" data-filtre name="f[structure]" value="{{ $f['structure'] ?? '' }}" placeholder="Structure / service…" class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400"></th>
                <th class="px-3 py-1.5 font-normal"><input type="text" data-filtre name="f[statut]" value="{{ $f['statut'] ?? '' }}" placeholder="Dossier…" class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400"></th>
                <th class="px-3 py-1.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($agents as $agent)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-sm">{{ $agent->matricule }}{{ $agent->cle }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('agents.show', $agent) }}" class="font-medium text-institution-700 hover:underline">{{ $agent->nom_complet }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->emploi?->libelle ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->structure?->niveauStructure() ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->structure?->niveauService() ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $agent->statut_dossier?->color() }}">{{ $agent->statut_dossier?->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('agents.show', $agent) }}" class="text-gray-500 hover:text-institution-600">Voir</a>
                        @can('agents.update')
                            <a href="{{ route('agents.edit', $agent) }}" class="ml-2 text-gray-500 hover:text-institution-600">Modifier</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun agent trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4" data-pagination>{{ $agents->links() }}</div>
