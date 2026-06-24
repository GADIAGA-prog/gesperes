{{-- Cascade hiérarchique de structures (réutilisable).
     Variables attendues : $nom (name du champ), $config (Structure::cascadeConfig()),
     $selected (valeur courante), $label (optionnel). --}}
<div class="sm:col-span-2 lg:col-span-3" data-cascade-structure
     x-data="cascadeStructure(@js($config), @js((string) ($selected ?? '')))">
    <label class="label">{{ $label ?? "Structure d'affectation" }}
        <span class="font-normal text-xs text-gray-400">— descendez jusqu'au service / poste</span>
    </label>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-1">
        <template x-for="niv in niveaux" :key="niv.index">
            <select class="input" @change="choisir(niv.index, $event.target.value)">
                <option value="">— Choisir —</option>
                <template x-for="opt in niv.options" :key="opt.id">
                    <option :value="opt.id" :selected="String(opt.id) === String(niv.valeur)"
                            x-text="opt.feuille ? opt.libelle : (opt.libelle + ' ›')"></option>
                </template>
            </select>
        </template>
    </div>
    <input type="hidden" name="{{ $nom }}" :value="structureId">
    @error($nom)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

@once
@push('scripts')
<script>
// Cascade hiérarchique des structures : descendre parent → … → service/poste.
function cascadeStructure(config, valeurActuelle) {
    return {
        enfants: config.enfants || {},
        parents: config.parents || {},
        selection: [],

        init() {
            this.selection = this.cheminVers(valeurActuelle);
        },

        cheminVers(id) {
            const chemin = [];
            let courant = id ? String(id) : '';
            let garde = 0;
            while (courant && this.parents[courant] !== undefined && garde < 12) {
                chemin.unshift(courant);
                const parent = this.parents[courant];
                courant = parent ? String(parent) : '';
                garde++;
            }
            return chemin;
        },

        get niveaux() {
            const niv = [];
            let parent = 'racine';
            for (let i = 0; ; i++) {
                const options = this.enfants[parent] || [];
                if (! options.length) break;
                const valeur = this.selection[i] || '';
                niv.push({ index: i, options, valeur });
                if (! valeur) break;
                parent = String(valeur);
            }
            return niv;
        },

        choisir(index, valeur) {
            this.selection = this.selection.slice(0, index);
            if (valeur) this.selection[index] = String(valeur);
        },

        get structureId() {
            const choisis = this.selection.filter(Boolean);
            return choisis.length ? choisis[choisis.length - 1] : '';
        },
    };
}
</script>
@endpush
@endonce
