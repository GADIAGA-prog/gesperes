<?php

namespace Database\Seeders;

use App\Models\Localite;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Découpage administratif du Burkina Faso : 13 régions, 45 provinces.
 * Chaque province est rattachée à sa région ; son chef-lieu est créé comme localité liée.
 *
 * Structure de référence (13 régions / 45 provinces). Si votre administration
 * applique le nouveau découpage, ajustez/complétez depuis l'interface Référentiels.
 */
class GeographieSeeder extends Seeder
{
    /**
     * [ code région => [ libellé région, [ [code province, libellé province, chef-lieu], ... ] ] ]
     */
    private array $decoupage = [
        'BMH' => ['Boucle du Mouhoun', [
            ['BALE', 'Balé', 'Boromo'],
            ['BANW', 'Banwa', 'Solenzo'],
            ['KOSS', 'Kossi', 'Nouna'],
            ['MOUH', 'Mouhoun', 'Dédougou'],
            ['NAYA', 'Nayala', 'Toma'],
            ['SOUR', 'Sourou', 'Tougan'],
        ]],
        'CAS' => ['Cascades', [
            ['COMO', 'Comoé', 'Banfora'],
            ['LERA', 'Léraba', 'Sindou'],
        ]],
        // Kadiogo (ex-Centre) : pas de provinces mais des circonscriptions d'éducation (MESFPTT).
        'KADIOGO' => ['Kadiogo', [
            ['CESFPTOUAGA1', 'CESFPT OUAGA 1', null],
            ['CESFPTOUAGA2', 'CESFPT OUAGA 2', null],
            ['CESFPTOUAGA3', 'CESFPT OUAGA 3', null],
            ['CESFPTOUAGA4', 'CESFPT OUAGA 4', null],
            ['CESFPTOUAGA5', 'CESFPT OUAGA 5', null],
        ]],
        'CES' => ['Centre-Est', [
            ['BOLG', 'Boulgou', 'Tenkodogo'],
            ['KOUL', 'Koulpélogo', 'Ouargaye'],
            ['KOUR', 'Kouritenga', 'Koupéla'],
        ]],
        'CNR' => ['Centre-Nord', [
            ['BAM',  'Bam', 'Kongoussi'],
            ['NAME', 'Namentenga', 'Boulsa'],
            ['SANM', 'Sanmatenga', 'Kaya'],
        ]],
        'COU' => ['Centre-Ouest', [
            ['BOUK', 'Boulkiemdé', 'Koudougou'],
            ['SANG', 'Sanguié', 'Réo'],
            ['SISS', 'Sissili', 'Léo'],
            ['ZIRO', 'Ziro', 'Sapouy'],
        ]],
        'CSD' => ['Centre-Sud', [
            ['BAZE', 'Bazèga', 'Kombissiri'],
            ['NAHO', 'Nahouri', 'Pô'],
            ['ZOUN', 'Zoundwéogo', 'Manga'],
        ]],
        'EST' => ['Est', [
            ['GNAG', 'Gnagna', 'Bogandé'],
            ['GOUR', 'Gourma', 'Fada N\'Gourma'],
            ['KOMA', 'Komandjoari', 'Gayéri'],
            ['KOMP', 'Kompienga', 'Pama'],
            ['TAPO', 'Tapoa', 'Diapaga'],
        ]],
        // Guiriko (ex-Hauts-Bassins) : circonscriptions d'éducation (MESFPTT).
        'GUIRIKO' => ['Guiriko', [
            ['CESFPTBOBO1', 'CESFPT BOBO 1', null],
            ['CESFPTBOBO2', 'CESFPT BOBO 2', null],
            ['CESFPTBOBO3', 'CESFPT BOBO 3', null],
            ['CESFPTBOBO4', 'CESFPT BOBO 4', null],
        ]],
        'NRD' => ['Nord', [
            ['LORO', 'Loroum', 'Titao'],
            ['PASS', 'Passoré', 'Yako'],
            ['YATE', 'Yatenga', 'Ouahigouya'],
            ['ZOND', 'Zondoma', 'Gourcy'],
        ]],
        'PCL' => ['Plateau-Central', [
            ['GANZ', 'Ganzourgou', 'Zorgho'],
            ['KORW', 'Kourwéogo', 'Boussé'],
            ['OUBR', 'Oubritenga', 'Ziniaré'],
        ]],
        'SHL' => ['Sahel', [
            ['OUDA', 'Oudalan', 'Gorom-Gorom'],
            ['SENO', 'Séno', 'Dori'],
            ['SOUM', 'Soum', 'Djibo'],
            ['YAGH', 'Yagha', 'Sebba'],
        ]],
        'SOU' => ['Sud-Ouest', [
            ['BOUG', 'Bougouriba', 'Diébougou'],
            ['IOBA', 'Ioba', 'Dano'],
            ['NOUM', 'Noumbiel', 'Batié'],
            ['PONI', 'Poni', 'Gaoua'],
        ]],
    ];

    public function run(): void
    {
        // Nettoyage des anciennes régions renommées par la réforme (DB déjà seedée).
        foreach (Region::whereIn('code', ['CEN', 'HBS'])->get() as $ancienne) {
            Province::where('region_id', $ancienne->id)->delete();
            $ancienne->delete();
        }

        $nbRegions = 0;
        $nbProvinces = 0;
        $nbLocalites = 0;

        foreach ($this->decoupage as $codeRegion => [$libelleRegion, $provinces]) {
            $region = Region::firstOrCreate(
                ['code' => $codeRegion],
                ['libelle' => $libelleRegion, 'actif' => true]
            );
            $nbRegions++;

            foreach ($provinces as [$codeProvince, $libelleProvince, $chefLieu]) {
                $province = Province::firstOrCreate(
                    ['code' => $codeProvince],
                    [
                        'libelle'   => $libelleProvince,
                        'region_id' => $region->id,
                        'chef_lieu' => $chefLieu,
                        'actif'     => true,
                    ]
                );
                $nbProvinces++;

                // Chef-lieu créé comme localité liée à la province (les circonscriptions n'en ont pas).
                if ($chefLieu) {
                    $codeLocalite = Str::of($chefLieu)->ascii()->upper()->replaceMatches('/[^A-Z0-9]/', '')->value();
                    Localite::firstOrCreate(
                        ['province_id' => $province->id, 'libelle' => $chefLieu],
                        [
                            'code'     => $codeLocalite,
                            'region'   => $libelleRegion,
                            'province' => $libelleProvince,
                            'commune'  => $chefLieu,
                            'actif'    => true,
                        ]
                    );
                    $nbLocalites++;
                }
            }
        }

        $this->command->info("✓ Géographie : {$nbRegions} régions, {$nbProvinces} provinces/circonscriptions, {$nbLocalites} chefs-lieux liés.");
    }
}
