@php $enfants = $tous->where('parent_id', $noeud->id); @endphp
<li>
    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50" style="margin-left: {{ $niveau * 1.25 }}rem">
        <div class="flex items-center gap-2 min-w-0">
            <span class="inline-flex h-6 items-center rounded bg-institution-50 px-2 text-[11px] font-medium text-institution-700">{{ $noeud->type?->label() }}</span>
            <a href="{{ route('structures.show', $noeud) }}" class="font-medium text-gray-800 hover:text-institution-600 truncate">{{ $noeud->libelle }}</a>
            <span class="text-xs text-gray-400 font-mono">{{ $noeud->code }}</span>
            @if ($noeud->agents_count)
                <span class="text-xs text-gray-400">· {{ $noeud->agents_count }} agent(s)</span>
            @endif
        </div>
        <div class="flex gap-2 text-sm shrink-0">
            @can('structures.update')<a href="{{ route('structures.edit', $noeud) }}" class="text-gray-400 hover:text-institution-600">Modifier</a>@endcan
        </div>
    </div>
    @if ($enfants->isNotEmpty())
        <ul class="space-y-1">
            @foreach ($enfants as $enfant)
                @include('structures._noeud', ['noeud' => $enfant, 'tous' => $tous, 'niveau' => $niveau + 1])
            @endforeach
        </ul>
    @endif
</li>
