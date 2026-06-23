<?php

namespace App\Imports;

use App\Models\Localite;
use App\Models\Province;
use App\Models\Zone;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import des localités au format GESPER : Region | Province | Localité | Zone | code.
 *  - libelle / commune = « Localité »
 *  - province_id        = résolu via le libellé de la province
 *  - zone_id            = résolu via le libellé de la zone (urbaine / semi-urbaine / rurale)
 *  - region (texte)     = « Region »
 * Upsert par code.
 */
class LocaliteGesperImport implements ToCollection, WithHeadingRow
{
    public int $importes = 0;
    public int $maj = 0;
    public array $erreurs = [];

    private Collection $provinces;
    private Collection $zones;

    public function collection(Collection $rows): void
    {
        $this->provinces = Province::pluck('id', 'libelle')
            ->mapWithKeys(fn ($id, $lib) => [$this->cle($lib) => $id]);

        // Mappage des libellés de zone GESPER vers les codes de zone internes.
        $this->zones = Zone::pluck('id', 'code')->mapWithKeys(fn ($id, $code) => [$code => $id]);

        foreach ($rows as $i => $row) {
            $ligne = $i + 2;
            $code = trim((string) ($row['code'] ?? ''));
            $nom = trim((string) ($row['localite'] ?? $row['localité'] ?? ''));

            if ($code === '' && $nom === '') {
                continue;
            }

            $provinceId = $this->provinces[$this->cle($row['province'] ?? '')] ?? null;
            if (! $provinceId && ! empty($row['province'])) {
                $this->erreurs[] = "Ligne {$ligne} : province « {$row['province']} » introuvable.";
            }

            $attrs = [
                'libelle'     => $nom ?: $code,
                'province_id' => $provinceId,
                'zone_id'     => $this->zones[$this->codeZone($row['zone'] ?? '')] ?? null,
                'region'      => trim((string) ($row['region'] ?? '')) ?: null,
                'province'    => trim((string) ($row['province'] ?? '')) ?: null,
                'commune'     => $nom ?: null,
                'actif'       => true,
            ];

            $existant = $code !== '' ? Localite::where('code', $code)->first() : null;
            if ($existant) {
                $existant->update($attrs);
                $this->maj++;
            } else {
                Localite::create(array_merge(['code' => $code ?: null], $attrs));
                $this->importes++;
            }
        }
    }

    /** Normalise un libellé pour comparaison (minuscule, sans accents). */
    private function cle($valeur): string
    {
        return Str::of((string) $valeur)->ascii()->lower()->trim()->value();
    }

    /** Déduit le code de zone interne (urbaine / semi_urbaine / rurale). */
    private function codeZone($valeur): ?string
    {
        $v = $this->cle($valeur);
        if ($v === '') {
            return null;
        }
        return match (true) {
            str_contains($v, 'semi')  => 'semi_urbaine',
            str_contains($v, 'urba')  => 'urbaine',
            str_contains($v, 'rural') => 'rurale',
            default => null,
        };
    }
}
