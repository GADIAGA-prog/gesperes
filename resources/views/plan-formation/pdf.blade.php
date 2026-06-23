<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 10px; color: #1f2937; }
    h1 { font-size: 16px; margin: 0 0 2px; color: #1e3a8a; }
    h2 { font-size: 12px; margin: 14px 0 4px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px; }
    .muted { color: #6b7280; }
    .meta { margin: 2px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    th, td { border: 1px solid #d1d5db; padding: 4px 5px; text-align: left; vertical-align: top; }
    th { background: #eff6ff; font-size: 9px; text-transform: uppercase; color: #374151; }
    td.num, th.num { text-align: right; }
    tfoot td { font-weight: bold; background: #f3f4f6; }
    .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 6px; margin-bottom: 8px; }
</style>
</head>
<body>
    <div class="header">
        <h1>{{ $plan->intitule }}</h1>
        <div class="meta muted">Période {{ $plan->periode }} — Statut : {{ $plan->statut?->label() }}</div>
    </div>

    @if($plan->vision)<div class="meta"><strong>Vision :</strong> {{ $plan->vision }}</div>@endif
    @if($plan->finalite)<div class="meta"><strong>Finalité :</strong> {{ $plan->finalite }}</div>@endif
    @if($plan->objectifs)<div class="meta"><strong>Objectifs :</strong> {{ $plan->objectifs }}</div>@endif

    @foreach($plan->programmes as $programme)
        @php $t = $synthese[$programme->id]; @endphp
        <h2>Programme {{ $programme->annee }}
            @if($programme->objectif_strategique) — {{ $programme->objectif_strategique }}@endif
        </h2>
        <div class="meta muted">
            Budget prévisionnel : {{ number_format($programme->budget_previsionnel, 0, ',', ' ') }} FCFA
            · Coût des actions : {{ number_format($t['cout'], 0, ',', ' ') }} FCFA
            · {{ number_format($t['agents'], 0, ',', ' ') }} agents · {{ $t['jours'] }} jours
        </div>

        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Action / Thème (1)</th>
                    <th>Modalité (2)</th>
                    <th>Public cible (3)</th>
                    <th class="num">Jours (4)</th>
                    <th class="num">Agents (5)</th>
                    <th class="num">Coût (6)</th>
                    <th>Financement (7)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programme->actions as $action)
                    <tr>
                        <td>{{ $action->numero_ordre }}</td>
                        <td>
                            <strong>{{ $action->action }}</strong>
                            @if($action->theme_module)<br><span class="muted">{{ $action->theme_module }}</span>@endif
                        </td>
                        <td>{{ $action->type_modalite?->label() }}</td>
                        <td>{{ $action->public_cible_label }}</td>
                        <td class="num">{{ $action->nombre_jours }}</td>
                        <td class="num">{{ number_format($action->nombre_agents, 0, ',', ' ') }}</td>
                        <td class="num">{{ number_format($action->cout, 0, ',', ' ') }}</td>
                        <td>{{ $action->source_financement }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Aucune action planifiée.</td></tr>
                @endforelse
            </tbody>
            @if($programme->actions->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4">Total programme {{ $programme->annee }}</td>
                    <td class="num">{{ $t['jours'] }}</td>
                    <td class="num">{{ number_format($t['agents'], 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($t['cout'], 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    @endforeach

    <p class="muted" style="margin-top:14px;">GesPerES — Plan de formation édité le {{ now()->format('d/m/Y') }}.</p>
</body>
</html>
