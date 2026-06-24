@php
    $avecDrh = $avecDrh ?? false;
    // Version allégée du logo pour limiter le poids des PDF (repli sur l'original).
    $logo = is_file(public_path('images/logo-pdf.png'))
        ? public_path('images/logo-pdf.png')
        : public_path('images/logo.png');

    // Timbre : chaîne hiérarchique descendant jusqu'à la structure de l'utilisateur connecté
    // (le ministère est déjà affiché en titre, on l'exclut de la chaîne).
    $chaineStructure = [];
    $noeud = auth()->user()?->structure;
    $garde = 0;
    while ($noeud && $garde < 12) {
        if ($noeud->type !== \App\Enums\TypeStructure::MINISTERE) {
            array_unshift($chaineStructure, mb_strtoupper($noeud->libelle));
        }
        $noeud = $noeud->parent;
        $garde++;
    }
@endphp
<table style="width:100%; border:none; margin-bottom:6px;">
    <tr>
        <td style="width:42%; text-align:center; vertical-align:top; font-size:9px; border:none;">
            <strong>MINISTÈRE DE L'ENSEIGNEMENT SECONDAIRE<br>
            ET DE LA FORMATION PROFESSIONNELLE ET TECHNIQUE</strong><br>
            =========<br>
            SECRÉTARIAT GÉNÉRAL<br>
            =========
            @if (! empty($chaineStructure))
                @foreach ($chaineStructure as $niveau)
                    <br>{{ $niveau }}<br>=========
                @endforeach
            @elseif ($avecDrh)
                <br>DIRECTION DES RESSOURCES HUMAINES<br>=========
            @endif
        </td>
        <td style="width:16%; text-align:center; vertical-align:top; border:none;">
            @if (is_file($logo))
                <img src="{{ $logo }}" style="height:70px;">
            @endif
        </td>
        <td style="width:42%; text-align:center; vertical-align:top; font-size:9px; border:none;">
            <strong>BURKINA FASO</strong><br>
            =========<br>
            <em>La Patrie ou la Mort, nous Vaincrons</em>
        </td>
    </tr>
</table>
