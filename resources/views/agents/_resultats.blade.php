<p class="text-sm text-gray-500 mb-3">{{ $agents->total() }} agent(s) trouvé(s)</p>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="table-head">Matricule</th>
                <th class="table-head">Nom & prénoms</th>
                <th class="table-head">Emploi</th>
                <th class="table-head">Structure</th>
                <th class="table-head">Service</th>
                <th class="table-head">Dossier</th>
                <th class="table-head text-right">Actions</th>
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
