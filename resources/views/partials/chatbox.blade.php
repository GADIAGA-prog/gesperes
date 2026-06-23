{{-- Assistant d'aide (chatbox) — répond à partir du manuel d'usage.
     Styles inline volontaires : visibles sans rebuild Tailwind (npm run build). --}}
@php $manuelData = \App\Support\ManuelUsage::rubriques(); @endphp
<div x-data="chatbox()" style="position:fixed; bottom:20px; right:20px; z-index:9999; display:flex; flex-direction:column; align-items:flex-end;">
    {{-- Panneau --}}
    <div x-show="open" x-transition
         style="margin-bottom:12px; width:360px; max-width:90vw; height:28rem; background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 20px 25px -5px rgba(0,0,0,.2); display:flex; flex-direction:column; overflow:hidden;">
        <div style="background:#1d4ed8; color:#fff; padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
            <span style="font-weight:600; font-size:14px;">Assistant GesPerES</span>
            <button type="button" @click="open = false" style="background:none; border:none; color:#cbd5e1; font-size:20px; cursor:pointer; line-height:1;">&times;</button>
        </div>
        <div x-ref="msgs" style="flex:1; overflow-y:auto; padding:12px; background:#f9fafb;">
            <template x-for="(m, i) in messages" :key="i">
                <div :style="m.from === 'bot' ? 'text-align:left' : 'text-align:right'" style="margin-bottom:10px;">
                    <div :style="m.from === 'bot' ? 'background:#fff; color:#374151' : 'background:#2563eb; color:#fff'"
                         style="display:inline-block; border-radius:8px; padding:6px 10px; font-size:13px; max-width:90%; box-shadow:0 1px 2px rgba(0,0,0,.08);">
                        <span x-text="m.text"></span>
                        <template x-if="m.link">
                            <a :href="m.link" style="display:block; margin-top:4px; font-size:11px; text-decoration:underline; color:#2563eb;">→ Voir dans le manuel</a>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        <form @submit.prevent="ask()" style="border-top:1px solid #e5e7eb; padding:8px; display:flex; gap:8px;">
            <input x-model="q" type="text" placeholder="Votre question…"
                   style="flex:1; border:1px solid #d1d5db; border-radius:8px; padding:6px 10px; font-size:13px;">
            <button type="submit" style="background:#1d4ed8; color:#fff; border:none; border-radius:8px; padding:6px 14px; font-size:13px; cursor:pointer;">OK</button>
        </form>
    </div>

    {{-- Bouton flottant (toujours visible) --}}
    <button type="button" @click="open = !open" title="Assistant GesPerES"
            style="width:56px; height:56px; border-radius:9999px; background:#1d4ed8; color:#fff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 15px -3px rgba(0,0,0,.3);">
        <svg style="height:26px; width:26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 21l1.8-4A7.97 7.97 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </button>
</div>

<script>
    window.chatbox = function () {
        return {
            open: false,
            q: '',
            manuel: @json($manuelData),
            manuelUrl: @json(route('manuel.index')),
            messages: [{ from: 'bot', text: "Bonjour 👋 Je suis l'assistant GesPerES. Posez une question (ex. « comment créer un agent ? », « où voir les indemnités ? », « pointage »)." }],
            norm(s) {
                s = (s || '').toLowerCase().normalize('NFD');
                let out = '';
                for (const ch of s) {
                    const c = ch.charCodeAt(0);
                    if (c >= 0x300 && c <= 0x36f) continue; // diacritiques combinants
                    out += /[a-z0-9]/.test(ch) ? ch : ' ';
                }
                return out.replace(/\s+/g, ' ').trim();
            },
            ask() {
                const question = (this.q || '').trim();
                if (!question) return;
                this.messages.push({ from: 'user', text: question });

                const mots = this.norm(question).split(' ').filter(w => w.length >= 3);
                let best = null, score = 0;
                this.manuel.forEach(r => {
                    const hay = this.norm(r.cles + ' ' + r.titre + ' ' + r.module);
                    let s = 0;
                    mots.forEach(w => { if (hay.indexOf(w) !== -1) s++; });
                    if (s > score) { score = s; best = r; }
                });

                if (best && score > 0) {
                    this.messages.push({ from: 'bot', text: best.titre + ' — ' + best.contenu, link: this.manuelUrl + '#manuel-' + best.id });
                } else {
                    this.messages.push({ from: 'bot', text: "Je n'ai pas trouvé de réponse précise. Consultez le manuel d'usage complet.", link: this.manuelUrl });
                }
                this.q = '';
                this.$nextTick(() => { if (this.$refs.msgs) this.$refs.msgs.scrollTop = this.$refs.msgs.scrollHeight; });
            },
        };
    };
</script>
