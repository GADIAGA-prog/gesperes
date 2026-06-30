@php
    $avecDrh = $avecDrh ?? false;
    // Version allégée du logo pour limiter le poids des PDF (repli sur l'original).
    $logo = is_file(public_path('images/logo-pdf.png'))
        ? public_path('images/logo-pdf.png')
        : public_path('images/logo.png');

    // Timbre : chaîne hiérarchique descendant jusqu'à la structure de l'utilisateur
    // connecté (le ministère est déjà en titre, on l'exclut). Le Secrétariat général
    // est la racine de l'organigramme : il apparaît donc naturellement dans la chaîne,
    // sans avoir à le coder en dur (ce qui causait un doublon « SECRÉTARIAT GÉNÉRAL »).
    $segments = [];
    $noeud = auth()->user()?->structure;
    $garde = 0;
    while ($noeud && $garde < 12) {
        if ($noeud->type !== \App\Enums\TypeStructure::MINISTERE) {
            array_unshift($segments, mb_strtoupper($noeud->libelle));
        }
        $noeud = $noeud->parent;
        $garde++;
    }

    // Repli si l'utilisateur n'a pas de structure rattachée.
    if (empty($segments)) {
        $segments = ['SECRÉTARIAT GÉNÉRAL'];
        if ($avecDrh) {
            $segments[] = 'DIRECTION DES RESSOURCES HUMAINES';
        }
    }

    // Supprime les répétitions consécutives identiques (sécurité anti-doublon).
    $segments = array_reduce($segments, function ($acc, $s) {
        if (end($acc) !== $s) {
            $acc[] = $s;
        }
        return $acc;
    }, []);
@endphp
<table style="width:100%; border:none; margin-bottom:6px;">
    <tr>
        <td style="width:42%; text-align:center; vertical-align:top; font-size:9px; border:none;">
            <strong>MINISTÈRE DE L'ENSEIGNEMENT SECONDAIRE<br>
            ET DE LA FORMATION PROFESSIONNELLE ET TECHNIQUE</strong><br>
            =========
            @foreach ($segments as $niveau)
                <br>{{ $niveau }}<br>=========
            @endforeach
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
