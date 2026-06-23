<?php

namespace App\Imports;

use App\Enums\TypeStructure;
use App\Models\Action;
use App\Models\Localite;
use App\Models\Structure;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Charge la hiérarchie organisationnelle GESPER dans la table `structures` :
 *   Niveau 1 (Direction) → Niveau 2 (Direction) → Niveau 3 (Service) → Niveau 4 (Établissement).
 * La profondeur réelle est portée par parent_id ; le type décrit la nature de la structure.
 * Le niveau 4 est rattaché à une ACTION (budget) et à une LOCALITÉ.
 *
 * Les fichiers attendus dans $dir : niveau1.xlsx, niveau2.xlsx,
 * Niveau3_provinces.xlsx, Niveau4.xlsx.
 */
class StructureGesperImport
{
    public int $n1 = 0;
    public int $n2 = 0;
    public int $n3 = 0;
    public int $n4 = 0;
    public int $n4SansAction = 0;
    public int $n4SansLocalite = 0;
    public array $orphelins = [];

    /** Codes déjà attribués, pour garantir l'unicité de structures.code. */
    private array $codesUtilises = [];

    public function run(string $dir): void
    {
        Structure::query()->forceDelete();

        // ── Niveau 1 ────────────────────────────────────────────────
        $map1 = [];
        foreach ($this->lire($dir . 'niveau1.xlsx') as $r) {
            $code = trim((string) ($r['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $structure = Structure::create([
                'code'    => $this->codeUnique('N1-' . $code),
                'libelle' => trim((string) ($r['libelle'] ?? $r['libelle_'] ?? $code)),
                'type'    => TypeStructure::DIRECTION->value,
                'actif'   => $this->bool($r['actif'] ?? true),
            ]);
            $map1[$code] ??= $structure;
            $this->n1++;
        }

        // ── Niveau 2 (rattaché au niveau 1 via abréviation/initiales) ──
        $map2 = [];
        foreach ($this->lire($dir . 'niveau2.xlsx') as $r) {
            $code = trim((string) ($r['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $parent = $this->resoudreNiveau1(trim((string) ($r['niveau1_code'] ?? '')), $map1);
            if (! $parent) {
                $this->orphelins[] = "Niveau2 {$code} : parent « {$r['niveau1_code']} » introuvable.";
            }
            $structure = Structure::create([
                'code'      => $this->codeUnique('N2-' . $code),
                'libelle'   => trim((string) ($r['libelle'] ?? '')),
                'type'      => TypeStructure::DIRECTION->value,
                'parent_id' => $parent?->id,
                'actif'     => $this->bool($r['actif'] ?? true),
            ]);
            $map2[$code] ??= $structure;
            $this->n2++;
        }

        // ── Niveau 3 (provinces éducatives, rattaché au niveau 2 par code) ──
        $map3 = [];
        foreach ($this->lire($dir . 'Niveau3_provinces.xlsx') as $r) {
            $code = trim((string) ($r['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $parent = $map2[trim((string) ($r['niveau2_code'] ?? ''))] ?? null;
            if (! $parent) {
                $this->orphelins[] = "Niveau3 {$code} : parent « {$r['niveau2_code']} » introuvable.";
            }
            $structure = Structure::create([
                'code'      => $this->codeUnique('N3-' . $code),
                'libelle'   => trim((string) ($r['libelle'] ?? '')),
                'type'      => TypeStructure::SERVICE->value,
                'parent_id' => $parent?->id,
                'actif'     => $this->bool($r['actif'] ?? true),
            ]);
            $map3[$code] ??= $structure;
            $this->n3++;
        }

        // ── Niveau 4 (structures opérationnelles : action + localité) ──
        $actions = Action::pluck('id', 'code');
        $localites = Localite::whereNotNull('code')->get(['id', 'code', 'region', 'province'])->keyBy('code');

        foreach ($this->lire($dir . 'Niveau4.xlsx') as $r) {
            $n3code = trim((string) ($r['niveau3_code'] ?? ''));
            $parent = $map3[$n3code] ?? null;
            $actionId = $actions[trim((string) ($r['action_code'] ?? ''))] ?? null;
            $loc = $localites[trim((string) ($r['localite_code'] ?? ''))] ?? null;

            if (! $actionId) {
                $this->n4SansAction++;
            }
            if (! $loc) {
                $this->n4SansLocalite++;
            }

            Structure::create([
                'code'        => $this->codeUnique('N4-' . $n3code . '-' . trim((string) ($r['code'] ?? ''))),
                'libelle'     => trim((string) ($r['libelle'] ?? '')),
                'type'        => TypeStructure::ETABLISSEMENT->value,
                'parent_id'   => $parent?->id,
                'action_id'   => $actionId,
                'localite_id' => $loc?->id,
                'region'      => $loc?->region,
                'province'    => $loc?->province,
                'actif'       => $this->bool($r['actif'] ?? true),
            ]);
            $this->n4++;
        }
    }

    /** Garantit un code unique en suffixant si nécessaire. */
    private function codeUnique(string $base): string
    {
        $code = $base;
        $i = 1;
        while (isset($this->codesUtilises[$code])) {
            $code = $base . '-' . (++$i);
        }
        $this->codesUtilises[$code] = true;
        return $code;
    }

    private function lire(string $path)
    {
        return Excel::toCollection(new class implements WithHeadingRow {}, $path)->first() ?? collect();
    }

    /** Résout un niveau 1 par code, initiales, ou préfixe de libellé (SG, Cab…). */
    private function resoudreNiveau1(string $ref, array $map1): ?Structure
    {
        if ($ref === '') {
            return null;
        }
        $ref = Str::of($ref)->ascii()->upper()->trim()->value();

        foreach ($map1 as $gcode => $structure) {
            $initiales = collect(preg_split('/\s+/', $structure->libelle))
                ->map(fn ($m) => Str::of($m)->ascii()->upper()->substr(0, 1)->value())
                ->implode('');
            $libelle = Str::of($structure->libelle)->ascii()->upper()->value();

            if (Str::upper($gcode) === $ref || $initiales === $ref || str_starts_with($libelle, $ref)) {
                return $structure;
            }
        }
        return null;
    }

    private function bool($valeur): bool
    {
        $v = mb_strtolower(trim((string) $valeur));
        return in_array($v, ['1', 'oui', 'true', 'vrai', 'yes', 'x'], true);
    }
}
