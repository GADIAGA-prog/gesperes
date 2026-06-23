@php
    $fmt = fn ($v) => $v ? number_format((float) $v, 0, ',', ' ') : '—';
    $sal = ['solde', 'ir', 'resp', 'astr', 'log', 'tech', 'autres', 'allo', 'carfo'];
    $totMap = ['solde' => 'si', 'ir' => 'ir', 'resp' => 'resp', 'astr' => 'astr', 'log' => 'log', 'tech' => 'tech', 'autres' => 'autres', 'allo' => 'af', 'carfo' => 'carfo'];
    $p = $provisions;
    $an = $annees ?? [date('Y'), date('Y') + 1, date('Y') + 2];
@endphp
<p class="annexe-entete">Programme {{ $pcode }} — {{ $plib }}<br>Structure : {{ $slib }}</p>
<table class="annexe">
    <thead>
        <tr>
            <th rowspan="2">Cat.</th>
            <th rowspan="2">Matricule</th>
            <th rowspan="2">Nom et prénom(s)</th>
            <th rowspan="2">Sexe</th>
            <th rowspan="2" class="r">Indice</th>
            <th rowspan="2" class="r">Solde indiciaire</th>
            <th rowspan="2" class="r">IR</th>
            <th colspan="5">Indemnités</th>
            <th rowspan="2" class="r">Allo. familiale</th>
            <th rowspan="2" class="r">CARFO 13,5%</th>
            <th rowspan="2" class="r">Incidence {{ $an[0] }}</th>
            <th rowspan="2" class="r">Incidence {{ $an[1] }}</th>
            <th rowspan="2" class="r">Incidence {{ $an[2] }}</th>
        </tr>
        <tr>
            <th class="r">Resp.</th><th class="r">Astr.</th><th class="r">Log.</th><th class="r">Tech.</th><th class="r">Autres</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($lignes as $l)
            <tr>
                <td>{{ $l['ref'] }}</td>
                <td>{{ $l['matricule'] }}</td>
                <td>{{ $l['nom'] }}</td>
                <td class="r">{{ $l['sexe'] }}</td>
                <td class="r">{{ $l['indice'] ?: '—' }}</td>
                @foreach ($sal as $k)
                    <td class="r">{{ $fmt($l[$k]) }}</td>
                @endforeach
                <td class="r">{{ $fmt($l['incidence']) }}</td>
                <td class="r">{{ $fmt($l['incidence']) }}</td>
                <td class="r">{{ $fmt($l['incidence']) }}</td>
            </tr>
        @endforeach

        <tr class="tot">
            <td colspan="5">TOTAL (annuel)</td>
            @foreach ($sal as $k)
                <td class="r">{{ $fmt($totaux[$totMap[$k]] ?? 0) }}</td>
            @endforeach
            <td class="r">{{ $fmt($p['t1']) }}</td><td class="r">{{ $fmt($p['t2']) }}</td><td class="r">{{ $fmt($p['t3']) }}</td>
        </tr>
        <tr>
            <td colspan="5">Provisions suppléments salariaux (3 %)</td>
            <td class="r">{{ $fmt($p['a']) }}</td>
            <td class="r">{{ $fmt($p['b']) }}</td>
            <td colspan="3" class="r">—</td>
            <td class="r">{{ $fmt($p['d']) }}</td>
            <td class="r">—</td>
            <td class="r">—</td>
            <td class="r">{{ $fmt($p['e']) }}</td>
            <td class="r">{{ $fmt($p['f']) }}</td><td class="r">{{ $fmt($p['g']) }}</td><td class="r">{{ $fmt($p['h']) }}</td>
        </tr>
        <tr>
            <td colspan="5">Provisions nouvelles naissances (5 %)</td>
            <td colspan="7" class="r">—</td>
            <td class="r">{{ $fmt($p['i1']) }}</td>
            <td class="r">—</td>
            <td class="r">{{ $fmt($p['i1']) }}</td><td class="r">{{ $fmt($p['i2']) }}</td><td class="r">{{ $fmt($p['i3']) }}</td>
        </tr>
        <tr class="tot">
            <td colspan="14">TOTAL GÉNÉRAL</td>
            <td class="r">{{ $fmt($p['tg1']) }}</td><td class="r">{{ $fmt($p['tg2']) }}</td><td class="r">{{ $fmt($p['tg3']) }}</td>
        </tr>
    </tbody>
</table>
