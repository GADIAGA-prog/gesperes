<h2>Alertes RH du {{ now()->translatedFormat('d F Y') }}</h2>
<p>Synthèse des alertes non lues :</p>
<ul>
    <li><strong>{{ $retraites }}</strong> départ(s) à la retraite proche(s)</li>
    <li><strong>{{ $docsExpires }}</strong> document(s) expiré(s)</li>
    <li><strong>{{ $docsBientot }}</strong> document(s) bientôt expiré(s)</li>
</ul>

@if ($recents->isNotEmpty())
    <h3>Détail (50 plus récentes)</h3>
    <ul>
        @foreach ($recents as $n)
            <li>[{{ strtoupper($n->niveau) }}] {{ $n->titre }} — {{ $n->message }}</li>
        @endforeach
    </ul>
@endif

<p style="color:#777;font-size:12px;">Message automatique — GesPerES / MESFPTT.</p>
